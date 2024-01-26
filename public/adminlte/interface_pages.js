var newsImage = null;
var fileGost = null;

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

function fileAttache(elem, e){
    $.each(e.target.files, function(key, file)
    {
        if(file['size'] > max_file_size){
            alert('Слишком большой размер файла. Максимальный размер 2Мб');
        } else {
            fileGost = file;
            renderImage(file, function (imgSrc) {
                var item = '<img class="img-polaroid" src="' + '/static/images/common/ico_pdf.svg' + '" height="40" data-image="' + '/static/images/common/ico_pdf.svg' + '" ' +'>';
                $('#file-block').html(item);
            });
        }
    });
}

function pageContent(url) {
    sendAjax(url, {}, function(html){
        $('#page-content').html(html);
    }, 'html');
    return false;
}

function pageSave(form, e) {
    var url = $(form).attr('action');
    var data = new FormData();
    $.each($(form).serializeArray(), function (key, value) {
        data.append(value.name, value.value);
    });
    $.each(settingFiles, function (key, value) {
        data.append(key, value);
    });
    if (newsImage) {
        data.append('image', newsImage);
    }
    sendFiles(url, data, function (json) {
        if (typeof json.errors != 'undefined') {
            applyFormValidate(form, json.errors);
            var errMsg = [];
            for (var key in json.errors) {
                errMsg.push(json.errors[key]);
            }
            $(form).find('[type=submit]').after(autoHideMsg('red', urldecode(errMsg.join(' '))));
        } else {
            newsImage = null;
        }
        if (typeof json.redirect != 'undefined') document.location.href = urldecode(json.redirect);
        if (typeof json.msg != 'undefined') $(form).find('[type=submit]').after(autoHideMsg('green', urldecode(json.msg)));
        if (json.alert) $(form).find('[type=submit]').after(autoHideMsg('red', urldecode(json.alert)));
        if (typeof json.row != 'undefined') {
            var id = $('#page-id').val();
            $('#pages-tree li[data-id=' + id + '] .tree-item').replaceWith(urldecode(json.row));
            var parent = $('#page-content [name=parent_id]').val();
            var cur_parent = $('#pages-tree li[data-id=' + id + ']').closest('ul').closest('li').data('id') || 0;
            if (cur_parent != parent) {
                var item = $('#pages-tree li[data-id=' + id + ']').clone();
                $('#pages-tree li[data-id=' + id + ']').remove();
                if (parent == 0) {
                    $('#pages-tree > .tree-lvl').append(item);
                } else {
                    $('#pages-tree li[data-id=' + parent + '] > ul').append(item);
                }
            }
        }
        if (typeof json.success != 'undefined' && json.success === true) {
            settingFiles = {};
        }
    });
    return false;
}

function pageDel(elem) {
    if (!confirm('Удалить страницу?')) return false;
    var url = $(elem).attr('href');
    sendAjax(url, {}, function (json) {
        if (typeof json.msg != 'undefined') alert(urldecode(json.msg));
        if (typeof json.success != 'undefined' && json.success === true) {
            $(elem).closest('li').fadeOut(300, function () {
                $(this).remove();
            });
        }
        if (json.alert) alert(urldecode(json.alert));
    });
    return false;
}

function addGostFile(elem, e) {
    e.preventDefault();
    var name = $('input[name=file_name]');
    var description = $('input[name=file_description]');
    var formData = new FormData();
    formData.append('file_name', name.val());
    formData.append('file_description', description.val());
    if (fileGost) {
        formData.append('file', fileGost);
    }
    var url = $(elem).attr('href');

    sendAjaxWithFile(url, formData, function(json){
        if(typeof json.row != 'undefined'){
            $('#file_list tbody').append(json.row);
            name.val('');
            description.val('');
            $('#gost-file').val('');
            $('#file-block').empty();
            fileGost = null
        }
    });
}

function updateGostFileOrder(elem, e) {
    e.preventDefault();
    var url = $(elem).attr('href');
    var order = $('input[name=file_order]').val()
    var data = {
        order: order,
    }
    sendAjax(url, data, function(json){
        if (json.msg) $('button[type=submit]').after(autoHideMsg('green', urldecode(json.msg)));
    });
}

function delGostFile(elem, e) {
    e.preventDefault();
    if(!confirm('Точно удалить этот файл?')) return;
    var url = $(elem).attr('href');
    var row = $(elem).closest('tr');

    sendAjax(url, {}, function(json){
        if(typeof json.success != 'undefined'){
            $(row).fadeOut(300, function(){ $(this).remove(); });
        }
    });
}

$(document).ready(function () {
    $('#pages-tree').jstree({
        "core": {
            "animation": 0,
            "check_callback": true,
            'force_text': false,
            "themes": {"stripes": true},
            'data': {
                'url': function (node) {
                    return node.id === '#' ? '/admin/pages/get-pages' : '/admin/pages/get-pages/' + node.id;
                }
            },
        },
        "plugins": ["contextmenu", "dnd", "search", "state", "types"],
        "contextmenu": {
            "items": function ($node) {
                var tree = $("#pages-tree").jstree(true);
                return {
                    "Create": {
                        "icon": "fa fa-plus text-blue",
                        "label": "Создать страницу",
                        "action": function (obj) {
                            // $node = tree.create_node($node);
                            // document.location.href = '/admin/pages/edit?parent=' + $node.id
                            pageContent('/admin/pages/edit?parent=' + $node.id);
                        }
                    },
                    "Edit": {
                        "icon": "fa fa-pencil text-yellow",
                        "label": "Редактировать страницу",
                        "action": function (obj) {
                            // tree.delete_node($node);
                            // document.location.href = '/admin/pages/edit/' + $node.id
                            pageContent('/admin/pages/edit/' + $node.id);
                        }
                    },
                    "Remove": {
                        "_disabled": ($node.id === 1),
                        "icon": "fa fa-trash text-red",
                        "label": "Удалить страницу",
                        "action": function (obj) {
                            if (confirm("Действительно удалить страницу?")) {
                                var url = '/admin/pages/delete/' + $node.id;
                                sendAjax(url, {}, function (json) {
                                    // document.location.href = '/admin/pages';
                                    if (json.success) tree.delete_node($node);
                                    if (json.alert) alert(urldecode(json.alert));
                                })
                            }
                            // tree.delete_node($node);
                        }
                    }
                };
            }
        }
    }).bind("move_node.jstree", function (e, data) {
        treeInst = $(this).jstree(true);
        parent =  treeInst.get_node( data.parent );
        var d = {
            'id':   data.node.id,
            'parent': (data.parent == '#')? 0: data.parent,
            'sorted': parent.children
        };
        sendAjax('/admin/pages/reorder', d);
    }).on("activate_node.jstree", function(e,data){
        if(data.event.button == 0){
            // window.location.href = '/admin/pages/edit/' + data.node.id;
            pageContent('/admin/pages/edit/' + data.node.id);
        }
    });
});
