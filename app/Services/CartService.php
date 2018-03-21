<?php

namespace App\Services;

use App\Order;
use App\Product;
use Auth;
use Carbon\Carbon;

class CartService
{
    public function getSingleProductPrice($product){
        $total = $product->price * $product->quantity;
        return $total;
    }
    public function getTotalCartPrice($order)
    {
         $totalCartPrice = 0;
         foreach ($order->orderProducts as $product)
         {
             $totalCartPrice += $product->quantity * $product->price;
         }
         return $totalCartPrice;
    }
    public function getTotalCartQuantity($order)
    {
        $totalCartQuantity = 0;
        foreach ($order->orderProducts as $product)
        {
            $totalCartQuantity += $product->quantity;
        }
        return $totalCartQuantity;
    }

    public function getStoreOrder($product_id, $request)
    {
        $user = Auth::user();
        $user_order = $user->orders()->asCart()->first();
        $product = Product::findOrfail($product_id);
        if (empty($user_order))
        {
            $order = $user->orders()->create([
                'status' => Order::PENDING,
                'date' => Carbon::now(),
                'type' => Order::ORDER
            ]);
        }else{
            $order = $user_order;
        }
        $order_product = $order->orderProducts->where('product_id', $product_id)->first();
        if ($order_product == null)
        {
            if ($request->quantity <= $product->stock()->first()->amount)
            {
                $quantity  = $request->quantity;
            }else{
                $quantity  = $product->stock()->first()->amount;
                $value = $request->quantity - $product->stock()->first()->amount;
            }
            $order->orderProducts()->create([
                    'product_id' => $product_id,
                    'price' => $product->PriceAmount,
                    'quantity' => $quantity,
                ]);
        }else{
            $amount = $order_product->quantity + $request->quantity;
            if ($amount <= $product->stock()->first()->amount)
            {
                $order_product->update(['quantity' => $amount]);
            }else{
                $order_product->update(['quantity' => $product->stock()->first()->amount]);
                $value = $amount - $product->stock()->first()->amount;
            }

        }
        return $value;
    }
    public function getStoreBackOrder($product_id, $quantity)
    {
        $user = Auth::user();
        $user_backorder = $user->orders()->asCartBackOrder()->first();
        $product = Product::findOrfail($product_id);
        if (empty($user_backorder))
        {
            $backorder = $user->orders()->create([
                'status' => Order::PENDING,
                'date' => Carbon::now(),
                'type' => Order::BACKORDER
            ]);
        }else{
            $backorder = $user_backorder;
        }
        $backorder_product = $backorder->orderProducts->where('product_id', $product_id)->first();
        if ($backorder_product == null)
        {
            $backorder->orderProducts()->create([
                    'product_id' => $product_id,
                    'price' => $product->PriceAmount,
                    'quantity' => $quantity
                ]);
        }else{
            $amount = $backorder_product->quantity + $quantity;
            $backorder_product->update(['quantity' => $amount]);
        }
    }

    public function getStorePreOrder($product_id, $quantity)
    {
        $user = Auth::user();
        $user_preorder = $user->orders()->asCartPreorder()->first();
        $product = Product::findOrfail($product_id);
        if (empty($user_preorder))
        {
            $preorder = $user->orders()->create([
                'status' => Order::PENDING,
                'date' => Carbon::now(),
                'type' => Order::PREORDER
            ]);
        }else{
            $preorder = $user_preorder;
        }
        $preorder_product = $preorder->orderProducts->where('product_id', $product_id)->first();
        if ($preorder_product == null)
        {
            $preorder->orderProducts()->create([
                'product_id' => $product_id,
                'price' => $product->PriceAmount,
                'quantity' => $quantity
            ]);
        }else{
            $amount = $preorder_product->quantity + $quantity;
            $preorder_product->update(['quantity' => $amount]);
        }
    }



}