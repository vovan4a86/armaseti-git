// import 'focus-visible';
// import './plugins';
// import './modules';
// import { utils } from './modules/utility';
// import { scrollTop } from './modules/scrollTop';
// import { maskedInputs } from './modules/inputMask';
//
// utils();
//
// scrollTop({ trigger: '.scrolltop' });
//
// maskedInputs({
//   phoneSelector: 'input[name="phone"]',
//   emailSelector: 'input[name="email"]'
// });

import $ from 'jquery';
import {sendAjax} from "./modules/customFront";

$('.btns button').click(function (e) {
    e.preventDefault();

    const form = $('#filter-form')

    const data = form.serialize();
    const url = form.attr('action');

    sendAjax(url, data, function (json) {
        if (json.success) {
            $('.products').empty();
            $('.products').append(json.items);
        }
    })
})

$('.favorite-link').click(function (e) {
    e.preventDefault();
    let elem = $(this);

    const url = $(this).attr('href');
    const id = $(this).closest('li').data('id');

    sendAjax(url, {id}, function (json) {
        if (json.success) {
            $('.favorite-block .favorite').text(json.count);

            if (json.add) {
                $(elem).text('В избранном');
            } else {
                $(elem).text('В избранное');
            }
        }
    })
})

$('.compare-link').click(function (e) {
    e.preventDefault();
    let elem = $(this);

    const url = $(this).attr('href');
    const id = $(this).closest('li').data('id');

    sendAjax(url, {id}, function (json) {
        if (json.success) {
            $('.compare-block .compare').text(json.count);

            if (json.add) {
                $(elem).text('В сравнении');
            } else {
                $(elem).text('Сравнить');
            }
        }
    })
})

$('.compare-delete').click(function (e) {
    e.preventDefault();

    const url = $(this).attr('href');
    const card = $(this).closest('.card');
    const id = card.data('id');

    sendAjax(url, {id}, function (json) {
        if (json.success) {
            $('.compare-block .compare').text(json.count);
            card.fadeOut(300, function () {
                $(this).remove();
                if (json.count === 0) {
                    $('.compare.row').append('<p>Пусто</p>');
                }
            });
        }
    })
})
