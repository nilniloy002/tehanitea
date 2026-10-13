<?php
namespace App\Services;
use App\Models\{Order,InventoryMovement,ProductVariant};
use Illuminate\Support\Facades\DB;
class OrderOperations {
    public function transition(Order $record,string $status,?int $actor,?string $note=null): void {
        DB::transaction(function() use($record,$status,$actor,$note) {
            $o=Order::lockForUpdate()->findOrFail($record->id);
            $allowed=['pending_confirmation'=>['confirmed','cancelled'],'pending_payment_verification'=>['cancelled'],'confirmed'=>['processing','cancelled'],'processing'=>['shipped','cancelled'],'shipped'=>['delivered','returned'],'delivered'=>['returned'],'returned'=>[],'cancelled'=>[]];
            if (!in_array($status,$allowed[$o->status]??[])) Commerce::fail('This status transition is not allowed.');
            if ($status==='cancelled' && !$o->stock_restored) {
                foreach($o->items()->orderBy('product_variant_id')->get() as $item) {
                    $v=ProductVariant::lockForUpdate()->find($item->product_variant_id);
                    if (!$v) continue;
                    $v->incrementQuietly('stock_quantity',$item->quantity);
                    InventoryMovement::create(['product_variant_id'=>$v->id,'user_id'=>$actor,'quantity_change'=>$item->quantity,'quantity_after'=>$v->stock_quantity,'reason'=>'Cancelled '.$o->order_number]);
                }
                $o->stock_restored=true;
            }
            $o->status=$status;
            if($status==='delivered' && $o->payment_method==='cod') { $o->payment_status='paid'; $o->payment()->update(['status'=>'paid']); }
            $o->save(); $o->histories()->create(['status'=>$status,'user_id'=>$actor,'note'=>$note]);
        });
    }
    public function verify(Order $record,bool $approve,int $actor,string $note): void {
        DB::transaction(function() use($record,$approve,$actor,$note) {
            $o=Order::lockForUpdate()->findOrFail($record->id);
            if($o->status!=='pending_payment_verification' || $o->payment_status!=='submitted') Commerce::fail('This payment is not awaiting verification.');
            $o->payment()->update(['status'=>$approve?'paid':'rejected','verified_by'=>$actor,'verified_at'=>now(),'verification_note'=>$note]);
            $o->update(['payment_status'=>$approve?'paid':'rejected','status'=>$approve?'confirmed':'pending_payment_verification']);
            $o->histories()->create(['status'=>$o->status,'user_id'=>$actor,'note'=>($approve?'bKash approved: ':'bKash rejected: ').$note]);
        });
    }
}
