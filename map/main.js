$.ajaxSetup({ async: false });

$.getJSON('fia_data.json', function (data) {
  cunliSalary = data;
});

var sidebar = new ol.control.Sidebar({ element: 'sidebar', position: 'right' });

var projection = ol.proj.get('EPSG:3857');
var projectionExtent = projection.getExtent();
var size = ol.extent.getWidth(projectionExtent) / 256;
var resolutions = new Array(20);
var matrixIds = new Array(20);
for (var z = 0; z < 20; ++z) {
  // generate resolutions and matrixIds arrays for this WMTS
  resolutions[z] = size / Math.pow(2, z);
  matrixIds[z] = z;
}
var container = document.getElementById('popup');
var content = document.getElementById('popup-content');
var closer = document.getElementById('popup-closer');

closer.onclick = function () {
  popup.setPosition(undefined);
  closer.blur();
  return false;
};

var popup = new ol.Overlay({
  element: container,
  autoPan: true,
  autoPanAnimation: {
    duration: 250
  }
});

var nlscMatrixIds = new Array(21);
for (var i = 0; i < 21; ++i) {
  nlscMatrixIds[i] = i;
}

var stylePool = {};

var vectorCunli = new ol.layer.Vector({
  source: new ol.source.Vector({
    url: 'https://kiang.github.io/taiwan_basecode/cunli/topo/20210324.json',
    format: new ol.format.TopoJSON()
  }),
  style: function (f) {
    var key = f.get('VILLCODE'), count = 0;
    if (cunliSalary[key] && cunliSalary[key][currentYear]) {
      count = cunliSalary[key][currentYear][valueKeys[currentButton]];
    }
    var fillColor = ColorBar(count);
    if (!stylePool[fillColor]) {
      stylePool[fillColor] = new ol.style.Style({
        stroke: new ol.style.Stroke({
          color: 'rgba(86,113,228,0.7)',
          width: 1
        }),
        fill: new ol.style.Fill({
          color: fillColor,
        })
      });
    }
    f.set('fillColor', fillColor);
    return stylePool[fillColor];
  }
});

var baseLayer = new ol.layer.Tile({
  source: new ol.source.WMTS({
    matrixSet: 'EPSG:3857',
    format: 'image/png',
    url: 'https://wmts.nlsc.gov.tw/wmts',
    layer: 'EMAP',
    tileGrid: new ol.tilegrid.WMTS({
      origin: ol.extent.getTopLeft(projectionExtent),
      resolutions: resolutions,
      matrixIds: matrixIds
    }),
    style: 'default',
    wrapX: true,
    attributions: '<a href="https://maps.nlsc.gov.tw/" target="_blank">國土測繪圖資服務雲</a>'
  }),
  opacity: 0.5
});

var appView = new ol.View({
  center: ol.proj.fromLonLat([120.20345985889435, 22.994906062625773]),
  zoom: 14
});

var map = new ol.Map({
  layers: [baseLayer, vectorCunli],
  overlays: [popup],
  target: 'map',
  view: appView
});

map.addControl(sidebar);

var geolocation = new ol.Geolocation({
  projection: appView.getProjection()
});

geolocation.setTracking(true);

geolocation.on('error', function (error) {
  console.log(error.message);
});

var positionFeature = new ol.Feature();

positionFeature.setStyle(new ol.style.Style({
  image: new ol.style.Circle({
    radius: 6,
    fill: new ol.style.Fill({
      color: '#3399CC'
    }),
    stroke: new ol.style.Stroke({
      color: '#fff',
      width: 2
    })
  })
}));

var geolocationCentered = false;
geolocation.on('change:position', function () {
  var coordinates = geolocation.getPosition();
  if (coordinates) {
    positionFeature.setGeometry(new ol.geom.Point(coordinates));
    if (false === geolocationCentered) {
      map.getView().setCenter(coordinates);
      geolocationCentered = true;
    }
  }
});

new ol.layer.Vector({
  map: map,
  source: new ol.source.Vector({
    features: [positionFeature]
  })
});

var currentYear = '2020', currentButton = 'mid', currentCunliCode = '',
  currentPlayIndex = false, cunli, cunliSalary,
  valueKeys = {
    avg: 'avg',
    mid: 'mid',
    sd: 'sd',
    mid1: 'mid1',
    mid3: 'mid3'
  }, buttonKeys = {
    avg: 'avg',
    mid: 'mid',
    sd: 'sd',
    mid1: 'mid1',
    mid3: 'mid3'
  };

var showCunli = function (theYear, theButton, cunliCode) {
  if (buttonKeys[theButton]) {
    theButton = buttonKeys[theButton];
  }
  currentYear = theYear;
  currentButton = theButton;
  currentCunliCode = cunliCode;
  if (!cunliCode) {
    cunliCode = '';
  } else {
    vectorCunli.getSource().forEachFeature(function (f) {
      if (f.get('VILLCODE') === cunliCode) {
        showFeature(f);
      }
    });
  }
  vectorCunli.getSource().changed();

  $('a.btn-year').each(function () {
    if ($(this).attr('data-year') === currentYear) {
      $(this).removeClass('btn-default').addClass('btn-primary');
    } else {
      $(this).removeClass('btn-primary').addClass('btn-default');
    }
  });
  $('a.btn-play').each(function () {
    if ($(this).attr('id') === currentButton) {
      $(this).removeClass('btn-default').addClass('btn-primary');
    } else {
      $(this).removeClass('btn-primary').addClass('btn-default');
    }
  });
};

function showFeature(feature) {
  var cunli = feature.get('COUNTYNAME') + feature.get('TOWNNAME') + feature.get('VILLNAME');
  var cunliKey = feature.get('VILLCODE');
  var headerPrinted = false;
  var detail = '<h3>' + cunli + '</h3><div style="float:right;">單位：金額(千元)</div><table class="table table-striped table-fixed" style="display: block;overflow:scroll;">';
  var targetHash = '#' + currentYear + '/' + currentButton + '/' + cunliKey;
  var chartDataSet1 = [], chartDataSet2 = [];
  if (cunliSalary[cunliKey]) {
    detail += '<canvas id="chart1" height="300"></canvas>';
    var chartData = {
      labels: [],
      datasets: []
    };
    for (y in cunliSalary[cunliKey]) {
      chartData.labels.push(y);
      chartDataSet1.push(cunliSalary[cunliKey][y].mid);
      chartDataSet2.push(cunliSalary[cunliKey][y].avg);
      var yLine = '<tr><td>' + y + '</td>';
      for (k in cunliSalary[cunliKey][y]) {
        if (false === headerPrinted) {
          detail += '<thead><tr><td>年度</td><td>納稅單位</td><td>綜合所得總額</td><td>平均數</td><td>中位數</td><td>第一分位數</td><td>第三分位數</td><td>標準差</td><td>變異係數</td></tr></thead><tbody>';
          headerPrinted = true;
        }
        yLine += '<td>' + cunliSalary[cunliKey][y][k] + '</td>';
      }
      detail += yLine + '</tr>';
    }

  }
  detail += '</tbody></table>';
  $('#sidebar-main-block').html(detail);
  if (cunliSalary[cunliKey]) {
    chartData.datasets.push({
      label: "中位數",
      backgroundColor: "rgb(255, 99, 132)",
      data: chartDataSet1
    });
    chartData.datasets.push({
      label: "平均數",
      backgroundColor: "rgb(132, 99, 255)",
      data: chartDataSet2
    });
    const config1 = {
      type: 'line',
      data: chartData,
      options: {
        scales: {
          xAxis: {
            stacked: true
          },
          yAxis: {
            stacked: true
          }
        }
      }
    };
    const ctx1 = document.getElementById('chart1').getContext('2d');
    const myChart1 = new Chart(ctx1, config1);
  }
  if (window.location.hash !== targetHash) {
    window.location.hash = targetHash;
  }
  map.getView().fit(feature.getGeometry());
  geolocationCentered = true;

  if (false === selectedFeature) {
    selectedFeature = new ol.Feature();
    new ol.layer.Vector({
      map: map,
      source: new ol.source.Vector({
        features: [selectedFeature]
      })
    });
  }
  selectedFeature.setStyle(new ol.style.Style({
    stroke: new ol.style.Stroke({
      color: 'rgba(255,0,0,0.7)',
      width: 5
    }),
    fill: new ol.style.Fill({
      color: 'rgba(255,255,0,1)',
    }),
    text: new ol.style.Text({
      font: 'bold 16px "Open Sans", "Arial Unicode MS", "sans-serif"',
      placement: 'point',
      fill: new ol.style.Fill({
        color: 'blue'
      }),
      text: cunli
    })
  }));
  selectedFeature.setGeometry(feature.getGeometry());
}

var selectedFeature = false;
map.on('singleclick', function (evt) {
  map.getView().setZoom(17);
  var sideBarOpened = false;
  $('#sidebar-main-block').html('');
  var featureFound = false;
  map.forEachFeatureAtPixel(evt.pixel, function (feature, layer) {
    var p = feature.getProperties();
    if (false === featureFound && p['VILLCODE']) {
      showFeature(feature);
      featureFound = true;
      sidebar.open('home');
    }
  });
});

map.once('postrender', function (e) {
  $('a.btn-play').click(function () {
    currentButton = $(this).attr('id');
    window.location.hash = '#' + currentYear + '/' + currentButton;
    return false;
  });

  $('a.btn-year').click(function () {
    currentYear = $(this).attr('data-year');
    window.location.hash = '#' + currentYear + '/' + currentButton;
    return false;
  });

  $('a.btn-city').click(function () {
    var cLat = parseFloat($(this).attr('data-lat'));
    var cLng = parseFloat($(this).attr('data-lng'));
    map.getView().setCenter(ol.proj.fromLonLat([cLng, cLat]));
    return false;
  });
  if (window.location.hash == '' || window.location.hash == '#') {
    window.location.hash = '#' + currentYear + '/' + currentButton;
  }
});

function ColorBar(value) {
  if (value == 0)
    return "rgba(255,255,255,0.6)" //white
  else if (value <= 500)
    return "rgba(0,255,0,0.6)" //green
  else if (value <= 700)
    return "rgba(255,255,0,0.6)" //yellow
  else if (value <= 900)
    return "rgba(255,165,0,0.6)" //orange
  else if (value <= 1100)
    return "rgba(255,0,0,0.6)" //red
  else if (value <= 1300)
    return "rgba(128,0,128,0.6)" //purple
  else if (value <= 1500)
    return "rgba(0,0,139,0.6)" //darkblue
  else
    return "rgba(0,0,0,0.6)" //black
}

routie(':theYear/:theButton/:cunliCode?', showCunli);

var firstFound = false;
vectorCunli.on('change', function (e) {
  if (currentCunliCode !== '' && false === firstFound && vectorCunli.getSource().getState() === 'ready') {
    vectorCunli.getSource().forEachFeature(function (f) {
      if (f.get('VILLCODE') === currentCunliCode) {
        showFeature(f);
        firstFound = true;
        sidebar.open('home');
      }
    });
  }
})
