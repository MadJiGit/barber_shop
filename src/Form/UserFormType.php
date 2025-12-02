<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\ChoiceList;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class,
                [
                    'label' => 'email: ',
                    'disabled' => true,
                ])
                ->add('roles', ChoiceType::class, [
                    'label' => 'Права',
                    'choices' => [
                        'Super Admin' => 'ROLE_SUPER_ADMIN',
                        'Admin' => 'ROLE_ADMIN',
                        'Manager' => 'ROLE_MANAGER',
                        'Receptionist' => 'ROLE_RECEPTIONIST',
                        'Senior Barber' => 'ROLE_BARBER_SENIOR',
                        'Barber' => 'ROLE_BARBER',
                        'Junior Barber' => 'ROLE_BARBER_JUNIOR',
                        'Client' => 'ROLE_CLIENT',
                    ],
                    'multiple' => true,
                    'expanded' => true,

                ])
            ->add('password', HiddenType::class, ['mapped' => false])
            ->add('first_name', TextType::class,
                [
                    'label' => 'Собствено име',
                    'required' => true,
                    'attr' => ['placeholder' => 'Вашето име'],
                ])
            ->add('last_name', TextType::class,
                [
                    'label' => 'Фамилно име',
                    'required' => true,
                    'attr' => ['placeholder' => 'Вашата фамилия'],
                ])
            ->add('nick_name', TextType::class,
                [
                    'label' => 'Потребителско име',
                    'required' => false,
                    'attr' => ['placeholder' => 'По желание (ако е празно ще използваме вашето име)'],
                ])
            ->add('phone', TextType::class,
                [
                    'label' => 'Телефон',
                    'required' => true,
                    'attr' => ['placeholder' => '+359 888 123 456'],
                ])
            ->add('date_added', TextType::class, [
                'label' => 'Създаден на: ',
                'disabled' => true,
            ])
            ->add('date_banned', TextType::class, [
                'label' => 'Премахнат на: ',
                'required' => false,
            ])
            ->add('date_last_update', TextType::class, [
                'label' => 'Последно променен на: ',
                'required' => false,
                'disabled' => true,
            ])
            ->add('save', SubmitType::class, ['label' => 'Запиши']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'choices' => [
                'Standard Shipping' => 'standard',
                'Expedited Shipping' => 'expedited',
                'Priority Shipping' => 'priority',
            ],
        ]);
    }

    //    public function getParent(): string
    //    {
    //        return ChoiceType::class;
    //    }
}
