<?php

namespace App\Controller;

use App\Entity\Brand;
use App\Entity\Equipment;
use App\Entity\SubCategory;
use App\Form\EquipmentType;
use App\Repository\EquipmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Serializer\FormErrorSerializer;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;


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
class EquipmentController extends FOSRestController implements ClassResourceInterface
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
        FormErrorSerializer $formErrorSerializer
    )
    {
        $this->entityManager = $entityManager;
        $this->equipmentRepository = $equipmentRepository;
        $this->formErrorSerializer = $formErrorSerializer;
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
     *     response=422,
     *     description="The form is not correct<BR/>
     * See the corresponding JSON error to see which field is not correct"
     *    )
     *    ,
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
     * Expose all Equipment
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
     *      @Model(type=Equipment::class)
     *     )
     * )
     *
     * @return \FOS\RestBundle\View\View
     * @TODO Add user restriction.
     */
    public function cgetAction()
    {
        return $this->view(
            $this->equipmentRepository->findAll()
        );
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
     *    @SWG\Parameter(
     *     name="The full JSON Equipment",
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
        $existingEquipment = $this->findEquipmentById($id);
        $form = $this->createForm(EquipmentType::class, $existingEquipment);
        $data = json_decode($request->getContent(), true);

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
     *    @SWG\Parameter(
     *     name="The full JSON Equipment",
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
        $existingEquipment = $this->findEquipmentById($id);
        $form = $this->createForm(EquipmentType::class
            , $existingEquipment);

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

        $this->entityManager->flush();

        return $this->view(null
            , Response::HTTP_NO_CONTENT);
    }

    /**
     * Delete an Equipment with the id.
     *
     * @SWG\Delete(
     *     summary="Delete an Equipment based on ID"
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
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the Equipment"
     * )
     * @param string $id
     * @return \FOS\RestBundle\View\View
     */
    public function deleteAction(string $id)
    {
        $equipment = $this->findEquipmentById($id);

        $this->entityManager->remove($equipment);
        $this->entityManager->flush();

        return $this->view(null
            , Response::HTTP_NO_CONTENT);
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
}
