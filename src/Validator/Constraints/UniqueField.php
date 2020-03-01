<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UniqueField extends Constraint
{
    public string $entityClass = '';
    public string $field = 'email';
    public string $message = 'This email is taken. Please choose another.';
}
