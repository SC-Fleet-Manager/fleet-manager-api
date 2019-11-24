<?php

namespace App\Form;

use App\Form\Dto\LostPasswordCreate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LostPasswordCreateForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('password', PasswordType::class, [
            'required' => true,
            'label' => 'Your new password',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => LostPasswordCreate::class,
            'allow_extra_fields' => true,
            'csrf_protection' => false,
        ]);
    }
}
