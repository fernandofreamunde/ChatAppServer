<?php

namespace App\Security;

use App\Repository\UserRepository;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\ValidationData;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class ApiAuthenticator extends AbstractGuardAuthenticator
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(UserRepository $repository)
    {
        $this->userRepository = $repository;
    }

    public function supports(Request $request)
    {
        return $request->attributes->get('_route') !== 'app_login';
    }

    public function getCredentials(Request $request)
    {
        if (!$request->headers->has('Authorization')) {
            throw new CustomUserMessageAuthenticationException('Authentication Token necessary!');
        }
        return substr($request->headers->get('Authorization'), 7);
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $token = (new Parser())->parse($credentials);
        $signer = new Sha256();

        if (!$token->verify($signer, LoginFormAuthenticator::TOKEN_SECRET)) {
            throw new CustomUserMessageAuthenticationException('Invalid Token');
        }

        $validationData = new ValidationData();
        $validationData->setAudience(LoginFormAuthenticator::TOKEN_AUDIENCE);
        $validationData->setIssuer(LoginFormAuthenticator::TOKEN_ISSUER);

        if (!$token->validate($validationData)) {
            throw new CustomUserMessageAuthenticationException('Invalid Token');
        }

        return $this->userRepository->findOneBy(['email' => $token->getClaim('email')]);
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new JsonResponse(['message' => $exception->getMessageKey()], 401);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // nothing to do here, just allow the request to continue
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        // todo
    }

    public function supportsRememberMe()
    {
        return false;
    }
}
