<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use App\Services\Payment\PaymentService;
use App\Http\Requests\Payment\PayRequest;
use App\Mail\SendOrderedImages;
use App\Models\Payment;
use App\Services\Payment\Requests\IDPayRequest;
use App\Services\Payment\Requests\IDPayVerifyRequest;
use Illuminate\Support\Facades\Mail;

class PaymentController extends Controller
{
    public function pay(PayRequest $request)
    {
        $validatedData = $request->validated();

        $user = User::firstOrCreate(['email' => $validatedData['email']], [
            'name' => $validatedData['name'],
            'mobile' => $validatedData['mobile'],
        ]);

        try {
            $orderItems = json_decode(Cookie::get('basket'), true);

            if (count($orderItems) <= 0)
                throw new \InvalidArgumentException('سبد خرید شما خالی است');

            $products = Product::findMany(array_keys($orderItems));

            $productsPrice = $products->sum('price');

            $refCode = Str::Random(30);

            $createdOrder = Order::create([
                'user_id' => $user->id,
                'amount' => $productsPrice,
                'ref_code' => $refCode,
                'status' => 'unpaid',
            ]);

            $orderItemsForCreatedOrder = $products->map(function ($product) {

                $currentProduct = $product->only('price', 'id');

                $currentProduct['product_id'] = $currentProduct['id'];

                unset($currentProduct['id']);

                return $currentProduct;
            });


            $createdOrder->orderItems()->createMany($orderItemsForCreatedOrder->toArray());

            $refID = rand(1111, 9999);

            $createdPayment = Payment::create([
                'order_id' => $createdOrder->id,
                'gateway' => 'idpay',
                'ref_code' => $refCode,
                'status' => 'unpaid',
            ]);

            $idPayRequest = new IDPayRequest([
                'amount' => $productsPrice,
                'user' => $user,
                'orderId' => $refCode,
                'apiKey' => 'e2c63665-8002-4d41-8917-0fc9e3a514b7',
                // 'apiKey' => config('services.gateways.id_pay.api_key'),
            ]);


            $paymentService = new PaymentService(PaymentService::IDPAY, $idPayRequest);
            return $paymentService->pay();
        } catch (\Exception $e) {
            return back()->with('failed', $e->getMessage());
        }
    }

    public function callback(Request $request)
    {
        $paymentInfo = $request->all();

        $idPayVerifyRequest = new IDPayVerifyRequest([
            'orderId' => $paymentInfo['order_id'],
            'id' => $paymentInfo['id'],
            'apiKey' => 'e2c63665-8002-4d41-8917-0fc9e3a514b7',
        ]);

        $paymentService = new PaymentService(PaymentService::IDPAY, $idPayVerifyRequest);
        $result = $paymentService->verify();

        if (!$result['status'])
            return redirect()->route('home.checkout.show')->with('failed', 'پرداخت شما انجام نشد');

            $currentPayment = Payment::where('ref_code', $result['data']['order_id'])->first();

            $currentPayment->update([
                'status' => 'paid',
                'res_id' => $result['data']['track_id'],
            ]);
    
            $currentPayment->Order()->update([
                'status' => 'paid',
            ]);
    
            $currentUser = $currentPayment->order->user;

            $bougthImages = $currentPayment->order->orderItems->map(function($orderItem){
                return $orderItem->product->source_url;
            });

            
            Mail::to($currentUser)->send(new SendOrderedImages($bougthImages->toArray(),$currentUser));

            Cookie::queue('basket',null);

            return redirect()->route('home.products.all')->with('success','خرید شما انجام شد و تصاویر برای شما ایمیل شدند');

        if ($result['status'] == 101)
            return redirect()->route('home.checkout.show')->with('failed', 'پرداخت شما قبلا تایید شده و تصاویر برای شما ایمیل شده است');


    }
}
