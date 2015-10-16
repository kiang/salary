$.ajaxSetup({async: false});

var map,
        currentPlayIndex = false,
        cunli, cunliSalary;

$.getJSON('data.json', function (data) {
    cunliSalary = data;
});
function initialize() {

    /*map setting*/
    $('#map-canvas').height(window.outerHeight / 2.2);

    map = new google.maps.Map(document.getElementById('map-canvas'), {
        zoom: 11,
        center: {lat: 23.00, lng: 120.30}
    });

    $.getJSON('cunli.json', function (data) {
        cunli = map.data.addGeoJson(topojson.feature(data, data.objects.cunli));
    });


    map.data.addListener('mouseover', function (event) {
        var Cunli = event.feature.getProperty('C_Name') + event.feature.getProperty('T_Name') + event.feature.getProperty('V_Name');
        map.data.revertStyle();
        map.data.overrideStyle(event.feature, {fillColor: 'white'});
        $('#content').html('<div>' + Cunli + ' ：' + event.feature.getProperty('num') + ' </div>').removeClass('text-muted');
    });

    map.data.addListener('mouseout', function (event) {
        map.data.revertStyle();
        $('#content').html('在地圖上滑動或點選以顯示數據').addClass('text-muted');
    });

    $('#playButton1').on('click', function () {
        $(this).addClass('active disabled').find('.glyphicon').show();
        $('#playButton2').removeClass('active disabled').find('.glyphicon').hide();
        cunli.forEach(function (value) {
            var key = value.getProperty('VILLAGE_ID'),
                    count = 0;
            if (cunliSalary[key]) {
                count = cunliSalary[key]['avg'];
            }
            value.setProperty('num', count);
        });

        map.data.setStyle(function (feature) {
            color = ColorBar(feature.getProperty('num'));
            return {
                fillColor: color,
                fillOpacity: 0.6,
                strokeColor: 'gray',
                strokeWeight: 1
            }
        });
        return false;
    });

    $('#playButton2').on('click', function () {
        $(this).addClass('active disabled').find('.glyphicon').show();
        $('#playButton1').removeClass('active disabled').find('.glyphicon').hide();
        cunli.forEach(function (value) {
            var key = value.getProperty('VILLAGE_ID'),
                    count = 0;
            if (cunliSalary[key]) {
                count = cunliSalary[key]['mid'];
            }
            value.setProperty('num', count);
        });

        map.data.setStyle(function (feature) {
            color = ColorBar(feature.getProperty('num'));
            return {
                fillColor: color,
                fillOpacity: 0.6,
                strokeColor: 'gray',
                strokeWeight: 1
            }
        });
        return false;
    });
    
    $('#playButton1').trigger('click');
}

google.maps.event.addDomListener(window, 'load', initialize);

function ColorBar(value) {
    if (value == 0)
        return "white"
    else if (value <= 500)
        return "green"
    else if (value <= 700)
        return "yellow"
    else if (value <= 900)
        return "orange"
    else if (value <= 1100)
        return "red"
    else if (value <= 1300)
        return "purple"
    else if (value <= 1500)
        return "darkblue"
    else
        return "black"
}