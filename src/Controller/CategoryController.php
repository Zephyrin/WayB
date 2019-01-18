<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
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
 *     "Category",
 *     pluralize=false
 * )
 */
class CategoryController extends FOSRestController implements ClassResourceInterface
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
    )
    {
        $this->entityManager = $entityManager;
        $this->categoryRepository = $categoryRepository;
        $this->formErrorSerializer = $formErrorSerializer;
    }

    public function postAction(Request $request)
    {
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
                    'errors' => $this->formErrorSerializer->normalize($form),
                ],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $this->entityManager->persist($form->getData());
        $this->entityManager->flush();

        return  $this->view([
                    'status' => 'ok',
                ],
            Response::HTTP_CREATED);
    }

    public function getAction(string $id)
    {
        return $this->view(
            $this->findCategoryById($id)
        );
    }

    public function cgetAction()
    {
        return $this->view(
            $this->categoryRepository->findAll()
        );
    }

    public function putAction(Request $request, string $id)
    {
        $existingCategory = $this->findCategoryById($id);

        $form = $this->createForm(CategoryType::class, $existingCategory);

        $form->submit($request->request->all());

        if (false === $form->isValid()) {
            return $this->view($form);
        }

        $this->entityManager->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    public function patchAction(Request $request, string $id)
    {
        $existingCategory = $this->findCategoryById($id);

        $form = $this->createForm(CategoryType::class, $existingCategory);

        $form->submit($request->request->all(), false);

        if (false === $form->isValid()) {
            return $this->view($form);
//            return new JsonResponse(
//                [
//                    'status' => 'error',
//                    'errors' => $this->formErrorSerializer->convertFormToArray($form),
//                ],
//                JsonResponse::HTTP_BAD_REQUEST
//            );
        }

        $this->entityManager->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    public function deleteAction(string $id)
    {
        $album = $this->findCategoryById($id);

        $this->entityManager->remove($album);
        $this->entityManager->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
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
            var_dump($id);
            throw new NotFoundHttpException();
        }

        return $existingCategory;
    }
}
