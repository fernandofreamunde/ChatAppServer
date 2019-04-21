<?php

namespace App\Controller;

use App\Entity\User;
use App\Security\LoginFormAuthenticator;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils)
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'errors' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    /**
     * @Route("/register", name="app_register", methods={"POST"})
     */
    public function register(Request $request, UserPasswordEncoderInterface $encoder, GuardAuthenticatorHandler $guardHandler, LoginFormAuthenticator $authenticator)
    {
        $user = new User();
        $user->setEmail($request->request->get('email'))
            ->setPassword($encoder->encodePassword($user, $request->request->get('password')));

        try {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
        }
        catch (\Exception $exception) {
            if ($exception instanceof UniqueConstraintViolationException) {
                return new JsonResponse(["error" => "Email in use."], 422, ['Content-Type' => 'application/json']);
            }
        };

        return $guardHandler->authenticateUserAndHandleSuccess(
            $user,
            $request,
            $authenticator,
            'main'
        );
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
    }
}
