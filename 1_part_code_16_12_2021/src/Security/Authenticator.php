<?php


namespace App\Security;

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
 * @package App\Security\Admin
 */
class Authenticator implements AuthenticatorInterface
{
    private string $code_error = "user_unauthorized";
    private string $message_error = "";
    private JWT $jwt;
    private EntityManagerInterface $entityManager;

    /**
     * Authenticator constructor.
     *
     * @param JWT                    $JWT
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(JWT $JWT, EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->jwt = $JWT;
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
        if (preg_match('/Bearer\s+(.*?)$/m', $request->headers->get("authorization"), $match)) {
            $token = $match[1];
        } elseif ($request->get("token", false)) {
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
     * @throws UnauthorizedException
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        try {
            $payload = $this->jwt->extractPayload($credentials["token"]);
            return $userProvider->loadUserById($payload["user_id"]);
        } catch (Exception $e) {
            throw new UnauthorizedException('Invalid access token', 'invalid_access_token');
        }
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

                if (!key_exists('organization_id', $payload)) {
                    $this->code_error = "invalid_payload";
                    $this->message_error = "Invalid payload in access token";
                    return false;
                }

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

                // Включаю фильтр и устанавливаю параметры
                // organization_id = идентификатор организация
                $this->entityManager
                    ->getFilters()
                    ->enable("organization")
                    ->setParameter("organization_id", (string)$payload['organization_id']);

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
