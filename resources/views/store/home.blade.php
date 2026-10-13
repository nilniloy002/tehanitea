@extends('store.layout')
@section('content')
<section class="cinema-hero" x-data="{playing:false}" x-init="if(!window.matchMedia('(prefers-reduced-motion: reduce)').matches){ $refs.film.play().catch(()=>{}) }">
    <video x-ref="film" muted loop playsinline preload="metadata" poster="{{ asset('media/tea-moments.webp') }}" @play="playing=true" @pause="playing=false" aria-label="Tehani tea story background"><source src="{{ asset('media/tea-story.mp4') }}" type="video/mp4"></video>
    <div class="cinema-shade"></div>
    <div class="wrap cinema-content">
        <span class="eyebrow">TEHANI PREMIUM TEA · MADE FOR YOUR MOMENTS</span>
        <h1>এক কাপ চা নয়,<br>এক কাপ <em>ভালোবাসা।</em></h1>
        <p>স্বাদে ভরপুর, বিশুদ্ধতায় বিশ্বস্ত।<br>আপনার প্রতিদিনের চায়ের সঙ্গী, এখন সারা বাংলাদেশে।</p>
        <div class="button-row"><a class="btn cinema-primary" href="#collection">আপনার চা বেছে নিন ↗</a><a class="cinema-link" href="{{ $wa }}">WhatsApp-এ জানুন ↗</a></div>
    </div>
    <div class="wrap cinema-bottom"><a href="#collection">SCROLL TO DISCOVER <span>↓</span></a><span>একটু থামুন। চা হোক। গল্প হোক।</span><button type="button" @click="playing ? $refs.film.pause() : $refs.film.play().catch(()=>{})" :aria-label="playing ? 'ভিডিও থামান' : 'ভিডিও চালু করুন'" x-text="playing ? 'Ⅱ PAUSE' : '▷ PLAY'">▷ PLAY</button></div>
</section>
<section class="trust-strip wrap">@foreach(['✦'=>'বড় দানার প্রিমিয়াম চা','♧'=>'স্বাদে ভরপুর','♡'=>'বিশুদ্ধতায় বিশ্বস্ত','↗'=>'সারা বাংলাদেশে ডেলিভারি'] as $icon=>$text)<div><span>{{ $icon }}</span>{{ $text }}</div>@endforeach</section>
<section class="section wrap collection-editorial" id="collection">
    <div class="section-heading"><div><span class="eyebrow">01 / THE TEHANI COLLECTION</span><h2>Tehani Premium Tea Collection</h2><div class="collection-promises"><span>Exceptional Quality</span><span>Rich Aroma</span><span>Refreshing Taste</span></div><p>Discover our premium selection of teas, carefully chosen to deliver a delightful tea-drinking experience.</p></div><a class="text-link" href="{{ route('shop') }}">সকল পণ্য দেখুন ↗</a></div>
    <div class="collection-grid">
    @foreach($products->take(4) as $product)
        @php
            $starting=$product->variants->where('is_active',true)->where('effective_price','>',0)->min('effective_price');
        @endphp
        <article class="collection-tile {{ $loop->even ? 'leaf-tile' : '' }}">
            <a class="collection-photo" href="{{ route('product',$product->slug) }}" aria-label="{{ $product->name }} দেখুন">
                @if($product->image_path && !str_starts_with($product->image_path, 'media/'))
                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy">
                @else
                    <div class="photo-pending"><span>TEHANI</span><strong>{{ $product->name }}</strong><small>PRODUCT PHOTO COMING SOON</small></div>
                @endif
                <span class="collection-explore">EXPLORE THE TEA ↗</span>
            </a>
            <div class="collection-info"><div><h3>{{ $product->name }}</h3><p>{{ $product->short_description }}</p><div class="catalog-packs">@foreach($product->variants->where('is_active',true)->sortBy('sort_order') as $variant)<span>{{ $variant->name }} <b>৳{{ number_format($variant->effective_price,0) }}</b></span>@endforeach</div></div><div class="collection-price"><strong>{{ $starting ? '৳'.number_format($starting,0).' থেকে' : 'মূল্য জানতে যোগাযোগ করুন' }}</strong><a href="{{ route('product',$product->slug) }}">প্যাক দেখুন ↗</a></div></div>
        </article>
    @endforeach
    </div>
</section>
@php
 $gold=$products->firstWhere('slug','premium-gold-tea');
 $packs=$gold?->variants->where('is_active',true)->sortBy('sort_order')->values()->map(fn($v)=>['id'=>$v->id,'name'=>$v->name,'price'=>$v->effective_price,'available'=>$v->is_active && $v->stock_quantity>0 && $v->effective_price>0]) ?? collect();
 $firstPack=$packs->firstWhere('available',true) ?? $packs->first();
@endphp
@if($gold)
<section class="gold-story" x-data="{selected:@js($firstPack)}">
    <div class="wrap gold-story-grid">
        <div class="gold-story-photo"><img src="{{ $gold->image_url }}" alt="{{ $gold->name }}" loading="lazy"><span class="gold-photo-caption">THE EVERYDAY FAVOURITE <b>GOLD, IN EVERY MOMENT.</b></span></div>
        <div class="gold-story-copy"><span class="eyebrow">02 / RICH. AROMATIC. FULL OF LIFE.</span><h2>প্রতিদিনের আড্ডায়,<br><em>একটু সোনালি ছোঁয়া।</em></h2><h3>Premium Gold Tea</h3><p>গাঢ় রং, মনভরা ঘ্রাণ ও ভরপুর স্বাদের একটি পরিপূর্ণ কাপের জন্য। সকালের শুরু থেকে পরিবারের আড্ডা—প্রতিটি মুহূর্ত হোক আরও সুন্দর।</p>
        <div class="gold-notes"><span>গাঢ় রং</span><span>মনভরা ঘ্রাণ</span><span>ভরপুর স্বাদ</span></div>
        <div class="gold-pack-title">আপনার প্যাক সাইজ বেছে নিন</div>
        <div class="gold-pack-list">@foreach($packs as $pack)<button type="button" @click="selected=@js($pack)" :class="{'chosen':selected?.id==={{ $pack['id'] }}}" :aria-pressed="selected?.id==={{ $pack['id'] }}">{{ $pack['name'] }}</button>@endforeach</div>
        <div class="gold-purchase"><div aria-live="polite"><strong x-text="selected?.price>0 ? '৳'+selected.price.toLocaleString() : 'মূল্য জানতে যোগাযোগ করুন'"></strong><small x-text="selected?.available ? 'স্টকে আছে · সারা বাংলাদেশে ডেলিভারি' : 'এই প্যাক এখন উপলব্ধ নয়'"></small></div>
        <form method="post" action="{{ route('cart.add') }}">@csrf<input type="hidden" name="variant_id" :value="selected?.id"><input type="hidden" name="quantity" value="1"><button class="btn" name="buy_now" value="1" :disabled="!selected?.available">এখনই কিনুন ↗</button></form></div>
        <a class="gold-details" href="{{ route('product',$gold->slug) }}">পণ্য সম্পর্কে আরও জানুন →</a></div>
    </div>
</section>
@endif
<section class="section wrap"><div class="section-heading"><div><span class="eyebrow">SMALL MOMENTS, BEAUTIFULLY BREWED</span><h2>প্রতিদিনের ছোট মুহূর্তে Tehani</h2></div><p>একটু থামুন। চা হোক। গল্প হোক।</p></div><div class="moments">@foreach(['সকালের শুরু','পরিবারের সঙ্গে সময়','অতিথি আপ্যায়ন','নিজের জন্য একটি বিরতি'] as $moment)<a class="moment" href="{{ url('/about') }}"><span class="moment-number">0{{ $loop->iteration }}</span><span class="moment-line"></span><h3>{{ $moment }}</h3><span>এক কাপ ভালোবাসা ↗</span></a>@endforeach</div></section>
<section class="delivery-strip"><div class="wrap"><span class="eyebrow">FROM OUR STORE TO YOUR DOOR</span><h2>চায়ের টানে, আপনার ঠিকানায়</h2><div class="delivery-grid">@foreach($zones as $zone)<div><span>{{ $zone->name_bn }}</span><strong>৳{{ number_format($zone->charge,0) }}</strong></div>@endforeach<div><span>সহজ পেমেন্ট</span><strong>COD / bKash</strong></div></div><a href="{{ url('/delivery') }}" class="text-link">ডেলিভারি ও পেমেন্ট সম্পর্কে জানুন →</a></div></section>
@endsection
