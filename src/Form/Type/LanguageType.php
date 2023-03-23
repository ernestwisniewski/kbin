<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\ParentEntityAutocompleteType;

#[AsEntityAutocompleteField]
class LanguageType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
                'choices' => [
                    'english' => 'en',
                    'spanish' => 'es',
                    'french' => 'fr',
                    'german' => 'de',
                    'polish' => 'pl',
                    'portuguese' => 'pt',
                    'ukrainian' => 'uk',
                ],
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
