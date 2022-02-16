<?php declare(strict_types=1);

namespace App\Form;

use App\DTO\UserSettingsDto;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserSettingsType extends AbstractType
{
    public function __construct(private TranslatorInterface $translator)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'darkTheme',
                CheckboxType::class
            )
            ->add(
                'turboMode',
                CheckboxType::class
            )
            ->add(
                'hideImages',
                CheckboxType::class
            )
            ->add(
                'hideAdult',
                CheckboxType::class
            )
            ->add(
                'rightPosImages',
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
