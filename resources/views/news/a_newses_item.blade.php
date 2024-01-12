<div class="a-newses__item">
    <div class="a-newses__top">
        <div class="a-newses__badge">
            @if($item->type == 'Новость')
                <div class="badge badge--alt">Новость</div>
            @elseif($item->type == 'Статья')
                <div class="badge badge--accent-alt">Статья</div>
            @else
                <div class="badge badge--accent">Акция</div>
            @endif
        </div>
        <div class="a-newses__date">{{ $item->dateFormat() }}</div>
    </div>
    <div class="a-newses__body">
        <a class="a-newses__title" href="{{ $item->url }}">{{ $item->name }}</a>
        <div class="a-newses__text">{{ $item->getAnnounce() }}</div>
    </div>
    <div class="a-newses__foot">
        <a class="read-link" href="{{ $item->url }}" title="Читать">
            <span class="read-link__label">Читать</span>
            <span class="read-link__icon iconify" data-icon="jam:arrow-right"
                  data-width="17"></span>
        </a>
    </div>
</div>
