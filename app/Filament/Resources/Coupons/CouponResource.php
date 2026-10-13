<?php
namespace App\Filament\Resources\Coupons;
use App\Models\Coupon;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Forms\Components\{TextInput,Select,DateTimePicker,Toggle};
use Filament\Tables\Table;
use Filament\Tables\Columns\{TextColumn,IconColumn};
use Filament\Actions\{EditAction,DeleteAction};
class CouponResource extends Resource {
    protected static ?string $model=Coupon::class;
    protected static string|\UnitEnum|null $navigationGroup='Sales';
    protected static string|\BackedEnum|null $navigationIcon='heroicon-o-ticket';
    public static function form(Schema $schema): Schema { return $schema->components([
        TextInput::make('code')->required()->unique(ignoreRecord:true)->dehydrateStateUsing(fn($state)=>strtoupper(trim($state))),
        Select::make('type')->options(['fixed'=>'Fixed amount (BDT)','percentage'=>'Percentage','free_delivery'=>'Free delivery'])->required(),
        TextInput::make('value')->numeric()->minValue(0)->required()->default(0),
        TextInput::make('minimum_order_amount')->numeric()->prefix('৳')->minValue(0)->default(0)->required(),
        TextInput::make('maximum_discount_amount')->numeric()->prefix('৳')->minValue(0),
        TextInput::make('usage_limit')->numeric()->integer()->minValue(1), TextInput::make('per_customer_limit')->numeric()->integer()->minValue(1),
        DateTimePicker::make('starts_at'),DateTimePicker::make('ends_at')->after('starts_at'),Toggle::make('is_active')->default(true),
    ]); }
    public static function table(Table $table): Table { return $table->columns([TextColumn::make('code')->searchable(),TextColumn::make('type')->badge(),TextColumn::make('value'),TextColumn::make('usage_count')->label('Redemptions'),TextColumn::make('ends_at')->dateTime(),IconColumn::make('is_active')->boolean()])->recordActions([EditAction::make(),DeleteAction::make()]); }
    public static function getPages(): array { return ['index'=>Pages\ManageCoupons::route('/')]; }
}
