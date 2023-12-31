<?php

namespace App\Http\Controllers\Admin;

use App\Models\Payment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PaymentsController extends Controller
{
    public function all()
    {
        $payments = Payment::paginate(10);
        return view('admin.payments.all',compact('payments'));
    }
}
