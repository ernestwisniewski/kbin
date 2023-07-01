<?php

namespace App\Form\Type;

use App\Service\SettingsManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\ChoiceList;
use Symfony\Component\Form\ChoiceList\Loader\IntlCallbackChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Intl\Exception\MissingResourceException;
use Symfony\Component\Intl\Languages;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;

#[AsEntityAutocompleteField]
class AllLanguagesChoiceType extends AbstractType
{
    public function __construct(
        private readonly SettingsManager $settingsManager,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'choice_loader' => function (Options $options) {
                    return ChoiceList::loader($this, new IntlCallbackChoiceLoader(function () {
                        foreach (Languages::getLanguageCodes() as $code) {
                            try {
                                $languages[$code] = Languages::getName($code, $code);
                            } catch (MissingResourceException) {
                            }
                        }
                    
                        return array_flip($languages);
                    }));
                },
                'multiple'      => true,
                'autocomplete'  => true,
            ],
        );
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'language';
    }
}
