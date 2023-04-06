<?php


namespace App\Security\Voter;


use LogicException;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class UserVoter
 *
 * @package App\Security\Voter
 */
class UserManagementVoter extends AbstractVoter implements VoterInterface
{
    public const CREATE = 'USER_CREATE';
    public const EDIT = 'USER_EDIT';
    public const DELETE = 'USER_DELETE';

    static public function getName(): string
    {
        return 'User management';
    }

    /**
     * @param string $attribute
     * @param mixed  $subject
     *
     * @return bool
     */
    protected function supports(string $attribute, $subject): bool
    {
        if (!in_array($attribute, self::getPermissions())) {
            return false;
        }

        if (!$subject instanceof User) {
            return false;
        }

        return true;
    }

    /**
     * @return array
     */
    static public function getPermissions(): array
    {
        return [
            self::CREATE,
            self::EDIT,
            self::DELETE
        ];
    }

    /**
     * @param string         $attribute
     * @param mixed          $subject
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        throw new LogicException('This code should not be reached!');
    }
}
