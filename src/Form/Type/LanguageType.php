<?php

declare(strict_types=1);

namespace App\Form\Type;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\ChoiceList;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Intl\Exception\MissingResourceException;
use Symfony\Component\Intl\Languages;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;

#[AsEntityAutocompleteField]
class LanguageType extends AbstractType
{
    private string $priorityLanguage;
    private array $preferredLanguages;

    public function __construct(
        private readonly Security $security,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'choice_loader' => function (Options $options) {
                    $this->preferredLanguages = $this->security->getUser()?->preferredLanguages ?? [];
                    $this->priorityLanguage = $options['priorityLanguage'];

                    if (0 === count($this->preferredLanguages)) {
                        $this->preferredLanguages = [$this->requestStack->getCurrentRequest()?->getLocale()];
                    }

                    return ChoiceList::loader($this, new CallbackChoiceLoader(function () {
                        foreach (Languages::getLanguageCodes() as $languageCode) {
                            try {
                                $choices[$languageCode] = Languages::getName($languageCode, $languageCode);
                            } catch (MissingResourceException) {
                            }
                        }

                        return array_flip($choices);
                    }), [$this->preferredLanguages, $this->priorityLanguage]);
                },
                'preferred_choices' => ChoiceList::preferred($this, function (string $choice): bool {
                    if (in_array($choice, $this->preferredLanguages) || $this->priorityLanguage === $choice) {
                        return true;
                    }

                    return false;
                }),
                'required' => true,
                'autocomplete' => false,
                'priorityLanguage' => '',
            ]
        );

        $resolver->addAllowedTypes('priorityLanguage', 'string');
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
