<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UniqueField extends Constraint
{
    public $entityClass = '';
    public $field = 'username';
    public $message = 'This username is taken. Please choose another.';
}
