<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Unixtime
 * @Annotation
 */
class Unixtime extends Constraint
{
    public $message = "Invalid unixtime";
}