<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ChangePasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('current_password', PasswordType::class, [
                'mapped' => false,
                'label' => 'Текуща парола',
                'required' => false,
                'attr' => [
                    'autocomplete' => 'current-password',
                    'placeholder' => 'Въведете текущата си парола'
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
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Not bound to any entity
        ]);
    }
}
