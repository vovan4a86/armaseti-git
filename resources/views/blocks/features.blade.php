<section class="features">
    <div class="features__container container">
        <div class="features__heading">
            <div class="page-title oh">
                <span data-aos="fade-down" data-aos-duration="900">Почему мы?</span>
            </div>
        </div>
        @if($why_we = Settings::get('why_we'))
            <div class="features__grid" data-aos="fade-up" data-aos-duration="600" data-aos-delay="150">
                @foreach($why_we as $item)
                    <div class="features__item">
                        @if($item['icon'])
                        <div class="features__icon lazy" data-bg="{{ Settings::fileSrc($item['icon']) }}"></div>
                        @endif
                        <div class="features__title">{{ $item['title'] }}</div>
                        <div class="features__body">{{ $item['text'] }}</div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
