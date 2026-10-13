<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_url')->label('Photo')->square()->imageSize(56)->extraImgAttributes(['style'=>'background:#183e30;padding:4px;border-radius:8px;object-fit:contain']),
                TextColumn::make('starting_price')->label('From')->money('BDT')->state(fn($record)=>$record->variants->where('is_active',true)->where('effective_price','>',0)->min('effective_price')),
                TextColumn::make('variants_count')->label('Packs')->counts('variants'),
                TextColumn::make('variants_sum_stock_quantity')->label('Stock')->sum('variants', 'stock_quantity'),
                TextColumn::make('category.name')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('name_bn')
                    ->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('slug')
                    ->searchable()->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_featured')
                    ->boolean(),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
