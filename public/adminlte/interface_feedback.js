function feedbackSelectAll(elem, e){
	e.preventDefault();
	$('[name="id[]"]').prop('checked', true);
}

function feedbackSelectNew(elem, e){
	e.preventDefault();
	$('.fb-new [name="id[]"]').prop('checked', true);
}

function feedbackUnSelectAll(elem, e){
	e.preventDefault();
	$('[name="id[]"]').prop('checked', false);
}

function feedbackReadSelect(elem, e){
	e.preventDefault();
	var url = $(elem).attr('href');
	var data = $('#feedbacks-form').serialize();

	sendAjax(url, data, function(json){
		if (typeof json.success != 'undefined' && json.success == true) {
			$('#feedbacks-form [name="id[]"]:checked').closest('tr').removeAttr('class');
		}
	});
}

function feedbackDelSelect(elem, e){
	e.preventDefault();
	if (!confirm('Вы уверены?')) return false;
	var url = $(elem).attr('href');
	var data = $('#feedbacks-form').serialize();

	sendAjax(url, data, function(json){
		if (typeof json.success != 'undefined' && json.success == true) {
			$('#feedbacks-form [name="id[]"]:checked').closest('tr').fadeOut(300, function(){ $(this).remove(); });
		}
	});
}

function feedbackDel(elem, e){
	e.preventDefault();
	if (!confirm('Вы уверены?')) return false;
	var url = $(elem).attr('href');

	sendAjax(url, {}, function(json){
		if (typeof json.success != 'undefined' && json.success == true) {
			$(elem).closest('tr').fadeOut(300, function(){ $(this).remove(); });
		}
	});
}