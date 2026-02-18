<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class MfcStep2Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $options['user'];
        $isStudent = in_array('ROLE_STUDENT', $user->getRoles(), true);
        $placeholder = $isStudent ? 'Номер зачетной книжки' : 'Табельный номер';

        $builder
            ->add('documentNumber', TextType::class, [
                'required' => true,
                'label' => false,
                'attr' => [
                    'maxlength' => 32,
                    'placeholder' => $placeholder,
                    'class' => 'form-input',
                ],
                'constraints' => [
                    new Assert\Length(max: 32, maxMessage: 'Максимальная длина поля: {{ limit }}'),
                    new Assert\NotBlank(message: 'Поле не должно быть пустым'),
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
