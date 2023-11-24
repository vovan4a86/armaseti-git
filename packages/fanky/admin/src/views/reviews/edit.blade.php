@extends('admin::template')

@section('scripts')
    <script type="text/javascript" src="/adminlte/interface_news.js"></script>
@stop

@section('page_name')
    <h1>
        Отзывы
        <small>{{ $review->id ? 'Редактировать' : 'Новый' }}</small>
    </h1>
@stop

@section('breadcrumb')
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Главная</a></li>
        <li><a href="{{ route('admin.reviews') }}">Отзывы</a></li>
        <li class="active">{{ $review->id ? 'Редактировать' : 'Новый' }}</li>
    </ol>
@stop

@section('content')
    <form action="{{ route('admin.reviews.save') }}" onsubmit="return newsSave(this, event)">
        <input type="hidden" name="id" value="{{ $review->id }}">

        <div class="box box-solid">
            <div class="box-body">

                {!! Form::groupDate('date', $review->date, 'Дата') !!}
                {!! Form::groupText('name', $review->name, 'Имя') !!}
                {!! Form::groupText('alias', $review->alias, 'Alias') !!}
                {!! Form::groupRichtext('announce', $review->announce, 'Анонс') !!}
                {!! Form::groupRichtext('text', $review->text, 'Текст') !!}

                <div class="form-group" style="display: flex; column-gap: 30px;">
                    <div>
                        <label for="article-image">Изображение</label>
                        <input id="article-image" type="file" name="image" value=""
                               onchange="return newsImageAttache(this, event)">
                        <div id="article-image-block">
                            @if ($review->image)
                                <img class="img-polaroid"
                                     src="{{ $review->image_src }}" width="200"
                                     data-image="{{ $review->image_src }}"
                                     onclick="return popupImage($(this).data('image'))" alt="">
                                <a class="images_del"
                                   href="{{ route('admin.reviews.delImage', [$review->id]) }}"
                                   onclick="return reviewImageDel(this, event)">
                                    <span class="glyphicon glyphicon-trash text-red"></span>
                                </a>
                            @else
                                <p class="text-yellow">Изображение не загружено.</p>
                            @endif
                        </div>
                    </div>
                </div>
                {!! Form::hidden('published', 0) !!}
                {!! Form::hidden('on_main', 0) !!}
                {!! Form::groupCheckbox('published', 1, $review->published, 'Показывать отзыв') !!}
                {!! Form::groupCheckbox('on_main', 1, $review->on_main, 'На главной') !!}

            </div>

            <div class="box-footer">
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </div>
        </div>
    </form>
@stop
