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

/**
 * @Rest\RouteResource(
 *     "Category/{CategoryId}/SubCategory/{SubCategoryId}/ExtraFieldDef",
 *     pluralize=false
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

    public function getAction(string $id)
    {
        return $this->view(
            $this->findExtraFieldDefById($id)
        );
    }

    public function cgetAction()
    {
        return $this->view(
            $this->extraFieldDefRepository->findAll()
        );
    }

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
