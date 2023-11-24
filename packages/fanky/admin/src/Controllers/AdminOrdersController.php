<?php namespace Fanky\Admin\Controllers;

use Fanky\Admin\Models\Order;

class AdminOrdersController extends AdminController {

    public function getIndex() {
        $per_page = 15;
        $orders = Order::orderBy('created_at', 'desc')->paginate($per_page);

        return view('admin::orders.main', ['orders' => $orders]);
    }

    public function getView($id) {
        $order = Order::find($id);
        $order->update(['new' => 0]);

        $items = $order->products;
        $all_count = 0;
        $all_summ = 0;

        foreach ($items as $item) {
            $all_summ += $item->pivot->price;
            $all_count += $item->pivot->count;
        }

        return view('admin::orders.view', [
            'order'     => $order,
            'items'     => $items,
            'all_count' => $all_count,
            'all_summ'  => $all_summ,
        ]);
    }

    public function postDelete($id) {
        $order = Order::find($id);
        if ($order) $order->delete();

        return ['success' => true];
    }
}

