<x-filament-widgets::widget>
    <div style="padding:32px;border:1px solid #d5e4d9;border-radius:20px;background:linear-gradient(110deg,#edf5ee,#fafcf8);color:#173d2e">
        <p style="font-size:12px;letter-spacing:2px;font-weight:700;margin-bottom:12px">TEHANI · STORE MANAGEMENT</p>
        <h2 style="font-size:28px;font-weight:700;margin-bottom:10px">Welcome back, {{ auth()->user()->name }}.</h2>
        <p style="color:#60766b;line-height:1.7">Your tea store at a glance. Manage products, keep your packs in stock, and take care of every order.</p>
        <div style="display:flex;flex-wrap:wrap;gap:12px;margin-top:22px">
            <a href="{{ url('/admin/products') }}" style="padding:11px 18px;background:#173d2e;color:white;border-radius:10px">Manage products →</a>
            <a href="{{ url('/') }}" target="_blank" style="padding:11px 18px;border:1px solid #b7cec0;border-radius:10px">Visit storefront ↗</a>
        </div>
    </div>
</x-filament-widgets::widget>
