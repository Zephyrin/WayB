<?php

namespace App\Controller;

use App\Entity\Backpack;
use App\Entity\Category;
use App\Entity\IntoBackpack;
use App\Entity\User;
use App\Form\IntoBackpackType;
use App\Repository\BackpackRepository;
use App\Repository\CategoryRepository;
use App\Repository\HaveRepository;
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
 *     "api/user/{userId}/backpack/{backpackId}/intoBackpack",
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
     * @var IntoBackpackRepository
     */
    private $intoBackpackRepository;

    /**
     * @var BackpackRepository
     */
    private $backpackRepository;
    /**
     * @var HaveRepository
     */
    private $haveRepository;

    /**
     * @var FormErrorSerializer
     */
    private $formErrorSerializer;

    public function __construct(
        EntityManagerInterface $entityManager,
        IntoBackpackRepository $intoBackpackRepository,
        HaveRepository $haveRepository,
        BackpackRepository $backpackRepository,
        FormErrorSerializer $formErrorSerializer
    )
    {
        $this->entityManager = $entityManager;
        $this->intoBackpackRepository = $intoBackpackRepository;
        $this->haveRepository = $haveRepository;
        $this->backpackRepository = $backpackRepository;
        $this->formErrorSerializer = $formErrorSerializer;
    }

    /**
     * Create a new IntoBackPack link to a Backpack. 
     * An IntoBackPack is an equipment that the user have or wish.
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
     *     name="userid",
     *     in="path",
     *     type="string",
     *     required=true,
     *     allowEmptyValue=false,
     *     description="The ID used to find the User and verify if the 
     * connected user is the same as the one of the backpack."
     *    ),
     *    @SWG\Parameter(
     *     name="The JSON IntoBackpack",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *       ref=@Model(type=IntoBackpack::class)
     *     ),
     *     description="The JSon IntoBackpack"
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
        if($connectUser->getId() != $request->attributes->get('userid')) {
            return new JsonResponse(
                [
                    'status' => 'error',
                    'message' => 'You are not allowed to change an equipment for other user',
                ],
                JsonResponse::HTTP_FORBIDDEN
            );
        }
        $backpack = $this->findBackpackByRequest($request, $connectUser);
        $data = json_decode(
            $request->getContent(),
            true
        );
        $data = $this->manageObjectToId($data);
        $this->findHaveById($data);
        
        $form = $this->createForm(
            IntoBackpackType::class,
            new IntoBackpack());
        $form->submit(
            $data
        );

        if(isset($data['backpack']) && $data['backpack'] != $backpack->getId()) {
            return new JsonResponse(
                [
                    'status' => 'error',
                    'message' => 'Validation error request parameters do not correspond with the backpack of the into-backpack',
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

        $into = $form->getData();
        $into->setBackpack($backpack);
        $this->entityManager->persist($into);
        $this->entityManager->flush();

        return  $this->view(
            $into,
            Response::HTTP_CREATED);
    }

    /**
     * Expose the IntoBackpack with the id.
     *
     * @SWG\Get(
     *     summary="Get the IntoBackpack based on its ID, the Id of the backpack and the one of the user",
     *     produces={"application/json"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return the IntoBackpack based on ID.",
     *     @SWG\Schema(ref=@Model(type=IntoBackpack::class))
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="The Backpack based on BackpackId or the User based on userId or the IntoBackpack is not found"
     * )
     *
     * @SWG\Parameter(
     *     name="userid",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the user"
     * )
     * @SWG\Parameter(
     *     name="backpackid",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the backpack"
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the IntoBackpack"
     * )
     *
     * @var $id
     * @return \FOS\RestBundle\View\View
     */
    public function getAction(Request $request, string $id)
    {
        $user = $this->findUserByRequest($request);
        $backpack = $this->findBackpackByRequest($request, $user);
        $intoBackpack = $this->findIntoBackpackById($id, $backpack);
        if($intoBackpack->getBackpack()->getId() == $backpack->getId())
            throw new NotFoundHttpException();
        return $this->view($intoBackpack);
    }

    /**
     * Expose all IntoBackpacks of a Backpack.
     *
     * @SWG\Get(
     *     summary="Get all IntoBackpacks belong to BackpackId",
     *     produces={"application/json"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return all the IntoBackpacks",
     *     @SWG\Schema(
     *      type="array",
     *      @SWG\Items(ref=@Model(type=IntoBackpack::class))
     *     )
     * )
     * 
     * @SWG\Parameter(
     *     name="userid",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the user"
     * )
     * @SWG\Parameter(
     *     name="backpackid",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the backpack"
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="The Backpack based on BackpackId or the User based on UserId is not found"
     * )
     *
     * @return \FOS\RestBundle\View\View
     */
    public function cgetAction(Request $request)
    {
        $user = $this->findUserByRequest($request);
        $backpack = $this->findBackpackByRequest($request, $user);
        return $this->view($backpack->getIntoBackpacks());
    }

    /**
     * Update an IntoBackpack
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
     *     description="The User based on UserId or the Backpack based on BackpackId or the IntoBackpack is not found"
     *    ),
     *    @SWG\Parameter(
     *     name="The full JSON IntoBackpack",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *       ref=@Model(type=IntoBackpack::class)
     *     ),
     *     description="The JSon IntoBackpack"
     *    ),
     *    @SWG\Parameter(
     *     name="userid",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the user"
     *    ),
     *    @SWG\Parameter(
     *     name="backpackid",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the backpack"
     *    ),
     *    @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the IntoBackpack"
     *   )
     * )
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
     *     description="The User based on UserId or the Backpack based on BackpackId or the IntoBackpack is not found"
     *    ),
     *    @SWG\Parameter(
     *     name="The JSON IntoBackpack",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *       ref=@Model(type=IntoBackpack::class)
     *     ),
     *     description="The JSon IntoBackpack"
     *    ),
     *    @SWG\Parameter(
     *     name="userid",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the user"
     *    ),
     *    @SWG\Parameter(
     *     name="backpackid",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the backpack"
     *    ),
     *    @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the IntoBackpack"
     *    )
     * )
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
        $user = $this->findUserByRequest($request);
        if($user->getId() != $this->getUser()->getId()) {
            return new JsonResponse(
                [
                    'status' => 'error',
                    'message' => 'You are not allowed to change an equipment for other user',
                ],
                JsonResponse::HTTP_FORBIDDEN
            );
        }
        $backpack = $this->findBackpackByRequest($request, $user);
        $existingIntoBackpack = $this->findIntoBackpackById($id, $backpack);
        $form = $this->createForm(IntoBackpackType::class, $existingIntoBackpack);
        $data = json_decode($request->getContent(), true);
        $data = $this->manageObjectToId($data);

        $form->submit($data, $clearMissing);
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
     * Delete an IntoBackpack with the id.
     *
     * @SWG\Delete(
     *     summary="Delete an IntoBackpack based on its ID"
     * )
     * @SWG\Response(
     *     response=204,
     *     description="The IntoBackpack is correctly delete",
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="The User based on UserId or backpack based on BackpackId or IntoBackpack based on ID is not found"
     * )
     *
     * @SWG\Parameter(
     *     name="userid",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the user"
     * )
     * @SWG\Parameter(
     *     name="backpackid",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the backpack"
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the IntoBackpack"
     * )
     * 
     * @param string $id
     * @return \FOS\RestBundle\View\View
     */
    public function deleteAction(Request $request, string $id)
    {
        $user = $this->findUserByRequest($request);
        if($user->getId() != $this->getUser()->getId()) {
            return new JsonResponse(
                [
                    'status' => 'error',
                    'message' => 'You are not allowed to change an equipment for other user',
                ],
                JsonResponse::HTTP_FORBIDDEN
            );
        }
        $backpack = $this->findBackpackByRequest($request, $user);
        $intoBackpack = $this->findIntoBackpackById($id, $backpack);
        
        $this->entityManager->remove($intoBackpack);
        $this->entityManager->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param string $id
     * @param Backpack $backpack
     *
     * @return IntoBackpack
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    private function findIntoBackpackById(string $id, Backpack $backpack)
    {
        foreach($backpack->getIntoBackpacks() as $into) {
            if($into->getId() == $id) return $into;
        }
        throw new NotFoundHttpException();
    }

    /**
     * @param Request $request
     *
     * @return Category
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    private function findBackpackByRequest(Request $request, User $user): Backpack
    {
        $backpack = $this->backpackRepository->findByIdAndCreatedBy(
            $request->attributes->get('backpackid'), $user);
        if($backpack == null)
            throw new NotFoundHttpException();
        return $backpack;
    }

    private function findHaveById($data) {
        $have = null;
        if (isset($data['equipment'])) {
            $have = $this->haveRepository->findById($data['equipment']);
        }
        
        if($have == null) throw new NotFoundHttpException("Equipment not found.");
        return $have;
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

    private function manageObjectToId($data) {
        if(isset($data['backpack'])) {
            if(isset($data['backpack']['id'])) {
                $data['backpack'] = $data['backpack']['id'];
            } else if (!is_int($data['backpack'])) {
                unset($data['backpack']);
            }
        }
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
