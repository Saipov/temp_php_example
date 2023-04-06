<?php


namespace App\Security\Open;

use App\Entity\User;
use App\Exception\Http\UnauthorizedException;
use App\Service\JWT;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AuthenticatorInterface;
use Symfony\Component\Security\Guard\Token\GuardTokenInterface;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

/**
 * Class Authenticator
 *
 * @package App\Security\Open
 */
class Authenticator implements AuthenticatorInterface
{
    private string $code_error = "user_unauthorized";
    private string $message_error = "";
    private JWT $jwt;
    private JWTTokenStorage $JWTTokenStorage;
    private EntityManagerInterface $entityManager;

    /**
     * Authenticator constructor.
     *
     * @param JWT             $JWT
     * @param JWTTokenStorage $JWTTokenStorage
     */
    public function __construct(JWT $JWT, JWTTokenStorage $JWTTokenStorage, EntityManagerInterface $entityManager)
    {
        $this->jwt = $JWT;
        $this->JWTTokenStorage = $JWTTokenStorage;
        $this->entityManager = $entityManager;
    }

    /**
     * @param Request $request
     *
     * @return bool|void
     */
    public function supports(Request $request)
    {
        return true;
    }

    /**
     * @param Request $request
     *
     * @return array|bool
     */
    public function getCredentials(Request $request)
    {
        if ($request->headers->has("authorization")) {
            if (!preg_match('/Bearer\s+(.*?)$/m', $request->headers->get("authorization"), $match)) {
                return false;
            }
            $token = $match[1];
        } elseif ($request->query->has("token") or $request->request->has("token")) {
            $token = $request->get("token");
        } else {
            return false;
        }

        return ["token" => $token];
    }

    /**
     * @param mixed                 $credentials
     * @param UserProviderInterface $userProvider
     *
     * @return UserInterface
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        return new User();
    }

    /**
     * @param mixed         $credentials
     * @param UserInterface $user
     *
     * @return bool
     * @throws UnauthorizedException
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        if (!is_array($credentials)) {
            return false;
        }

        if (array_key_exists("token", $credentials)) {
            if ($this->verification($credentials["token"])) {
                try {
                    return true;
                } catch (Exception $e) {
                    throw new UnauthorizedException('Invalid access token', 'invalid_access_token');
                }
            }
        }

        return false;
    }

    /**
     * @param string $token
     *
     * @return bool
     */
    public function verification(string $token): bool
    {
        try {
            $verifierResult = $this->jwt->verification($token);

            if ($verifierResult) {
                $payload = $this->jwt->extractPayload($token);
                if (time() < $payload["nbf"]) {
                    $this->code_error = "token_not_before";
                    $this->message_error = "Access denied: Token is relevant, but did not start its action";
                    return false;
                }

                if (time() > $payload["exp"]) {
                    $this->code_error = "token_time_expired";
                    $this->message_error = "The access token expired";
                    return false;
                }

                if ($this->entityManager->getFilters()->has("organization_id")) {
                    $this->entityManager
                        ->getFilters()
                        ->enable("organization")
                        ->setParameter("organization_id", $payload["organization_id"]);
                }

                $this->JWTTokenStorage->setPayload($payload);
                return true;
            } else {
                $this->code_error = "token_failed_authentication";
                $this->message_error = "Access denied: Token failed authentication";
                return false;
            }

        } catch (Exception $exception) {
            $this->code_error = "token_is_not_correct";
            $this->message_error = "The access token is not correct";
            return false;
        }
    }

    /**
     * @param Request                 $request
     * @param AuthenticationException $exception
     *
     * @return JsonResponse
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new JsonResponse([
            "error_code" => $this->code_error,
            "error_message" => empty($this->message_error) ? "User unauthorized" : $this->message_error,
        ], Response::HTTP_UNAUTHORIZED, [
            "Access-Control-Allow-Origin" => "*"
        ]);
    }

    /**
     * @inheritDoc
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey)
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function supportsRememberMe()
    {
        return false;
    }

    /**
     * @param UserInterface $user
     * @param string        $providerKey
     *
     * @return GuardTokenInterface|void
     */
    public function createAuthenticatedToken(UserInterface $user, string $providerKey)
    {
        return new PostAuthenticationGuardToken(
            $user,
            $providerKey,
            $user->getRoles()
        );
    }
}