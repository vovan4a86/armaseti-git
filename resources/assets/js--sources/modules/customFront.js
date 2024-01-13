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
$('.b-filter__submit').click(function (e) {
    e.preventDefault();
    const form = $(this).closest('form');
    const url = $(form).attr('action');
    const data = $(form).serialize();
    const news_list = $('.cat-view__list');

    sendAjax(url, data, function (json) {
        if (json.items) {
            $(news_list).empty();
            $(news_list).append(json.items);
        }
    });
});


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
//
// export const applySelectedDays = (days) => {
//     const url = '/ajax/apply-calendar';
//     const archive = $('.s-news__data').data('type') === 'archive' ? 1 : 0;
//     const news_list = $('.s-news__list');
//     const btn = $('.b-loader button');
//     const lang = $('html').attr('lang');
//     const message = lang === 'ru' ? 'Нет новостей на указанную дату' : 'No news on the date specified';
//
//     $(btn).hide();
//     $(news_list).empty();
//
//     sendAjax(url, {days, archive}, function(json) {
//         if(json.success && json.items) {
//             $(news_list).append(json.items);
//         }
//         if(!json.items) {
//             const no_news = '<h4 class="no-news" style="text-align: center">' + message + '</h4>'
//             $(news_list).append(no_news);
//         }
//     });
// }
