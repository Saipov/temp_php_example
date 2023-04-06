<?php

namespace App\Security\Voter;

use App\Entity\Permission;
use App\Entity\User;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Абстрактный класс AbstractVoter
 * Базовый класс для всех Voter'ов, создежащий реализацию повторяющегося метода hasAttribute()
 *
 * @package App\Security\Voter
 */
abstract class AbstractVoter extends Voter
{
    /**
     * Проверяет разрешения (permissions) роли пользователя на конкретный атрибут
     *
     * @param User   $user
     * @param string $attribute
     *
     * @return bool
     */
    protected function hasAttribute(User $user, string $attribute): bool
    {
        return $user->getRole()->getPermissions()->exists(function ($i, Permission $e) use ($attribute) {
            return $attribute === $e->getValue();
        });
    }
}
