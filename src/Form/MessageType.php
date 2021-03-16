<?php declare(strict_types=1);

namespace App\Form;

use App\DTO\MessageDto;
use App\DTO\PostDto;
use App\Entity\Magazine;
use App\Form\Constraint\ImageConstraint;
use App\Form\EventListener\DisableFieldsOnEntryEdit;
use App\Form\EventListener\ImageListener;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use App\DTO\EntryDto;

class MessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('body', TextareaType::class)
            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => MessageDto::class,
            ]
        );
    }
}
