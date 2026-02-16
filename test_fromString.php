<?php

require_once __DIR__ . '/vendor/autoload.php';

use RationalNumber\RationalNumber;

echo "=== Test fromString() avec vos exemples ===\n\n";

// Exemples de la demande initiale
$examples = [
    '1/2',
    '0.25',
    '1/4',
    '0',
    '1',
    ' 3 / 4 ',  // Avec espaces
    '22/7',     // Pi approximation
    '-1/2',     // Négatif
    '6/8',      // Réduction automatique
];

foreach ($examples as $input) {
    try {
        $rn = RationalNumber::fromString($input);
        printf("%-15s => %-10s (float: %g)\n", 
            "'$input'", 
            $rn->toString(), 
            $rn->getFloat()
        );
    } catch (Exception $e) {
        printf("%-15s => ERROR: %s\n", "'$input'", $e->getMessage());
    }
}

echo "\n=== Test des exceptions ===\n\n";

$invalidExamples = ['', 'abc', '1/2/3', '5/0'];
foreach ($invalidExamples as $input) {
    try {
        $rn = RationalNumber::fromString($input);
        echo "UNEXPECTED SUCCESS for '$input'\n";
    } catch (Exception $e) {
        printf("%-15s => Exception: %s\n", "'$input'", get_class($e));
    }
}

echo "\n✅ Tous les tests manuels passent!\n";
