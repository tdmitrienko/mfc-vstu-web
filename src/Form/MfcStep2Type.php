<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class MfcStep2Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $options['user'];
        $documents = $user->getDocuments();
        $documents = array_combine($documents, $documents);

        $builder
            ->add('documentNumber', ChoiceType::class, [
                'choices' => $documents,
                'required' => true,
                'label' => false,
                'placeholder' => 'Выберите номер документа',
                'attr' => [
                    'class' => 'form-input',
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'Необходимо выбрать номер документа'),
                ]
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
