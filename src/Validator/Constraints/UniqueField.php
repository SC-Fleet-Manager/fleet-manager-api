<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UniqueField extends Constraint
{
    public string $entityClass = '';
    public string $field = 'username';
    public string $message = 'This username is taken. Please choose another.';
}
