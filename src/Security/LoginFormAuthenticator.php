<?php

namespace App\Security;

use App\Repository\UserRepository;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class LoginFormAuthenticator extends AbstractGuardAuthenticator
{
    const TOKEN_SECRET ='SomeSecretString';
    const TOKEN_ISSUER ='http://localhost:8000';
    const TOKEN_AUDIENCE ='http://localhost:8080';
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    public function __construct(
        UserRepository $userRepository,
        UserPasswordEncoderInterface $encoder
    )
    {
        $this->userRepository = $userRepository;
        $this->encoder = $encoder;
    }

    public function supports(Request $request)
    {
        // there is a better place for this for sure.
        if ($request->headers->get('Content-Type') == 'application/json') {
            $data = json_decode($request->getContent(), true);
            $request->request->replace(is_array($data) ? $data : array());
        }

        return $request->attributes->get('_route') === 'app_login'
            && $request->isMethod('POST');
    }

    public function getCredentials(Request $request)
    {
        return [
            'email'    => $request->request->get('email'),
            'password' => $request->request->get('password'),
        ];
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        return $this->userRepository->findOneBy(['email' => $credentials['email']]);
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return $this->encoder->isPasswordValid($user, $credentials['password']);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $signer = new Sha256();
        $jwt = (new Builder())
            ->setIssuer(self::TOKEN_ISSUER)
            ->setAudience(self::TOKEN_AUDIENCE)
            ->setIssuedAt(time())
            ->setNotBefore(time())
            ->setExpiration(time() + 3600)
            ->set('uid', $token->getUser()->getId())
            ->set('username', $token->getUser()->getUsername())
            ->set('email', $token->getUser()->getEmail())
            ->sign($signer, SELF::TOKEN_SECRET)
            ->getToken();

        return new JsonResponse([
                "token_type"=> "Bearer",
                "token"=> $jwt->__toString(),
            ], 200, ['Content-Type' => 'application/json']);

    }
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new Response('Auth header required', 401);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new JsonResponse(["error" => "Invalid credentials"], 403, ['Content-Type' => 'application/json']);
    }

    public function supportsRememberMe()
    {
        return false;
    }
}
