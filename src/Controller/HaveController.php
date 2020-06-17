<?php

namespace App\Controller;

use App\Entity\Have;
use App\Entity\User;
use App\Form\HaveType;
use App\Repository\HaveRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Serializer\FormErrorSerializer;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Swagger\Annotations as SWG;

/**
 * Class HaveController
 * @package App\Controller
 *
 * @Rest\RouteResource(
 *     "api/user/{userId}/have",
 *     pluralize=false
 * )
 * @SWG\Tag(
 *     name="User has"
 * )
 */use Nelmio\ApiDocBundle\Annotation\Model;
class HaveController extends AbstractFOSRestController implements ClassResourceInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var haveRepository
     */
    private $haveRepository;
    /**
     * @var FormErrorSerializer
     */
    private $formErrorSerializer;

    public function __construct(
        EntityManagerInterface $entityManager,
        HaveRepository $haveRepository,
        FormErrorSerializer $formErrorSerializer
    )
    {
        $this->entityManager = $entityManager;
        $this->haveRepository = $haveRepository;
        $this->formErrorSerializer = $formErrorSerializer;
    }

    /**
     * Create a new link between an Equipment and an User using Have
     *
     * @SWG\Post(
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *      response=200,
     *      description="Successful operation with the new value insert",
     *      @SWG\Schema(
     *       ref=@Model(type=Have::class)
     *      )
     *    ),
     *    @SWG\Response(
     *     response=422,
     *     description="The form is not correct<BR/>
     * See the corresponding JSON error to see which field is not correct"
     *    ),
     *    @SWG\Response(
     *     response=403,
     *     description="You are not allow to create a link for an another user"
     *    ),
     *    @SWG\Parameter(
     *     name="userId",
     *     in="path",
     *     type="string",
     *     required=true,
     *     allowEmptyValue=false,
     *     description="The ID used to find the User"
     *    ),
     *    @SWG\Parameter(
     *     name="The JSON Characteristic",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *       ref=@Model(type=Have::class)
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
        $user = $this->findUserByRequest($request);
        $login_user = $this->getUser();
        if($user !== $login_user) {
            $this->denyAccessUnlessGranted("ROLE_ADMIN"
                , null
                , "You cannot link an equipment for other user");
        }
        $data = json_decode(
            $request->getContent(),
            true
        );
        $data = $this->manageObjectToId($data);
        $form = $this->createForm(
            HaveType::class,
            new Have());
        $form->submit(
            $data
        );
        if (false === $form->isValid()) {
            return new JsonResponse(
                [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'errors' => $this->formErrorSerializer->normalize($form),
                    'data' => $data
                ],
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $have = $form->getData();
        $have->setUser($user);
        $this->entityManager->persist($have);
        $this->entityManager->flush();

        return  $this->view(
            $have,
            Response::HTTP_CREATED);
    }

    /**
     * Expose the User Equipment using Have and the ID.
     *
     * @SWG\Get(
     *     summary="Get the Equipment based on its ID",
     *     produces={"application/json"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return the Have Entity based on ID",
     *     @SWG\Schema(ref=@Model(type=Have::class))
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="The user does not has any information this equipment based on ID"
     * )
     *
     * @SWG\Parameter(
     *     name="userid",
     *     in="path",
     *     type="string",
     *     description="The user ID used to find the Equipment"
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the information about the equipment"
     * )
     *
     *
     * @param string $id
     * @param User $user
     * @return \FOS\RestBundle\View\View
     */
    public function getAction(string $id, User $user)
    {
        return $this->view(
            $this->findHaveById($id)
        );
    }

    /**
     * Expose all Equipments and their information
     *
     * @SWG\Get(
     *     summary="Get all Equipments belong to User",
     *     produces={"application/json"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return all the Equipments and their user information",
     *     @SWG\Schema(
     *      type="array",
     *      @SWG\Items(ref=@Model(type=Have::class))
     *     )
     * )
     * @SWG\Parameter(
     *     name="userid",
     *     in="path",
     *     type="string",
     *     description="The ID of the User"
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="The User based on UserId is not found"
     * )
     *
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function cgetAction(Request $request)
    {
        $user = $this->findUserByRequest($request);
        return $this->view(
            $this->haveRepository->findAllOfUser($user)
        );
    }

    /**
     * Update an the Quantity or other field of an Equipment
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
     *     description="The User based on UserId or the Have based on ID is not found"
     *    ),
     *    @SWG\Parameter(
     *     name="The full JSON Have",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *       ref=@Model(type=Have::class)
     *     ),
     *     description="The JSon Have"
     *    ),
     *    @SWG\Parameter(
     *     name="userid",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the user"
     *    ),
     *    @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the Have"
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
        $user = $this->findUserByRequest($request);
        $login_user = $this->getUser();
        if($user !== $login_user) {
            return new JsonResponse(
                [
                    'status' => 'error',
                    'message' => 'You are not allowed to change for other user',
                ],
                JsonResponse::HTTP_FORBIDDEN
            );
        }
        $existingHaveField = $this->findHaveById($id);
        $form = $this->createForm(HaveType::class, $existingHaveField);
        $data = json_decode($request->getContent(), true);
        $data = $this->manageObjectToId($data);
        $data['user'] = $request->attributes->get('userid');
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
     * Update a part of a User Equipment own information Have
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
     *     description="The User based on UserId or the Have based on ID is not found"
     *    ),
     *    @SWG\Response(
     *     response=403,
     *     description="You are not allowed to update a Have of an another user"
     *    ),
     *    @SWG\Parameter(
     *     name="The full JSON Have",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *       ref=@Model(type=Have::class)
     *     ),
     *     description="The JSon Have"
     *    ),
     *    @SWG\Parameter(
     *     name="userid",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the User"
     *    ),
     *    @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the Have"
     *    )
     * )
     *
     * @param Request $request
     * @param string $id of the Have to update
     * @return \FOS\RestBundle\View\View|JsonResponse
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function patchAction(Request $request, string $id)
    {
        $user = $this->findUserByRequest($request);
        $login_user = $this->getUser();
        if($user !== $login_user) {
            return new JsonResponse(
                [
                    'status' => 'error',
                    'message' => 'You are not allowed to change for other user',
                ],
                JsonResponse::HTTP_FORBIDDEN
            );
        }
        $existingHave = $this->findHaveById($id);
        $equipment = $existingHave->getEquipment();
        $form = $this->createForm(HaveType::class, $existingHave);
        $data = json_decode($request->getContent(), true);
        $data = $this->manageObjectToId($data);
        $form->submit($data, false);
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

    /**
     * Delete an Have with the id.
     *
     * @SWG\Delete(
     *     summary="Delete an Have based on ID"
     * )
     * @SWG\Response(
     *     response=204,
     *     description="The Have is correctly delete",
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="The User based on UserId or the Have based on ID is not found"
     * )
     *
     * @SWG\Parameter(
     *     name="userid",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the User"
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the Have"
     * )
     * @param string $id
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function deleteAction(Request $request, string $id)
    {
        $user = $this->findUserByRequest($request);
        $login_user = $this->getUser();
        if($user !== $login_user) {
            return new JsonResponse(
                [
                    'status' => 'error',
                    'message' => 'You are not allowed to change for other user',
                ],
                JsonResponse::HTTP_FORBIDDEN
            );
        }
        $have = $this->findHaveById($id);
        $this->entityManager->remove($have);
        $this->entityManager->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param string $id
     *
     * @return Have
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    private function findHaveById(string $id)
    {
        $existingHave = $this->haveRepository->find($id);
        if (null === $existingHave) {
            throw new NotFoundHttpException();
        }
        return $existingHave;
    }

    private function manageObjectToId($data) {
        if(isset($data['characteristic'])) {
            if(isset($data['characteristic']['id'])) {
                $data['characteristic'] = $data['characteristic']['id'];
            } else if (!is_int($data['characteristic'])) {
                unset($data['characteristic']);
            }
        }
        if(isset($data['equipment'])) {
            if(isset($data['equipment']['id'])) {
                $data['equipment'] = $data['equipment']['id'];
            }
        }
        return $data;
    }
    /**
     * @param Request $request
     *
     * @return User
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    private function findUserByRequest(Request $request)
    {
        $user = $this->entityManager->find(
            User::class,
            $request->attributes->get('userid'));
        if($user == null)
            throw new NotFoundHttpException();
        return $user;
    }
}
