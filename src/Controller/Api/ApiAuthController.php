<?php
namespace App\Controller\Api;

use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Serializer\FormErrorSerializer;
use App\Entity\User;
use App\Form\UserType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;


/**
 * @Route("/auth")
 * @SWG\Tag(
 *     name="User"
 * )
 */
class ApiAuthController extends AbstractController
{
    /**
     * @var FormErrorSerializer
     */
    private $formErrorSerializer;

    public function __construct(
        FormErrorSerializer $formErrorSerializer
    ) {
        $this->formErrorSerializer = $formErrorSerializer;
    }
    /**
     * Register an user to the DB.
     *
     * @Route("/register", name="api_auth_register",  methods={"POST"})
     *
     * @SWG\Post(
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *      response=307,
     *      description="Redirect to login form with the user as parameter"
     *    ),
     *    @SWG\Response(
     *     response=500,
     *     description="The form is not correct<BR/>
     * See the corresponding JSON error to see which field is not correct"
     *    )
     * )
     *
     * @param Request $request
     * @param UserManagerInterface $userManager
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function register(Request $request, UserManagerInterface $userManager)
    {
        $data = json_decode(
            $request->getContent(),
            true
        );
        $form = $this->createForm(
            UserType::class
            , new User()
        );

        $form->submit($data);

        if(false == $form->isValid())
        {
            return new JsonResponse(
                [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'errors' => $this->formErrorSerializer->normalize($form),
                ],
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $user = $form->getData();
        $user->setPassword(null);
        $user->setPlainPassword($data['password']);

        $user->setRoles(['ROLE_USER']);
        $user->setSuperAdmin(false);
        $user->setEnabled(true);
        try {
            $userManager->updateUser($user, true);
        } catch (\Exception $e) {
            return new JsonResponse(["error" => $e->getMessage()], 500);
        }
        # Code 307 preserves the request method, while redirectToRoute() is a shortcut method.
        return $this->redirectToRoute('api_auth_login', [
            'username' => $data['username'],
            'password' => $data['password']
        ], 307);
    }
}