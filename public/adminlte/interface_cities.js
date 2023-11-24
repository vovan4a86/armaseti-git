/**
 * Created by aleks_new on 12.08.2016.
 */

var map;
var marker;
function initTree(data){
    $('#tree').treeview({
        data: data,
        showCheckbox: true,
        showTags: true,
        onNodeChecked: function(event, data){
            if(typeof data.nodes != 'undefined'){
                $.each( data.nodes, function( index, child ) {
                    $('#tree').treeview('checkNode', child.nodeId);
                });
            }
        },
        onNodeUnchecked: function(event, data){
            if(typeof data.nodes != 'undefined'){
                $.each( data.nodes, function( index, child ) {
                    $('#tree').treeview('uncheckNode', child.nodeId);
                });
            }
        },

    });
}

function citySave(form, e){
    e.preventDefault();
    var url = $(form).attr('action');
    var data = new FormData();
    $.each($(form).serializeArray(), function(key, value){
        data.append(value.name, value.value);
    });
    var checkedNode = $('#tree').treeview('getChecked', 0);
    var checked_city = [];
    $.each(checkedNode, function(key, node){
        if(typeof node.type != 'undefined' && node.type === 'city'){
            checked_city.push(node.value);
        }
    });
    data.append('sxgeo_city_id', checked_city);


    sendFiles(url, data, function(json){
        if (typeof json.errors != 'undefined') {
            applyFormValidate(form, json.errors);
            var errMsg = [];
            for (var key in json.errors) { errMsg.push(json.errors[key]);  }
            $(form).find('[type=submit]').after(autoHideMsg('red', urldecode(errMsg.join(' '))));
        }
        if (typeof json.redirect != 'undefined') document.location.href = urldecode(json.redirect);
        if (typeof json.msg != 'undefined') $(form).find('[type=submit]').after(autoHideMsg('green', urldecode(json.msg)));
    });

    return false;
}

function cityDel(elem){
    if (!confirm('Удалить город?')) return false;
    var url = $(elem).attr('href');
    sendAjax(url, {}, function(json){
        if (typeof json.success != 'undefined' && json.success == true) {
            $(elem).closest('tr').fadeOut(300, function(){ $(this).remove(); });
        }
    });
    return false;
}

function initMap(){
    var myLatLng = {lat: parseFloat($('#map').data('center-lat')), lng: parseFloat($('#map').data('center-long'))};
    map = new google.maps.Map(document.getElementById('map'), {
        center: myLatLng,
        zoom: 18
    });

    marker = new google.maps.Marker({
        position: myLatLng,
        map: map,
    });

    map.addListener('click', function(e) {
        placeMarkerAndPanTo(e.latLng);
    });

    $('input[name="lat"], input[name="long"]').on('change', function(){
        var lat = parseFloat($('input[name="lat"]').val());
        var lng = parseFloat($('input[name="long"]').val());
        if(lat && lng){
            myLatLng = new google.maps.LatLng({lat: lat, lng: lng});
            placeMarkerAndPanTo(myLatLng);
            map.panTo(myLatLng)
        }
        console.log(lat);
        console.log(lng);
    })
}

function placeMarkerAndPanTo(latLng) {
    marker.setPosition(latLng);
    $('input[name="lat"]').val(latLng.lat());
    $('input[name="long"]').val(latLng.lng());
}
