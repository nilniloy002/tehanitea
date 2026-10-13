<?php
namespace App\Services;
use App\Models\{ProductVariant,DeliveryZone,TaxRate,Coupon,CouponUsage,Customer,Order,StoreSetting,InventoryMovement};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class Commerce {
    public static function fail(string $message): never { throw ValidationException::withMessages(['order' => $message]); }
    public static function phone(string $phone): string {
        $phone = preg_replace('/[^0-9]/', '', strtr($phone, ['০'=>'0','১'=>'1','২'=>'2','৩'=>'3','৪'=>'4','৫'=>'5','৬'=>'6','৭'=>'7','৮'=>'8','৯'=>'9']));
        if (str_starts_with($phone, '880')) $phone = substr($phone, 2);
        if (!preg_match('/^01[3-9][0-9]{8}$/', $phone)) static::fail('সঠিক ১১ সংখ্যার মোবাইল নম্বর দিন।');
        return $phone;
    }
    public function quote(array $cart, ?int $zoneId = null, ?string $code = null, ?string $phone = null, bool $lock = false): array {
        $items = []; $subtotal = 0;
        foreach (collect($cart)->sortKeys() as $id => $quantity) {
            $query = ProductVariant::with('product')->whereKey($id);
            if ($lock) $query->lockForUpdate();
            $variant = $query->first();
            if (!$variant || !$variant->is_active || !$variant->product->is_active || $variant->effective_price <= 0) static::fail('এই প্যাকটি এখন অর্ডারের জন্য উপলব্ধ নয়।');
            if ($quantity < 1 || $quantity > $variant->stock_quantity) static::fail($variant->product->name.' — '.$variant->name.': পর্যাপ্ত স্টক নেই।');
            $unit = (int) round((float) $variant->effective_price * 100);
            $items[] = ['variant'=>$variant, 'quantity'=>(int)$quantity, 'unit'=>$unit, 'total'=>$unit*$quantity];
            $subtotal += $unit*$quantity;
        }
        $zone = $zoneId ? DeliveryZone::where('is_active',true)->find($zoneId) : null;
        if ($zoneId && !$zone) static::fail('সঠিক ডেলিভারি এলাকা নির্বাচন করুন।');
        $delivery = $zone ? (int) round((float)$zone->charge*100) : 0;
        if ($zone && $zone->free_delivery_threshold !== null && $subtotal >= (int)round((float)$zone->free_delivery_threshold*100)) $delivery=0;
        $coupon=null; $discount=0;
        if ($code) {
            $query=Coupon::where('code',strtoupper(trim($code)));
            if ($lock) $query->lockForUpdate();
            $coupon=$query->first();
            if (!$coupon || !$coupon->is_active || ($coupon->starts_at && $coupon->starts_at->isFuture()) || ($coupon->ends_at && $coupon->ends_at->isPast())) static::fail('কুপনটি সঠিক নয় অথবা মেয়াদ শেষ হয়েছে।');
            if ($subtotal < (int)round((float)$coupon->minimum_order_amount*100)) static::fail('এই কুপনের জন্য ন্যূনতম অর্ডারের পরিমাণ পূরণ হয়নি।');
            if ($coupon->usage_limit !== null && $coupon->usage_count >= $coupon->usage_limit) static::fail('কুপন ব্যবহারের সীমা শেষ হয়েছে।');
            if ($phone && $coupon->per_customer_limit && CouponUsage::where('coupon_id',$coupon->id)->where('phone',$phone)->count() >= $coupon->per_customer_limit) static::fail('আপনি এই কুপনটি সর্বোচ্চ সংখ্যক বার ব্যবহার করেছেন।');
            if ($coupon->type==='free_delivery') $delivery=0;
            else {
                $discount=$coupon->type==='percentage' ? (int)round($subtotal*(float)$coupon->value/100) : (int)round((float)$coupon->value*100);
                if ($coupon->maximum_discount_amount !== null) $discount=min($discount,(int)round((float)$coupon->maximum_discount_amount*100));
                $discount=max(0,min($subtotal,$discount));
            }
        }
        $tax=0; $exclusiveTax=0; $taxes=[];
        foreach (TaxRate::where('is_active',true)->get() as $rate) {
            $amount=(int)round(($subtotal-$discount)*(float)$rate->percentage/($rate->is_inclusive ? 100+(float)$rate->percentage : 100));
            $tax+=$amount; if (!$rate->is_inclusive) $exclusiveTax+=$amount;
            $taxes[]=['name'=>$rate->name,'percentage'=>$rate->percentage,'inclusive'=>$rate->is_inclusive,'amount'=>$amount/100];
        }
        return compact('items','subtotal','zone','delivery','coupon','discount','tax','exclusiveTax','taxes')+['total'=>$subtotal-$discount+$delivery+$exclusiveTax];
    }
    public function place(array $cart, array $data): Order {
        return DB::transaction(function() use($cart,$data) {
            if (!$cart) static::fail('আপনার কার্ট খালি।');
            $phone=static::phone($data['phone']);
            // Serialize checkouts for the same phone so customer coupon limits cannot race.
            Customer::firstOrCreate(['phone'=>$phone], ['name'=>$data['name']]);
            $customer=Customer::where('phone',$phone)->lockForUpdate()->firstOrFail();
            $quote=$this->quote($cart,(int)$data['delivery_zone_id'],$data['coupon']??null,$phone,true);
            $settings=StoreSetting::pluck('value','key');
            $bkash=$data['payment_method']==='bkash';
            if ($bkash && ($settings->get('bkash_active')!=='1' || !$settings->get('bkash_number'))) static::fail('bKash পেমেন্ট এখন চালু নেই।');
            if (!$bkash && $settings->get('cod_active','1')!=='1') static::fail('Cash on Delivery এখন চালু নেই।');
            $customer->update(['name'=>$data['name'],'default_address'=>$data['address']]);
            $order=Order::create([
                'order_number'=>'TH-'.now()->format('Ymd').'-'.strtoupper(Str::random(8)), 'public_token'=>(string)Str::uuid(),
                'customer_id'=>$customer->id, 'customer_name'=>$data['name'], 'phone'=>$phone, 'delivery_address'=>$data['address'], 'customer_note'=>$data['note']??null,
                'delivery_zone_id'=>$quote['zone']->id, 'delivery_zone_name'=>$quote['zone']->name_bn ?: $quote['zone']->name,
                'coupon_id'=>$quote['coupon']?->id, 'coupon_code'=>$quote['coupon']?->code, 'subtotal'=>$quote['subtotal']/100,
                'discount_total'=>$quote['discount']/100, 'delivery_total'=>$quote['delivery']/100, 'tax_total'=>$quote['tax']/100, 'tax_snapshot'=>$quote['taxes'],
                'grand_total'=>$quote['total']/100, 'payment_method'=>$data['payment_method'], 'payment_status'=>$bkash?'submitted':'pending',
                'status'=>$bkash?'pending_payment_verification':'pending_confirmation',
            ]);
            foreach ($quote['items'] as $item) {
                $v=$item['variant'];
                $order->items()->create(['product_variant_id'=>$v->id,'product_name'=>$v->product->name,'variant_name'=>$v->name,'sku'=>$v->sku,'unit_price'=>$item['unit']/100,'quantity'=>$item['quantity'],'line_total'=>$item['total']/100]);
                $v->decrementQuietly('stock_quantity',$item['quantity']);
                InventoryMovement::create(['product_variant_id'=>$v->id,'quantity_change'=>-$item['quantity'],'quantity_after'=>$v->stock_quantity,'reason'=>'Order '.$order->order_number]);
            }
            $order->payment()->create(['method'=>$data['payment_method'],'status'=>$order->payment_status,'amount'=>$order->grand_total,'transaction_id'=>$bkash?strtoupper(trim($data['transaction_id'])):null,'bkash_number'=>$bkash?$settings->get('bkash_number'):null,'bkash_account_name'=>$bkash?$settings->get('bkash_account_name'):null]);
            $order->histories()->create(['status'=>$order->status,'note'=>'Guest checkout']);
            if ($quote['coupon']) {
                $quote['coupon']->increment('usage_count');
                CouponUsage::create(['coupon_id'=>$quote['coupon']->id,'order_id'=>$order->id,'customer_id'=>$customer->id,'phone'=>$phone,'discount_amount'=>$quote['discount']/100]);
            }
            return $order;
        },3);
    }
}
