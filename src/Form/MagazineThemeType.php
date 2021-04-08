<?php declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use App\Form\EventListener\ImageListener;
use App\Form\Constraint\ImageConstraint;
use Symfony\Component\Form\AbstractType;
use App\DTO\MagazineThemeDto;

class MagazineThemeType extends AbstractType
{
    public function __construct(private ImageListener $imageListener)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'cover',
                FileType::class,
                [
                    'constraints' => ImageConstraint::default(),
                    'mapped'      => false,
                ]
            )
            ->add('customCss', TextareaType::class)
            ->add('customJs', TextareaType::class)
            ->add('submit', SubmitType::class);

        $builder->addEventSubscriber($this->imageListener->setFieldName('cover'));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => MagazineThemeDto::class,
            ]
        );
    }
}
