<?php


namespace App\SQLFilter;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;
use LogicException;
use App\Entity\OrganizationFilterableInterface;

/**
 * Class OrganizationFilter
 *
 * Этот класс нужен в первую очередь для безопасности данных.
 * Любая выборка данных, реализующая OrganizationInterface подвергается фильтрации по organization_id
 * То есть любой пользователь системы (кроме SuperAdmin) может получить данные только в рамках своей организации.
 * Пояснение: понятие "Организация" в системе является логической чертой изоляции и пользователи организации "А" не
 * могут и не имеют прав получить данных связанные с организацией "В"
 * Этот фильтр релизует данный функционал безопасности.
 * Сам механизм является частью Symfony и выполняется на низком уровне: на уровне SQL-запроса
 *
 * @package App\SQLFilter
 */
class OrganizationFilter extends SQLFilter
{
    /**
     * @inheritDoc
     */
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        // Check if the entity implements the LocalAware interface
        if (!$targetEntity->reflClass->implementsInterface(OrganizationFilterableInterface::class)) {
            return "";
        }

        if (!$this->hasParameter("organization_id")) {
            throw new LogicException("Further logic assumes that the parameter must be!");
        }

        return sprintf('%s.organization_id = %s', $targetTableAlias, $this->getParameter("organization_id"));
    }

}
