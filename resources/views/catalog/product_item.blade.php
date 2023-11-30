<li data-id="{{ $product->id }}">{{ $product->name }}
    | <a href="{{ route('ajax.compare') }}" class="compare-link">{{ in_array($product->id, session('compare', [])) ? 'В сравнении' : 'Сравнить' }}</a>
    | <a href="{{ route('ajax.favorite') }}" class="favorite-link">{{ in_array($product->id, session('favorites', [])) ? 'В избранном' : 'В избранное' }}</a></li>
