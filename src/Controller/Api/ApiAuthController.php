<?php
namespace App\Controller\Api;

use App\Enum\GenderEnum;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Route("/auth")
 */
class ApiAuthController extends AbstractController
{
    /**
     * @Route("/register", name="api_auth_register",  methods={"POST"})
     * @param Request $request
     * @param UserManagerInterface $userManager
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse     */
    public function register(Request $request, UserManagerInterface $userManager)
    {
        $data = json_decode(
            $request->getContent(),
            true
        );

        $validator = Validation::createValidator();
        $constraint = new Assert\Collection(array(
            // the keys correspond to the keys in the input array
            'username' => new Assert\Length(array('min' => 1)),
            'password' => new Assert\Length(array('min' => 1)),
            'email' => new Assert\Email(),
            'gender' => new Assert\Length(array('min' => 1, 'max' => 6))                                                                                                                                                                                                                                    
        ));

        $violations = $validator->validate($data, $constraint);

        if ($violations->count() > 0) {
            return new JsonResponse(["error" => (string)$violations], 500);
        }

        $username = $data['username'];
        $password = $data['password'];
        $email = $data['email'];
        $gender = $data['gender'];
        $user = new User();

        $user
            ->setUsername($username)
            ->setPlainPassword($password)
            ->setEmail($email)
            ->setEnabled(true)
            ->setRoles(['ROLE_USER', 'ROLE_AMBASSADOR'])
            ->setSuperAdmin(false)
            ->setGender($gender);
        ;

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
        //return new JsonResponse(["success" => $user->getUsername(). " has been registered!"], 200);
    }
}