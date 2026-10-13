<?php
namespace App\Filament\Widgets;
use Filament\Widgets\Widget;
class StoreWelcome extends Widget {
    protected string $view = 'filament.widgets.store-welcome';
    protected int|string|array $columnSpan = 'full';
    protected static ?int $sort = -10;
}
