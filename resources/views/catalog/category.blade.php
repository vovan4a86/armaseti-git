@extends('template')
@section('content')
    <main>
        <section class="catalog-view">
            <div class="catalog-view__container container">
                @include('blocks.bread')
                <div class="catalog-view__heading">
                    <div class="page-title oh">
                        <span data-aos="fade-down" data-aos-duration="900">{{ $h1 }}</span>
                    </div>
                </div>
                @if(isset($children) && count($children))
                    <div class="catalog-view__grid">
                        @foreach($children as $child)
                            <div class="catalog-view__item" data-aos="fade-down" data-aos-duration="900"
                             data-aos-delay="{{ $loop->index > 0 ? $loop->index * 50 + 150 : 150}}">
                            <div class="card">
                                @if($child->image)
                                <img class="card__pic lazy"
                                     src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="
                                     data-src="{{ $child->thumb(2) }}" width="250" height="208" alt="{{ $child->name }}"/>
                                @endif
                                <div class="card__body">
                                    <a class="card__title" href="{{ $child->url }}" title="{{ $child->name }}">{{ $child->name }}</a>
                                    <div class="card__txt">
                                        {!! $child->announce !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
        @include('blocks.features')
        @include('blocks.content_view')
    </main>
@endsection
