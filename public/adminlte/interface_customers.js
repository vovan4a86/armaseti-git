var details;

function detailsAttache(elem, e) {
	$.each(e.target.files, function (key, file) {
		if (file['size'] > max_file_size) {
			alert('Слишком большой размер файла. Максимальный размер 10Мб');
		} else {
			details = file;
			renderImage(file, function () {
				let item = '<img class="img-polaroid" src="/adminlte/document_small.png" height="100" alt="document">';
				$('#details').html(item);
			});
		}
	});
	$(elem).val('');
}

function customerSave(form, e){
	e.preventDefault();
	const url = $(form).attr('action');
	let data = new FormData();
	$.each($(form).serializeArray(), function (key, value) {
		data.append(value.name, value.value);
	});
	if (details) {
		data.append('details', details);
	}
	sendFiles(url, data, function(json){
		if (typeof json.row != 'undefined') {
			if ($('#users-list tr[data-id='+json.id+']').length) {
				$('#users-list tr[data-id='+json.id+']').replaceWith(urldecode(json.row));
			} else {
				$('#users-list').append(urldecode(json.row));
			}
		}
		if (typeof json.errors != 'undefined') {
			applyFormValidate(form, json.errors);
			var errMsg = [];
			for (var key in json.errors) { errMsg.push(json.errors[key]);  }
			$(form).find('[type=submit]').after(autoHideMsg('red', urldecode(errMsg.join(' '))));
		}
		if (typeof json.success != 'undefined' && json.success === true) {
			details = null;
			popupClose();
		}
	});
	return false;
}

function customerDel(elem){
	if (!confirm('Удалить покупателя?')) return false;
	var url = $(elem).attr('href');
	sendAjax(url, {}, function(json){
		if (typeof json.success != 'undefined' && json.success === true) {
			$(elem).closest('tr').fadeOut(300, function(){ $(this).remove(); });
		}
	});
	return false;
}

function detailsDelete(elem, e) {
	e.preventDefault();
	if (!confirm('Удалить реквизиты?')) return false;
	let url = $(elem).attr('href');
	sendAjax(url, {}, function (json) {
		if (typeof json.msg != 'undefined') alert(urldecode(json.msg));
		if (typeof json.success != 'undefined' && json.success === true) {
			$(elem).closest('#details').fadeOut(300, function () {
				$(this).empty();
			});
		}
	});
	return false;
}
