<?php

namespace App\Filament\Resources\StoreSettings;

use App\Filament\Resources\StoreSettings\Pages\CreateStoreSetting;
use App\Filament\Resources\StoreSettings\Pages\EditStoreSetting;
use App\Filament\Resources\StoreSettings\Pages\ListStoreSettings;
use App\Filament\Resources\StoreSettings\Schemas\StoreSettingForm;
use App\Filament\Resources\StoreSettings\Tables\StoreSettingsTable;
use App\Models\StoreSetting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StoreSettingResource extends Resource
{
    protected static ?string $model = StoreSetting::class;
    protected static string|\UnitEnum|null $navigationGroup = 'Store settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return StoreSettingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StoreSettingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStoreSettings::route('/'),
            'create' => CreateStoreSetting::route('/create'),
            'edit' => EditStoreSetting::route('/{record}/edit'),
        ];
    }
}
