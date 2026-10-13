<?php

namespace App\Filament\Resources\TaxRates\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TaxRateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('percentage')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_inclusive')
                    ->required(),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
