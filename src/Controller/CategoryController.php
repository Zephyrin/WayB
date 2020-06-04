<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\SubCategory;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Doctrine\Common\Collections\Collection;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Controller\Annotations\QueryParam;

/**
 * @Rest\RouteResource(
 *     "api/Category",
 *     pluralize=false
 * )
 * @SWG\Tag(
 *     name="Category"
 * )
 */
class CategoryController extends AbstractFOSRestController implements ClassResourceInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var FormErrorSerializer
     */
    private $formErrorSerializer;

    public function __construct(
        EntityManagerInterface $entityManager,
        CategoryRepository $categoryRepository,
        FormErrorSerializer $formErrorSerializer
    ) {
        $this->entityManager = $entityManager;
        $this->categoryRepository = $categoryRepository;
        $this->formErrorSerializer = $formErrorSerializer;
    }

    /**
     * Create a new Category.
     *
     * The SubCategories can be created during the POST.
     *
     * @SWG\Post(
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *      response=201,
     *      description="Successful operation with the new value insert",
     *      @SWG\Schema(
     *       ref=@Model(type=Category::class)
     *     )
     *    ),
     *    @SWG\Response(
     *     response=422,
     *     description="The form is not correct<BR/>
     * See the corresponding JSON error to see which field is not correct"
     *    ),
     *    @SWG\Parameter(
     *     name="The JSON Category",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *       ref=@Model(type=Category::class)
     *     ),
     *     description="The JSon Category"
     *    )
     *
     * )
     * 
     * @param Request $request
     * @return \FOS\RestBundle\View\View|JsonResponse
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function postAction(Request $request)
    {
        $connectUser = $this->getUser();
        $data = json_decode(
            $request->getContent(),
            true
        );
        $form = $this->createForm(CategoryType::class, new Category());

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
     * Expose the Category with the id
     *
     * @SWG\Get(
     *     summary="Get the Category based on ID",
     *     produces={"application/json"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return the Category based on ID",
     *     @SWG\Schema(ref=@Model(type=Category::class))
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="The Category based on ID is not found"
     * )
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the Category"
     * )
     *
     * @var $id
     * @return \FOS\RestBundle\View\View
     */
    public function getAction(string $id)
    {
        return $this->view(
            $this->findCategoryById($id)
        );
    }

    /**
     * Expose all Categories
     *
     * @SWG\Get(
     *     summary="Get all Categories",
     *     produces={"application/json"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return all the Categories",
     *     @SWG\Schema(
     *      type="array",
     *      @SWG\Items(ref=@Model(type=Category::class))
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
     * @QueryParam(name="noPagination"
     * , requirements="(true|false)"
     * , default=false
     * , description="Don't care about pagination")
     * @QueryParam(name="sort"
     * , requirements="(asc|desc)"
     * , allowBlank=false
     * , default="asc"
     * , description="Sort direction")
     * @QueryParam(name="sortBy"
     * , requirements="(name|validate|askValidate|subCategoryCount)"
     * , default="name"
     * , description="Sort by name or validate or ask validate")
     * @QueryParam(name="search"
     * , nullable=true
     * , description="Search on name and sub-category's name")
     * @QueryParam(name="validate"
     * , nullable=true
     * , allowBlank=true
     * , requirements="(false|true)"
     * , description="Item validate or not")
     * @QueryParam(name="askValidate"
     * , nullable=true
     * , allowBlank=true
     * , requirements="(false|true)"
     * , description="Item asked to be validated or not")
     * @QueryParam(name="subCategoryCount"
     * , requirements="(l|le|e|g|ge)(\d+)((l|le|e|g|ge)(\d+))?"
     * , nullable=true
     * , description="Retourne only category which have greater or equal or lower than the number")
     *
     * @return \FOS\RestBundle\View\View
     */
    public function cgetAction(ParamFetcher $paramFetcher)
    {
        $page = $paramFetcher->get('page');
        $limit = $paramFetcher->get('limit');
        $noPagination = $paramFetcher->get('noPagination');
        $sort = $paramFetcher->get('sort');
        $sortBy = $paramFetcher->get('sortBy');
        $search = $paramFetcher->get('search');
        $validate = $paramFetcher->get('validate');
        $askValidate = $paramFetcher->get('askValidate');
        $subCategoryCount = $paramFetcher->get('subCategoryCount');
        $categoryAndCount = [];
        if ($this->isGranted("ROLE_AMBASSADOR")) {
            $categoryAndCount = $this->categoryRepository->findForAmbassador(
                $page,
                $limit,
                $noPagination == 'true' ? true : false,
                $sort,
                $sortBy,
                $search,
                $validate,
                $askValidate,
                $subCategoryCount
            );
        } else {
            $user = $this->getUser();
            $categoryAndCount = $this->categoryRepository->findByUserOrValidate(
                $user,
                $page,
                $noPagination == 'true' ? true : false,
                $limit,
                $sort,
                $sortBy,
                $search,
                $validate,
                $askValidate,
                $subCategoryCount
            );
            foreach ($categoryAndCount[0] as $cat) {
                $subs = $cat->getSubCategories();
                for ($i = count($subs) - 1; $i >= 0; $i--) {
                    if (!($subs[$i]->getValidate() || $subs[$i]->getCreatedBy() == $user)) {
                        $cat->removeSubCategory($subs[$i]);
                    }
                }
            }
        }
        $view = $this->view(
            $categoryAndCount[0]
        );
        $view->setHeader('X-Total-Count', $categoryAndCount[1]);
        if (!$noPagination) {
            $view->setHeader('X-Pagination-Count', $categoryAndCount[2]);
            $view->setHeader('X-Pagination-Page',  $categoryAndCount[3]);
            $view->setHeader('X-Pagination-Limit', $categoryAndCount[4]);
            $view->setHeader(
                'Access-Control-Expose-Headers',
                'X-Total-Count, X-Pagination-Count, X-Pagination-Page, X-Pagination-Limit'
            );
        } else {
            $view->setHeader(
                'Access-Control-Expose-Headers',
                'X-Total-Count'
            );   
        }
        return $view;
    }

    /**
     * Update a Category and its SubCategories.
     *
     * Be careful, a missing subCategory in the array subCategories will be delete.
     * You can also update a part of a subCategory.
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
     *     description="The Category based on ID is not found"
     *    ),
     *    @SWG\Parameter(
     *     name="The full JSON Category",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *       ref=@Model(type=Category::class)
     *     ),
     *     description="The JSon Category"
     *    ),
     *    @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the Category"
     *    )
     * )
     *
     * @param Request $request
     * @param string $id of the Category to update
     * @return \FOS\RestBundle\View\View|JsonResponse
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function putAction(Request $request, string $id)
    {
        $connectUser = $this->getUser();
        $existingCategory = $this->findCategoryById($id);
        if ($existingCategory->getCreatedBy() !== $connectUser)
            $this->denyAccessUnlessGranted("ROLE_AMBASSADOR");
        $form = $this->createForm(CategoryType::class, $existingCategory);
        $data = $request->request->all();
        $validate = $existingCategory->getValidate();
        $form->submit($data);

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
        if ($existingCategory->getValidate() !== $validate) {
            $this->denyAccessUnlessGranted("ROLE_AMBASSADOR");
        }
        $this->entityManager->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Update a part of a Category
     *
     * All missing attribute will not be update.
     *
     * If you want update one SubCategory during this process, you need to add all other SubCategory in the correct order otherwise they will be delete.
     *
     * Be careful, when updating SubCategories, you need to range it in correct order.
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
     *     description="The Category based on ID is not found"
     *    ),
     *    @SWG\Parameter(
     *     name="A part of a JSON Category",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *       ref=@Model(type=Category::class)
     *     ),
     *     description="A part of a JSon Category"
     *    ),
     *    @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the Category"
     *    )
     * )
     *
     * @param Request $request
     * @param string $id of the Category to update
     * @return \FOS\RestBundle\View\View|JsonResponse
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function patchAction(Request $request, string $id)
    {
        $connectUser = $this->getUser();
        $existingCategory = $this->findCategoryById($id);
        if ($existingCategory->getCreatedBy() !== $connectUser)
            $this->denyAccessUnlessGranted("ROLE_AMBASSADOR");

        $validate = $existingCategory->getValidate();

        $form = $this->createForm(CategoryType::class, $existingCategory);
        $data = $request->request->all();

        $form->submit($data, false);

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
        if ($existingCategory->getValidate() !== $validate) {
            $this->denyAccessUnlessGranted("ROLE_AMBASSADOR");
        }

        $this->entityManager->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Delete a Category with the id
     *
     * Also delete all SubCategory link to Category.
     * And of course all ExtraFieldDef link to all previously deleted SubCategory.
     *
     * You should know what your are doing ! Cannot be reverse.
     *
     * @SWG\Delete()
     * @SWG\Response(
     *     response=204,
     *     description="The category is correctly delete",
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="The category based on ID is not found"
     * )
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the Category"
     * )
     *
     * @param string $id
     * @return \FOS\RestBundle\View\View
     */
    public function deleteAction(string $id)
    {
        $category = $this->findCategoryById($id);
        if ($category->getCreatedBy() !== $this->getUser())
            $this->denyAccessUnlessGranted(
                "ROLE_ADMIN",
                null,
                "This category don't belong to you and your are not an admin."
            );
        if (count($category->getSubCategories()) < 1) {
            $this->entityManager->remove($category);
            $this->entityManager->flush();
            return $this->view(null, Response::HTTP_NO_CONTENT);
        }
        if (count($category->getSubCategories()) > 0) {
            return new JsonResponse(
                [
                    'status' => 'error',
                    'message' => 'There are sub categories link to this category.'
                ],
                JsonResponse::HTTP_PRECONDITION_FAILED
            );
        }
    }

    /**
     * @param string $id
     *
     * @return Category
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    private function findCategoryById(string $id)
    {
        $existingCategory = $this->categoryRepository->find($id);
        if (null === $existingCategory) {
            throw new NotFoundHttpException();
        }

        return $existingCategory;
    }
}
