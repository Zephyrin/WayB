<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\IntoBackpack;
use App\Form\IntoBackpackType;
use App\Repository\CategoryRepository;
use App\Repository\IntoBackpackRepository;
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
 *     "api/backpack/{backpackId}/IntoBackpack",
 *     pluralize=false
 * )
 *
 * @SWG\Tag(
 *     name="Into-backpack"
 * )
 */
class IntoBackpackController extends AbstractFOSRestController implements ClassResourceInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var intoBackpackRepository
     */
    private $intoBackpackRepository;

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
        IntoBackpackRepository $intoBackpackRepository,
        CategoryRepository $categoryRepository,
        FormErrorSerializer $formErrorSerializer
    )
    {
        $this->entityManager = $entityManager;
        $this->intoBackpackRepository = $intoBackpackRepository;
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
     *       ref=@Model(type=IntoBackpack::class)
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
     *       ref=@Model(type=IntoBackpack::class)
     *     ),
     *     description="The JSon Sub-Category"
     *    )
     *
     * )
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
        $data = $this->manageObjectToId($data);
        $category = $this->findBackpackByRequest($request);
        $form = $this->createForm(
            IntoBackpackType::class,
            new IntoBackpack());
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
     *     @SWG\Schema(ref=@Model(type=IntoBackpack::class))
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
    public function getAction(Request $request, string $id)
    {
        $backpack = $this->findBackpackByRequest($request);
        $intoBackpack = $this->findIntoBackpackById($id);
        if($intoBackpack->getBackpack()->getId() == $backpack->getId())
            throw new NotFoundHttpException();
        return $this->view($intoBackpack);
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
     *      @SWG\Items(ref=@Model(type=IntoBackpack::class))
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
    public function cgetAction(Request $request)
    {
        $category = $this->findBackpackByRequest($request);
        return $this->view($category->getSubCategories());
    }

    /**
     * Update a IntoBackpack
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
     *       ref=@Model(type=IntoBackpack::class)
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
        $this->managePutOrPatch($request, $id, true);
    }

    /**
     * Update a part of a IntoBackpack
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
     *       ref=@Model(type=IntoBackpack::class)
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
        $this->managePutOrPatch($request, $id, false);
    }

    private function managePutOrPatch(Request $request, string $id, bool $clearMissing) {
        $existingIntoBackpack = $this->findIntoBackpackById($id);
        $backpack = $this->findBackpackByRequest($request);
        if($backpack->getId() != $existingIntoBackpack->getBackpack()->getId())
        {
            throw new NotFoundHttpException();
        }
        $form = $this->createForm(IntoBackpackType::class, $existingIntoBackpack);
        $data = json_decode($request->getContent(), true);
        $data = $this->manageObjectToId($data);

        $form->submit($data, $clearMissing);
        if (false === $form->isValid()) {
            //return $this->view($form);
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
     * Delete a IntoBackpack with the id.
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
     * @param string $id
     * @return \FOS\RestBundle\View\View
     */
    public function deleteAction(Request $request, string $id)
    {
        $intoBackpack = $this->findIntoBackpackById($id);
        $backpack = $this->findBackpackByRequest($request);
        if($backpack->getId() != $intoBackpack->getBackpack()->getId())
        {
            throw new NotFoundHttpException();
        }
        $this->entityManager->remove($intoBackpack);
        $this->entityManager->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param string $id
     *
     * @return IntoBackpack
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    private function findIntoBackpackById(string $id)
    {
        $existingIntoBackpack = $this->intoBackpackRepository->find($id);
        if (null === $existingIntoBackpack) {
            throw new NotFoundHttpException();
        }
        return $existingIntoBackpack;
    }

    /**
     * @param Request $request
     *
     * @return Category
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    private function findBackpackByRequest(Request $request)
    {
        /* $category = $this->entityManager->find(
            Category::class, */
        $category = $this->categoryRepository->findById(
            $request->attributes->get('backpackid'));
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
