<?php
namespace Tests\Feature;
use App\Models\{Product,ProductVariant,DeliveryZone,TaxRate,Coupon,Order,StoreSetting,User};
use App\Services\{Commerce,OrderOperations};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
class CommerceTest extends TestCase {
    use RefreshDatabase;
    private function setupStore(): array {
        $p=Product::create(['name'=>'Test tea','slug'=>'test-tea','is_active'=>true]);
        $v=ProductVariant::create(['product_id'=>$p->id,'name'=>'250g','sku'=>'TEST-250','price'=>250,'regular_price'=>300,'stock_quantity'=>10,'is_active'=>true]);
        $z=DeliveryZone::create(['name'=>'Dhaka','name_bn'=>'ঢাকা','charge'=>60,'is_active'=>true]);
        return [$v,$z];
    }
    private function customer($z):array{return ['name'=>'Test customer','phone'=>'+8801712345678','address'=>'Test address, Dhaka 1200','delivery_zone_id'=>$z->id,'payment_method'=>'cod'];}
    public function test_cod_checkout_snapshots_totals_and_reduces_stock():void {
        [$v,$z]=$this->setupStore();
        $response=$this->withSession(['cart'=>[$v->id=>2]])->post('/checkout',$this->customer($z));
        $order=Order::firstOrFail(); $response->assertRedirect(route('confirmation',$order->public_token));
        $this->assertEquals(560,$order->grand_total);$this->assertEquals(8,$v->fresh()->stock_quantity);
        $this->assertEquals('01712345678',$order->phone);$this->assertCount(1,$order->items);
        $this->get(route('confirmation',$order->public_token))->assertOk()->assertSee($order->order_number);
    }
    public function test_coupon_and_exclusive_tax_are_server_calculated():void {
        [$v,$z]=$this->setupStore();
        Coupon::create(['code'=>'SAVE10','type'=>'percentage','value'=>10,'is_active'=>true]);
        TaxRate::create(['name'=>'Test tax','percentage'=>5,'is_inclusive'=>false,'is_active'=>true]);
        $q=app(Commerce::class)->quote([$v->id=>2],$z->id,'SAVE10');
        $this->assertSame(5000,$q['discount']);$this->assertSame(2250,$q['exclusiveTax']);$this->assertSame(53250,$q['total']);
    }
    public function test_out_of_stock_cannot_be_added_or_ordered():void {
        [$v,$z]=$this->setupStore();
        $this->post('/cart',['variant_id'=>$v->id,'quantity'=>11])->assertSessionHasErrors('order');
        $this->withSession(['cart'=>[$v->id=>11]])->post('/checkout',$this->customer($z))->assertSessionHasErrors('order');
        $this->assertDatabaseCount('orders',0);$this->assertEquals(10,$v->fresh()->stock_quantity);
    }
    public function test_bkash_requires_id_and_creates_verification_record():void {
        [$v,$z]=$this->setupStore();
        StoreSetting::create(['key'=>'bkash_active','value'=>'1']); StoreSetting::create(['key'=>'bkash_number','value'=>'01700000000']);
        $data=array_merge($this->customer($z),['payment_method'=>'bkash']);
        $this->withSession(['cart'=>[$v->id=>1]])->post('/checkout',$data)->assertSessionHasErrors('transaction_id');
        $this->withSession(['cart'=>[$v->id=>1]])->post('/checkout',$data+['transaction_id'=>'TESTTX123'])->assertRedirect();
        $o=Order::firstOrFail();$this->assertSame('submitted',$o->payment_status);$this->assertSame('TESTTX123',$o->payment->transaction_id);
        $this->assertSame('01700000000',$o->payment->bkash_number);
    }
    public function test_cancellation_restores_stock_once():void {
        [$v,$z]=$this->setupStore();$o=app(Commerce::class)->place([$v->id=>2],$this->customer($z));
        app(OrderOperations::class)->transition($o,'cancelled',null);
        $this->assertEquals(10,$v->fresh()->stock_quantity);
        try { app(OrderOperations::class)->transition($o,'cancelled',null); $this->fail('Repeated cancellation should fail'); } catch(ValidationException $e) { $this->assertEquals(10,$v->fresh()->stock_quantity); }
    }
    public function test_customer_coupon_limit_and_expiry():void {
        [$v,$z]=$this->setupStore();$c=Coupon::create(['code'=>'ONCE','type'=>'fixed','value'=>20,'per_customer_limit'=>1,'is_active'=>true]);
        app(Commerce::class)->place([$v->id=>1],$this->customer($z)+['coupon'=>'ONCE']);
        $this->withSession(['cart'=>[$v->id=>1]])->post('/checkout',$this->customer($z)+['coupon'=>'ONCE'])->assertSessionHasErrors('order');
        $c->update(['ends_at'=>now()->subDay()]);
        $this->postJson('/checkout/quote',['coupon'=>'ONCE'])->assertUnprocessable();
    }
    public function test_pages_and_admin_product_actions_render():void {
        [$v,$z]=$this->setupStore();
        foreach(['/','/shop','/products/test-tea','/cart','/about','/delivery','/contact','/tracking'] as $url) $this->get($url)->assertOk();
        config(['app.env'=>'local']); // Match the existing local-only Filament access policy.
        $u=User::factory()->create(['email'=>'admin@tehani.local']);$this->actingAs($u);
        foreach(['/admin','/admin/products','/admin/products/'.$v->product_id,'/admin/products/'.$v->product_id.'/edit','/admin/orders','/admin/coupons'] as $url) $this->get($url)->assertOk();
        $this->get('/admin/products')->assertSee('View')->assertSee('Edit')->assertSee('Delete');
    }
    public function test_payment_verification_permission_is_restricted():void {
        $owner=User::factory()->create(['email'=>'admin@tehani.local']);$other=User::factory()->create();
        $this->assertTrue($owner->can('verify-payments'));$this->assertFalse($other->can('verify-payments'));
    }
    public function test_checkout_page_and_zone_quote_render():void {
        [$v,$z]=$this->setupStore();
        $this->withSession(['cart'=>[$v->id=>2]])->get('/checkout')->assertOk()->assertSee('checkoutForm')->assertSee('Cash on Delivery');
        $this->postJson('/checkout/quote',['delivery_zone_id'=>$z->id])->assertOk()->assertJsonPath('delivery',6000)->assertJsonPath('total',56000);
    }
    public function test_product_editor_saves_existing_variant_without_duplicate_sku_error():void {
        [$v,$z]=$this->setupStore();
        config(['app.env'=>'local']);
        $this->actingAs(User::factory()->create(['email'=>'admin@tehani.local']));
        \Livewire\Livewire::test(\App\Filament\Resources\Products\Pages\EditProduct::class,['record'=>$v->product_id])
            ->fillForm(['name'=>'Updated tea'])->call('save')->assertHasNoFormErrors();
        $this->assertSame('Updated tea',$v->product->fresh()->name);
        $this->assertSame('TEST-250',$v->fresh()->sku);
    }
    public function test_regular_price_without_offer_supports_cart_buy_now_and_order():void {
        [$v,$z]=$this->setupStore();
        $v->update(['price'=>0,'regular_price'=>450]);
        $this->get('/products/test-tea')->assertOk()->assertSee('\\u0022price\\u0022:450',false)->assertSee('\\u0022available\\u0022:true',false);
        $this->post('/cart',['variant_id'=>$v->id,'quantity'=>2])->assertRedirect(route('cart'));
        $this->get('/cart')->assertSee('900.00');
        $this->patch('/cart',['variant_id'=>$v->id,'quantity'=>1])->assertRedirect();
        $this->post('/cart',['variant_id'=>$v->id,'quantity'=>1,'buy_now'=>1])->assertRedirect(route('checkout'));
        $this->post('/checkout',$this->customer($z))->assertRedirect();
        $this->assertEquals(960,Order::firstOrFail()->grand_total);
    }
    public function test_optional_offer_admin_validation():void {
        [$v,$z]=$this->setupStore();
        config(['app.env'=>'local']);
        $this->actingAs(User::factory()->create(['email'=>'admin@tehani.local']));
        $form=\Livewire\Livewire::test(\App\Filament\Resources\ProductVariants\Pages\EditProductVariant::class,['record'=>$v->id]);
        $form->fillForm(['regular_price'=>450,'price'=>null])->call('save')->assertHasNoFormErrors();
        $this->assertEquals(450,$v->fresh()->effective_price);
        $form->fillForm(['price'=>450])->call('save')->assertHasFormErrors(['price']);
        $form->fillForm(['price'=>500])->call('save')->assertHasFormErrors(['price']);
        $form->fillForm(['price'=>400])->call('save')->assertHasNoFormErrors();
        $this->assertEquals(400,$v->fresh()->effective_price);
        $this->assertSame(40000,app(Commerce::class)->quote([$v->id=>1])['subtotal']);
    }
    public function test_homepage_video_and_gold_pack_purchase_form_render():void {
        [$v,$z]=$this->setupStore();
        $v->product->update(['slug'=>'premium-gold-tea']);
        $this->get('/')->assertOk()->assertSee('cinema-hero')->assertSee('tea-story.mp4')
            ->assertSee('gold-pack-list')->assertSee('name="buy_now"',false)
            ->assertSee(':aria-pressed="selected?.id==='.$v->id.'"',false);
    }
}
