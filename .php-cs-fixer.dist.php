<?php

declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,

        # defined as "risky" as they could break code. Since our codebase is passing that's fine
        'declare_strict_types' => true,
        'strict_comparison' => true,
        'native_function_invocation' => true,
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder)
    ;
