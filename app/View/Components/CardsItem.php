<?php

namespace App\View\Components;

use Illuminate\View\Component;

class CardsItem extends Component {
    public $item, $params;

    public function __construct($item) {
        $this->item = $item;
        $this->params = $item->params_on_list;
    }

    public function render() {
        return view('components.cards-item');
    }
}