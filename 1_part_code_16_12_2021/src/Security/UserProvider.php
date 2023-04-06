<?php


namespace App\Security;


use Doctrine\ORM\EntityManagerInterface;
use Exception;
use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Class UserProvider
 *
 * @package App\Security\Admin
 */
class UserProvider implements UserProviderInterface
{
    private EntityManagerInterface $entityManager;

    /**
     * UserProvider constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    /**
     * @inheritDoc
     */
    public function loadUserByUsername(string $username)
    {
        return $this->entityManager->getRepository(User::class)->findOneBy(["login" => $username]);
    }

    /**
     * @param int $id
     *
     * @return mixed
     */
    public function loadUserById(int $id)
    {
        return $this->entityManager->getRepository(User::class)->find($id);
    }

    /**
     * @inheritDoc
     */
    public function refreshUser(UserInterface $user)
    {
        throw new Exception('TODO: fill in loadUserByUsername() inside ' . __FILE__);
    }

    /**
     * @inheritDoc
     */
    public function supportsClass(string $class)
    {
        return User::class === $class;
    }
}
