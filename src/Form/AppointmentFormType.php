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
        foreach ($getAllBarbers as $barber) {
            // $barber is now a User object
            $nickName = $barber->getNickName();
            if (!empty($nickName)) {
                $roles = $barber->getRoles();
                if (in_array('ROLE_BARBER_JUNIOR', $roles)) {
                    $res['BARBER_JUNIOR'][$nickName] = $nickName;
                } elseif (in_array('ROLE_BARBER_SENIOR', $roles)) {
                    $res['BARBER_MASTER'][$nickName] = $nickName;
                } elseif (in_array('ROLE_BARBER', $roles)) {
                    $res['BARBER'][$nickName] = $nickName;
                }
            }
        }

        return $res;
    }
}
