var newsImage = null;
function newsImageAttache(elem, e){
    $.each(e.target.files, function(key, file)
    {
        if(file['size'] > max_file_size){
            alert('Слишком большой размер файла. Максимальный размер 2Мб');
        } else {
            newsImage = file;
            renderImage(file, function (imgSrc) {
                var item = '<img class="img-polaroid" src="' + imgSrc + '" height="100" data-image="' + imgSrc + '" onclick="return popupImage($(this).data(\'image\'))">';
                $('#article-image-block').html(item);
            });
        }
    });
    $(elem).val('');
}

function newsSave(form, e){
    e.preventDefault();
    var url = $(form).attr('action');
    var data = new FormData();
    $.each($(form).serializeArray(), function(key, value){
        data.append(value.name, value.value);
    });
    if (newsImage) {
        data.append('image', newsImage);
    }

    sendFiles(url, data, function(json){
        if (typeof json.errors != 'undefined') {
            applyFormValidate(form, json.errors);
            var errMsg = [];
            for (var key in json.errors) { errMsg.push(json.errors[key]);  }
            $(form).find('[type=submit]').after(autoHideMsg('red', urldecode(errMsg.join(' '))));
        }
        if (typeof json.redirect != 'undefined') document.location.href = urldecode(json.redirect);
        if (typeof json.msg != 'undefined') $(form).find('[type=submit]').after(autoHideMsg('green', urldecode(json.msg)));
        newsImage = null;
    });

    return false;
}

function newsAddTag(el) {
    var input = $(el).closest('.input-group').find('input[type="text"]');
    if(input.val().length > 0){
        var url = $(el).data('url');
        sendAjax(url, {tag: input.val()}, function (json) {
            if (typeof json.row !== 'undefined') {
                $(el).closest('.tab-pane').find('table tbody').append(json.row);
            }
        });
    }
}

function newsAddDel(el, e) {
    e.preventDefault();
    if (!confirm('Удалить метку?')) return false;
    $(el).closest('tr').fadeOut(300, function(){ $(this).remove(); });
}

function newsDel(elem){
    if (!confirm('Удалить строку?')) return false;
    var url = $(elem).attr('href');
    sendAjax(url, {}, function(json){
        if (typeof json.success != 'undefined' && json.success == true) {
            $(elem).closest('tr').fadeOut(300, function(){ $(this).remove(); });
        }
    });
    return false;
}

function newsImageDel(el, e){
    e.preventDefault();
    if (!confirm('Удалить изображение?')) return false;
    var url = $(el).attr('href');
    sendAjax(url, {}, function(json){
        if (typeof json.success != 'undefined' && json.success == true) {
            $(el).closest('#article-image-block').html('');
        }
    });
}

function productImageUpload(elem, e){
    var url = $(elem).data('url');
    files = e.target.files;
    var data = new FormData();
    $.each(files, function(key, value)
    {
        if(value['size'] > max_file_size){
            alert('Слишком большой размер файла. Максимальный размер 2Мб');
        } else {
            data.append('images[]', value);
        }
    });
    $(elem).val('');

    sendFiles(url, data, function(json){
        if (typeof json.html != 'undefined') {
            $('.images_list').append(urldecode(json.html));
            if (!$('.images_list img.active').length) {
                $('.images_list .img_check').eq(0).trigger('click');
            }
        }
    });
}

function delImg(elem, e) {
    e.preventDefault();
    if(!confirm('Точно удалить это изображение?')) return;
    var url = $(elem).attr('href');
    var row = $(elem).closest('tr');

    sendAjax(url, {}, function(json){
        if(typeof json.success != 'undefined'){
            $(row).fadeOut(300, function(){ $(this).remove(); });
        }
    });
}

function reviewImageDel(el, e) {
    e.preventDefault();
    if (!confirm('Удалить изображение?')) return false;
    var url = $(el).attr('href');
    sendAjax(url, {}, function (json) {
        if (json.success === true) {
            $(el).closest('#article-image-block').html('');
        }
    });
}

function updateOrder(form, e) {
    e.preventDefault();
    var button = $(form).find('[type="submit"]');
    button.attr('disabled', 'disabled');
    var url = $(form).attr('action');
    var data = $(form).serialize();
    sendAjax(url, data, function (json) {
        button.removeAttr('disabled');
    });
}

function galleryItemEdit(elem, e){
    e.preventDefault();
    var url = $(elem).attr('href');
    popupAjax(url);
}

function galleryImageDataSave(form, e){
    e.preventDefault();
    var url = $(form).attr('action');
    var data = $(form).serialize();
    sendAjax(url, data, function(json){
        if (typeof json.success != 'undefined' && json.success == true) {
            popupClose();
        }
    });
}

$(document).ready(function () {
    if($('#tag_name')){
        init_autocomplete($('#tag_name'));
    }
});
