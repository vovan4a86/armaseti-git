<?php namespace Fanky\Admin;

use Fanky\Admin\Models\Product;
use Session;

class Cart {

    private static $key = 'cart';

    public static function add($item) {
        $cart = self::all();

        $cart[$item['id']] = $item;
        Session::put(self::$key, $cart);
    }

    public static function remove($id) {
        $cart = self::all();
        unset($cart[$id]);
        Session::put(self::$key, $cart);
    }

    public static function ifInCart($id): bool {
        $cart = self::all();
        return isset($cart[$id]);
    }

    public static function updateCount($id, $count) {
        $cart = self::all();
        if (isset($cart[$id])) {
            $cart[$id]['count'] = $count;
            Session::put(self::$key, $cart);
        }
    }

    public static function purge() {
        Session::put(self::$key, []);
    }

    public static function all(): array {
        $res = Session::get(self::$key, []);
        return is_array($res) ? $res : [];
    }

    public static function sum(): int {
        $cart = self::all();
        $sum = 0;
        foreach ($cart as $item) {
            $sum += $item['count'] * $item['price'];
        }
        return $sum;
    }

    public static function total_weight(): int {
        $cart = self::all();
        $total = 0;
        foreach ($cart as $item) {
            if ($item['measure'] == 'т') {
                $total += $item['weight'] * 1000;
            } elseif ($item['measure'] == 'кг') {
                $total += $item['weight'];
            } elseif ($item['measure'] == 'м') {
                $total += $item['weight'] * 1000;
            } else {
                $total += $item['weight'] * $item['count'];
            }
        }

        return round($total);
    }
}
