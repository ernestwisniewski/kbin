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
        'cz' => 'czech',
        'dk' => 'danish',
        'nl' => 'dutch',
        'en' => 'english',
        'fr' => 'french',
        'de' => 'german',
        'gr' => 'greek',
        'hu' => 'hungarian',
        'it' => 'italian',
        'jp' => 'japanese',
        'pl' => 'polish',
        'pt' => 'portuguese',
        'es' => 'spanish',
        'uk' => 'ukrainian',
    ];

    public function configureOptions(OptionsResolver $resolver)
    {
        $default = 'en'; // @todo: get default language from config
        $resolver->setDefaults([
                'choices' => array_merge([self::$choices[$default] => $default], array_flip(self::$choices)),
                'required' => true,
                'empty_data' => 'en',
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
