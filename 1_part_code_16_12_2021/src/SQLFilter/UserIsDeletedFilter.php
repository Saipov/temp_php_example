<?php


namespace App\SQLFilter;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;
use App\Entity\UserIsDeletedFilterableInterface;

/**
 * Class UserIsDeletedFilter
 *
 * @package App\SQLFilter
 */
class UserIsDeletedFilter extends SQLFilter
{
    /**
     * @inheritDoc
     */
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        // Check if the entity implements the LocalAware interface
        if (!$targetEntity->reflClass->implementsInterface(UserIsDeletedFilterableInterface::class)) {
            return "";
        }

        // Проверка на удаление
        return sprintf('%s.deleted_at IS NULL', $targetTableAlias);
    }

}
