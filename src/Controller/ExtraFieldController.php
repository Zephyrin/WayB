<?php

namespace App\Controller;

use App\Entity\Equipment;
use App\Entity\ExtraField;
use App\Form\ExtraFieldType;
use App\Repository\ExtraFieldRepository;
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

/**
 * Class ExtraFieldController
 * @package App\Controller
 *
 * @Rest\RouteResource(
 *     "api/equipment/{equipmentId}/extraField",
 *     pluralize=false
 * )
 * @SWG\Tag(
 *     name="ExtraField"
 * )
 */
class ExtraFieldController extends AbstractFOSRestController implements ClassResourceInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var ExtraFieldRepository
     */
    private $extraFieldRepository;
    /**
     * @var FormErrorSerializer
     */
    private $formErrorSerializer;

    public function __construct(
        EntityManagerInterface $entityManager,
        ExtraFieldRepository $extraFieldRepository,
        FormErrorSerializer $formErrorSerializer
    )     {
        $this->entityManager = $entityManager;
        $this->extraFieldRepository = $extraFieldRepository;
        $this->formErrorSerializer = $formErrorSerializer;
    }

    /**
     * Create a new ExtraField link to an Equipment
     *
     * @SWG\Post(
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *      response=200,
     *      description="Successful operation with the new value insert",
     *      @SWG\Schema(
     *       ref=@Model(type=ExtraField::class)
     *      )
     *    ),
     *    @SWG\Response(
     *     response=422,
     *     description="The form is not correct<BR/>
     * See the corresponding JSON error to see which field is not correct"
     *    )
     *    ,
     *    @SWG\Parameter(
     *     name="equipmentid",
     *     in="path",
     *     type="string",
     *     required=true,
     *     allowEmptyValue=false,
     *     description="The ID used to find the Equipment"
     *    ),
     *    @SWG\Parameter(
     *     name="The JSON ExtraField",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *       ref=@Model(type=ExtraField::class)
     *     ),
     *     description="The JSon ExtraField"
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
        $data = json_decode(
            $request->getContent(),
            true
        );
        $form = $this->createForm(
            ExtraFieldType::class,
            new ExtraField());
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

        $extraField = $form->getData();
        $equipment = $this->entityManager->find(
            Equipment::class,
            $request->attributes->get('equipmentid'));
        if($equipment == null)
            throw new NotFoundHttpException();
        $extraField->setEquipment($equipment);
        $this->entityManager->persist($extraField);
        $this->entityManager->flush();

        return  $this->view(
            $extraField,
            Response::HTTP_CREATED);
    }

    /**
     * Expose the ExtraField with the id.
     *
     * @SWG\Get(
     *     summary="Get the ExtraField based on its ID and the EquipmentId of the equipment",
     *     produces={"application/json"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return the ExtraField based on ID and the EquipmentId of the equipment",
     *     @SWG\Schema(ref=@Model(type=ExtraField::class))
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="The Equipment based on EquipmentId or the ExtraField based on ID is not found"
     * )
     *
     * @SWG\Parameter(
     *     name="equipmentid",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the Equipment"
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the ExtraField"
     * )
     *
     * @var $id
     * @return \FOS\RestBundle\View\View
     */
    public function getAction(string $id)
    {
        return $this->view(
            $this->findExtraFieldById($id)
        );
    }

    /**
     * Expose all ExtraField
     *
     * @SWG\Get(
     *     summary="Get all ExtraFields belong to EquipmentId",
     *     produces={"application/json"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return all the ExtraFields",
     *     @SWG\Schema(
     *      type="array",
     *      @SWG\Items(ref=@Model(type=ExtraField::class))
     *     )
     * )
     * @SWG\Parameter(
     *     name="equipmentid",
     *     in="path",
     *     type="string",
     *     description="The ID of the Equipment which ExtraFields belong to"
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="The Equipment based on EquipmentId is not found"
     * )
     *
     * @return \FOS\RestBundle\View\View
     */
    public function cgetAction()
    {
        return $this->view(
            $this->extraFieldRepository->findAll()
        );
    }

    /**
     * Update an ExtraField
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
     *     description="The Equipment based on EquipmentId or the ExtraField based on ID is not found"
     *    ),
     *    @SWG\Parameter(
     *     name="The full JSON ExtraField",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *       ref=@Model(type=ExtraField::class)
     *     ),
     *     description="The JSon ExtraField"
     *    ),
     *    @SWG\Parameter(
     *     name="equipmentid",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the Equipment"
     *    ),
     *    @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the ExtraField"
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
        $existingExtraField = $this->findExtraFieldById($id);
        $form = $this->createForm(ExtraFieldType::class, $existingExtraField);
        $data = json_decode($request->getContent(), true);

        $data['equipment'] = $request->attributes->get('equipmentid');
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
     * Update a part of an ExtraField
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
     *     description="The Equipment based on EquipmentId or the ExtraField based on ID is not found"
     *    ),
     *    @SWG\Parameter(
     *     name="The full JSON ExtraField",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *       ref=@Model(type=ExtraField::class)
     *     ),
     *     description="The JSon ExtraField"
     *    ),
     *    @SWG\Parameter(
     *     name="equipmentid",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the Equipment"
     *    ),
     *    @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the ExtraField"
     *    )
     * )
     *
     * @param Request $request
     * @param string $id of the ExtraField to update
     * @return \FOS\RestBundle\View\View|JsonResponse
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function patchAction(Request $request, string $id)
    {
        $existingExtraField = $this->findExtraFieldById($id);
        $form = $this->createForm(ExtraFieldType::class, $existingExtraField);

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
     * Delete an ExtraField with the id.
     *
     * @SWG\Delete(
     *     summary="Delete an ExtraField based on ID"
     * )
     * @SWG\Response(
     *     response=204,
     *     description="The ExtraField is correctly delete",
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="The Equipement based on EquipmentId or the ExtraField based on ID is not found"
     * )
     *
     * @SWG\Parameter(
     *     name="equipmentid",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the Equipment"
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the ExtraField"
     * )
     * @param string $id
     * @return \FOS\RestBundle\View\View
     */
    public function deleteAction(string $id)
    {
        $extraField = $this->findExtraFieldById($id);

        $this->entityManager->remove($extraField);
        $this->entityManager->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param string $id
     *
     * @return ExtraField
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    private function findExtraFieldById(string $id)
    {
        $existingExtraField = $this->extraFieldRepository->find($id);
        if (null === $existingExtraField) {
            throw new NotFoundHttpException();
        }
        return $existingExtraField;
    }
}
