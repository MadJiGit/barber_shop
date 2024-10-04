<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;

class ProcedureFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', TextType::class,
                [
                    'label' => 'Услуга:',
                ])
            ->add('price_master', MoneyType::class,
                [
                    'label' => 'Цена майстор:',
                    'currency' => 'BGN',
                ])
            ->add('price_junior', MoneyType::class,
                [
                    'label' => 'Цена джуниър:',
                    'currency' => 'BGN',
                ])
            ->add('duration_master', TextType::class,
                [
                    'label' => 'Продължителност майстор min:',
                ])
            ->add('duration_junior', TextType::class,
                [
                    'label' => 'Продължителност джуниър min:',
                ])
            ->add('available', CheckboxType::class, [
                'label' => 'Активна услуга: ',
            ])
            ->add('date_added', TextType::class, [
                'label' => 'Създаден на: ',
                'disabled' => true,
            ])
            ->add('date_stopped', TextType::class, [
                'label' => 'Спряна на: ',
                'required' => false,
                'disabled' => true,
            ])
            ->add('date_last_update', TextType::class, [
                'label' => 'Последно променен на: ',
                'required' => false,
                'disabled' => true,
            ])
            ->add('save', SubmitType::class, ['label' => 'Запиши']);
    }
}
