<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StorefrontController as Store;

Route::get('/',[Store::class,'home'])->name('home');
Route::get('/shop',[Store::class,'shop'])->name('shop');
Route::get('/products/{product:slug}',[Store::class,'product'])->name('product');
Route::get('/cart',[Store::class,'cart'])->name('cart');
Route::post('/cart',[Store::class,'add'])->block()->name('cart.add');
Route::patch('/cart',[Store::class,'update'])->block()->name('cart.update');
Route::get('/checkout',[Store::class,'checkout'])->name('checkout');
Route::post('/checkout/quote',[Store::class,'quote'])->middleware('throttle:60,1')->name('checkout.quote');
Route::post('/checkout',[Store::class,'place'])->middleware('throttle:10,1')->block()->name('checkout.place');
Route::get('/orders/{token}',[Store::class,'confirmation'])->name('confirmation');
Route::post('/tracking',[Store::class,'track'])->middleware('throttle:10,1');
Route::get('/{page}',[Store::class,'info'])->whereIn('page',['about','delivery','contact','privacy','terms','returns','tracking'])->name('info');
