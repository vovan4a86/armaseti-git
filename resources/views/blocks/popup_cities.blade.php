<!DOCTYPE html>
<html lang="ru-RU">

<head>
    <meta charset="utf-8">
    <title>Выберите регион</title>
</head>

<body class="cities-page">
<div class="container cities-page__container">
    <div class="cities-page__title">Выберите регион:</div>
    <div class="cities-page__current"
         data-home="{{ route('main') }}"
         data-current="{{ url()->previous() }}">

        <a class="default-city" href="{{ route('main') }}">
            Россия
        </a>
    </div>
    <div class="cities-page__content">
        @foreach($cities as $letter => $letterCities)
            <ul class="cities-page__list list-reset">
                <span class="cities-page__label">{{ $letter }}</span>
                @foreach($letterCities as $letterCity)
                    <li>
                        <a class="cities-page__link {{ isset($current_city) && $current_city->name == $letterCity->name ? 'cities-page__link--current' : '' }}"
                           href="/{{ $letterCity->alias }}"
                           data-cur_url="{{ request()->path() }}"
                           data-id="{{ $letterCity->id }}">
                            {{ $letterCity->name }}
                        </a>
                    </li>
                @endforeach
            </ul>
        @endforeach
    </div>
</div>
<script src="/static/js/cities.js" defer></script>
</body>
</html>
