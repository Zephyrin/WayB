<?php

namespace App\Controller;

use App\Controller\Helpers\HelperController;
use App\Entity\Backpack;
use App\Entity\User;
use App\Form\BackpackType;
use App\Repository\BackpackRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Serializer\FormErrorSerializer;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class BackpackController
 * @package App\Controller
 *
 * @Route("api/user/{userId}/backpack")
 * @SWG\Tag(
 *     name="Backpack"
 * )
 */
class BackpackController extends AbstractFOSRestController
{
    use HelperController;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var backpackRepository
     */
    private $backpackRepository;
    /**
     * @var FormErrorSerializer
     */
    private $formErrorSerializer;

    public function __construct(
        EntityManagerInterface $entityManager,
        BackpackRepository $backpackRepository,
        FormErrorSerializer $formErrorSerializer
    ) {
        $this->entityManager = $entityManager;
        $this->backpackRepository = $backpackRepository;
        $this->formErrorSerializer = $formErrorSerializer;
    }

    /**
     * Create a new link between an Equipment and an User using Backpack
     *
     * @SWG\Post(
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *      response=200,
     *      description="Successful operation with the new value insert",
     *      @SWG\Schema(
     *       ref=@Model(type=Backpack::class)
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
     *       ref=@Model(type=Backpack::class)
     *     ),
     *     description="The JSon Characteristic"
     *    )
     *
     * )
     *
     * @param Request $request
     * @return View|JsonResponse
     * @throws ExceptionInterface
     */
    public function postAction(Request $request)
    {
        $user = $this->findUserByRequest($request);
        $login_user = $this->getUser();
        if ($user !== $login_user) {
            $this->denyAccessUnlessGranted(
                "ROLE_ADMIN",
                null,
                "You cannot link an equipment for other user"
            );
        }
        $data = json_decode($request->getContent(), true);
        //$data = $this->manageObjectToId($data);
        $form = $this->createForm(
            BackpackType::class,
            new Backpack()
        );
        $form->submit($data);
        $validation = $this->validationError($form, $this);
        if ($validation instanceof JsonResponse)
            return $validation;

        $backpack = $form->getData();
        $backpack->setCreatedBy($user);
        $this->entityManager->persist($backpack);
        $this->entityManager->flush();

        return  $this->view(
            $backpack,
            Response::HTTP_CREATED
        );
    }

    /**
     * Expose the User Equipment using Backpack and the ID.
     *
     * @SWG\Get(
     *     summary="Get the Equipment based on its ID",
     *     produces={"application/json"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return the Backpack Entity based on ID",
     *     @SWG\Schema(ref=@Model(type=Backpack::class))
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
     * @param Request $request
     * @param string $id
     * @return View
     */
    public function getAction(Request $request, string $id)
    {
        $user = $this->findUserByRequest($request);

        return $this->view(
            $this->findBackpackById($id, $user)
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
     *      @SWG\Items(ref=@Model(type=Backpack::class))
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
     * @QueryParam(name="page"
     * , requirements="\d+"
     * , default="1"
     * , description="Page of the overview.")
     * @QueryParam(name="limit"
     * , requirements="\d+"
     * , default="10"
     * , description="Item count limit")
     * @QueryParam(name="sort"
     * , requirements="(asc|desc)"
     * , allowBlank=false
     * , default="asc"
     * , description="Sort direction")
     * @QueryParam(name="sortBy"
     * , requirements="(name|id)"
     * , default="name"
     * , description="Sort by name or uri")
     * @QueryParam(name="search"
     * , nullable=true
     * , description="Search on name and uri")
     *
     * @param Request $request
     * @param ParamFetcher $paramFetcher
     * @return View
     */
    public function cgetAction(Request $request, ParamFetcher $paramFetcher)
    {
        $page = $paramFetcher->get('page');
        $limit = $paramFetcher->get('limit');
        $sort = $paramFetcher->get('sort');
        $sortBy = $paramFetcher->get('sortBy');
        $search = $paramFetcher->get('search');
        $user = $this->findUserByRequest($request);
        $count = $this->backpackRepository->findByUser(
            $user,
            $page,
            $limit,
            $sort,
            $sortBy,
            $search
        );
        return $this->setPaginateToView($count, $this);
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
     *     description="The User based on UserId or the Backpack based on ID is not found"
     *    ),
     *    @SWG\Parameter(
     *     name="The full JSON Backpack",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *       ref=@Model(type=Backpack::class)
     *     ),
     *     description="The JSon Backpack"
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
     *     description="The ID used to find the Backpack"
     *    )
     * )
     *
     * @param Request $request
     * @param string $id of the Category to update
     * @return View|JsonResponse
     * @throws ExceptionInterface
     */
    public function putAction(Request $request, string $id)
    {
        return $this->putOrPatch($request, $id, true);
    }

    /**
     * Update a part of a User Equipment own information Backpack
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
     *     description="The User based on UserId or the Backpack based on ID is not found"
     *    ),
     *    @SWG\Response(
     *     response=403,
     *     description="You are not allowed to update a Backpack of an another user"
     *    ),
     *    @SWG\Parameter(
     *     name="The full JSON Backpack",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *       ref=@Model(type=Backpack::class)
     *     ),
     *     description="The JSon Backpack"
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
     *     description="The ID used to find the Backpack"
     *    )
     * )
     *
     * @param Request $request
     * @param string $id of the Backpack to update
     * @return View|JsonResponse
     * @throws ExceptionInterface
     */
    public function patchAction(Request $request, string $id)
    {
        return $this->putOrPatch($request, $id, false);
    }

    /**
     * @see putAction
     * @see patchAction
     * @param Request $request
     * @param string $id
     * @param bool $isPut
     * @return View|JsonResponse
     */
    private function putOrPatch(Request $request, string $id, bool $isPut)
    {
        $user = $this->findUserByRequest($request);
        $login_user = $this->getUser();
        if ($user !== $login_user) {
            return new JsonResponse(
                [
                    'status' => 'error',
                    'message' => 'You are not allowed to change for other user',
                ],
                JsonResponse::HTTP_FORBIDDEN
            );
        }
        $existingBackpack = $this->findBackpackById($id, $user);
        $form = $this->createForm(BackpackType::class, $existingBackpack);
        $data = json_decode($request->getContent(), true);
        $data = $this->manageObjectToId($data);
        $form->submit($data, $isPut);
        $validation = $this->validationError($form, $this);
        if ($validation instanceof JsonResponse)
            return $validation;

        $this->entityManager->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }
    /**
     * Delete an Backpack with the id.
     *
     * @SWG\Delete(
     *     summary="Delete an Backpack based on ID"
     * )
     * @SWG\Response(
     *     response=204,
     *     description="The Backpack is correctly delete",
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="The User based on UserId or the Backpack based on ID is not found"
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
     *     description="The ID used to find the Backpack"
     * )
     * @param string $id
     * @param Request $request
     * @return View|JsonResponse
     */
    public function deleteAction(Request $request, string $id)
    {
        $user = $this->findUserByRequest($request);
        $login_user = $this->getUser();
        if ($user !== $login_user) {
            return new JsonResponse(
                [
                    'status' => 'error',
                    'message' => 'You are not allowed to delete for other user',
                ],
                JsonResponse::HTTP_FORBIDDEN
            );
        }
        $this->findUserByRequest($request);
        $backpack = $this->findBackpackById($id, $user);
        $this->entityManager->remove($backpack);
        $this->entityManager->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param string $id
     *
     * @param User $user
     * @return Backpack
     */
    private function findBackpackById(string $id, User $user)
    {
        $existingBackpack = $this->backpackRepository->findByIdAndCreatedBy($id, $user);
        if (null === $existingBackpack) {
            throw new NotFoundHttpException();
        }
        return $existingBackpack;
    }

    private function manageObjectToId($data)
    {
        if (isset($data['characteristic'])) {
            if (isset($data['characteristic']['id'])) {
                $data['characteristic'] = $data['characteristic']['id'];
            } else if (!is_int($data['characteristic'])) {
                unset($data['characteristic']);
            }
        }
        if (isset($data['equipment'])) {
            if (isset($data['equipment']['id'])) {
                $data['equipment'] = $data['equipment']['id'];
            }
        }
        return $data;
    }
    /**
     * @param Request $request
     *
     * @return User
     * @throws NotFoundHttpException
     */
    private function findUserByRequest(Request $request)
    {
        $user = $this->entityManager->find(
            User::class,
            $request->attributes->get('userid')
        );
        if ($user == null)
            throw new NotFoundHttpException();
        return $user;
    }
}
