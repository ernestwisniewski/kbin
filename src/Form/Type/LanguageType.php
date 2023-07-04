<?php

namespace App\Form\Type;

use App\Service\SettingsManager;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\ChoiceList;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Intl\Exception\MissingResourceException;
use Symfony\Component\Intl\Languages;
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

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'choice_loader'     =>  function (Options $options) {
                    return ChoiceList::loader($this, new CallbackChoiceLoader(function () {
                        foreach (Languages::getLanguageCodes() as $languageCode) {
                            try {
                                $choices[$languageCode] = Languages::getName($languageCode, $languageCode);
                            } catch (MissingResourceException) {
                            }
                        }
                        
                        return array_flip($choices);
                    }));
                },
                'preferred_choices' => ChoiceList::preferred($this, function(string $choice): bool {
                    $preferredLanguages = $this->security->getUser()?->preferredLanguages ?? [$this->settingsManager->get('KBIN_DEFAULT_LANG')];
                    
                    if (in_array($choice, $preferredLanguages)) {
                        return true;
                    }

                    return false;
                }),
                'data'              => $this->settingsManager->get('KBIN_DEFAULT_LANG'),
                'required'          => true,
                'autocomplete'      => false,
            ]
        );
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
