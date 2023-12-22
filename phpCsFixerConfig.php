<?php
$finder = (new PhpCsFixer\Finder())
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/tests/unit'
    ])
;

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR2' => true,
        '@PhpCsFixer' => true,
        '@PHP73Migration' => true,
        'global_namespace_import' => true,
        'self_static_accessor' => true,
        'void_return' => true,
        'array_syntax' => ['syntax' => 'short'],
        'concat_space' => ['spacing' => 'one'],
        'multiline_whitespace_before_semicolons' => ['strategy' => 'no_multi_line'],
        'single_quote' => ['strings_containing_single_quote_chars' => true],
        'yoda_style' => false,
        'blank_line_before_statement' => [],
        'method_chaining_indentation' => false,
        'logical_operators' => true,
        'modernize_types_casting' => true,
        'php_unit_test_class_requires_covers' => false,
        'strict_comparison' => true,
        'psr_autoloading' => true,
        'is_null' => true,
        'no_alias_functions' => true,
        'no_unreachable_default_argument_value' => true,
        'dir_constant' => true,
        'combine_nested_dirname' => true,
        'no_alternative_syntax' => false,
        'increment_style' => ['style' => 'post'],
        'single_line_empty_body' => false,
    ])
    ->setFinder($finder);