<?php
namespace App\Filament\Resources\Coupons\Pages;
use App\Filament\Resources\Coupons\CouponResource;
use Filament\Resources\Pages\ManageRecords;
use Filament\Actions\CreateAction;
class ManageCoupons extends ManageRecords { protected static string $resource=CouponResource::class; protected function getHeaderActions():array{return [CreateAction::make()];} }
