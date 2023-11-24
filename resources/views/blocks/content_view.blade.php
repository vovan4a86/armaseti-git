@if($text)
    <section class="content-view">
        <!-- обёртка для спойлера в контенте-->
        <!-- высота по-умолчанию, вынесена inline, чтобы можно было добавить поле в админке (может быть любым)-->
        <!-- (style="height: 800px")-->
        <div class="content-view__container container container--small js-hide_container" data-aos="fade-down"
             data-aos-duration="1200">
            <div class="text-block js-hide_container__inn" style="height: {{ Settings::get('text_height', 800) }}px">
                {!! $text !!}
            </div>
            <button class="btn-reset js-hide_container__btn" type="button" aria-label="показать/скрыть текст">
                <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" fill="none">
                    <circle cx="30" cy="30" r="30" fill="#8A847F"/>
                    <path fill="#fff"
                          d="M42.35 25a.59.59 0 0 0-.455.195L30 36.443 18.105 25.195A.59.59 0 0 0 17.65 25c-.39 0-.65.26-.65.65a.59.59 0 0 0 .195.455l12.35 11.704c.13.13.26.195.455.195a.59.59 0 0 0 .455-.195l12.35-11.704A.59.59 0 0 0 43 25.65c0-.39-.26-.65-.65-.65Z"
                    />
                </svg>
            </button>
        </div>
    </section>
@endif
