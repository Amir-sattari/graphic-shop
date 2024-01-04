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
use App\Models\Payment;
use App\Services\Payment\Requests\IDPayRequest;

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
            $orderItems = json_decode(Cookie::get('basket'),true);

            $products = Product::findMany(array_keys($orderItems));

            $productsPrice = $products->sum('price');

            $refCode = Str::Random(30);
            
            $createdOrder = Order::create([
                'user_id' => $user->id,
                'amount' => $productsPrice,
                'ref_code' => $refCode,
                'status' => 'unpaid',
            ]);

            $orderItemsForCreatedOrder = $products->map(function($product){

                $currentProduct = $product->only('price','id');

                $currentProduct['product_id'] = $currentProduct['id'];

                unset($currentProduct['id']);

                return $currentProduct;
            });


            $createdOrder->orderItems()->createMany($orderItemsForCreatedOrder->toArray());

            $refID = rand(1111,9999);

            $createdPayment = Payment::create([
                'order_id' => $createdOrder->id,
                'gateway' => 'idpay',
                'res_id' => $refID,
                'ref_id' => $refID,
                'status' => 'unpaid',
            ]);

            $idPayRequest = new IDPayRequest([
                'amount' => $productsPrice,
                'user' => $user,
                'orderId' => $refCode,
            ]);
    
            $paymentService = new PaymentService(PaymentService::IDPAY,$idPayRequest);
            return $paymentService->pay();

        } catch (\Exception $e) {
            return back()->with('failed', $e->getMessage());
        }

    }

    public function callback()
    {

    }
}
