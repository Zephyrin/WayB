<?php

namespace App\Controller;

namespace App\Controller;

use App\Entity\Category;
use App\Entity\ExtraFieldDef;
use App\Entity\SubCategory;
use App\Form\ExtraFieldDefType;
use App\Repository\ExtraFieldDefRepository;
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
 * @Rest\RouteResource(
 *     "api/Category/{CategoryId}/SubCategory/{SubCategoryId}/ExtraFieldDef",
 *     pluralize=false
 * )
 *
 * @SWG\Tag(
 *     name="Extra Field Definition"
 * )
 */
class ExtraFieldDefController extends FOSRestController implements ClassResourceInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var ExtraFieldDefRepository
     */
    private $extraFieldDefRepository;

    /**
     * @var FormErrorSerializer
     */
    private $formErrorSerializer;

    public function __construct(
        EntityManagerInterface $entityManager,
        ExtraFieldDefRepository $extraFieldDefRepository,
        FormErrorSerializer $formErrorSerializer
    )
    {
        $this->entityManager = $entityManager;
        $this->extraFieldDefRepository = $extraFieldDefRepository;
        $this->formErrorSerializer = $formErrorSerializer;
    }

    /**
     * Create a new ExtraFieldDef
     *
     * @SWG\Post(
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *      response=200,
     *      description="Successful operation"
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
     *     name="subcategoryid",
     *     in="path",
     *     type="string",
     *     required=true,
     *     allowEmptyValue=false,
     *     description="The ID used to find the Sub-Category"
     *    ),
     *    @SWG\Parameter(
     *     name="The JSON Category",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *       ref=@Model(type=ExtraFieldDef::class)
     *     ),
     *     description="The JSon ExtraFieldDef"
     *    )
     *
     * )
     *
     * @param Request $request
     * @return \FOS\RestBundle\View\View|JsonResponse
     */
    public function postAction(Request $request)
    {

        $data = json_decode(
            $request->getContent(),
            true
        );
        $form = $this->createForm(
            ExtraFieldDefType::class,
            new ExtraFieldDef());
        $form->submit(
            $data
        );
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
        $extraFieldDef = $form->getData();
        $subCategory = $this->entityManager->find(
            SubCategory::class,
            $request->attributes->get('subcategoryid'));
        $extraFieldDef->setSubCategory($subCategory);
        $this->entityManager->persist($extraFieldDef);
        $this->entityManager->flush();

        return  $this->view([
            'status' => 'ok',
        ],
            Response::HTTP_CREATED);
    }
    /**
     * Expose the ExtraFieldDef with the id.
     *
     * @SWG\Get(
     *     summary="Get the ExtraFieldDef based on its ID, the CategoryId of the category and the SubCategoryId of the sub-category",
     *     produces={"application/json"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return the ExtraFieldDef based on its ID, the CategoryId of the category and the SubCategoryId of the sub-category",
     *     @SWG\Schema(ref=@Model(type=ExtraFieldDef::class))
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="The ExtraFieldDef based on its ID or Category based on CategoryId or the Sub-Category based on SubCategoryId is not found"
     * )
     *
     * @SWG\Parameter(
     *     name="categoryid",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the Category"
     * )
     * @SWG\Parameter(
     *     name="subcategoryid",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the Sub-Category"
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the Extra Field Def"
     * )
     *
     * @var $id
     * @return \FOS\RestBundle\View\View
     */
    public function getAction(string $id)
    {
        return $this->view(
            $this->findExtraFieldDefById($id)
        );
    }

    /**
     * Expose all Extra Field Def
     *
     * @SWG\Get(
     *     summary="Get all Extra Field Def belong to Sub-Categories that belong to CategoryId",
     *     produces={"application/json"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return all the ExtraFieldDef",
     *     @SWG\Schema(
     *      type="array",
     *      @Model(type=ExtraFieldDef::class)
     *     )
     * )
     * @SWG\Parameter(
     *     name="categoryid",
     *     in="path",
     *     type="string",
     *     description="The ID of the Category which Sub-Categories belong to"
     * )
     * @SWG\Parameter(
     *     name="subcategoryid",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the Sub-Category"
     * )
     * @SWG\Response(
     *     response=404,
     *     description="The Category based on CategoryId or the Sub-Category based on SubCategoryId is not found"
     * )
     *
     * @return \FOS\RestBundle\View\View
     */
    public function cgetAction()
    {
        return $this->view(
            $this->extraFieldDefRepository->findAll()
        );
    }

    /**
     * Update an ExtraFieldDef
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
     *     description="The Category based on CategoryId or the Sub-Category based on SubCategoryId or the ExtraFieldDef based on ID is not found"
     *    ),
     *    @SWG\Parameter(
     *     name="The full JSON ExtraFieldDef",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *       ref=@Model(type=ExtraFieldDef::class)
     *     ),
     *     description="The JSon ExtraFieldDef"
     *    ),
     *    @SWG\Parameter(
     *     name="categoryid",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the Category"
     *    ),
     *    @SWG\Parameter(
     *     name="subcategoryid",
     *     in="path",
     *     type="string",
     *     description="The SubCategoryId used to find the Sub-Category"
     *    ),
     *    @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the ExtraFieldDef"
     *    )
     * )
     *
     * @param Request $request
     * @param string $id of the Category to update
     * @return \FOS\RestBundle\View\View|JsonResponse
     */
    public function putAction(Request $request, string $id)
    {
        $existingExtraFieldDef = $this->findExtraFieldDefById($id);
        $data = json_decode($request->getContent(), true);

        $form = $this->createForm(
            ExtraFieldDefType::class,
            $existingExtraFieldDef);

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

        return $this->view(null, JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * Update a part of an ExtraFieldDef
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
     *     description="The Category based on CategoryId or the Sub-Category based on SubCategoryId or the ExtraFieldDef based on ID is not found"
     *    ),
     *    @SWG\Parameter(
     *     name="The full JSON ExtraFieldDef",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *       ref=@Model(type=ExtraFieldDef::class)
     *     ),
     *     description="The JSon Category"
     *    ),
     *    @SWG\Parameter(
     *     name="categoryid",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the Category"
     *    ),
     *    @SWG\Parameter(
     *     name="subcategoryid",
     *     in="path",
     *     type="string",
     *     description="The SubCategoryId used to find the Sub-Category"
     *    ),
     *    @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the ExtraFieldDef"
     *    )
     * )
     *
     * @param Request $request
     * @param string $id of the Category to update
     * @return \FOS\RestBundle\View\View|JsonResponse
     */
    public function patchAction(Request $request, string $id)
    {
        $existingExtraFieldDef = $this->findExtraFieldDefById($id);
        $form = $this->createForm(ExtraFieldDefType::class, $existingExtraFieldDef);
        $data = json_decode($request->getContent(), true);
        $form->submit($data, false);
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
     * Delete an ExtraFieldDef with the id.
     *
     * @SWG\Delete(
     *     summary="Delete an ExtraFieldDef based on ID"
     * )
     * @SWG\Response(
     *     response=204,
     *     description="The ExtraFieldDef is correctly delete",
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="The category based on CategoryId or the sub-category based on SubCategoryId or the ExtraFieldDef based on ID is not found"
     * )
     *
     * @SWG\Parameter(
     *     name="categoryid",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the Category"
     * )
     * @SWG\Parameter(
     *     name="subcategoryid",
     *     in="path",
     *     type="string",
     *     description="The SubCategoryId used to find the Sub-Category"
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the ExtraFieldDef"
     * )
     * @param string $id
     * @return \FOS\RestBundle\View\View
     */
    public function deleteAction(string $id)
    {
        $extraFieldDef = $this->findExtraFieldDefById($id);

        $this->entityManager->remove($extraFieldDef);
        $this->entityManager->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param string $id
     *
     * @return Category
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    private function findExtraFieldDefById(string $id)
    {
        $existingExtraFieldDef = $this->extraFieldDefRepository->find($id);
        if (null === $existingExtraFieldDef) {
            throw new NotFoundHttpException();
        }
        return $existingExtraFieldDef;
    }

}
