<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\ChoiceList;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

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
                    'required' => false,
                    'mapped' => false,
                ])
            ->add('password', HiddenType::class, ['mapped' => false])
            ->add('first_name', TextType::class,
                [
                    'label' => 'profile.form.first_name',
                    'translation_domain' => 'messages',
                    'required' => true,
                ])
            ->add('last_name', TextType::class,
                [
                    'label' => 'profile.form.last_name',
                    'translation_domain' => 'messages',
                    'required' => true,
                ])
            ->add('nick_name', TextType::class,
                [
                    'label' => 'Потребителско име',
                    'required' => false,
                    'attr' => ['placeholder' => 'По желание (ако е празно ще използваме вашето име)'],
                ])
            ->add('phone', TextType::class,
                [
                    'label' => 'profile.form.phone',
                    'translation_domain' => 'messages',
                    'required' => true,
                ])
            ->add('date_added', DateTimeType::class, [
                'label' => 'Създаден на: ',
                'disabled' => true,
                'widget' => 'single_text',
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
            // Password change fields (unmapped - handled separately)
            ->add('current_password', PasswordType::class, [
                'mapped' => false,
                'label' => 'Текуща парола',
                'required' => false,
                'attr' => [
                    'autocomplete' => 'off',
                    'placeholder' => 'Само ако искате да смените паролата'
                ],
            ])
            ->add('new_password', PasswordType::class, [
                'mapped' => false,
                'label' => 'Нова парола',
                'required' => false,
                'attr' => [
                    'autocomplete' => 'new-password',
                    'placeholder' => 'Минимум 6 символа'
                ],
                'constraints' => [
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Паролата трябва да е минимум {{ limit }} символа',
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('new_password_repeat', PasswordType::class, [
                'mapped' => false,
                'label' => 'Повтори новата парола',
                'required' => false,
                'attr' => [
                    'autocomplete' => 'new-password',
                    'placeholder' => 'Повторете новата парола'
                ],
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
