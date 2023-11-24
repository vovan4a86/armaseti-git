import $ from 'jquery';
import { showSuccessDialog } from './popups'

export const resetForm = (form) => {
    $(form).trigger('reset');
    $(form).find('.err-msg-block').remove();
    $(form).find('.has-error').remove();
    $(form).find('.invalid').attr('title', '').removeClass('invalid');
}

export const sendAjax = (url, data, callback, type) => {
    data = data || {};
    if (typeof type == 'undefined') type = 'json';
    $.ajax({
        type: 'post',
        url: url,
        data: data,
        // processData: false,
        // contentType: false,
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

//прайс
$('#get-price form').submit(function (e) {
   e.preventDefault();
    let form = $(this);
    let data = form.serialize();
    let url = form.attr('action');
    sendAjax(url, data, function (json) {
        if (typeof json.errors !== 'undefined') {
            let focused = false;
            for (let key in json.errors) {
                if (!focused) {
                    form.find('#' + key).focus();
                    focused = true;
                }
                form.find('#' + key).after('<span class="has-error">' + json.errors[key] + '</span>');
            }
            form.find('.popup__fields').after('<div class="err-msg-block has-error">Заполните, пожалуйста, обязательные поля.</div>');
        } else {
            resetForm(form);
            $('.carousel__button.is-close').click();
            showSuccessDialog({
                title: 'Успешно!',
                body: 'В самое ближайшее время мы вышлем Вам наш прайс-лист.'
            });
        }
    });

});

//перезвонить
$('#callback form').submit(function (e) {
    e.preventDefault();
    let form = $(this);
    let data = form.serialize();
    let url = form.attr('action');
    sendAjax(url, data, function (json) {
        if (typeof json.errors !== 'undefined') {
            let focused = false;
            for (let key in json.errors) {
                if (!focused) {
                    form.find('#' + key).focus();
                    focused = true;
                }
                form.find('#' + key).after('<span class="has-error">' + json.errors[key] + '</span>');
            }
            form.find('.popup__fields').after('<div class="err-msg-block has-error">Заполните, пожалуйста, обязательные поля.</div>');
        } else {
            resetForm(form);
            $('.carousel__button.is-close').click();
            showSuccessDialog({
                title: 'Ваш запрос получен!',
                body: 'В самое ближайшее время мы Вам перезвоним.'
            });
        }
    });
})

//отзыв
$('.s-callback__form').submit(function (e)  {
    e.preventDefault();
    let form = $(this);
    let data = form.serialize();
    let url = form.attr('action');
    sendAjax(url, data, function (json) {
        if (typeof json.errors !== 'undefined') {
            let focused = false;
            for (let key in json.errors) {
                if (!focused) {
                    form.find('#' + key).focus();
                    focused = true;
                }
                form.find('#' + key).after('<span class="has-error">' + json.errors[key] + '</span>');
            }
            form.find('.s-callback__submit').before('<div class="err-msg-block has-error">Заполните, пожалуйста, обязательные поля.</div>');
        } else {
            resetForm(form);
            showSuccessDialog({
                title: 'Успешно!',
                body: 'Ваш отзыв отправлен.'
            });
        }
    });
})

//расчет цены
$('#calc form').submit(function (e)  {
    e.preventDefault();
    let form = $(this);
    let text = $('#calc .popup__label').text()
    let data = form.serialize() + '&text=' + text;

    let url = form.attr('action');
    sendAjax(url, data, function (json) {
        if (typeof json.errors !== 'undefined') {
            let focused = false;
            for (let key in json.errors) {
                if (!focused) {
                    form.find('#' + key).focus();
                    focused = true;
                }
                form.find('#' + key).after('<span class="has-error">' + json.errors[key] + '</span>');
            }
            form.find('.popup__fields').after('<div class="err-msg-block has-error">Заполните, пожалуйста, обязательные поля.</div>');
        } else {
            resetForm(form);
            $('.carousel__button.is-close').click();
            showSuccessDialog({
                title: 'Заявка успешно отправлена!',
                body: 'Мы свяжемся с Вами в ближайшее время.'
            });
        }
    });
})
