<?php

namespace App\Controller;

use App\Entity\Equipment;
use App\Entity\Characteristic;
use App\Form\CharacteristicType;
use App\Repository\CharacteristicRepository;
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
 * Class CharacteristicController
 * @package App\Controller
 *
 * @Rest\RouteResource(
 *     "api/equipment/{equipmentId}/characteristic",
 *     pluralize=false
 * )
 * @SWG\Tag(
 *     name="Characteristic"
 * )
 */
class CharacteristicController extends AbstractFOSRestController implements ClassResourceInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var CharacteristicRepository
     */
    private $characteristicRepository;
    /**
     * @var FormErrorSerializer
     */
    private $formErrorSerializer;

    public function __construct(
        EntityManagerInterface $entityManager,
        CharacteristicRepository $characteristicRepository,
        FormErrorSerializer $formErrorSerializer
    )     {
        $this->entityManager = $entityManager;
        $this->characteristicRepository = $characteristicRepository;
        $this->formErrorSerializer = $formErrorSerializer;
    }

    /**
     * Create a new Characteristic link to an Equipment
     *
     * @SWG\Post(
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *      response=200,
     *      description="Successful operation with the new value insert",
     *      @SWG\Schema(
     *       ref=@Model(type=Characteristic::class)
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
     *     name="The JSON Characteristic",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *       ref=@Model(type=Characteristic::class)
     *     ),
     *     description="The JSon Characteristic"
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
        $data = $this->manageObjectToId($data);
        $equipment = $this->findEquipmentByRequest($request);
        $form = $this->createForm(
            CharacteristicType::class,
            new Characteristic());
        $form->submit(
            $data
        );
        if(isset($data['equipment']) && $data['equipment'] != $equipment->getId()) {
            return new JsonResponse(
                [
                    'status' => 'error',
                    'message' => 'Validation error: request parameters do not correspond with the equipment of the characteristic',
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

        $characteristic = $form->getData();
        if(!$this->isGranted("ROLE_AMBASSADOR")
            || $characteristic->getValidate() === null)
            $characteristic->setValidate(false);
        $characteristic->setCreatedBy($connectUser);
        $characteristic->setEquipment($equipment);
        $this->entityManager->persist($characteristic);
        $this->entityManager->flush();

        return  $this->view(
            $characteristic,
            Response::HTTP_CREATED);
    }

    /**
     * Expose the Characteristic with the id.
     *
     * @SWG\Get(
     *     summary="Get the Characteristic based on its ID and the EquipmentId of the equipment",
     *     produces={"application/json"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return the Characteristic based on ID and the EquipmentId of the equipment",
     *     @SWG\Schema(ref=@Model(type=Characteristic::class))
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="The Equipment based on EquipmentId or the Characteristic based on ID is not found"
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
     *     description="The ID used to find the Characteristic"
     * )
     *
     * @var $id
     * @return \FOS\RestBundle\View\View
     */
    public function getAction($request, string $id)
    {
        $equipment = $this->findEquipmentByRequest($request);
        $characteristic = $this->findCharacteristicById($id);
        if($characteristic->getEquipment()->getId() != $equipment->getId())
            throw new NotFoundHttpException();
        return $this->view(
            $characteristic
        );
    }

    /**
     * Expose all Characteristic
     *
     * @SWG\Get(
     *     summary="Get all Characteristics belong to EquipmentId",
     *     produces={"application/json"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return all the Characteristics",
     *     @SWG\Schema(
     *      type="array",
     *      @SWG\Items(ref=@Model(type=Characteristic::class))
     *     )
     * )
     * @SWG\Parameter(
     *     name="equipmentid",
     *     in="path",
     *     type="string",
     *     description="The ID of the Equipment which Characteristics belong to"
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="The Equipment based on EquipmentId is not found"
     * )
     *
     * @return \FOS\RestBundle\View\View
     */
    public function cgetAction($request)
    {
        $equipment = $this->findEquipmentByRequest($request);
        if($this->isGranted("ROLE_AMBASSADOR"))
            return $this->view(
                $equipment->getCharacteristics()
            );
        $user = $this->getUser();
        $chas = $equipment->getCharacteristics();
        for($i = count($chas) - 1; $i >= 0; $i--) {
            if(!($chas[$i]->getValidate() || $chas[$i]->getCreatedBy() == $user)) {
                $chas->removeCharacteristic($chas[$i]);
            }
        }
        return $this->view($chas);
    }

    /**
     * Update an Characteristic
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
     *     description="The Equipment based on EquipmentId or the Characteristic based on ID is not found"
     *    ),
     *    @SWG\Parameter(
     *     name="The full JSON Characteristic",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *       ref=@Model(type=Characteristic::class)
     *     ),
     *     description="The JSon Characteristic"
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
     *     description="The ID used to find the Characteristic"
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
        $equipment = $this->findEquipmentByRequest($request);
        $existingCharacteristic = $this->findCharacteristicById($id);
        $form = $this->createForm(CharacteristicType::class, $existingCharacteristic);
        $data = json_decode($request->getContent(), true);
        $data = $this->manageObjectToId($data);
        $validate = $existingCharacteristic->getValidate();
        if($equipment->getId() != $request->attributes->get('equipmentid'))
        {
            return new JsonResponse(
                [
                    'status' => 'error',
                    'message' => 'Validation error: request parameters do not correspond with the equipment of the characteristic',
                    'errors' => $this->formErrorSerializer->normalize($form),
                ],
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY
            );
        }
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
        if($existingCharacteristic->getValidate() !== $validate) {
            $this->denyAccessUnlessGranted("ROLE_AMBASSADOR");
        }
        $this->entityManager->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Update a part of an Characteristic
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
     *     description="The Equipment based on EquipmentId or the Characteristic based on ID is not found"
     *    ),
     *    @SWG\Parameter(
     *     name="The full JSON Characteristic",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *       ref=@Model(type=Characteristic::class)
     *     ),
     *     description="The JSon Characteristic"
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
     *     description="The ID used to find the Characteristic"
     *    )
     * )
     *
     * @param Request $request
     * @param string $id of the Characteristic to update
     * @return \FOS\RestBundle\View\View|JsonResponse
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function patchAction(Request $request, string $id)
    {
        $equipment = $this->findEquipmentByRequest($request);
        $existingCharacteristic = $this->findCharacteristicById($id);
        $validate = $existingCharacteristic->getValidate();
        $form = $this->createForm(CharacteristicType::class, $existingCharacteristic);
        $data = $request->request->all();
        $data = $this->manageObjectToId($data);
        if($equipment->getId() != $request->attributes->get('equipmentid'))
        {
            return new JsonResponse(
                [
                    'status' => 'error',
                    'message' => 'Validation error: request parameters do not correspond with the equipment of the characteristic',
                    'errors' => $this->formErrorSerializer->normalize($form),
                ],
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        $form->submit($data, false);
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
        if($existingCharacteristic->getValidate() !== $validate) {
            $this->denyAccessUnlessGranted("ROLE_AMBASSADOR");
        }
        $this->entityManager->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Delete an Characteristic with the id.
     *
     * @SWG\Delete(
     *     summary="Delete an Characteristic based on ID"
     * )
     * @SWG\Response(
     *     response=204,
     *     description="The Characteristic is correctly delete",
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="The Equipement based on EquipmentId or the Characteristic based on ID is not found"
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
     *     description="The ID used to find the Characteristic"
     * )
     * @param string $id
     * @return \FOS\RestBundle\View\View
     */
    public function deleteAction(Request $request, string $id)
    {
        $equipment = $this->findEquipmentByRequest($request);

        $characteristic = $this->findCharacteristicById($id);
        if(count($characteristic->getHaves()) > 1) {
            return new JsonResponse(
                [
                    'status' => 'error',
                    'message' => 'To many users use this equipment'
                ],
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        $this->entityManager->remove($characteristic);
        $this->entityManager->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param string $id
     *
     * @return Characteristic
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    private function findCharacteristicById(string $id)
    {
        $existingCharacteristic = $this->characteristicRepository->find($id);
        if (null === $existingCharacteristic) {
            throw new NotFoundHttpException();
        }
        return $existingCharacteristic;
    }

    /**
     * @param Request $request
     *
     * @return User
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    private function findEquipmentByRequest(Request $request)
    {
        $equipment = $this->entityManager->find(
            Equipment::class,
            $request->attributes->get('equipmentid'));
        if($equipment == null)
            throw new NotFoundHttpException();
        return $equipment;
    }

    private function manageObjectToId($data) {
        if(isset($data['equipment'])) {
            if(isset($data['equipment']['id'])) {
                $data['equipment'] = $data['equipment']['id'];
            } else if (!is_int($data['equipment'])) {
                unset($data['equipment']);
            }
        }
        return $data;
    }
}
