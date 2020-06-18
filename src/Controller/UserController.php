<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Serializer\FormErrorSerializer;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Swagger\Annotations as SWG;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

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
   * @var UserRepository
   */
  private $userRepository;

  public function __construct(
    EntityManagerInterface $entityManager,
    UserRepository $userRepository,
    FormErrorSerializer $formErrorSerializer
  ) {
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
   * @param Request $request
   * @param string $id of the User to update
   * @return View|JsonResponse
   * @throws ExceptionInterface
   */
  public function patchAction(Request $request, string $id)
  {
    $existingUser = $this->findUserById($id);
    $connectUser = $this->getUser();
    if ($existingUser != $connectUser)
      $this->denyAccessUnlessGranted("ROLE_ADMIN");
    $form = $this->createForm(UserType::class, $existingUser);
    /* $form->handleRequest($request, false); */
    /* $form->submit(
            $request
            , false); */
    $partialUser = $request->request->all();
    if (array_key_exists("roles", $partialUser)) {
      if ($partialUser["roles"] != $existingUser->getRoles())
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
    }

    $form->submit($request->request->all(), false);
    if (/*!$form->isSubmitted() ||*/false === $form->isValid()) {
      return new JsonResponse(
        [
          'status' => 'error',
          'message' => 'Validation failed',
          'errors' => $this->formErrorSerializer->normalize($form),
        ],
        JsonResponse::HTTP_UNPROCESSABLE_ENTITY
      );
    }
    $user = $form->getData();
    if (array_key_exists("password", $partialUser)) {
      $user->setPassword($this->passwordEncoder->encodePassword(
        $user,
        $user->getPassword()
      ));
    }
    if (array_key_exists("roles", $partialUser)) {
      foreach ($partialUser["roles"] as $role) {
        $user->setRoles($partialUser["roles"]);
      }
    }
    $this->entityManager->flush();

    return $this->view(null, Response::HTTP_NO_CONTENT);
  }

  /**
   * @param string $id
   *
   * @return User
   * @throws NotFoundHttpException
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
   * @throws NotFoundHttpException
   */
  public function getAction(Request $request, string $username)
  {

    $existingUser = $this->userRepository->findUserByUsernameOrEmail(
      $username
    );

    if (null == $existingUser) {
      throw new NotFoundHttpException();
    }
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
     * , requirements="(username|roles|gender|email|lastLogin)"
     * , default="username"
     * , description="Sort by name or uri")
     * @QueryParam(name="search"
     * , nullable=true
     * , description="Search on name and uri")
     *
     * @param ParamFetcher $paramFetcher
     * @return View
     */
  public function cgetAction(ParamFetcher $paramFetcher)
  {
    $page = $paramFetcher->get('page');
    $limit = $paramFetcher->get('limit');
    $sort = $paramFetcher->get('sort');
    $sortBy = $paramFetcher->get('sortBy');
    $search = $paramFetcher->get('search');
    $usersAndCount = $this->userRepository->findByParams(
      $page,
      $limit,
      $sort,
      $sortBy,
      $search
    );
    $view = $this->view(
      $usersAndCount[0]
    );
    $view->setHeader('X-Total-Count', $usersAndCount[1]);
    $view->setHeader('X-Pagination-Count', $usersAndCount[2]);
    $view->setHeader('X-Pagination-Page', $usersAndCount[3]);
    $view->setHeader('X-Pagination-Limit', $usersAndCount[4]);
    $view->setHeader(
      'Access-Control-Expose-Headers',
      'X-Total-Count, X-Pagination-Count, X-Pagination-Page, X-Pagination-Limit'
    );
    return $view;
  }
}
