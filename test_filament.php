<?php

require __DIR__.'/vendor/autoload.php';

use Filament\Forms\Form;

try {
    $form = new Form();
    echo "Filament\Forms\Form loaded successfully!\n";
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

