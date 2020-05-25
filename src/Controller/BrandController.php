<?php

namespace App\Controller;

use App\Entity\Brand;
use App\Entity\User;
use App\Form\BrandType;
use App\Repository\BrandRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Serializer\FormErrorSerializer;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Swagger\Annotations as SWG;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Nelmio\ApiDocBundle\Annotation\Model;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RequestParam;

/**
 * Class BrandController
 * @package App\Controller
 *
 * @Rest\RouteResource(
 *     "api/brand",
 *     pluralize=false
 * )
 * @SWG\Tag(
 *     name="Brand"
 * )
 * 
 */
class BrandController extends AbstractFOSRestController implements ClassResourceInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var BrandRepository
     */
    private $brandRepository;

    /**
     * @var FormErrorSerializer
     */
    private $formErrorSerializer;

    public function __construct(
        EntityManagerInterface $entityManager,
        BrandRepository $brandRepository,
        FormErrorSerializer $formErrorSerializer
    ) {
        $this->entityManager = $entityManager;
        $this->brandRepository = $brandRepository;
        $this->formErrorSerializer = $formErrorSerializer;
    }

    /**
     * Create a new Brand if user.
     *
     *
     * @SWG\Post(
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *      response=201,
     *      description="Successful operation with the new value insert",
     *      @SWG\Schema(
     *       ref=@Model(type=Brand::class)
     *     )
     *    ),
     *    @SWG\Response(
     *     response=422,
     *     description="The form is not correct<BR/>
     * See the corresponding JSON error to see which field is not correct"
     *    ),
     *    @SWG\Parameter(
     *     name="The JSON Brand",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *       ref=@Model(type=Brand::class)
     *     ),
     *     description="The JSon Brand"
     *    )
     *
     * )
     * 
     * @param Request $request
     * @return \FOS\RestBundle\View\View|JsonResponse
     */
    public function postAction(Request $request)
    {
        $connectUser = $this->getUser();

        $data = json_decode(
            $request->getContent(),
            true
        );
        $data = $this->manageObjectToId($data);
        $form = $this->createForm(BrandType::class, new Brand());
        $form->submit(
            $data
        );
        if (false === $form->isValid()) {
            return new JsonResponse(
                [
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $this->formErrorSerializer->normalize($form),
                ],
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $insertData = $form->getData();
        $insertData->setCreatedBy($connectUser);
        if (
            !$this->isGranted("ROLE_AMBASSADOR")
            || $insertData->getValidate() === null
        )
            $insertData->setValidate(false);

        $this->entityManager->persist($insertData);

        $this->entityManager->flush();
        return  $this->view(
            $insertData,
            Response::HTTP_CREATED
        );
    }

    /**
     * Expose the Brand with the id
     *
     * @SWG\Get(
     *     summary="Get the Brand based on ID",
     *     produces={"application/json"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return the Brand based on ID",
     *     @SWG\Schema(ref=@Model(type=Brand::class))
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="The Brand based on ID is not found"
     * )
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the Brand"
     * )
     *
     * @var $id
     * @return \FOS\RestBundle\View\View
     */
    public function getAction(string $id)
    {
        return $this->view(
            $this->findBrandById($id)
        );
    }

    /**
     * Expose all Brands
     *
     * @SWG\Get(
     *     summary="Get all Brands",
     *     produces={"application/json"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return all the Brands",
     *     @SWG\Schema(
     *      type="array",
     *      @SWG\Items(ref=@Model(type=Brand::class))
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
     * , requirements="(name|uri|validate|askValidate)"
     * , default="name"
     * , description="Sort by name or uri")
     * @QueryParam(name="search"
     * , nullable=true
     * , description="Search on name and uri")
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
     * 
     * @return \FOS\RestBundle\View\View
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
        $brandsAndCount = [];
        if ($this->isGranted("ROLE_AMBASSADOR")) {
            $brandsAndCount = 
                $this->brandRepository->findForAmbassador(
                    $page
                    , $limit
                    , $sort
                    , $sortBy
                    , $search
                    , $validate
                    , $askValidate);
        } else {
            $user = $this->getUser();
            $brandsAndCount = $this->brandRepository->findByUserOrValidate($user
            , $page
            , $limit
            , $sort
            , $sortBy
            , $search
            , $validate
            , $askValidate);
        }
        $view = $this->view(
            $brandsAndCount[0]
        );
        $view->setHeader('X-Total-Count', $brandsAndCount[1]);
        $view->setHeader('X-Pagination-Count', $brandsAndCount[2]);
        $view->setHeader('X-Pagination-Page', $brandsAndCount[3]);
        $view->setHeader('X-Pagination-Limit', $brandsAndCount[4]);
        $view->setHeader('Access-Control-Expose-Headers'
            , 'X-Total-Count, X-Pagination-Count, X-Pagination-Page, X-Pagination-Limit');
        return $view;
    }

    /**
     * Update a Brand.
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
     *     description="The Brand based on ID is not found"
     *    ),
     *    @SWG\Parameter(
     *     name="The full JSON Brand",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *       ref=@Model(type=Brand::class)
     *     ),
     *     description="The JSon Brand"
     *    ),
     *    @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the Brand"
     *    )
     * )
     *
     * @param Request $request
     * @param string $id of the Brand to update
     * @return \FOS\RestBundle\View\View|JsonResponse
     */
    public function putAction(Request $request, string $id)
    {
        return $this->putOrPatch($request, $id, true);
    }

    /**
     * Update a part of a Brand
     *
     * All missing attribute will not be update.
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
     *     description="The Brand based on ID is not found"
     *    ),
     *    @SWG\Parameter(
     *     name="A part of a JSON Brand",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *       ref=@Model(type=Brand::class)
     *     ),
     *     description="A part of a JSon Brand"
     *    ),
     *    @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the Brand"
     *    )
     * )
     *
     * @param Request $request
     * @param string $id of the Brand to update
     * @return \FOS\RestBundle\View\View|JsonResponse
     */
    public function patchAction(Request $request, string $id)
    {
        return $this->putOrPatch($request, $id, false);
    }

    private function putOrPatch(Request $request, string $id, bool $clearData) {
        $connectUser = $this->getUser();
        $existingBrand = $this->findBrandById($id);
        if ($existingBrand->getCreatedBy() !== $connectUser)
            $this->denyAccessUnlessGranted("ROLE_AMBASSADOR");
        $form = $this->createForm(BrandType::class, $existingBrand);
        $validate = $existingBrand->getValidate();
        $data = json_decode($request->getContent(), true);
        $logo = $existingBrand->getLogo();
        $data = $this->manageObjectToId($data);
        $form->submit($data, $clearData);

        if (false === $form->isValid()) {
            return new JsonResponse(
                [
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $this->formErrorSerializer->normalize($form),
                ],
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        if ($existingBrand->getValidate() !== $validate) {
            $this->denyAccessUnlessGranted("ROLE_AMBASSADOR");
        }
        if ($logo != null && $existingBrand->getLogo() == null) {
            unlink($this->getParameter('media_object') . "/" . $logo->getFilePath());
            $this->entityManager->remove($logo);
        }
        $this->entityManager->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Delete a Brand with the id
     *
     *
     * You should know what your are doing ! Cannot be reverse.
     *
     * @SWG\Delete()
     * @SWG\Response(
     *     response=204,
     *     description="The brand is correctly delete",
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="The brand based on ID is not found"
     * )
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the Brand"
     * )
     * 
     * @param string $id
     * @return \FOS\RestBundle\View\View
     */
    public function deleteAction(string $id)
    {
        $brand = $this->findBrandById($id);
        if ($brand->getCreatedBy() !== $this->getUser())
            $this->denyAccessUnlessGranted(
                "ROLE_ADMIN",
                null,
                "This brand don't belong to you and your are not an admin."
            );
        if (count($brand->getEquipments()) < 1) {
            $logo = $brand->getLogo();
            if($logo != null) {
                unlink($this->getParameter('media_object') . "/" . $logo->getFilePath());
                $this->entityManager->remove($logo);
            }
            $this->entityManager->remove($brand);
            $this->entityManager->flush();

            return $this->view(
                null,
                Response::HTTP_NO_CONTENT
            );
        }
        return new JsonResponse(
            [
                'status' => 'error',
                'message' => 'Too many equipments use this brand.'
            ],
            JsonResponse::HTTP_PRECONDITION_FAILED
        );
    }

    /**
     * @param string $id
     *
     * @return Brand
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    private function findBrandById(string $id)
    {
        $existingBrand = $this->brandRepository->find($id);

        if (null === $existingBrand) {
            throw new NotFoundHttpException();
        }

        return $existingBrand;
    }

    private function manageObjectToId($data) {
        if(isset($data['logo'])) {
            if(isset($data['logo']['id'])) {
                $data['logo'] = $data['logo']['id'];
            } else if (!is_int($data['logo'])) {
                $data['logo'] = null;
            }
        }
        return $data;
    }
}
