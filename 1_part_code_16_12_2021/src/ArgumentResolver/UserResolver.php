<?php


namespace App\ArgumentResolver;


use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Security\Core\Security;

/**
 * Class UserResolver
 *
 * @package App\ArgumentResolver
 */
class UserResolver implements ArgumentValueResolverInterface
{
    private Security $security;

    /**
     * UserResolver constructor.
     *
     * @param Security $security
     */
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @inheritDoc
     */
    public function supports(Request $request, ArgumentMetadata $argument)
    {
        if (User::class !== $argument->getType()) {
            return false;
        }

        $user = $this->security->getUser();

        if (!$user instanceof User) {
            return false;
        }

        return $user instanceof User;
    }

    /**
     * @inheritDoc
     */
    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        yield $this->security->getUser();
    }
}