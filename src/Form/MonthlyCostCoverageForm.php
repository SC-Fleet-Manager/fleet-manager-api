<?php

namespace App\Form;

use App\Form\Dto\MonthlyCostCoverage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MonthlyCostCoverageForm extends AbstractType
{
    public const MODE_CREATE = 'create';
    public const MODE_EDIT = 'edit';

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $currentYear = (int) date('Y');
        $yearsChoices = range($currentYear, $currentYear + 10);

        $builder
            ->add('target', MoneyType::class, [
                'required' => false,
                'currency' => 'USD',
                'divisor' => 100,
            ]);
        if (!$options['default_coverage']) {
            $builder
                ->add('month', DateType::class, [
                    'required' => true,
                    'disabled' => $options['mode'] === self::MODE_EDIT,
                    'years' => $yearsChoices,
                    'input' => 'datetime_immutable',
                    'format' => \IntlDateFormatter::LONG,
                ])
                ->add('postpone', CheckboxType::class, [
                    'required' => false,
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MonthlyCostCoverage::class,
            'allow_extra_fields' => true,
            'csrf_protection' => true,
            'default_coverage' => false,
            'mode' => self::MODE_CREATE,
        ]);
    }
}
