<?php

namespace App\Form;

use App\Entity\Appointments;
use App\Entity\Procedure;
use App\Repository\ProcedureRepository;
use App\Repository\UserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AppointmentFormType extends AbstractType
{
    private $procedureRepository;
    private $userRepository;
//    private Procedure $procedure;
    private array $procedure;
    private array $barbers;

    public function __construct(ProcedureRepository $procedureRepository, UserRepository $userRepository)
    {
        $this->procedureRepository = $procedureRepository;
        $this->userRepository = $userRepository;
//        $this->procedure = new Procedure();
        $this->procedure = $this->getProcedures($this->procedureRepository->getAllProceduresTypes());
        $this->barbers = $this->getBarbers($this->userRepository->getAllBarbers());

        //    echo '<pre>'.var_export(array_keys($this->procedures), true).'</pre>';
        //        echo '<pre>'.var_export($this->barbers, true).'</pre>';
        //        exit;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('procedure_type', ChoiceType::class, [
                'label' => 'Услуга',
                'choices' => $this->procedure,
                'multiple' => true,
                'expanded' => true,
            ])
             ->add('date', DateType::class, [
                 'widget' => 'single_text',
                 'attr' => ['class' => 'js-datepicker'],
             ])

            ->add('save', SubmitType::class, ['label' => 'Потвърди часа']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        //        $resolver->setDefault('data_class', Appointments::class);
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
