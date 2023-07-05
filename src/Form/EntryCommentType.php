<?php

declare(strict_types=1);

namespace App\Form;

use App\DTO\EntryCommentDto;
use App\Form\Constraint\ImageConstraint;
use App\Form\EventListener\DefaultLanguage;
use App\Form\EventListener\ImageListener;
use App\Form\Type\LanguageType;
use App\Service\SettingsManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntryCommentType extends AbstractType
{
    public function __construct(
        private readonly ImageListener $imageListener,
        private readonly DefaultLanguage $defaultLanguage,
        private readonly SettingsManager $settingsManager,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('body', TextareaType::class, ['required' => false, 'empty_data' => ''])
            ->add('lang', LanguageType::class, ['priorityLanguage' => $options['entryLanguage']])
            ->add(
                'image',
                FileType::class,
                [
                    'constraints' => ImageConstraint::default(),
                    'mapped' => false,
                    'required' => false,
                ]
            )
            ->add('imageUrl', UrlType::class, ['required' => false])
            ->add('imageAlt', TextareaType::class, ['required' => false])
            ->add('submit', SubmitType::class);

        $builder->addEventSubscriber($this->defaultLanguage);
        $builder->addEventSubscriber($this->imageListener);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class'    => EntryCommentDto::class,
                'entryLanguage' => $this->settingsManager->get('KBIN_DEFAULT_LANG'),
            ]
        );

        $resolver->addAllowedTypes('entryLanguage', 'string');
    }
}
