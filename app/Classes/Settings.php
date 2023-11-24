<?php

namespace App\Classes;

use Fanky\Admin\Models\Setting;
use Illuminate\Support\Arr;

class Settings {
  private $items = [];

  public function get($code, $default = null) {
    if (!Arr::has($this->items, $code)) {
      $item = Setting::whereCode($code)->first();
      if (!$item) {
        $this->items[$code] = [
          'code' => $code,
          'value' => $default,
          'type' => 0,
          'group_id' => 0
        ];
        return $default;
      } else {
        $this->items[$code] = [
          'code' => $item->code,
          'value' => $item->value,
          'type' => $item->type,
          'group_id' => $item->group_id
        ];
      }
    }
    return $this->items[$code]['value'];
  }

    public function getPhoneFromCode($code, $default = null) {
        if (!Arr::has($this->items, $code)) {
            $item = Setting::whereCode($code)->first();
            if (!$item) {
                $this->items[$code] = [
                    'code' => $code,
                    'value' => $default,
                    'type' => 0,
                    'group_id' => 0
                ];
                return $default;
            } else {
                $this->items[$code] = [
                    'code' => $item->code,
                    'value' => $item->value,
                    'type' => $item->type,
                    'group_id' => $item->group_id
                ];
            }
        }
        return preg_replace('/\D/', '', $this->items[$code]['value']);
    }

  public function set($code, $value) {
    $this->items[$code] = $value;
    if (Setting::whereCode($code)->exist()) {
      Setting::whereCode($code)->update(['value' => $value]);
    } else {
      $order = Setting::max('order') + 1;
      Setting::whereCode($code)->insert([
        'value' => $value, 'order' => $order,
        'name' => $code
      ]);
    }

  }

  public function fileSrc($value) {
    return Setting::UPLOAD_URL . $value;
  }

  public function getAlterImage(string $url, string $replace) {
    if(strripos($url, '.')) {
      return str_replace('.', $replace, $url);
    }

    return $url;
  }
}