<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ResetPasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Нова парола',
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Моля, въведете парола',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Паролата трябва да е минимум {{ limit }} символа',
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('plainRepeatPassword', PasswordType::class, [
                'label' => 'Потвърдете паролата',
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Моля, потвърдете паролата',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Паролата трябва да е минимум {{ limit }} символа',
                        'max' => 4096,
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
