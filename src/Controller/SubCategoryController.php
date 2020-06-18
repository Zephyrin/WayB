<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\SubCategory;
use App\Form\SubCategoryType;
use App\Repository\CategoryRepository;
use App\Repository\SubCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Serializer\FormErrorSerializer;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

/**
 * @Rest\RouteResource(
 *     "api/Category/{CategoryId}/SubCategory",
 *     pluralize=false
 * )
 *
 * @SWG\Tag(
 *     name="Sub-Category"
 * )
 */
class SubCategoryController extends AbstractFOSRestController implements ClassResourceInterface
{
    use AbstractController;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var subCategoryRepository
     */
    private $subCategoryRepository;

    /**
     * @var FormErrorSerializer
     */
    private $formErrorSerializer;

    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        SubCategoryRepository $subCategoryRepository,
        CategoryRepository $categoryRepository,
        FormErrorSerializer $formErrorSerializer
    )
    {
        $this->entityManager = $entityManager;
        $this->subCategoryRepository = $subCategoryRepository;
        $this->categoryRepository = $categoryRepository;
        $this->formErrorSerializer = $formErrorSerializer;
    }

    /**
     * Create a new Sub-Category link to a Category
     *
     * @SWG\Post(
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *      response=200,
     *      description="Successful operation with the new value insert",
     *      @SWG\Schema(
     *       ref=@Model(type=SubCategory::class)
     *      )
     *    ),
     *    @SWG\Response(
     *     response=422,
     *     description="The form is not correct<BR/>
     * See the corresponding JSON error to see which field is not correct"
     *    )
     *    ,
     *    @SWG\Parameter(
     *     name="categoryid",
     *     in="path",
     *     type="string",
     *     required=true,
     *     allowEmptyValue=false,
     *     description="The ID used to find the Category"
     *    ),
     *    @SWG\Parameter(
     *     name="The JSON Sub-Category",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *       ref=@Model(type=SubCategory::class)
     *     ),
     *     description="The JSon Sub-Category"
     *    )
     *
     * )
     * @param Request $request
     * @return View|JsonResponse
     * @throws ExceptionInterface
     */
    public function postAction(Request $request)
    {
        $connectUser = $this->getUser();

        $data = json_decode(
            $request->getContent(),
            true
        );
        $data = $this->manageObjectToId($data);
        $category = $this->findCategoryByRequest($request);
        $form = $this->createForm(
            SubCategoryType::class,
            new SubCategory());
        $form->submit(
            $data
        );

        if(isset($data['category']) && $data['category'] != $category->getId()) {
            return new JsonResponse(
                [
                    'status' => 'error',
                    'message' => 'Validation error request parameters do not correspond with the category of the sub-category',
                    'errors' => $this->formErrorSerializer->normalize($form),
                ],
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        $validation = $this->validationError($form, $this);
        if($validation instanceof JsonResponse)
            return $validation;

        $subCat = $form->getData();
        if(!$this->isGranted("ROLE_AMBASSADOR")
            || $subCat->getValidate() === null)
            $subCat->setValidate(false);
        $subCat->setCreatedBy($connectUser);
        $subCat->setCategory($category);
        $this->entityManager->persist($subCat);
        $this->entityManager->flush();

        return  $this->view(
            $subCat,
            Response::HTTP_CREATED);
    }

    /**
     * Expose the Sub-Category with the id.
     *
     * @SWG\Get(
     *     summary="Get the Sub-Category based on its ID and the CategoryId of the category",
     *     produces={"application/json"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return the Sub-Category based on ID and the CategoryId of the category",
     *     @SWG\Schema(ref=@Model(type=SubCategory::class))
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="The Category based on CategoryId or the Sub-Category based on ID is not found"
     * )
     *
     * @SWG\Parameter(
     *     name="categoryid",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the Category"
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the Sub-Category"
     * )
     *
     *
     * @param Request $request
     * @param string $id
     * @return View
     */
    public function getAction(Request $request, string $id)
    {
        $category = $this->findCategoryByRequest($request);
        $subCat = $this->findSubCategoryById($id);
        if($subCat->getCategory()->getId() == $category->getId())
            throw new NotFoundHttpException();
        return $this->view($category);
    }

    /**
     * Expose all Sub-Categories
     *
     * @SWG\Get(
     *     summary="Get all Sub-Categories belong to CategoryId",
     *     produces={"application/json"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return all the Sub-Categories",
     *     @SWG\Schema(
     *      type="array",
     *      @SWG\Items(ref=@Model(type=SubCategory::class))
     *     )
     * )
     * @SWG\Parameter(
     *     name="categoryid",
     *     in="path",
     *     type="string",
     *     description="The ID of the Category which Sub-Categories belong to"
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="The Category based on CategoryId is not found"
     * )
     *
     * @param Request $request
     * @return View
     */
    public function cgetAction(Request $request)
    {
        $category = $this->findCategoryByRequest($request);
        return $this->view($category->getSubCategories());
    }

    /**
     * Update a SubCategory
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
     *     description="The Category based on CategoryId or the Sub-Category based on ID is not found"
     *    ),
     *    @SWG\Parameter(
     *     name="The full JSON Sub-Category",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *       ref=@Model(type=SubCategory::class)
     *     ),
     *     description="The JSon Sub-Category"
     *    ),
     *    @SWG\Parameter(
     *     name="categoryid",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the Category"
     *    ),
     *    @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the Sub-Category"
     *    )
     * )
     * @Security("has_role('ROLE_AMBASSADOR')")
     * @param Request $request
     * @param string $id of the Category to update
     * @return void
     */
    public function putAction(Request $request, string $id)
    {
        $this->managePutOrPatch($request, $id, true);
    }

    /**
     * Update a part of a SubCategory
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
     *     description="The Category based on CategoryId or the Sub-Category based on ID is not found"
     *    ),
     *    @SWG\Parameter(
     *     name="The full JSON Sub-Category",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *       ref=@Model(type=SubCategory::class)
     *     ),
     *     description="The JSon Sub-Category"
     *    ),
     *    @SWG\Parameter(
     *     name="categoryid",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the Category"
     *    ),
     *    @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the Sub-Category"
     *    )
     * )
     * @Security("has_role('ROLE_AMBASSADOR')")
     * @param Request $request
     * @param string $id of the Category to update
     * @return void
     */
    public function patchAction(Request $request, string $id)
    {
        $this->managePutOrPatch($request, $id, false);
    }

    private function managePutOrPatch(Request $request, string $id, bool $clearMissing) {
        $existingSubCategory = $this->findSubCategoryById($id);
        $validate = $existingSubCategory->getValidate();
        $category = $this->findCategoryByRequest($request);
        if($category->getId() != $existingSubCategory->getCategory()->getId())
        {
            throw new NotFoundHttpException();
        }
        $form = $this->createForm(SubCategoryType::class, $existingSubCategory);
        $data = json_decode($request->getContent(), true);
        $data = $this->manageObjectToId($data);

        $form->submit($data, $clearMissing);
        $validation = $this->validationError($form, $this);
        if($validation instanceof JsonResponse)
            return $validation;
        if($existingSubCategory->getValidate() !== $validate) {
            $this->denyAccessUnlessGranted("ROLE_AMBASSADOR");
        }
        $this->entityManager->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Delete a SubCategory with the id.
     *
     * @SWG\Delete(
     *     summary="Delete a Sub-Category based on ID"
     * )
     * @SWG\Response(
     *     response=204,
     *     description="The sub-category is correctly delete",
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="The category based on CategoryId or the sub-category based on ID is not found"
     * )
     *
     * @SWG\Parameter(
     *     name="categoryid",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the Category"
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the Sub-Category"
     * )
     *
     * @Security("has_role('ROLE_AMBASSADOR')")
     * @param Request $request
     * @param string $id
     * @return View|JsonResponse
     */
    public function deleteAction(Request $request, string $id)
    {
        $subCategory = $this->findSubCategoryById($id);
        $category = $this->findCategoryByRequest($request);
        if($category->getId() != $subCategory->getCategory()->getId())
        {
            throw new NotFoundHttpException();
        }
        if(count($subCategory->getEquipments()) > 0) {
            return new JsonResponse(
                [
                    'status' => 'error',
                    'message' => 'To many equipments are linked to this sub-category'
                ],
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        $this->entityManager->remove($subCategory);
        $this->entityManager->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param string $id
     *
     * @return SubCategory
     * @throws NotFoundHttpException
     */
    private function findSubCategoryById(string $id)
    {
        $existingSubCategory = $this->subCategoryRepository->find($id);
        if (null === $existingSubCategory) {
            throw new NotFoundHttpException();
        }
        return $existingSubCategory;
    }

    /**
     * @param Request $request
     *
     * @return Category
     * @throws NotFoundHttpException
     */
    private function findCategoryByRequest(Request $request)
    {
        /* $category = $this->entityManager->find(
            Category::class, */
        $category = $this->categoryRepository->findById(
            $request->attributes->get('categoryid'));
        if($category == null)
            throw new NotFoundHttpException();
        return $category;
    }

    private function manageObjectToId($data) {
        if(isset($data['category'])) {
            if(isset($data['category']['id'])) {
                $data['category'] = $data['category']['id'];
            } else if (!is_int($data['category'])) {
                unset($data['category']);
            }
        }
        return $data;
    }
}
