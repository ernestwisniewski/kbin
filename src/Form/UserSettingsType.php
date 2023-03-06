<?php

declare(strict_types=1);

namespace App\Form;

use App\DTO\UserSettingsDto;
use App\Entity\User;
use App\Form\DataTransformer\FeaturedMagazinesBarTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'hideAdult',
                CheckboxType::class
            )
            ->add('homepage', ChoiceType::class, [
                    'choices' => [
                        $this->translator->trans('topbar.all') => User::HOMEPAGE_ALL,
                        $this->translator->trans('topbar.subscriptions') => User::HOMEPAGE_SUB,
                        $this->translator->trans('topbar.moderated') => User::HOMEPAGE_MOD,
                    ],
                ]
            )
            ->add('featuredMagazines', TextareaType::class)
            ->add(
                'showProfileSubscriptions',
                CheckboxType::class
            )
            ->add(
                'showProfileFollowings',
                CheckboxType::class
            )
            ->add(
                'notifyOnNewEntry',
                CheckboxType::class
            )
            ->add(
                'notifyOnNewEntryReply',
                CheckboxType::class
            )
            ->add(
                'notifyOnNewEntryCommentReply',
                CheckboxType::class
            )
            ->add(
                'notifyOnNewPost',
                CheckboxType::class
            )
            ->add(
                'notifyOnNewPostReply',
                CheckboxType::class
            )
            ->add(
                'notifyOnNewPostCommentReply',
                CheckboxType::class
            )
            ->add('submit', SubmitType::class);

        $builder->get('featuredMagazines')->addModelTransformer(
            new FeaturedMagazinesBarTransformer()
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => UserSettingsDto::class,
            ]
        );
    }
}
