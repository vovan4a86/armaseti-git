<div class="newses__item">
    <div class="card">
        <a class="card__view" href="{{ $item->url }}"
           title="{{ $item->name }}">
            <div class="card__badge">
                @if($item->type == 'Новость')
                    <div class="badge badge--alt">Новость</div>
                @elseif($item->type == 'Статья')
                    <div class="badge badge--accent-alt">Статья</div>
                @else
                    <div class="badge badge--accent">Акция</div>
                @endif
            </div>
            @if($item->image)
                <img class="card__pic" src="{{ $item->thumb(2) }}"
                     width="380" height="260" alt="{{ $item->name }}"
                     loading="lazy"/>
            @endif
        </a>
        <div class="card__body">
            <div class="card__date">{{ $item->dateFormat() }}</div>
            <a class="card__title" href="{{ $item->url }}">{{ $item->name }}</a>
            <div class="card__text">{{ $item->getAnnounce() }}</div>
        </div>
        <div class="card__foot">
            <a class="read-link" href="{{ $item->url }}" title="Читать">
                <span class="read-link__label">Читать</span>
                <span class="read-link__icon iconify" data-icon="jam:arrow-right"
                      data-width="17"></span>
            </a>
        </div>
    </div>
</div>
