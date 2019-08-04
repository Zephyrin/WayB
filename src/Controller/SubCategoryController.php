<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\SubCategory;
use App\Form\SubCategoryType;
use App\Repository\SubCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Serializer\FormErrorSerializer;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

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

    public function __construct(
        EntityManagerInterface $entityManager,
        SubCategoryRepository $subCategoryRepository,
        FormErrorSerializer $formErrorSerializer
    )
    {
        $this->entityManager = $entityManager;
        $this->subCategoryRepository = $subCategoryRepository;
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
     * @Security("has_role('ROLE_AMBASSADOR')")
     * @param Request $request
     * @return \FOS\RestBundle\View\View|JsonResponse
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function postAction(Request $request)
    {
        $data = json_decode(
            $request->getContent(),
            true
        );
        $form = $this->createForm(
            SubCategoryType::class,
            new SubCategory());
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

        $subCat = $form->getData();
        $category = $this->entityManager->find(
            Category::class,
            $request->attributes->get('categoryid'));
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
     * @var $id
     * @return \FOS\RestBundle\View\View
     */
    public function getAction(string $id)
    {
        return $this->view(
            $this->findSubCategoryById($id)
        );
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
     * @return \FOS\RestBundle\View\View
     */
    public function cgetAction()
    {
        return $this->view(
            $this->subCategoryRepository->findAll()
        );
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
     * @return \FOS\RestBundle\View\View|JsonResponse
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function putAction(Request $request, string $id)
    {
        $existingSubCategory = $this->findSubCategoryById($id);
        $form = $this->createForm(SubCategoryType::class, $existingSubCategory);
        $data = json_decode($request->getContent(), true);

        $data['category'] = $request->attributes->get('categoryid');
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
     * @return \FOS\RestBundle\View\View|JsonResponse
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function patchAction(Request $request, string $id)
    {
        $existingSubCategory = $this->findSubCategoryById($id);
        $form = $this->createForm(SubCategoryType::class, $existingSubCategory);

        $form->submit($request->request->all(), false);
        if (false === $form->isValid()) {
            //return $this->view($form);
            return new JsonResponse(
                [
                    'status' => 'error',
                    'errors' => $this->formErrorSerializer->normalize($form),
                ],
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY
            );
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
     * @Security("has_role('ROLE_ADMIN')")
     * @param string $id
     * @return \FOS\RestBundle\View\View
     */
    public function deleteAction(string $id)
    {
        $subCategory = $this->findSubCategoryById($id);

        $this->entityManager->remove($subCategory);
        $this->entityManager->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param string $id
     *
     * @return SubCategory
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    private function findSubCategoryById(string $id)
    {
        $existingSubCategory = $this->subCategoryRepository->find($id);
        if (null === $existingSubCategory) {
            throw new NotFoundHttpException();
        }
        return $existingSubCategory;
    }
}
