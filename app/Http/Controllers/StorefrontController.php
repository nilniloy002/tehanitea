<?php
namespace App\Http\Controllers;
use App\Models\{Product,Category,DeliveryZone,StoreSetting,Order,ProductVariant};
use App\Services\Commerce;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StorefrontController extends Controller {
    protected function page(string $view, array $data=[]) {
        $settings=StoreSetting::pluck('value','key');
        $wa='https://wa.me/'.preg_replace('/^0/','880',preg_replace('/\D/','',$settings->get('whatsapp_number','01711893169')));
        return view('store.'.$view,$data+compact('settings','wa')+['cartCount'=>array_sum(session('cart',[])),'zones'=>DeliveryZone::where('is_active',true)->orderBy('sort_order')->get()]);
    }
    public function home() { return $this->page('home',['products'=>Product::with('variants')->where('is_active',true)->orderByDesc('is_featured')->orderBy('sort_order')->get()]); }
    public function shop(Request $r) {
        $q=Product::with('variants','category')->where('is_active',true);
        if ($r->filled('category')) $q->whereHas('category',fn($q)=>$q->where('slug',$r->string('category')));
        if ($r->filled('q')) $q->where('name','like','%'.$r->string('q').'%');
        $products=$q->orderBy('sort_order')->get();
        if (in_array($r->input('sort'),['price_asc','price_desc'])) {
            $products=$products->sortBy(fn($p)=>$p->variants->where('is_active',true)->where('effective_price','>',0)->min('effective_price') ?? PHP_INT_MAX, SORT_REGULAR, $r->input('sort')==='price_desc');
        }
        return $this->page('shop',compact('products')+['categories'=>Category::where('is_active',true)->get()]);
    }
    public function product(Product $product) {
        abort_unless($product->is_active,404);
        $product->load('variants','category');
        return $this->page('product',compact('product')+['related'=>Product::with('variants')->where('is_active',true)->whereKeyNot($product->id)->limit(3)->get()]);
    }
    public function add(Request $r) {
        $d=$r->validate(['variant_id'=>'required|integer','quantity'=>'required|integer|min:1|max:1000','buy_now'=>'nullable|boolean']);
        $cart=$r->session()->get('cart',[]); $id=$d['variant_id'];
        $cart[$id]=($cart[$id]??0)+(int)$d['quantity'];
        app(Commerce::class)->quote($cart);
        $r->session()->put('cart',$cart);
        return redirect($r->boolean('buy_now')?route('checkout'):route('cart'))->with('success','কার্টে যোগ হয়েছে।');
    }
    public function cart() {
        $items=[];
        foreach (session('cart',[]) as $id=>$quantity) {
            $v=ProductVariant::with('product')->find($id);
            if($v) $items[]=['variant'=>$v,'quantity'=>$quantity];
        }
        return $this->page('cart',compact('items'));
    }
    public function update(Request $r) {
        $d=$r->validate(['variant_id'=>'required|integer','quantity'=>'required|integer|min:0|max:1000']);
        $cart=session('cart',[]);
        if ($d['quantity']==0) unset($cart[$d['variant_id']]);
        else {
            app(Commerce::class)->quote([$d['variant_id']=>$d['quantity']]);
            $cart[$d['variant_id']]=$d['quantity'];
        }
        session(['cart'=>$cart]); return back();
    }
    public function checkout(Request $r) {
        if(!session('cart')) return redirect()->route('cart');
        return $this->page('checkout',['quote'=>app(Commerce::class)->quote(session('cart'))]);
    }
    public function quote(Request $r) {
        $d=$r->validate(['delivery_zone_id'=>'nullable|integer','coupon'=>'nullable|string|max:60','phone'=>'nullable|string|max:30']);
        $q=app(Commerce::class)->quote(session('cart',[]),$d['delivery_zone_id']??null,$d['coupon']??null,!empty($d['phone'])?Commerce::phone($d['phone']):null);
        return response()->json(collect($q)->only(['subtotal','discount','delivery','exclusiveTax','total']));
    }
    public function place(Request $r) {
        $r->merge(['transaction_id'=>strtoupper(trim($r->input('transaction_id','')))]);
        $d=$r->validate(['name'=>'required|string|max:120','phone'=>'required|string|max:30','address'=>'required|string|min:10|max:1000','delivery_zone_id'=>'required|integer','note'=>'nullable|string|max:1000','coupon'=>'nullable|string|max:60','payment_method'=>['required',Rule::in(['cod','bkash'])],'transaction_id'=>'exclude_unless:payment_method,bkash|required|string|alpha_num|min:6|max:50|unique:payments,transaction_id'],[
            'name.required'=>'আপনার নাম দিন।','phone.required'=>'আপনার মোবাইল নম্বর দিন।','address.required'=>'পূর্ণ ডেলিভারি ঠিকানা দিন।','transaction_id.required'=>'bKash Transaction ID দিন।','transaction_id.unique'=>'এই Transaction ID ইতিমধ্যে ব্যবহার হয়েছে।',
        ]);
        $order=app(Commerce::class)->place(session('cart',[]),$d);
        session()->forget('cart');
        return redirect()->route('confirmation',$order->public_token);
    }
    public function confirmation(string $token) { return $this->page('confirmation',['order'=>Order::with('items','payment','histories')->where('public_token',$token)->firstOrFail()]); }
    public function info(string $page) { abort_unless(in_array($page,['about','delivery','contact','privacy','terms','returns','tracking']),404); return $this->page('info',compact('page')); }
    public function track(Request $r) {
        $d=$r->validate(['order_number'=>'required|string|max:40','phone'=>'required|string|max:30']);
        $order=Order::where('order_number',$d['order_number'])->where('phone',Commerce::phone($d['phone']))->first();
        if(!$order) return back()->withErrors(['order'=>'অর্ডার নম্বর বা মোবাইল নম্বর মেলেনি।']);
        return redirect()->route('confirmation',$order->public_token);
    }
}
