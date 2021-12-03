<?php declare(strict_types=1);

namespace App\Form;

use App\DTO\MagazineThemeDto;
use App\Form\Constraint\ImageConstraint;
use App\Form\EventListener\ImageListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
            ->add('primaryColor', ColorType::class)
            ->add('primaryDarkerColor', ColorType::class)
            ->add('backgroundImage', ChoiceType::class, [
                'multiple' => false,
                'expanded' => true,
                'choices'  => [
                    'none' => 'none',
                    'shape1' => 'shape1',
                    'shape2' => 'shape2',
                    'url' => 'url',
                ],
            ])
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
