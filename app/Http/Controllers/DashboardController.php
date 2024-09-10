<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function DashboardPage(){
        return view('pages.dashboard.dashboard-page');
    }

    public function Summary(Request $request){
        
        $user_id = $request->header('id');

        $product = Product::where('user_id',$user_id)->count();
        $category = Category::where('user_id',$user_id)->count();
        $customer = Customer::where('user_id',$user_id)->count();
        $invoice = Invoice::where('user_id',$user_id)->count();
        $total = Invoice::where('user_id',$user_id)->sum('total');
        $vat = Invoice::where('user_id',$user_id)->sum('vat');
        $payable = Invoice::where('user_id',$user_id)->sum('payable');
        
        return [
            'product' => $product,
            'category' => $category,
            'customer' => $customer,
            'invoice' => $invoice,
            'total' => round($total,2),
            'vat' => round($vat,2),
            'payable' => round($payable,2)
        ];


    } 

}
