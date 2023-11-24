import $ from "jquery";
import {sendAjax} from "../js--sources/modules/customFront";

//Выбор города в попапе
$('.cities-page__link').click(function (e) {
    const elem = $(this);
    e.preventDefault();
    const homeLink = $('.cities-page__current').data('home');
    const cur_url = $('.cities-page__current').data('current');
    const url = $(elem).prop('href');
    const city_id = $(elem).data('id');

    const data = {city_id}
    if (cur_url === homeLink + '/') {
        sendAjax(url, data, function (json) {
            if (typeof json.success !== 'undefined') {
                location.reload();
            }
        })
    } else {
        sendAjax(url, data, function () {
            redirect_to_current_city(city_id, cur_url);
        });
    }
});
export const redirect_to_current_city = (city_id, cur_url) => {
    sendAjax('/ajax/get-correct-region-link', {city_id, cur_url}, function (json) {
        if (typeof json.redirect != 'undefined') {
            location.href = json.redirect;
            // console.log(json.redirect);
        }
    });
}
