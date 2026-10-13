<?php

namespace Database\Seeders;

use App\Models\{Category, Product};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TehaniFinalCatalogSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $category = Category::firstOrCreate(['slug'=>'premium-tea'], ['name'=>'Premium Tea','name_bn'=>'প্রিমিয়াম চা','is_active'=>true]);
            // Keep the existing Green Tea URL, images and order-linked variant IDs.
            $catalog = [
                ['full-leaf-green-tea','Orthodox Green Tea (Full Leaf)','Whole-leaf green tea for a refreshing tea-drinking experience.','TGT',[[125,375],[250,700],[500,1350]]],
                ['premium-gold-tea','Premium Gold Tea','গাঢ় রং, মনভরা ঘ্রাণ ও ভরপুর স্বাদের জন্য বড় দানার প্রিমিয়াম চা।','TPG',[[500,350],[1000,700]]],
                ['bt-2-tea','BT 2 Tea','Part of the Tehani Premium Tea Collection. Choose your favourite pack.','TBT2',[[500,300],[1000,600]]],
                ['season-flush-tea','Season Flush Tea','Our Finest Premium Quality Collection','TSF',[[500,500],[1000,980]]],
            ];
            foreach ($catalog as $sort => [$slug,$name,$description,$prefix,$packs]) {
                $product=Product::firstOrNew(['slug'=>$slug]);
                $product->fill(['category_id'=>$category->id,'name'=>$name,'name_bn'=>$name,'short_description'=>$description,'is_featured'=>true,'is_active'=>true,'sort_order'=>$sort]);
                if (!$product->exists || $slug==='full-leaf-green-tea') $product->description=$description;
                $product->save();
                $retained=[];
                foreach ($packs as $packSort => [$grams,$price]) {
                    $variant=$product->variants()->where('weight_grams',$grams)->first()
                        ?? $product->variants()->make(['sku'=>$prefix.'-'.$grams,'stock_quantity'=>0,'low_stock_threshold'=>5]);
                    $variant->fill(['name'=>$grams>=1000 ? ($grams/1000).'kg' : $grams.'g','weight_grams'=>$grams,'regular_price'=>$price,'price'=>0,'is_active'=>true,'sort_order'=>$packSort]);
                    $variant->save();
                    $retained[]=$variant->id;
                }
                // Retire obsolete packs without deleting stock or historical order references.
                $product->variants()->whereNotIn('id',$retained)->update(['is_active'=>false]);
                $this->command?->info($name.': '.implode(', ',array_map(fn($p)=>$p[0].'g = BDT '.$p[1],$packs)));
            }
        });
    }
}
