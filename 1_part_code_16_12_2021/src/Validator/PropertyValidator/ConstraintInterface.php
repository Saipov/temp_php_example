<?php


namespace App\Validator\PropertyValidator;

/**
 * Interface ConstraintInterface
 *
 * @package App\Validator\PropertyValidator
 */
interface ConstraintInterface
{
    public function build(ConstraintBuilder $builder, $options = null);
}