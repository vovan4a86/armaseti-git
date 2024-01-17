import $ from 'jquery';
import {counter} from "./counter";
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

export const sendFiles = (url, data, callback, type) => {
    if (typeof type == 'undefined') type = 'json';
    $.ajax({
        url: url,
        type: 'POST',
        data: data,
        cache: false,
        dataType: type,
        processData: false, // Don't process the files
        contentType: false, // Set content type to false as jQuery will tell the server its a query string request
        beforeSend: function (request) {
            return request.setRequestHeader('X-CSRF-Token', $("meta[name='csrf-token']").attr('content'));
        },
        success: function (json, textStatus, jqXHR) {
            if (typeof callback == 'function') {
                callback(json);
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            alert('Не удалось выполнить запрос! Ошибка на сервере.');
        }
    });
}

export const sendAjaxWithFile = (url, data, callback, type) => {
    data = data || {};
    if (typeof type == 'undefined') type = 'json';
    $.ajax({
        type: 'post',
        url: url,
        data: data,
        processData: false,
        contentType: false,
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
        },
    });
}

export const resetForm = (form) => {
    $(form).trigger('reset');
    $(form).find('.err-msg-block').remove();
    $(form).find('.has-error').remove();
    $(form).find('.invalid').attr('title', '').removeClass('invalid');
}

//загрузить еще новости
export const loadMoreNews = () => {
    $('.news-layout__row .b-loader').click(function () {
        const btn = $(this);
        const news_list = $('.newses__grid');
        const pagination = $('.news-layout__row .b-pagination')
        const url = $(btn).data('url');
        $(btn).hide();

        sendAjax(url, {}, function (json) {
            if (json.items) {
                $(news_list).append(json.items);
            }
            if (json.btn) {
                $(btn).replaceWith(json.btn);
                $(btn).show();
                loadMoreNews();
            }
            $(pagination).replaceWith(json.paginate)
        });
    });
}
loadMoreNews();

//загрузить еще товары в каталоге
export const loadMoreProducts = () => {
    $('.cat-view__load .b-loader').click(function () {
        const btn = $(this);
        const news_list = $('.cat-view__list');
        const pagination = $('.cat-view__pagination .b-pagination')
        const url = $(btn).data('url');
        $(btn).hide();

        sendAjax(url, {}, function (json) {
            history.pushState('', '', json.current_url);
            if (json.items) {
                $(news_list).append(json.items);
            }
            if (json.btn) {
                $(btn).replaceWith(json.btn);
                $(btn).show();
                loadMoreProducts();
            }
            $(pagination).replaceWith(json.paginate)
        });
    });
}
loadMoreProducts();

//применить фильтр
export const applyFilter = () => {
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
            history.pushState('', '', json.current_url);
            if (json.items) {
                $(news_list).empty();
                $(news_list).append(json.items);
            }
            if (json.btn) {
                $(btn).replaceWith(json.btn);
                $(btn).show();
                loadMoreProducts();
            }
            $(pagination).replaceWith(json.paginate)
        });
    });
}
applyFilter();

//сбросить фильтр
export const resetFilter = () => {
    $('.b-filter__actions .b-filter__submit--reset').click(function () {
        const form = $(this).closest('form');
        const ajax_url = $(form).attr('action');
        const news_list = $('.cat-view__list');

        const btn = $('.cat-view__load .b-loader');
        const pagination = $('.cat-view__pagination .b-pagination')
        $(btn).hide();

        sendAjax(ajax_url, {reset: 1}, function (json) {
            history.pushState('', '', json.current_url);
            if (json.items) {
                $(news_list).empty();
                $(news_list).append(json.items);
            }
            if (json.btn) {
                $(btn).replaceWith(json.btn);
                $(btn).show();
                loadMoreProducts();
            }
            $(pagination).replaceWith(json.paginate)
        });
    });
}
resetFilter();

//добавление товара со страницы товара
$('.prod__cart .btn-cart').click(function () {
    const url = '/ajax/add-to-cart'
    const id = $(this).closest('.prod__data').data('id');
    const count = $('input[name=count]').val();

    const header_cart = $('[data-header-cart]');

    sendAjax(url, {id, count}, function (json) {
        if (json.success) {
            header_cart.replaceWith(json.header_cart);
        }
    });

})

//добавление товара из карточки
$('.prod-card .btn-cart').click(function () {
    const url = '/ajax/add-to-cart'
    const id = $(this).closest('.prod-card__data--order').data('id');
    const count = $(this).closest('.prod-card__data--order').find('input[name=count]').val();

    const header_cart = $('[data-header-cart]');

    sendAjax(url, {id, count}, function (json) {
        if (json.success) {
            header_cart.replaceWith(json.header_cart);
        }
    });
})

//удаление товара из корзины
export const removeFromCart = () => {
    $('.cart-item__close').click(function () {
        const url = '/ajax/remove-from-cart';
        const card = $(this).closest('.b-cart__item');
        const id = $(card).data('id');
        const header_cart = $('[data-header-cart]');
        const cart_total = $('.b-cart__sum-data');

        sendAjax(url, {id}, function (json) {
            if (json.success) {
                card.replaceWith(json.del_cart_item);
                header_cart.replaceWith(json.header_cart);
                cart_total.replaceWith(json.cart_total)
                restoreFromCart();
                updateCountUp();
                updateCountDown();
                counter();
            }
        });
    })
}
removeFromCart();

//восстановление товара из корзины
export const restoreFromCart = () => {
    $('.del-item__action').click(function () {
        const url = '/ajax/restore-from-cart';
        const card = $(this).closest('.b-cart__item');
        const id = $(card).data('id');
        const header_cart = $('[data-header-cart]');
        const cart_total = $('.b-cart__sum-data');

        sendAjax(url, {id}, function (json) {
            if (json.success) {
                card.replaceWith(json.restore_cart_item);
                header_cart.replaceWith(json.header_cart);
                cart_total.replaceWith(json.cart_total)
                removeFromCart();
                updateCountUp();
                updateCountDown();
                counter();
            }
        });
    })
}
restoreFromCart();

//увеличение количества +
export const updateCountUp = () => {
    $('.cart-item__counter .b-counter__btn--next').click(function () {
        const url = '/ajax/update-count';
        const card = $(this).closest('.b-cart__item');
        const id = $(card).data('id');
        const row_summary = $(card).find('.cart-item__summary');
        const cart_total = $('.b-cart__sum-data');
        let count = $('input[name=count]').val();
        count++;

        sendAjax(url, {id, count}, function (json) {
            if (json.success) {
                row_summary.replaceWith(json.row_summary);
                cart_total.replaceWith(json.cart_total)
            }
        });
    })
}
updateCountUp();

//уменьшение количества +
export const updateCountDown = () => {
    $('.cart-item__counter .b-counter__btn--prev').click(function () {
        const url = '/ajax/update-count';
        const card = $(this).closest('.b-cart__item');
        const id = $(card).data('id');
        const row_summary = $(card).find('.cart-item__summary');
        const cart_total = $('.b-cart__sum-data');
        let count = $('input[name=count]').val();

        if (count != 1) {
            count--;
        }

        sendAjax(url, {id, count}, function (json) {
            if (json.success) {
                row_summary.replaceWith(json.row_summary);
                cart_total.replaceWith(json.cart_total)
            }
        });
    })
}
updateCountDown();

//Создать заказ
export const makeOrder = () => {
    $('.order__item .btn').click(function (e) {
        e.preventDefault();
        const form = $(this).closest('form');
        const url = $(form).attr('action');

        const file = $('input[name=file]');
        const details = $('input[name=details]');

        let data = new FormData();
        $.each($(form).serializeArray(), function (key, value) {
            data.append(value.name, value.value);
        });

        data.append('file', file.prop('files')[0]);
        data.append('details', details.prop('files')[0]);

        sendAjaxWithFile(url, data, function (json) {
            if (json.success) {
                alert('Отправлено!');
                resetForm(form);
                location.href = json.redirect;
            }
            if (json.errors) {
                console.error(json.errors);
            }

        });
    })
}
makeOrder();

//Отправить заявку
export const sendRequest = () => {
    $('.s-req__body .btn').click(function (e) {
        e.preventDefault();
        const form = $(this).closest('form');
        const url = $(form).attr('action');

        const file = $('input[name=file]');
        const details = $('input[name=details]');

        let data = new FormData();
        $.each($(form).serializeArray(), function (key, value) {
            data.append(value.name, value.value);
        });

        data.append('file', file.prop('files')[0]);
        data.append('details', details.prop('files')[0]);

        sendAjaxWithFile(url, data, function (json) {
            if (json.success) {
                resetForm(form);
                alert('Отправлено!');
                // showSuccessRequestDialog();
            }
            if (json.errors) {
                console.error(json.errors);
            }

        });
    })
}
sendRequest();

//Добавить в избранное
export const addToFavorites = () => {
    $('[data-favorites]').click(function () {
        const url = '/ajax/favorites';
        const form = $(this).closest('form');
        const id = $(form).find('.prod-card__data--order').data('id');
        const header_favorites = $('[data-header-favorites]');

        sendAjax(url, {id}, function (json) {
            if (json.success) {
                header_favorites.replaceWith(json.header_favorites);
            }
        });

    })
}
addToFavorites();

//Добавить в сравнение
export const addToCompare = () => {
    $('[data-compare]').click(function () {
        const url = '/ajax/compare';
        const form = $(this).closest('form');
        const id = $(form).find('.prod-card__data--order').data('id');
        const header_compare = $('[data-header-compare]');

        sendAjax(url, {id}, function (json) {
            if (json.success) {
                header_compare.replaceWith(json.header_compare);
            }
        });

    })
}
addToCompare();
