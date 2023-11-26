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

    sendAjax(url, data, function(json) {
        if(json.success) {
            console.log(json.items);
        }
    })

})