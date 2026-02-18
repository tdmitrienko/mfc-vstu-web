<?php

namespace App\Form;

use App\Entity\ApplicationType;
use App\Entity\User;
use App\Repository\ApplicationTypeRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MfcStep1Type extends AbstractType
{
    public function __construct(
        private readonly ApplicationTypeRepository $applicationTypeRepository,
    )
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $options['user'];
        $types = $this->applicationTypeRepository->findSuitableByUser($user);

        $builder
            ->add('applicationType', EntityType::class, [
                'class' => ApplicationType::class,
                'choices' => $types,
                'choice_value' => 'slug',
                'choice_label' => 'name',
                'required' => true,
                'label' => false,
                'placeholder' => 'Выберите справку',
                'multiple' => false,
                'expanded' => false,
                'attr' => [
                    'class' => 'form-select'
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Далее',
                'attr' => [
                    'class' => 'btn-primary',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'POST',
            'csrf_protection' => true,
            'user' => null,
            'attr' => [
                'class' => 'mfc-form',
            ],
        ]);

        $resolver->setAllowedTypes('user', User::class);
    }
}
