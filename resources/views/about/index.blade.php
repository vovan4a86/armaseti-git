@extends('template')
@section('content')
    <main>
        <section class="about-hero lines">
            <div class="about-hero__view">
                <picture>
                    <source srcset="/" data-srcset="/static/images/common/about-bg.webp" type="image/webp">
                    <img class="about-hero__bg lazy" src="/" data-src="/static/images/common/about-bg.jpg" width="1920"
                         height="680" alt="">
                </picture>
            </div>
            <div class="about-hero__container container">
                <div class="about-hero__title">О компании</div>
                {{--                <div class="about-hero__subtitle">Настенные</div>--}}
                <div class="about-hero__body text-block">
                    {!! $text !!}
                </div>
            </div>
            <img class="about-hero__decor lazy" src="/" data-src="/static/images/common/about-hero-decor.png"
                 width="262" height="468" alt="">
        </section>
        @if (Settings::get('about_teaser'))
            <section class="about-teaser lines lines--white">
                <div class="about-teaser__container container">
                    <div class="about-teaser__body">
                        {!! Settings::get('about_teaser') !!}
                    </div>
                </div>
                <img class="about-teaser__decor lazy" src="/" data-src="/static/images/common/teaser-decor.png"
                     width="314" height="312" alt="">
            </section>
        @endif
        <section class="about-body">
            <div class="about-body__container container">
                @if ($text = Settings::get('about_text_img'))
                    <div class="about-body__grid">
                        <div class="about-body__content text-block">
                            {!! array_get($text, 'text') !!}
                        </div>
                        @if (array_get($text, 'img'))
                            <div class="about-body__view">
                                <picture>
                                    <img class="about-body__pic lazy" src="/"
                                         data-src="{{ Settings::fileSrc(array_get($text, 'img')) }}"
                                         width="488" height="594" alt="">
                                </picture>
                            </div>
                        @endif
                    </div>
                @endif
                @if ($command = Settings::get('about_command'))
                    <div class="about-body__staff">
                        <div class="about-body__title">Команда</div>
                        <div class="about-body__row">
                            @foreach($command as $person)
                                <div class="b-staff">
                                <div class="b-staff__view" data-animate="data-animate">
                                    <img class="b-staff__pic lazy" src="/" data-src="{{ Settings::fileSrc(array_get($person, 'photo')) }}"
                                         width="387" height="296" alt="{{array_get($person, 'name')}}"/>
                                </div>
                                <div class="b-staff__about" data-animate="data-animate">
                                    <div class="b-staff__name">{{ array_get($person, 'name') }}</div>
                                    <div class="b-staff__position">{{ array_get($person, 'job') }}</div>
                                </div>
                                <div class="b-staff__links" data-animate="data-animate">
                                    <a class="b-staff__link" href="tel:{{ preg_replace('/\D/', '', array_get($person, 'phone')) }}">
                                        <svg class="svg-sprite-icon icon-quality" width="1em" height="1em">
                                            <use xlink:href="/static/images/sprite/symbol/sprite.svg#quality"></use>
                                        </svg>
                                        <span>{{ array_get($person, 'phone') }}</span>
                                    </a>
                                    <a class="b-staff__link" href="mailto:{{ array_get($person, 'email') }}">
                                        <svg class="svg-sprite-icon icon-quality" width="1em" height="1em">
                                            <use xlink:href="/static/images/sprite/symbol/sprite.svg#quality"></use>
                                        </svg>
                                        <span>{{ array_get($person, 'email') }}</span>
                                    </a>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </section>
        @include('blocks.feedbacks')
        @include('blocks.request_price')
    </main>
@stop
