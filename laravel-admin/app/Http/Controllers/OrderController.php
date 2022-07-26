<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index() {
        $orders = Order::with('orderItems')->paginate();
        return OrderResource::collection($orders);
    }

    public function show($id) {
        $order = Order::with('orderItems')->find($id);
        return new OrderResource($order);
    }

    public function export() {
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=orders.csv",
            "Pragma" => "no-cache",
            "Cache-control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0",
        ];

        $callback = function() {
            $orders = Order::all();
            $file = fopen('php://output', 'w');

            fputcsv($file, ['ID', 'Name', 'Email', 'Order Title', 'Price', 'Quantity']);

            foreach($orders as $order) {
                fputcsv($file, [$order->id, $order->name, $order->email, '', '', '']);

                foreach($order->orderItem as $orderItem) {
                    fputcsv($file, ['', '', '', $orderItem->product_title, $orderItem->price, $orderItem->quantity]);
                }
            }

            fclose($file);
        };

        return \Response::stream($callback, 200, $headers);
    }

    public function chart() {
        return $orders = Order::query()
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->selectRaw("DATE_FORMAT(orders.create_at, '%Y-%m-%d') as date, SUM(order_items.price * order_items.quantity) as sum")
            ->groupBy('date')
            ->get();
    }
}
