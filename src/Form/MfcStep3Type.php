<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class MfcStep3Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('files', FileType::class, [
            'mapped' => false,
            'multiple' => true,
            'required' => true,
            'constraints' => [
                new Assert\Count(
                    min: 1, minMessage: 'Минимально допустимое кол-во файлов: {{ limit }}',
                    max: 3, maxMessage: 'Превышено максимальное количество файлов: {{ limit }}',
                ),
                new Assert\All([
                    'constraints' => [
                        new Assert\File(
                            maxSize: '20M',
                            mimeTypes: [
                                'application/pdf',
                                'image/jpeg',
                                'application/msword',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            ],
                            mimeTypesMessage: 'Разрешены только PDF, JPEG, DOC, DOCX.'
                        ),
                    ],
                ]),
            ],
            'attr' => [
                'class' => 'file-input',
                'accept' => '.pdf,.jpeg,.jpg,.doc,.docx',
            ]
        ])->add('submit', SubmitType::class, [
            'label' => 'Отправить',
            'attr' => [
                'class' => 'btn-primary m-0',
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
