<?php


namespace App\ArgumentResolver;


use App\Entity\User;
use App\Entity\UserRole;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Security\Core\Security;

/**
 * Class RoleResolver
 *
 * @package App\ArgumentResolver
 */
class RoleResolver implements ArgumentValueResolverInterface
{
    private Security $security;

    /**
     * RoleResolver constructor.
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
        if (UserRole::class !== $argument->getType()) {
            return false;
        }

        $user = $this->security->getUser();

        if (!$user instanceof User) {
            return false;
        }

        return $user->getRole() instanceof UserRole;
    }

    /**
     * @inheritDoc
     */
    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        yield $this->security->getUser()->getRole();
    }
}