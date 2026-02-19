<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class OtpType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('otp', TextType::class, [
                'required' => true,
                'label' => false,
                'attr' => [
                    'placeholder' => 'OTP',
                    'class' => 'otp-input',
                    'inputmode' => 'numeric',
                    'autocomplete' => 'one-time-code',
                    'maxlength' => (string) $options['otp_length'],
                    'pattern' => '\d{' . (int) $options['otp_length'] . '}',
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'Введите OTP-код.'),
                    new Assert\Regex([
                        'pattern' => '/^\d{' . (int) $options['otp_length'] . '}$/',
                        'message' => sprintf('OTP должен состоять из %d цифр.', (int) $options['otp_length']),
                    ]),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Войти',
                'attr' => [
                    'class' => 'btn-primary otp-btn m-0 ',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'POST',
            'csrf_protection' => true,
            'otp_length' => 6,
            'attr' => [
                'class' => 'otp-form',
            ],
        ]);

        $resolver->setAllowedTypes('otp_length', 'int');
    }
}
