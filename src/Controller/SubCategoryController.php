<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\SubCategory;
use App\Form\SubCategoryType;
use App\Repository\SubCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Serializer\FormErrorSerializer;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Rest\RouteResource(
 *     "Category/{CategoryId}/SubCategory",
 *     pluralize=false
 * )
 */
class SubCategoryController extends FOSRestController implements ClassResourceInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var CategoryRepository
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

    public function postAction(Request $request)
    {
        $data = json_decode(
            $request->getContent(),
            true
        );
        $form = $this->createForm(SubCategoryType::class, new SubCategory());
        $form->submit(
            $data
        );
        if (false === $form->isValid()) {
            return new JsonResponse(
                [
                    'status' => 'error',
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

        return  $this->view([
            'status' => 'ok',
        ],
            Response::HTTP_CREATED);
    }

    public function getAction(string $id)
    {
        return $this->view(
            $this->findSubCategoryById($id)
        );
    }

    public function cgetAction()
    {
        return $this->view(
            $this->subCategoryRepository->findAll()
        );
    }

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
                    'errors' => $this->formErrorSerializer->normalize($form),
                ],
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $this->entityManager->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

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
     * @return Category
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    private function findSubCategoryById(string $id)
    {
        $existingSubCategory = $this->subCategoryRepository->find($id);

        if (null === $existingSubCategory) {
            var_dump($id);
            throw new NotFoundHttpException();
        }

        return $existingSubCategory;
    }
}
