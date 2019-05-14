<?php

namespace App\Form;

use App\Form\Dto\LinkAccount;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LinkAccountForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('handleSC', TextType::class, []);

        // listener to sanitize a the handle if someone sets his RSI profile URL
        $builder->get('handleSC')->addEventListener(FormEvents::PRE_SUBMIT, static function (FormEvent $event): void {
            $handle = $event->getData();

            if (strpos($handle, 'robertsspaceindustries.com') !== false) {
                $parts = explode('/', $handle);
                $event->setData(end($parts));
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => LinkAccount::class,
            'allow_extra_fields' => true,
            'csrf_protection' => false,
        ]);
    }
}
