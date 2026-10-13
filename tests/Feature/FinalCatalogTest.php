<?php

namespace Tests\Feature;

use App\Models\{Product,ProductVariant};
use Database\Seeders\TehaniFinalCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinalCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_final_catalog_preserves_media_stock_and_retires_old_packs(): void
    {
        $gold=Product::create(['name'=>'Premium Gold Tea','slug'=>'premium-gold-tea','image_path'=>'products/existing.jpg','gallery'=>['products/detail.jpg'],'is_active'=>true]);
        $kept=$gold->variants()->create(['name'=>'500g','sku'=>'TPG-500','weight_grams'=>500,'regular_price'=>750,'price'=>650,'stock_quantity'=>10,'is_active'=>true]);
        $old=$gold->variants()->create(['name'=>'2kg','sku'=>'TPG-2000','weight_grams'=>2000,'regular_price'=>1500,'price'=>0,'stock_quantity'=>6,'is_active'=>true]);
        $this->seed(TehaniFinalCatalogSeeder::class);
        $this->seed(TehaniFinalCatalogSeeder::class);
        $this->assertDatabaseCount('products',4);
        $this->assertSame(9,ProductVariant::where('is_active',true)->count());
        $this->assertSame('products/existing.jpg',$gold->fresh()->image_path);
        $this->assertSame(['products/detail.jpg'],$gold->fresh()->gallery);
        $this->assertEquals(10,$kept->fresh()->stock_quantity);
        $this->assertEquals(350,$kept->fresh()->effective_price);
        $this->assertFalse($old->fresh()->is_active);
        $this->assertEquals(6,$old->fresh()->stock_quantity);
        $expected=['full-leaf-green-tea'=>[125=>375,250=>700,500=>1350],'premium-gold-tea'=>[500=>350,1000=>700],'bt-2-tea'=>[500=>300,1000=>600],'season-flush-tea'=>[500=>500,1000=>980]];
        foreach($expected as $slug=>$prices) {
            $p=Product::where('slug',$slug)->firstOrFail();
            foreach($prices as $weight=>$price) {
                $v=$p->variants()->where('weight_grams',$weight)->firstOrFail();
                $this->assertEquals($price,$v->effective_price);
                $this->assertEquals(0,$v->price);
                if($v->id!==$kept->id)$this->assertEquals(0,$v->stock_quantity);
            }
        }
        $this->get('/')->assertOk()->assertSee('Orthodox Green Tea (Full Leaf)')->assertSee('BT 2 Tea')->assertSee('Season Flush Tea')->assertDontSee('2kg');
        $this->get('/products/premium-gold-tea')->assertOk()->assertDontSee('2kg');
    }
}
