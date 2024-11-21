<?php

namespace App\Form;

use App\Entity\Procedure;
use App\Entity\User;
use App\Repository\ProcedureRepository;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class AppointmentFormType extends AbstractType
{
    private $procedureRepository;
    private $userRepository;
    private array $procedures;
    private array $barbers;

    public function __construct(ProcedureRepository $procedureRepository, UserRepository $userRepository)
    {
        $this->procedureRepository = $procedureRepository;
        $this->userRepository = $userRepository;
        $this->procedures = $this->getProcedures($this->procedureRepository->getAllProceduresTypes());
        $this->barbers = $this->getBarbers($this->userRepository->getAllBarbers());

        //    echo '<pre>'.var_export(array_keys($this->procedures), true).'</pre>';
        //        echo '<pre>'.var_export($this->barbers, true).'</pre>';
        //        exit;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
//            ->add('procedure_type', ChoiceType::class, [
//                'label' => 'Услуга',
//                'choices' => $this->procedures,
//                'multiple' => true,
//                'expanded' => true,
//            ])
//             ->add('date', DateType::class, [
//                 'widget' => 'single_text',
//                 'attr' => ['class' => 'js-datepicker'],
//                 //                                  'html5' => false,
//             ])
//            ->add('procedure_type', EntityType::class, array(
//                'label' => 'Taxe',
//                'class' => Procedure::class,
//                'choice_label' => 'name',
//                'placeholder' => 'Procedure',
//                'multiple' => true,
//                'expanded' => false,
//                'required' => false,
//                'query_builder' => function (ProcedureRepository $er) {
//                    return $er->createQueryBuilder('p')
//                        ->select('p.type')
//                        ->orderBy('p.type', 'ASC')
//                        ;
//                'choice_value' => function ($procedure) {
//                    return $procedure->getType();
//                },
//            ))
//            ->add('barber', ChoiceType::class, [
//                'label' => 'Бръснар',
// //                'class' => User::class,
//                'choices' => ['Бръснар' => $this->barbers['BARBER'],
//                    'Старши Бръснар' => $this->barbers['BARBER_MASTER'],
//                    'Младеши Бръснар' => $this->barbers['BARBER_JUNIOR'],
//                ],
//                'multiple' => true,
// //                'expanded' => true,
//            ])
            ->add('save', SubmitType::class, ['label' => 'Потвърди часа']);

//        $builder->addEventListener(
//            FormEvents::PRE_SET_DATA,
//            function (FormEvent $event): void {
//                $form = $event->getForm();
//
//                $data = $event->getData();
//
//
//            }
//        );

    }

    private function getProcedures(array $getAllProceduresTypes): array
    {
        $res = [];
        foreach ($getAllProceduresTypes as $key => $value) {
            $res[$value['type']] = $value['type'];
        }

        return $res;
    }

    private function getBarbers(array $getAllBarbers)
    {
        $res = ['BARBER_JUNIOR' => [],
            'BARBER_MASTER' => [],
            'BARBER' => [],
        ];
        foreach ($getAllBarbers as $key => $value) {
            //            echo '<pre>'.var_export($value, true).'</pre>';
            //            echo '<pre>'.var_export(in_array('BARBER', $value['roles']), true).'</pre>';
            //            exit();
            if (!empty($value['nick_name'])) {
                if (in_array('BARBER_JUNIOR', $value['roles'])) {
                    $res['BARBER_JUNIOR'][$value['nick_name']] = $value['nick_name'];
                } elseif (in_array('BARBER_MASTER', $value['roles'])) {
                    $res['BARBER_MASTER'][$value['nick_name']] = $value['nick_name'];
                } elseif (in_array('BARBER', $value['roles'])) {
                    $res['BARBER'][$value['nick_name']] = $value['nick_name'];
                }
            }
        }

        return $res;
    }
}
