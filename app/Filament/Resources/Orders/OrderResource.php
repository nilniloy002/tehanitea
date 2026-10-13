<?php
namespace App\Filament\Resources\Orders;
use App\Models\Order;
use App\Services\OrderOperations;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\Action;
use Filament\Forms\Components\{Select,Textarea};
class OrderResource extends Resource {
    protected static ?string $model=Order::class;
    protected static string|\UnitEnum|null $navigationGroup='Sales';
    protected static string|\BackedEnum|null $navigationIcon='heroicon-o-shopping-bag';
    protected static ?int $navigationSort=1;
    public static function canCreate(): bool { return false; }
    public static function table(Table $table): Table {
        return $table->defaultSort('created_at','desc')->columns([
            TextColumn::make('order_number')->searchable()->label('Order'),
            TextColumn::make('customer_name')->searchable()->description(fn(Order $r)=>$r->phone),
            TextColumn::make('grand_total')->money('BDT')->sortable(),
            TextColumn::make('status')->badge()->color(fn(string $state)=>match($state){'delivered'=>'success','cancelled','returned'=>'danger',default=>'warning'}),
            TextColumn::make('payment_method')->badge(), TextColumn::make('payment_status')->badge(),
            TextColumn::make('created_at')->dateTime('d M Y, H:i')->sortable(),
        ])->filters([
            SelectFilter::make('status')->options(array_combine($s=['pending_confirmation','pending_payment_verification','confirmed','processing','shipped','delivered','cancelled','returned'],$s)),
            SelectFilter::make('payment_method')->options(['cod'=>'Cash on Delivery','bkash'=>'bKash']),
            SelectFilter::make('payment_status')->options(['pending'=>'Pending','submitted'=>'Submitted','paid'=>'Paid','rejected'=>'Rejected']),
        ])->recordActions([
            Action::make('details')->label('View')->icon('heroicon-o-eye')->modalHeading(fn(Order $r)=>$r->order_number)->modalContent(fn(Order $record)=>view('filament.orders.details',['order'=>$record->load('items','payment','histories')]))->modalSubmitAction(false),
            Action::make('status')->label('Update status')->schema([Select::make('status')->options(['confirmed'=>'Confirmed','processing'=>'Processing','shipped'=>'Shipped','delivered'=>'Delivered','cancelled'=>'Cancelled','returned'=>'Returned'])->required(),Textarea::make('note')->label('Internal note')])->action(fn(Order $record,array $data)=>app(OrderOperations::class)->transition($record,$data['status'],auth()->id(),$data['note']??null)),
            Action::make('verify')->label('Verify bKash')->color('success')->visible(fn(Order $r)=>auth()->user()->can('verify-payments') && $r->payment_status==='submitted')->schema([Select::make('decision')->options(['approve'=>'Approve','reject'=>'Reject'])->required(),Textarea::make('note')->required()])->action(function(Order $record,array $data){abort_unless(auth()->user()->can('verify-payments'),403);app(OrderOperations::class)->verify($record,$data['decision']==='approve',auth()->id(),$data['note']);}),
        ]);
    }
    public static function getPages(): array { return ['index'=>Pages\ManageOrders::route('/')]; }
}
