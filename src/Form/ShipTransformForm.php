<?php

namespace App\Form;

use App\Form\Dto\ShipTransform;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ShipTransformForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('myHangarNamePattern', TextType::class, [
                'required' => true,
            ])
            ->add('providerId', TextType::class, [
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ShipTransform::class,
            'allow_extra_fields' => true,
            'csrf_protection' => true,
        ]);
    }
}
