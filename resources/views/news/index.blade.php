@extends('template')
@section('content')
    <main>
        <section class="s-newses">
            <div class="s-newses__container container">
                @include('blocks.bread')
                <div class="s-newses__heading">
                    <div class="page-title oh">
                        <span data-aos="fade-down" data-aos-duration="900">{{ $h1 }}</span>
                    </div>
                </div>
                @if(isset($items) && count($items))
                    <div class="s-newses__grid">
                        @foreach($items as $item)
                            <div class="s-newses__item" data-aos="fade-down" data-aos-duration="900" data-aos-delay="{{ $loop->index > 0 ? $loop->index * 50 : 0}}">
                                @if($item->image)
                                    <img class="s-newses__pic lazy"
                                         src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="
                                         data-src="{{ $item->thumb(2) }}" width="356" height="263" alt="{{ $item->name }}"/>
                                @endif
                                <a class="s-newses__title" href="{{ $item->url }}"
                                   title="{{ $item->name }}">{{ $item->name }}</a>
                                <div class="s-newses__body">
                                    <p>{{ $item->announce }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    </main>
@stop
