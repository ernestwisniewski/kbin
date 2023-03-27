<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;

#[AsEntityAutocompleteField]
class LanguageType extends AbstractType
{
    public static array $choices = [
        'en' => 'english',
        'es' => 'spanish',
        'fr' => 'french',
        'de' => 'german',
        'pl' => 'polish',
        'pt' => 'portuguese',
        'uk' => 'ukrainian',
    ];

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
                'choices' => array_flip(self::$choices),
                'required' => true,
                'autocomplete' => false,
                'tom_select_options' => [
                    'allowEmptyOption' => false,
                ],
            ]
        );
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
