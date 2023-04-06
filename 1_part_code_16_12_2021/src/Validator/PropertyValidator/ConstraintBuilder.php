<?php


namespace App\Validator\PropertyValidator;


use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\Constraints\Collection;

/**
 * Class ConstraintBuilder
 *
 * @package App\Validator\RequestValidator
 */
class ConstraintBuilder
{
    private array $constraints = [];
    private PropertyAccessor $propertyAccess;

    public function __construct()
    {
        $this->propertyAccess = PropertyAccess::createPropertyAccessor();
    }

    /**
     * @param string           $name
     * @param array|Collection $constraint
     *
     * @return ConstraintBuilder
     */
    public function add(string $name, $constraint)
    {
        $this->propertyAccess->setValue($this->constraints, "[$name]", $constraint);
        return $this;
    }

    /**
     * @return array
     */
    public function getConstraints(): array
    {
        return $this->constraints;
    }
}