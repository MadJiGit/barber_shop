<?php

namespace App\Form;

use App\Entity\RolesNew;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class AppointmentFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('appointmetn_type', ChoiceType::class, [
            'label' => 'Usluga',
            'choices' => RolesNew::getRoles(),
            'multiple' => true,
            'expanded' => true,
        ]);
    }
}
