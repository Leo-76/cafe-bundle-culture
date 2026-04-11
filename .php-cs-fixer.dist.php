<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests')
    ->name('*.php')
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony'                       => true,
        '@Symfony:risky'                 => true,
        'declare_strict_types'           => true,
        'array_syntax'                   => ['syntax' => 'short'],
        'ordered_imports'                => ['sort_algorithm' => 'alpha'],
        'no_unused_imports'              => true,
        'trailing_comma_in_multiline'    => true,
        'phpdoc_order'                   => true,
        'void_return'                    => true,
        'native_function_invocation'     => ['include' => ['@compiler_optimized'], 'scope' => 'namespaced'],
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder)
;
