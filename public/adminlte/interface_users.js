function userSave(form){
	var url = $(form).attr('action');
	var data = $(form).serialize();
	sendAjax(url, data, function(json){
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
		if (typeof json.success != 'undefined' && json.success == true) {
			popupClose();
		}
	});
	return false;
}

function userDel(elem){
	if (!confirm('Удалить пользователя?')) return false;
	var url = $(elem).attr('href');
	sendAjax(url, {}, function(json){
		if (typeof json.success != 'undefined' && json.success == true) {
			$(elem).closest('tr').fadeOut(300, function(){ $(this).remove(); });
		}
	});
	return false;
}