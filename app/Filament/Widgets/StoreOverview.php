<?php
namespace App\Filament\Widgets;
use App\Models\{Order,Payment,ProductVariant};
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
class StoreOverview extends StatsOverviewWidget {
    protected function getStats(): array {
        return [
            Stat::make('Today’s orders', Order::whereDate('created_at', today())->count())->description('New customer orders')->color('success')->icon('heroicon-o-shopping-bag'),
            Stat::make('Today’s paid sales', '৳'.number_format(Order::whereDate('created_at', today())->where('payment_status','paid')->sum('grand_total'),2))->description('Verified payments')->color('success')->icon('heroicon-o-banknotes'),
            Stat::make('bKash verification', Payment::where('status','submitted')->count())->description('Payments awaiting review')->color('warning')->icon('heroicon-o-credit-card'),
            Stat::make('Low stock packs', ProductVariant::whereColumn('stock_quantity','<=','low_stock_threshold')->count())->description('Review inventory')->color('danger')->icon('heroicon-o-cube'),
        ];
    }
}
