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
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Security\Core\Security;
use FOS\RestBundle\Controller\Annotations\QueryParam;

/**
 * Class EquipmentController
 * @package App\Controller
 *
 * @Rest\RouteResource(
 *     "api/Equipment",
 *     pluralize=false
 * )
 * @SWG\Tag(
 *     name="Equipment"
 * )
 */
class EquipmentController extends AbstractFOSRestController implements ClassResourceInterface
{
    use AbstractController;
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
    ) {
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
     *     response=403,
     *     description="You don't have enought right"
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
     * @return View|JsonResponse
     * @throws ExceptionInterface
     * @TODO Add user check and user link.
     */
    public function postAction(Request $request)
    {
        $connectUser = $this->getUser();
        $data = json_decode(
            $request->getContent(),
            true
        );
        $data = $this->manageObjectToId($data);
        $form = $this->createForm(
            EquipmentType::class,
            new Equipment());
        $form->submit(
            $data
        );
        $validation = $this->validationError($form, $this);
        if($validation instanceof JsonResponse)
            return $validation;

        $equipment = $form->getData();
        $equipment->setCreatedBy($connectUser);
        if($equipment->getSubCategory() == null) {
            if (isset($data['subCategory'])) {
                if (is_int(intval($data['subCategory']))) {
                    $subCat = $this->entityManager
                        ->getRepository(SubCategory::class)
                        ->find($data['subCategory']);
                    $equipment->setSubCategory($subCat);
                }
            } else {
                return new JsonResponse(
                    [
                        'status' => 'error',
                        'message' => 'Validation error',
                        'errors' => $this->formErrorSerializer->normalize($form),
                    ],
                    JsonResponse::HTTP_UNPROCESSABLE_ENTITY
                );
            }
        }
        if($equipment->getBrand() == null
            && isset($data['brand'])) {
            if(is_int(intval($data['brand']))) {
                $brand = $this->entityManager
                    ->getRepository(Brand::class)
                    ->find($data['brand']);
                $equipment->setBrand($brand);
            }
        }
        if(!$this->isGranted("ROLE_AMBASSADOR")
            || $equipment->getValidate() === null)
            $equipment->setValidate(false);

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
     * @return View
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
     * @QueryParam(name="page"
     * , requirements="\d+"
     * , default="1"
     * , description="Page of the overview.")
     * @QueryParam(name="limit"
     * , requirements="\d+"
     * , default="10"
     * , description="Item count limit")
     * @QueryParam(name="sort"
     * , requirements="(asc|desc)"
     * , allowBlank=false
     * , default="asc"
     * , description="Sort direction")
     * @QueryParam(name="sortBy"
     * , requirements="(name|description|subCategory|brand|validate|askValidate)"
     * , default="name"
     * , description="Sort by name or uri")
     * @QueryParam(name="search"
     * , nullable=true
     * , description="Search on name or description or sub-category name or category name or brand name or brand description")
     * @QueryParam(name="weight"
     * , nullable=true
     * , requirements="(gt\d+)?(lt\d+)?(eq\d+)?"
     * , description="Retrieve all equipment that have a weight greater than x or lower than x or equal x")
     * @QueryParam(name="price"
     * , nullable=true
     * , requirements="(gt\d+)?(lt\d+)?(eq\d+)?"
     * , description="Retrieve all equipment that have a price greater than x or lower than x or equal x")
     * @QueryParam(name="owned"
     * , nullable=true
     * , requirements="(gt\d+)?(lt\d+)?(eq\d+)?"
     * , description="Retrieve all equipment that user have greater than x or lower than x or equal x")
     * @QueryParam(name="wishes"
     * , nullable=true
     * , requirements="(gt\d+)?(lt\d+)?(eq\d+)?"
     * , description="Retrieve all equipment that user wish greater than x or lower than x or equal x")
     * @QueryParam(name="others"
     * , nullable=true
     * , requirements="(true|false)"
     * , description="Retrieve all equipment that user does not wish and does not owned")
     * @QueryParam(name="validate"
     * , nullable=true
     * , allowBlank=true
     * , requirements="(false|true)"
     * , description="Item validate or not")
     * @QueryParam(name="askValidate"
     * , nullable=true
     * , allowBlank=true
     * , requirements="(false|true)"
     * , description="Item validate or not")
     * @QueryParam(name="belongToSubCategories"
     * , nullable=true
     * , allowBlank=true
     * , requirements="\[\d+(,\d+)*\]"
     * , description="list of sub-category id that equipment belong to. Empty means all")
     * @QueryParam(name="belongToBrands"
     * , nullable=true
     * , allowBlank=true
     * , requirements="\[\d+(,\d+)*\]"
     * , description="list of brand id that equipment belong to. Empty means all")
     * @param ParamFetcher $paramFetcher
     * @return View
     */
    public function cgetAction(ParamFetcher $paramFetcher)
    {
        $page = $paramFetcher->get('page');
        $limit = $paramFetcher->get('limit');
        $sort = $paramFetcher->get('sort');
        $sortBy = $paramFetcher->get('sortBy');
        $search = $paramFetcher->get('search');
        $validate = $paramFetcher->get('validate');
        $askValidate = $paramFetcher->get('askValidate');
        $weight = $paramFetcher->get('weight');
        $price = $paramFetcher->get('price');
        $owned = $paramFetcher->get('owned');
        $wishes = $paramFetcher->get('wishes');
        $others = $paramFetcher->get('others');
        $belongToSubCategories = $paramFetcher->get('belongToSubCategories');
        $belongToBrands = $paramFetcher->get('belongToBrands');
        $equipments = null;
        if($this->isGranted("ROLE_AMBASSADOR"))
            $equipments = $this->equipmentRepository->findForAmbassador(
                $page
                , $limit
                , $sort
                , $sortBy
                , $search
                , $validate
                , $askValidate
                , $weight
                , $price
                , $owned
                , $wishes
                , $others
                , $belongToSubCategories
                , $belongToBrands
            );
        else {
            $user = $this->getUser();
            $equipments = $this->equipmentRepository->findByUserOrValidate($user
                , $page
                , $limit
                , $sort
                , $sortBy
                , $search
                , $validate
                , $askValidate
                , $weight
                , $price
                , $owned
                , $wishes
                , $others
                , $belongToSubCategories
                , $belongToBrands    
            );
            foreach($equipments as $eq) {
                $cha = $eq->getCharacteristics();
                for($i = count($cha) - 1; $i >= 0; $i--) {
                    if (!($cha[$i]->getValidate() || $cha[$i]->getCreatedBy() == $user)) {
                        $cha->removeCharacteristic($cha);
                    }
                }
            }
        }
        return $this->setPaginateToView($equipments, $this);
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
     * @return View|JsonResponse
     * @throws ExceptionInterface
     */
    public function putAction(Request $request, string $id)
    {
        $connectUser = $this->getUser();
        $existingEquipment = $this->findEquipmentById($id);
        if($existingEquipment->getCreatedBy() !== $connectUser)
            $this->denyAccessUnlessGranted("ROLE_AMBASSADOR");

        $form = $this->createForm(EquipmentType::class, $existingEquipment);
        $data = json_decode($request->getContent(), true);
        $data = $this->manageObjectToId($data);
        $validate = $existingEquipment->getValidate();
        $form->submit($data);
        $validation = $this->validationError($form, $this);
        if($validation instanceof JsonResponse)
            return $validation;
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
     * @return View|JsonResponse
     * @throws ExceptionInterface
     */
    public function patchAction(Request $request, string $id)
    {
        $connectUser = $this->getUser();
        $existingEquipment = $this->findEquipmentById($id);
        if($existingEquipment->getCreatedBy() !== $connectUser)
             $this->denyAccessUnlessGranted("ROLE_AMBASSADOR");

        $form = $this->createForm(EquipmentType::class
            , $existingEquipment);
        $validate = $existingEquipment->getValidate();

        $data = $this->manageObjectToId($request->request->all());
        $form->submit($data, false);
        $validation = $this->validationError($form, $this);
        if($validation instanceof JsonResponse)
            return $validation;
        if($existingEquipment->getValidate() !== $validate) {
            $this->denyAccessUnlessGranted("ROLE_AMBASSADOR");
        }
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
     * @return View|JsonResponse
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
     * @throws NotFoundHttpException
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
     * @throws NotFoundHttpException
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

    private function manageObjectToId($data) {
        if(isset($data['brand'])) {
            if(isset($data['brand']['id'])) {
                $data['brand'] = $data['brand']['id'];
            } else if (!is_int($data['brand'])) {
                unset($data['brand']);
            }
        }
        if(isset($data['subCategory'])) {
            if(isset($data['subCategory']['id'])) {
                $data['subCategory'] = $data['subCategory']['id'];
            } else if(!is_int($data['subCategory'])) {
                unset($data['subCategory']);
            }
        }
        return $data;
    }
}
