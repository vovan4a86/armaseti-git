import $ from "jquery";

function sendAjax(url, data, callback, type){
   data = data || {};
   if (typeof type == 'undefined') type = 'json';
   $.ajax({
      type: 'post',
      url: url,
      data: data,
      dataType: type,
      beforeSend: function(request) {
         return request.setRequestHeader('X-CSRF-Token', $("meta[name='csrf-token']").attr('content'));
      },
      success: function(json){
         if (typeof callback == 'function') {
            callback(json);
         }
      },
      error: function(XMLHttpRequest, textStatus, errorThrown){
         alert('Не удалось выполнить запрос! Ошибка на сервере.');
      },
   });
}

$('.b-loader').click(function () {
   const btn = $(this);
   const news_list = $('.newses__grid');
   const pagination = $('.b-pagination')
   const url = $(btn).data('url');
   $(btn).hide();

   sendAjax(url, {}, function(json) {
      if (json.items) {
         $(news_list).append(json.items);
      }
      if (json.btn) {
         $(btn).replaceWith(json.btn);
         $(btn).show();
      }
      $(pagination).replaceWith(json.paginate)
   });
});
