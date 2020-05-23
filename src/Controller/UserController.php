<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Model\UserManagerInterface;
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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Rest\RouteResource(
 *     "api/user",
 *     pluralize=false
 * )
 * @SWG\Tag(
 *     name="User"
 * )
 */
class UserController extends AbstractFOSRestController implements ClassResourceInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var FormErrorSerializer
     */
    private $formErrorSerializer;

    /**
     * @var FosUserRepository
     */
    private $userRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserManagerInterface $userRepository,
        FormErrorSerializer $formErrorSerializer
    )
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->formErrorSerializer = $formErrorSerializer;
    }

    /**
     * Update a part of an User
     *
     * All missing attribute will not be update.
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
     *     description="The User based on ID is not found"
     *    ),
     *    @SWG\Parameter(
     *     name="A part of a JSON User",
     *     in="body",
     *     required=true,
     *     @SWG\Property(
     *      type="string"
     *     ),
     *     description="A part of a JSon User"
     *    ),
     *    @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the User"
     *    )
     * )
     *
     * @Security("has_role('ROLE_ADMIN')")
     * @param Request $request
     * @param string $id of the User to update
     * @return \FOS\RestBundle\View\View|JsonResponse
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function patchAction(Request $request, string $id)
    {
        $existingUser = $this->findUserById($id);

        $form = $this->createForm(UserType::class, $existingUser);
        /* $form->handleRequest($request, false); */
        /* $form->submit(
            $request
            , false); */
        $partialUser = $request->request->all();
        if(array_key_exists("roles", $partialUser)){
            $roles = $existingUser->getRoles();
            foreach($roles as $role) {
                $existingUser->removeRole($role);
            }
            foreach($partialUser["roles"] as $role) {
                $existingUser->addRole($role);
            }
        }

        $form->submit($request->request->all(), false);
        if (/*!$form->isSubmitted() ||*/ false === $form->isValid()) {
            return new JsonResponse(
                [
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $this->formErrorSerializer->normalize($form),
                ],
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $this->entityManager->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param string $id
     *
     * @return User
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    private function findUserById(string $id)
    {
        $existingUser = $this->entityManager->find(
            User::class,
            $id
        );

        if (null === $existingUser) {
            throw new NotFoundHttpException();
        }

        return $existingUser;
    }

    /** 
     * @param Request $request
     * @param string username
     * 
     * @return User
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function getAction(Request $request, string $username)
    {
        $existingUser = $this->userRepository->findUserByUsernameOrEmail(
            $username
        );

        if (null == $existingUser) {
            throw new NotFoundHttpException();
        }
        $existingUser->setPassword("");
        return $existingUser;
    }

    /**
     * Expose all Users
     *
     * @SWG\Get(
     *     summary="Get all Users",
     *     produces={"application/json"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return all the Users",
     *     @SWG\Schema(
     *      type="string"
     *     )
     * )
     *
     *
     * @return \FOS\RestBundle\View\View
     */
    public function cgetAction()
    {
        return $this->view(
            $this->userRepository->findUsers()    
        );
    }
}
