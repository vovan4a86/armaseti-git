@extends('template')
@section('content')
    <main>
        <section class="s-handbook">
            <div class="s-handbook__container container">
                @include('blocks.bread')
                <div class="s-handbook__heading">
                    <div class="page-title oh">
                        <span data-aos="fade-down" data-aos-duration="900">{{ $h1 }}</span>
                    </div>
                </div>
                @if(count($items))
                    <div class="s-handbook__grid">
                        @foreach($items as $item)
                            <div class="s-handbook__item" data-aos="fade-down" data-aos-duration="900" data-aos-delay="0">
                                <img class="s-handbook__pic lazy" src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="
                                     data-src="{{ $item->thumb(2) }}" width="356" height="263" alt="{{ $item->name }}" />
                                <a class="s-handbook__title" href="{{ $item->url }}"
                                   title="{{ $item->name }}">{{ $item->name }}</a>
                                <div class="s-handbook__body">
                                    <p>{!! $item->getAnnounce() !!}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="s-handbook__pagination">
                        @include('paginations.with_pages', ['paginator' => $items])
                    </div>
                @endif
            </div>
        </section>
    </main>
@stop
