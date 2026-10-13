<?php

namespace App\Filament\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;

class VariantPricing
{
    public static function fields(): array
    {
        return [
            TextInput::make('regular_price')->label('Regular price')->prefix('৳')->numeric()
                ->formatStateUsing(fn ($state) => (float) $state > 0 ? $state : null)
                ->dehydrateStateUsing(fn ($state) => blank($state) ? null : $state)
                ->required(fn (Get $get) => $get('is_active') || (float) $get('price') > 0)
                ->minValue(0.01)->helperText('Normal customer price, used when no offer is entered.'),
            TextInput::make('price')->label('Selling price (offer, optional)')->prefix('৳')->numeric()
                ->minValue(0.01)
                ->formatStateUsing(fn ($state) => (float) $state > 0 ? $state : null)
                ->dehydrateStateUsing(fn ($state) => blank($state) ? 0 : $state)
                ->rules([fn (Get $get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                    if (filled($value) && (float) $value >= (float) $get('regular_price')) {
                        $fail('The offer price must be lower than the regular price.');
                    }
                }])->helperText('Leave blank for no offer. Must be lower than the regular price.'),
        ];
    }
}
