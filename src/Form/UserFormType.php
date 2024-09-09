<?php

namespace App\Form;

use App\Entity\Appointments;
use App\Entity\Roles;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
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
//            ->add($builder->create(
//                'roles', EnumType::class, [
//                    'choices' => Roles::cases(),
//                    'attr' => ['class' => 'dropdown'],
//                ]
//            )
//
//            )
//            ->
//            add('roles', CollectionType::class,
//                [
//                    'label' => 'Права: ',
//                    'required' => false,
//                ])
//            ->add('roles', ChoiceType::class, [
//                'class' => Roles::class,
//                'choices' => Roles::cases(),
//                'attr' => ['class' => 'dropdown'],
//            ])
//            ->
//            add('roles', ChoiceType::class,
//                [
//                    'class' => Roles::class,
//                ])
            ->add('password', HiddenType::class, ['mapped' => false])
            ->add('first_name', TextType::class,
                [
                    'label' => 'Собствено име: ',
                ])
            ->add('last_name', TextType::class,
                [
                    'label' => 'Фамилно име: ',
                ])
            ->add('nick_name', TextType::class,
                [
                    'label' => 'Никнейм: ',
                ])
            ->add('phone', TextType::class,
                [
                    'label' => 'Телефон: ',
                ])
            ->add('date_added', TextType::class, [
                'label' => 'Създаден на: ',
                //                'required' => false,
                'disabled' => true,
                //                'mapped' => false,
            ])
//            ->add('date_banned', TextType::class, [
//                'label' => 'Премахнат на: ',
//                'required' => false,
//                'disabled' => true,
//            ])
//            ->add('date_last_update', TextType::class, [
//                'label' => 'Последно променен на: ',
//                'required' => false,
//                'disabled' => true,
//                'mapped' => false,
//            ])
//            ->add('barber', EntityType::class, [
//                'label' => 'Избери фризьор: ',
//                'class' => Appointments::class,
//                'choice_label' => 'name',
//            ])
//            ->add('client', EntityType::class, [
//                'label' => 'Избери фризьор: ',
//                'class' => Appointments::class,
//                'choice_label' => 'name',
//            ])
            ->add('save', SubmitType::class, ['label' => 'Запиши']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
