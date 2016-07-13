$.ajaxSetup({async: false});

var map, currentYear = '2014', currentButton = 'playButton1',
        currentPlayIndex = false,
        cunli, cunliSalary;

$.getJSON('fia_data.json', function (data) {
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

    map.data.addListener('click', function (event) {
        var cunli = event.feature.getProperty('C_Name') + event.feature.getProperty('T_Name') + event.feature.getProperty('V_Name');
        var cunliKey = event.feature.getProperty('VILLAGE_ID');
        var headerPrinted = false;
        var detail = '<h3>' + cunli + '</h3><div style="float:right;">單位：金額(千元)</div><table class="table table-boarded">';
        if (cunliSalary[cunliKey]) {
            for (y in cunliSalary[cunliKey]) {
                var yLine = '<tr><td>' + y + '</td>';
                for (k in cunliSalary[cunliKey][y]) {
                    if (false === headerPrinted) {
                        detail += '<tr><td>年度</td><td>納稅單位</td><td>綜合所得總額</td><td>平均數</td><td>中位數</td><td>第一分位數</td><td>第三分位數</td><td>標準差</td><td>變異係數</td></tr>';
                        headerPrinted = true;
                    }
                    yLine += '<td>' + cunliSalary[cunliKey][y][k] + '</td>';
                }
                detail += yLine + '</tr>';
            }
        }
        detail += '</table>';
        $('#cunliDetail').html(detail);
    });

    map.data.addListener('mouseout', function (event) {
        map.data.revertStyle();
        $('#content').html('在地圖上滑動或點選以顯示數據').addClass('text-muted');
    });

    $('#playButton1').on('click', function () {
        currentButton = 'playButton1';
        $('.btn-primary').removeClass('active disabled').find('.glyphicon').hide();
        $(this).addClass('active disabled').find('.glyphicon').show();
        cunli.forEach(function (value) {
            var key = value.getProperty('VILLAGE_ID'),
                    count = 0;
            if (cunliSalary[key] && cunliSalary[key][currentYear]) {
                count = cunliSalary[key][currentYear]['avg'];
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
        currentButton = 'playButton2';
        $('.btn-primary').removeClass('active disabled').find('.glyphicon').hide();
        $(this).addClass('active disabled').find('.glyphicon').show();
        cunli.forEach(function (value) {
            var key = value.getProperty('VILLAGE_ID'),
                    count = 0;
            if (cunliSalary[key] && cunliSalary[key][currentYear]) {
                count = cunliSalary[key][currentYear]['mid'];
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

    $('#playButton3').on('click', function () {
        currentButton = 'playButton3';
        $('.btn-primary').removeClass('active disabled').find('.glyphicon').hide();
        $(this).addClass('active disabled').find('.glyphicon').show();
        cunli.forEach(function (value) {
            var key = value.getProperty('VILLAGE_ID'),
                    count = 0;
            if (cunliSalary[key] && cunliSalary[key][currentYear]) {
                count = cunliSalary[key][currentYear]['sd'];
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

    $('#playButton4').on('click', function () {
        currentButton = 'playButton4';
        $('.btn-primary').removeClass('active disabled').find('.glyphicon').hide();
        $(this).addClass('active disabled').find('.glyphicon').show();
        cunli.forEach(function (value) {
            var key = value.getProperty('VILLAGE_ID'),
                    count = 0;
            if (cunliSalary[key] && cunliSalary[key][currentYear]) {
                count = cunliSalary[key][currentYear]['mid1'];
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

    $('#playButton5').on('click', function () {
        currentButton = 'playButton5';
        $('.btn-primary').removeClass('active disabled').find('.glyphicon').hide();
        $(this).addClass('active disabled').find('.glyphicon').show();
        cunli.forEach(function (value) {
            var key = value.getProperty('VILLAGE_ID'),
                    count = 0;
            if (cunliSalary[key] && cunliSalary[key][currentYear]) {
                count = cunliSalary[key][currentYear]['mid3'];
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

    $('#' + currentButton).trigger('click');

    $('a.btn-year').click(function () {
        currentYear = $(this).attr('data-year');
        $('a.btn-year').each(function () {
            if ($(this).attr('data-year') === currentYear) {
                $(this).removeClass('btn-default').addClass('btn-primary');
            } else {
                $(this).removeClass('btn-primary').addClass('btn-default');
            }
        });
        $('#' + currentButton).trigger('click');
    });
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