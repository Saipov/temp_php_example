<?php


namespace App\Classes;


use App\Entity\Organization;
use App\Entity\User;
use App\Security\Open\JWTTokenStorage;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Inherit this class to have additional functionality in your controller
 *
 * Class AbstractController
 *
 * @package App\Classes
 */
abstract class AbstractController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    use LoggerTrait;

    private JWTTokenStorage $JWTTokenStorage;

    /**
     * AbstractController constructor.
     *
     * @param JWTTokenStorage $JWTTokenStorage
     */
    public function __construct(JWTTokenStorage $JWTTokenStorage)
    {
        $this->JWTTokenStorage = $JWTTokenStorage;
    }

    /**
     * @return object|Organization|null
     */
    public function getCurrentOrganization()
    {
        return $this->getDoctrine()
            ->getRepository(Organization::class)
            ->find($this->JWTTokenStorage->get("organization_id"));
    }

    /**
     * @return User|UserInterface|null
     */
    public function getUser()
    {
        return parent::getUser();
    }
}
