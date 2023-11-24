function galleryOpen(elem){
	var url = $(elem).attr('href');
	sendAjax(url, {}, function(html){
		$('#gallery-content').html(html);
	}, 'html');

	return false;
}

function galleryEdit(elem){
	var block = $(elem).closest('li');
	block.find('.text a').hide();
	block.find('.tools').hide();
	block.find('.text').css('width', '85%');
	block.find('.text form').show().find('[name=name]').trigger('focus');
}

function galleryCreate(form){
	var url = $(form).attr('action');
	var data = $(form).serialize();
	sendAjax(url, data, function(json){
		if (typeof json.view != 'undefined') {
			$('#galleries').append(urldecode(json.view));
		}
		if (typeof json.success != 'undefined' && json.success == true) {
			$(form).find('[name=name]').val('');
		}
	});
	return false;
}

function gallerySave(form){
	var url = $(form).attr('action');
	var data = $(form).serialize();
	sendAjax(url, data, function(json){
		if (typeof json.view != 'undefined') {
			$(form).closest('li').replaceWith(urldecode(json.view));
		}
	});
	return false;
}

function galleryDel(elem){
	if (!confirm('Удалить галерею со всеми изображениями?')) return false;
	var url = $(elem).data('url');
	sendAjax(url, {}, function(json){
		if (typeof json.success != 'undefined' && json.success == true) {
			$(elem).closest('li').fadeOut(300, function(){ $(this).remove(); });
		}
	});
	return false;
}

function galleryUpload(elem, e){
	var url = $(elem).data('url');
	var data = new FormData();
	files = e.target.files;
    $.each(files, function(key, file)
    {
        if(file['size'] > max_file_size){
            alert('Слишком большой размер файла. Максимальный размер 2Мб');
        } else {
            data.append($(elem).attr('name'), file);
        }
    });
    $(elem).val('');
    sendFiles(url, data, function(json){
    	if (typeof json.html != 'undefined') {
    		$(elem).closest('.gallery-block').find('.gallery-items').append(urldecode(json.html));
    	}
    });
}

function galleryItemDel(elem){
	if (!confirm('Удалить изображение?')) return false;
	var url = $(elem).attr('href');
	sendAjax(url, {}, function(json){
		if (typeof json.success != 'undefined' && json.success == true) {
			$(elem).closest('.images_item').fadeOut(300, function(){ $(this).remove(); });
		}
	});
	return false;
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