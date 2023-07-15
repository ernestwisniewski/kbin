<?php

declare(strict_types=1);

namespace App\Form;

use App\DTO\UserSettingsDto;
use App\Entity\User;
use App\Form\DataTransformer\FeaturedMagazinesBarTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\LanguageType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserSettingsType extends AbstractType
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'hideAdult',
                CheckboxType::class,
                ['required' => false]
            )
            ->add('homepage', ChoiceType::class, [
                    'autocomplete' => true,
                    'choices' => [
                        $this->translator->trans('all') => User::HOMEPAGE_ALL,
                        $this->translator->trans('subscriptions') => User::HOMEPAGE_SUB,
                        $this->translator->trans('favourites') => User::HOMEPAGE_FAV,
                        $this->translator->trans('moderated') => User::HOMEPAGE_MOD,
                    ],
                ]
            )
            ->add('featuredMagazines', TextareaType::class, ['required' => false])
            ->add('preferredLanguages', LanguageType::class, [
                'required' => false,
                'preferred_choices' => [$this->translator->getLocale()],
                'autocomplete' => true,
                'multiple' => true,
                'choice_self_translation' => true,
            ])
            ->add(
                'showProfileSubscriptions',
                CheckboxType::class,
                ['required' => false]
            )
            ->add(
                'showProfileFollowings',
                CheckboxType::class,
                ['required' => false]
            )
            ->add(
                'notifyOnNewEntry',
                CheckboxType::class,
                ['required' => false]
            )
            ->add(
                'notifyOnNewEntryReply',
                CheckboxType::class,
                ['required' => false]
            )
            ->add(
                'notifyOnNewEntryCommentReply',
                CheckboxType::class,
                ['required' => false]
            )
            ->add(
                'notifyOnNewPost',
                CheckboxType::class,
                ['required' => false]
            )
            ->add(
                'notifyOnNewPostReply',
                CheckboxType::class,
                ['required' => false]
            )
            ->add(
                'notifyOnNewPostCommentReply',
                CheckboxType::class,
                ['required' => false]
            )
            ->add(
                'addMentionsEntries',
                CheckboxType::class,
                ['required' => false]
            )
            ->add(
                'addMentionsPosts',
                CheckboxType::class,
                ['required' => false]
            )
            ->add('submit', SubmitType::class);

        $builder->get('featuredMagazines')->addModelTransformer(
            new FeaturedMagazinesBarTransformer()
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => UserSettingsDto::class,
            ]
        );
    }
}
