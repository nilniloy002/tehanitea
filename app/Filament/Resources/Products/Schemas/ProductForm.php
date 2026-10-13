<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Repeater;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Product details')->description('Tell customers what makes this tea special.')->schema([
                Select::make('category_id')
                    ->relationship('category', 'name'),
                TextInput::make('name')
                    ->required(),
                TextInput::make('name_bn'),
                TextInput::make('slug')
                    ->required()->unique(ignoreRecord: true),
                Textarea::make('short_description')
                    ->columnSpanFull(),
                Textarea::make('description')
                    ->columnSpanFull(),
                ])->columns(2)->columnSpanFull(),
                Section::make('Product photography')->schema([
                    FileUpload::make('image_path')->label('Cover image')->image()->disk('public')->visibility('public')->directory('products')->maxSize(5120),
                    FileUpload::make('gallery')->label('Gallery images')->multiple()->reorderable()->image()->disk('public')->visibility('public')->directory('products')->maxFiles(8)->maxSize(5120),
                ])->columns(2)->columnSpanFull(),
                Section::make('Pack sizes & pricing')->description('All prices in BDT. Activate a pack only after confirming its price and stock.')->schema([
                    Repeater::make('variants')->relationship()->schema([
                        TextInput::make('name')->label('Pack size')->required()->maxLength(100),
                        TextInput::make('sku')->label('SKU')->required()->distinct()->unique(ignoreRecord: true),
                        ...\App\Filament\Schemas\VariantPricing::fields(),
                        TextInput::make('weight_grams')->numeric()->integer()->minValue(1),
                        TextInput::make('stock_quantity')->label('Stock')->numeric()->integer()->minValue(0)->default(0)->required(),
                        TextInput::make('low_stock_threshold')->numeric()->integer()->minValue(0)->default(5)->required(),
                        Toggle::make('is_active')->label('Available for sale')->live()->default(false)->helperText('Turn on to enable purchases. A regular price and stock are also needed.'),
                    ])->columns(4)->defaultItems(0)->addActionLabel('Add pack size')->itemLabel(fn(array $state): ?string => $state['name'] ?? null)->columnSpanFull(),
                ])->columnSpanFull(),
                Section::make('Tea information')->schema([
                    Textarea::make('ingredients'), Textarea::make('brewing_guide'), Textarea::make('storage_instructions'),
                ])->columns(3)->columnSpanFull(),
                Section::make('Publishing')->schema([
                Toggle::make('is_featured')
                    ->required(),
                Toggle::make('is_active')
                    ->required(),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
                ])->columns(3)->columnSpanFull(),
            ]);
    }
}
