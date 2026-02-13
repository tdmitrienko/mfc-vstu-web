<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class MfcStep2Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('files', FileType::class, [
            'mapped' => false,
            'multiple' => true,
            'required' => false,
            'constraints' => [
                new Assert\Count(max: 3, maxMessage: 'Превышено максимальное количество файлов: {{ limit }}'),
            ],
            'attr' => [
                'class' => 'file-input'
            ]
        ])->add('submit', SubmitType::class, [
            'label' => 'Отправить',
            'attr' => [
                'class' => 'btn-primary',
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'POST',
            'csrf_protection' => true,
            'attr' => [
                'class' => 'upload-form',
            ],
        ]);
    }
}
