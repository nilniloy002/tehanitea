<?php
namespace App\Filament\Resources\Products\Pages;
use App\Filament\Resources\Products\ProductResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\EditAction;
class ViewProduct extends ViewRecord {
    protected static string $resource = ProductResource::class;
    protected function getHeaderActions(): array { return [EditAction::make()]; }
}
