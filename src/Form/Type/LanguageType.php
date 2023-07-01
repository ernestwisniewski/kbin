<?php

namespace App\Form\Type;

use App\Service\SettingsManager;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\ChoiceList;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;

#[AsEntityAutocompleteField]
class LanguageType extends AbstractType
{
    public function __construct(
        private readonly SettingsManager $settingsManager,
        private readonly Security $security,
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
        $resolver->setDefaults(
            [
                'choice_loader' =>  function (Options $options) {
                    return ChoiceList::loader($this, new CallbackChoiceLoader(function () {
                        $preferredLanguages = $this->security->getUser()->preferredLanguages;

                        $choices = 0 < count($preferredLanguages)
                            ? array_filter(static::$choices, function ($code) use ($preferredLanguages) {
                                return in_array($code, $preferredLanguages);
                            }, ARRAY_FILTER_USE_KEY)
                            : static::$choices;
                        
                        return array_flip($choices);
                    }));
                },
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
