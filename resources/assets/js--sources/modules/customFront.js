import $ from 'jquery';
// import "../plugins/jquery.autocomplete.min";
// import {showSuccessDialog} from "./popups";
// import {Fancybox} from "@fancyapps/ui";

export const sendAjax = (url, data, callback, type) => {
    data = data || {};
    if (typeof type == 'undefined') type = 'json';
    $.ajax({
        type: 'post',
        url: url,
        data: data,
        dataType: type,
        beforeSend: function (request) {
            return request.setRequestHeader('X-CSRF-Token', $("meta[name='csrf-token']").attr('content'));
        },
        success: function (json) {
            if (typeof callback == 'function') {
                callback(json);
            }
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            alert('Не удалось выполнить запрос! Ошибка на сервере.');
            console.log(errorThrown);
        },
    });
}

//применить фильтр
$('.b-filter').submit(function (e) {
    e.preventDefault();
    const form = $(this).closest('form');
    const ajax_url = $(form).attr('action');
    const data = $(form).serialize();
    const news_list = $('.cat-view__list');
    const current_url = $(form).data('current-url');

    const btn = $('.cat-view__load .b-loader');
    const pagination = $('.cat-view__pagination .b-pagination')
    $(btn).hide();

    sendAjax(ajax_url, data, function (json) {
        history.pushState('', '', current_url + json.url);
        if (json.items) {
            $(news_list).empty();
            $(news_list).append(json.items);
        }
        if (json.btn) {
            $(btn).replaceWith(json.btn);
            $(btn).show();
        }
        $(pagination).replaceWith(json.paginate)
    });
});

$('.btn-cart').click(function () {
    const url = '/ajax/add-to-cart'
    const id = $(this).closest('.prod__data').data('id');
    const count = $('input[name=count]').val();

    sendAjax(url, {id, count}, function (json) {
        if(json.success) {
            alert('added');
        }
    });

})

// export const resetForm = (form) => {
//     $(form).trigger('reset');
//     $(form).find('.err-msg-block').remove();
//     $(form).find('.has-error').remove();
//     $(form).find('.invalid').attr('title', '').removeClass('invalid');
// }

//
// $('#message').submit(function (e) {
//     e.preventDefault();
//     const form = $(this);
//     const url = form.attr('action');
//     const data = form.serialize();
//
//     const lang = $('html').attr('lang');
//
//     sendAjax(url, data, function (json) {
//         if (json.success) {
//             resetForm(form);
//             showSuccessDialog({
//                 title: lang === 'ru' ? 'Сообщение отправлено!' : 'Message sent!'
//             });
//         }
//     })
//
// });