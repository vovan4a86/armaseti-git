@if(count($related))
    <section class="related" data-aos="fade-up" data-aos-duration="600">
        <div class="related__container container">
            <div class="related__heading">
                <div class="page-title oh">
                    <span data-aos="fade-down" data-aos-duration="900">Ещё товары из каталога</span>
                </div>
            </div>
            <div class="related__grid">
                @foreach($related as $item)
                    <div class="related__item" data-aos="fade-down" data-aos-duration="900" data-aos-delay="{{ $loop->index > 0 ? $loop->index * 50 + 100 : 0}}">
                        <div class="prod">
                            <a href="{{ $item->url }}" title="{{ $item->name }}">
                                @if($img = $item->images()->first())
                                    <img class="prod__pic lazy"
                                         src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="
                                         data-src="{{ $img->thumb(2) }}" width="250" height="208"
                                         alt="{{ $item->name }}"/>
                                @endif
                            </a>
                            <div class="prod__body">
                                <a class="prod__title" href="{{ $item->url }}"
                                   title="{{ $item->name }}">{{ $item->name }}</a>
                                @if($item->length && $item->width && $item->height)
                                    <div class="prod__descr">Внутренний размер ящиков, мм. Д х Ш х В:{{ $item->length }}х{{ $item->width }}х{{ $item->height }}</div>
                                @endif
                                <div class="prod__action">
                                    <button class="prod__btn btn-reset" data-request="data-request" data-src="#calc"
                                            data-label="{{ $item->name }}"
                                            aria-label="Расчёт цены">
                                    <span class="prod__btn-icon lazy"
                                          data-bg="/static/images/common/ico_calc.svg"></span>
                                        <span class="prod__btn-label">Расчёт цены</span>
                                        <svg width="86" height="19" viewBox="0 0 86 19" fill="none"
                                             xmlns="http://www.w3.org/2000/svg">
                                            <path d="M73.286 18L85.0001 9.5M85.0001 9.5L73.0001 0.999993M85.0001 9.5L6.02921e-05 9.5"
                                                  stroke="currentColor"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
