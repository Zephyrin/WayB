<?php

namespace App\Controller;

use App\Entity\Brand;
use App\Entity\Equipment;
use App\Entity\SubCategory;
use App\Entity\User;
use App\Form\EquipmentType;
use App\Repository\EquipmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Serializer\FormErrorSerializer;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Security\Core\Security;

/**
 * Class EquipmentController
 * @package App\Controller
 *
 * @Rest\RouteResource(
 *     "api/User/{userId}/Equipment",
 *     pluralize=false
 * )
 * @SWG\Tag(
 *     name="Equipment"
 * )
 */
class EquipmentController extends AbstractFOSRestController implements ClassResourceInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var EquipmentRepository
     */
    private $equipmentRepository;

    /**
     * @var FormErrorSerializer
     */
    private $formErrorSerializer;

    public function __construct(
        EntityManagerInterface $entityManager,
        EquipmentRepository $equipmentRepository,
        FormErrorSerializer $formErrorSerializer,
        Security $security
    )
    {
        $this->entityManager = $entityManager;
        $this->equipmentRepository = $equipmentRepository;
        $this->formErrorSerializer = $formErrorSerializer;
        $this->security = $security;
    }

    /**
     * Create a new Equipment
     *
     * @SWG\Post(
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *      response=200,
     *      description="Successful operation with the new value insert",
     *      @SWG\Schema(
     *       ref=@Model(type=Equipment::class)
     *      )
     *    ),
     *    @SWG\Response(
     *     response=412,
     *     description="You cannot add an equipment to other user"
     *    ),
     *    @SWG\Response(
     *     response=422,
     *     description="The form is not correct<BR/>
     * See the corresponding JSON error to see which field is not correct"
     *    ),
     *    @SWG\Parameter(
     *     name="The JSON Equipment",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *       ref=@Model(type=Equipment::class)
     *     ),
     *     description="The JSon Equipment"
     *    )
     *
     * )
     *
     * @param Request $request
     * @return \FOS\RestBundle\View\View|JsonResponse
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     * @TODO Add user check and user link.
     */
    public function postAction(Request $request)
    {
        $user = $this->findUserByRequest($request);
        $connectUser = $this->getUser();
        if($user !== $connectUser)
            return new JsonResponse(
                [ 
                    'status' => 'error',
                    'Message' => 'You cannot add an equipment to other user'
                ],
                JsonResponse::HTTP_PRECONDITION_FAILED
            );
        $data = json_decode(
            $request->getContent(),
            true
        );
        $form = $this->createForm(
            EquipmentType::class,
            new Equipment());
        $form->submit(
            $data
        );
        if (false === $form->isValid()) {
            return new JsonResponse(
                [
                    'status' => 'error',
                    'Message' => 'Validation error',
                    'errors' => $this->formErrorSerializer->normalize($form),
                ],
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $equipment = $form->getData();
        $equipment->setCreatedBy($user);
        $equipment->setValidate(false);
        if($equipment->getSubCategory() == null
            && is_int(intval($data['subCategory']))
        ) {
            $subCat = $this->entityManager
                ->getRepository(SubCategory::class)
                ->find($data['subCategory']);
            $equipment->setSubCategory($subCat);
        }
        if($equipment->getBrand() == null
            && is_int(intval($data['brand']))
        ) {
            $brand = $this->entityManager
                ->getRepository(Brand::class)
                ->find($data['brand']);
            $equipment->setBrand($brand);
        }
        $this->entityManager->persist($equipment);
        $this->entityManager->flush();

        return  $this->view(
            $equipment,
            Response::HTTP_CREATED);
    }

    /**
     * Expose the Equipment with the id.
     *
     * @SWG\Get(
     *     summary="Get the Equipment based on its ID",
     *     produces={"application/json"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return the Equipment based on ID",
     *     @SWG\Schema(ref=@Model(type=Equipment::class))
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="The Equipment based on ID is not found"
     * )
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the Equipment"
     * )
     *
     * @var $id
     * @return \FOS\RestBundle\View\View
     */
    public function getAction(string $id)
    {
        return $this->view(
            $this->findEquipmentById($id)
        );
    }

    /**
     * Expose all Equipment belong to the user or has been validate.
     * If you are an ambassador, return all equipments.
     *
     * @SWG\Get(
     *     summary="Get all Equipment",
     *     produces={"application/json"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return all the Equipments",
     *     @SWG\Schema(
     *      type="array",
     *      @SWG\Items(ref=@Model(type=Equipment::class))
     *     )
     * )
     *
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function cgetAction(Request $request)
    {
        if($this->isGranted("ROLE_AMBASSADOR"))
            return $this->view(
                $this->equipmentRepository->findAll()
            );
        $user = $this->findUserByRequest($request);
        $equipments = $this->equipmentRepository->findByUserOrValidate($user);
        return $this->view($equipments);
    }

    /**
     * Update an Equipment
     *
     * @SWG\Put(
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *      response=204,
     *      description="Successful operation"
     *    ),
     *    @SWG\Response(
     *     response=422,
     *     description="The form is not correct<BR/>
     * See the corresponding JSON error to see which field is not correct"
     *    ),
     *    @SWG\Response(
     *     response=404,
     *     description="The Equipment based on ID is not found"
     *    ),
     *    @SWG\Response(
     *     response=403,
     *     description="The Equipment based on ID don't belong to you or you are not an ambassador"
     *    ),
     *    @SWG\Parameter(
     *     name="The full JSON Equipment, the field validate cannot be update.",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *       ref=@Model(type=Equipment::class)
     *     ),
     *     description="The JSon Equipment"
     *    ),
     *    @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the Equipment"
     *    )
     * )
     *
     * @param Request $request
     * @param string $id of the Equipment to update
     * @return \FOS\RestBundle\View\View|JsonResponse
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function putAction(Request $request, string $id)
    {
        $user = $this->findUserByRequest($request);
        $connectUser = $this->getUser();
        if($user !== $connectUser)
             $this->denyAccessUnlessGranted("ROLE_AMBASSADOR");
        $existingEquipment = $this->findEquipmentById($id);
        $form = $this->createForm(EquipmentType::class, $existingEquipment);
        $data = json_decode($request->getContent(), true);
        $validate = $existingEquipment->getValidate();
        $form->submit($data);
        if (false === $form->isValid()) {
            return new JsonResponse(
                [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'errors' => $this->formErrorSerializer->normalize($form),
                ],
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        if($existingEquipment->getValidate() !== $validate) {
            $this->denyAccessUnlessGranted("ROLE_AMBASSADOR");
        }
        $this->entityManager->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Update a part of an Equipment
     *
     * @SWG\Patch(
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *      response=204,
     *      description="Successful operation"
     *    ),
     *    @SWG\Response(
     *     response=422,
     *     description="The form is not correct<BR/>
     * See the corresponding JSON error to see which field is not correct"
     *    ),
     *    @SWG\Response(
     *     response=404,
     *     description="The Equipment based on ID is not found"
     *    ),
     *    @SWG\Response(
     *     response=403,
     *     description="The Equipment based on ID don't belong to you or you are not an ambassador"
     *    ),
     *    @SWG\Parameter(
     *     name="The full JSON Equipment. The field validate cannot be update by normal user. Should be update by AMBASSADOR users.",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *       ref=@Model(type=Equipment::class)
     *     ),
     *     description="The JSon Equipment"
     *    ),
     *    @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the Equipment"
     *    )
     * )
     *
     * @param Request $request
     * @param string $id of the Equipment to update
     * @return \FOS\RestBundle\View\View|JsonResponse
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function patchAction(Request $request, string $id)
    {
        $user = $this->findUserByRequest($request);
        $connectUser = $this->getUser();
        if($user !== $connectUser)
             $this->denyAccessUnlessGranted("ROLE_AMBASSADOR");
        $existingEquipment = $this->findEquipmentById($id);
        $form = $this->createForm(EquipmentType::class
            , $existingEquipment);
        $validate = $existingEquipment->getValidate();
        $form->submit($request->request->all()
            , false);
        if (false === $form->isValid()) {
            return new JsonResponse(
                [
                    'status' => 'error',
                    'errors' => $this->formErrorSerializer->normalize($form),
                ],
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        if($existingEquipment->getValidate() !== $validate) {
            $this->denyAccessUnlessGranted("ROLE_AMBASSADOR");
        }
        $existingEquipment->setValidate($validate);
        $this->entityManager->flush();

        return $this->view(null
            , Response::HTTP_NO_CONTENT);
    }

    /**
     * Delete an Equipment with the id. Must belong to
     *  the user and should not be linked with other user
     *  if the equipment is validate.
     *
     * @SWG\Delete(
     *     summary="Delete an Equipment based on ID Must belong to
     *      the user and should not be linked with other user
     *      if the equipment is validate."
     * )
     * @SWG\Response(
     *     response=204,
     *     description="The Equipment is correctly delete",
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="The Equipment based on ID is not found"
     * )
     * 
     *  @SWG\Response(
     *     response=403,
     *     description="The Equipment based on ID don't belong to you."
     * )
     * 
     *  @SWG\Response(
     *     response=412,
     *     description="The Equipment based on ID is linked to too many user.
     * It cannot be delete yet."
     * )
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the Equipment"
     * )
     * 
     * @param Request $request
     * @param string $id
     * @return \FOS\RestBundle\View\View
     */
    public function deleteAction(Request $request, string $id)
    {
        $equipment = $this->findEquipmentById($id);
        if($equipment->getCreatedBy() !== $this->getUser()) 
            $this->denyAccessUnlessGranted("ROLE_ADMIN"
                , null
                , "This equipment don't belong to you and your are not an admin.");
        if(!$equipment->getValidate() 
            || count($equipment->getHaves()) < 1) {
            $this->entityManager->remove($equipment);
            $this->entityManager->flush();

            return $this->view(null
                , Response::HTTP_NO_CONTENT);
        }
        if (count($equipment->getHaves()) > 1) {
            return new JsonResponse(
                [ 
                    'status' => 'error',
                    'errors' => 'Too many other users use this equipment.'
                ],
                JsonResponse::HTTP_PRECONDITION_FAILED
            );            
        }
        return new JsonResponse(
            [ 
                'status' => 'error',
                'errors' => 'This equipment don\'t belong to you'
            ],
            JsonResponse::HTTP_METHOD_NOT_ALLOWED
        );
    }

    /**
     * @param string $id
     *
     * @return Equipment
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    private function findEquipmentById(string $id)
    {
        $existingEquipment = $this->equipmentRepository->find($id);
        if (null === $existingEquipment) {
            throw new NotFoundHttpException();
        }
        return $existingEquipment;
    }

    /**
     * @param Request $request
     *
     * @return User
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    private function findUserByRequest(Request $request)
    {
        $user = $this->entityManager->find(
            User::class,
            $request->attributes->get('userid'));
        if($user == null)
            throw new NotFoundHttpException();
        return $user;
    }
}
