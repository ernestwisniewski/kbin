<?php

namespace App\Form\Type;

use App\Service\SettingsManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;

#[AsEntityAutocompleteField]
class LanguageType extends AbstractType
{
    public function __construct(
        private readonly SettingsManager $settingsManager,
    ) {
    }

    public static array $choices = [
        'cz' => 'czech',
        'dk' => 'danish',
        'nl' => 'dutch',
        'en' => 'english',
        'fi' => 'finnish',
        'fr' => 'french',
        'de' => 'german',
        'gr' => 'greek',
        'he' => 'hebrew',
        'hu' => 'hungarian',
        'it' => 'italian',
        'ja' => 'japanese',
        'pl' => 'polish',
        'pt' => 'portuguese',
        'es' => 'spanish',
        'uk' => 'ukrainian',
    ];

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
                'choices' => array_flip(self::$choices),
                'data' => $this->settingsManager->get('KBIN_DEFAULT_LANG'),
                'required' => true,
                'autocomplete' => false,
            ]
        );
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
