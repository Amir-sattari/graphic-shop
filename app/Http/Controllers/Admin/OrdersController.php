<?php

namespace App\Http\Controllers\Admin;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OrdersController extends Controller
{
    public function all()
    {
        $orders = Order::paginate(10);
        return view('admin.orders.all',compact('orders'));
    }
}
