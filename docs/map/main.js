/* 台灣所得地圖 — Leaflet edition */
(function () {
  'use strict';

  var METRICS = {
    mid: '中位數',
    avg: '平均數',
    mid1: '第一分位數',
    mid3: '第三分位數',
    sd: '標準差'
  };
  var FIELDS = {
    adm: '納稅單位',
    total: '綜合所得總額',
    avg: '平均數',
    mid: '中位數',
    mid1: '第一分位數',
    mid3: '第三分位數',
    sd: '標準差',
    cv: '變異係數'
  };
  var COLOR_STEPS = [
    { limit: 300, color: '#fee8c8' },
    { limit: 400, color: '#fdd49e' },
    { limit: 500, color: '#fdbb84' },
    { limit: 700, color: '#fc8d59' },
    { limit: 900, color: '#ef6548' },
    { limit: 1100, color: '#d7301f' },
    { limit: 1300, color: '#b30000' },
    { limit: 1500, color: '#7f0000' },
    { limit: Infinity, color: '#400000' }
  ];
  var NO_DATA_COLOR = '#f4f2ec';
  var CITIES = [
    ['台北', 25.0537, 121.5078], ['宜蘭', 24.7525, 121.7711],
    ['新竹', 24.8045, 120.9885], ['台中', 24.1678, 120.6582],
    ['嘉義', 23.4773, 120.4301], ['台南', 22.9962, 120.2013],
    ['高雄', 22.6439, 120.3178], ['屏東', 22.6742, 120.5011],
    ['花蓮', 23.9995, 121.6067], ['台東', 22.7932, 121.1243],
    ['南投', 23.9620, 120.9633], ['澎湖', 23.5620, 119.6081],
    ['金門', 24.4465, 118.3762], ['馬祖', 26.1495, 119.9362]
  ];

  var numFmt = new Intl.NumberFormat('zh-TW');

  var salaryData = null;   // VILLCODE -> year -> metrics
  var years = [];          // sorted ascending
  var state = { year: null, metric: 'mid', code: null };
  var rankCache = {};      // 'year/metric' -> { list: [{code,value}...desc], rank: {code: n} }
  var nameIndex = [];      // {code, name, county, town, vill}
  var layersByCode = {};   // VILLCODE -> leaflet layer
  var geoLayer = null;
  var selectedLayer = null;
  var pendingCode = null;
  var trendChart = null;
  var playTimer = null;
  var suppressHash = false;

  /* ---------- loading with progress ---------- */
  var loaded = { topo: 0, data: 0 };
  var totals = { topo: 12900000, data: 25100000 };
  function updateProgress() {
    var pct = Math.min(99, Math.round(100 * (loaded.topo + loaded.data) / (totals.topo + totals.data)));
    document.getElementById('loading-bar').style.width = pct + '%';
  }
  function fetchJSON(url, key) {
    return fetch(url).then(function (res) {
      if (!res.ok) throw new Error(url + ' ' + res.status);
      var len = parseInt(res.headers.get('Content-Length') || '0', 10);
      if (len) totals[key] = len;
      if (!res.body || !res.body.getReader) return res.json();
      var reader = res.body.getReader();
      var chunks = [];
      function pump() {
        return reader.read().then(function (r) {
          if (r.done) return;
          chunks.push(r.value);
          loaded[key] += r.value.length;
          updateProgress();
          return pump();
        });
      }
      return pump().then(function () {
        var size = chunks.reduce(function (s, c) { return s + c.length; }, 0);
        var buf = new Uint8Array(size), off = 0;
        chunks.forEach(function (c) { buf.set(c, off); off += c.length; });
        return JSON.parse(new TextDecoder().decode(buf));
      });
    });
  }

  /* ---------- helpers ---------- */
  function getValue(code, year, metric) {
    var d = salaryData[code];
    if (d && d[year] && typeof d[year][metric] === 'number') return d[year][metric];
    if (d && d[year] && d[year][metric]) return parseFloat(d[year][metric]) || 0;
    return 0;
  }
  function colorFor(value) {
    if (!value) return NO_DATA_COLOR;
    for (var i = 0; i < COLOR_STEPS.length; i++) {
      if (value <= COLOR_STEPS[i].limit) return COLOR_STEPS[i].color;
    }
    return COLOR_STEPS[COLOR_STEPS.length - 1].color;
  }
  function getRanks(year, metric) {
    var key = year + '/' + metric;
    if (rankCache[key]) return rankCache[key];
    var list = [];
    for (var code in salaryData) {
      var v = getValue(code, year, metric);
      if (v) list.push({ code: code, value: v });
    }
    list.sort(function (a, b) { return b.value - a.value; });
    var rank = {}, prevValue = null, prevRank = 0;
    for (var i = 0; i < list.length; i++) {
      var r = (list[i].value === prevValue) ? prevRank : (i + 1);
      rank[list[i].code] = r;
      prevValue = list[i].value;
      prevRank = r;
    }
    rankCache[key] = { list: list, rank: rank };
    return rankCache[key];
  }
  function nameOf(code) {
    var p = layersByCode[code] && layersByCode[code].feature.properties;
    if (!p) return code;
    return (p.COUNTYNAME || '') + (p.TOWNNAME || '') + (p.VILLNAME || '');
  }

  /* ---------- map ---------- */
  var map = L.map('map', {
    preferCanvas: true,
    zoomControl: false,
    attributionControl: true
  }).setView([23.7, 120.96], 8);
  L.control.zoom({ position: 'bottomright' }).addTo(map);

  var baseCarto = L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
    maxZoom: 19,
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions">CARTO</a>'
  }).addTo(map);
  var baseNlsc = L.tileLayer('https://wmts.nlsc.gov.tw/wmts/EMAP/default/GoogleMapsCompatible/{z}/{y}/{x}', {
    maxZoom: 19,
    attribution: '<a href="https://maps.nlsc.gov.tw/" target="_blank">國土測繪圖資服務雲</a>'
  });
  L.control.layers(
    { '簡潔底圖': baseCarto, '台灣通用電子地圖': baseNlsc },
    null,
    { position: 'bottomright' }
  ).addTo(map);

  var canvasRenderer = L.canvas({ padding: 0.3 });

  function baseStyle(feature) {
    var v = getValue(feature.properties.VILLCODE, state.year, state.metric);
    return {
      renderer: canvasRenderer,
      weight: 0.6,
      color: 'rgba(60, 40, 20, 0.35)',
      fillColor: colorFor(v),
      fillOpacity: v ? 0.72 : 0.25
    };
  }
  var selectedStyle = {
    weight: 3,
    color: '#2a78d6',
    fillOpacity: 0.85
  };

  function tooltipHtml(props) {
    var code = props.VILLCODE;
    var v = getValue(code, state.year, state.metric);
    var html = '<div class="tip-name">' + (props.COUNTYNAME || '') + (props.TOWNNAME || '') + (props.VILLNAME || '未編定村里') + '</div>';
    if (v) {
      var ranks = getRanks(state.year, state.metric);
      html += '<div class="tip-val">' + state.year + ' 年' + METRICS[state.metric] + '：<b>' + numFmt.format(v) + '</b> 千元</div>';
      if (ranks.rank[code]) {
        html += '<div class="tip-rank">全國第 ' + numFmt.format(ranks.rank[code]) + ' 名 / ' + numFmt.format(ranks.list.length) + ' 村里</div>';
      }
    } else {
      html += '<div class="tip-val">' + state.year + ' 年無資料</div>';
    }
    return html;
  }

  function buildGeoLayer(topo) {
    var objName = Object.keys(topo.objects)[0];
    var geo = topojson.feature(topo, topo.objects[objName]);
    geoLayer = L.geoJSON(geo, {
      renderer: canvasRenderer,
      style: baseStyle,
      onEachFeature: function (feature, layer) {
        var p = feature.properties;
        layersByCode[p.VILLCODE] = layer;
        if (p.VILLNAME) {
          nameIndex.push({
            code: p.VILLCODE,
            name: (p.COUNTYNAME || '') + (p.TOWNNAME || '') + p.VILLNAME
          });
        }
        layer.on({
          mouseover: function (e) {
            if (layer !== selectedLayer) layer.setStyle({ weight: 2, color: '#0b0b0b' });
            layer.bindTooltip(tooltipHtml(p), { sticky: true, className: 'cunli-tip', direction: 'top' }).openTooltip(e.latlng);
          },
          mouseout: function () {
            if (layer !== selectedLayer) geoLayer.resetStyle(layer);
            layer.closeTooltip();
          },
          click: function () {
            selectVillage(p.VILLCODE, { zoom: true });
          }
        });
      }
    }).addTo(map);
  }

  function restyleAll() {
    if (!geoLayer) return;
    geoLayer.setStyle(baseStyle);
    if (selectedLayer) selectedLayer.setStyle(selectedStyle);
  }

  /* ---------- state / routing ---------- */
  function updateHash(replace) {
    var h = '#' + state.year + '/' + state.metric + (state.code ? '/' + state.code : '');
    if (window.location.hash === h) return;
    suppressHash = true;
    if (replace) {
      history.replaceState(null, '', h);
      suppressHash = false;
    } else {
      window.location.hash = h;
    }
  }
  function applyHash() {
    var parts = window.location.hash.replace(/^#/, '').split('/');
    var changed = false;
    if (parts[0] && years.indexOf(parts[0]) !== -1 && parts[0] !== state.year) {
      state.year = parts[0]; changed = true;
    }
    if (parts[1] && METRICS[parts[1]] && parts[1] !== state.metric) {
      state.metric = parts[1]; changed = true;
    }
    if (changed) refreshControls();
    if (parts[2]) {
      selectVillage(parts[2], { zoom: true, fromHash: true });
    }
  }
  window.addEventListener('hashchange', function () {
    if (suppressHash) { suppressHash = false; return; }
    applyHash();
  });

  function setYear(y) {
    if (y === state.year) return;
    state.year = y;
    refreshControls();
    updateHash(true);
    if (state.code) renderDetail(state.code, { keepView: true });
  }
  function setMetric(m) {
    if (m === state.metric) return;
    state.metric = m;
    refreshControls();
    updateHash(true);
    if (state.code) renderDetail(state.code, { keepView: true });
  }
  function refreshControls() {
    document.getElementById('year-slider').value = years.indexOf(state.year);
    document.getElementById('year-value').textContent = state.year;
    var chips = document.querySelectorAll('#metric-chips .chip');
    chips.forEach(function (c) {
      c.classList.toggle('active', c.dataset.metric === state.metric);
    });
    document.getElementById('legend-title').textContent = METRICS[state.metric] + '（千元）';
    restyleAll();
    renderRankingList();
  }

  /* ---------- selection & detail ---------- */
  function selectVillage(code, opts) {
    opts = opts || {};
    var layer = layersByCode[code];
    if (!layer) { pendingCode = code; return; }
    if (selectedLayer && selectedLayer !== layer) geoLayer.resetStyle(selectedLayer);
    selectedLayer = layer;
    layer.setStyle(selectedStyle);
    if (layer.bringToFront) layer.bringToFront();
    state.code = code;
    if (opts.zoom) {
      var pad = window.innerWidth < 768 ? [20, 20] : [80, 80];
      map.fitBounds(layer.getBounds(), { padding: pad, maxZoom: 15 });
    }
    renderDetail(code, {});
    updateHash(!opts.fromHash);
  }
  function clearSelection() {
    if (selectedLayer) { geoLayer.resetStyle(selectedLayer); selectedLayer = null; }
    state.code = null;
    document.getElementById('detail-panel').classList.remove('open');
    updateHash(true);
  }

  function renderDetail(code, opts) {
    var layer = layersByCode[code];
    if (!layer) return;
    var p = layer.feature.properties;
    var panel = document.getElementById('detail-panel');
    panel.classList.add('open');
    document.getElementById('detail-crumb').textContent = (p.COUNTYNAME || '') + ' › ' + (p.TOWNNAME || '');
    document.getElementById('detail-name').textContent = p.VILLNAME || '未編定村里';

    var d = salaryData[code];
    var statsBox = document.getElementById('detail-stats');
    var tableBox = document.getElementById('detail-table');
    if (!d) {
      statsBox.innerHTML = '<div class="stat-tile" style="grid-column:1/-1;"><div class="k">' + state.year + ' 年</div><div class="v">無資料</div></div>';
      tableBox.innerHTML = '';
      if (trendChart) { trendChart.destroy(); trendChart = null; }
      return;
    }

    var year = d[state.year] ? state.year : Object.keys(d).sort().pop();
    var row = d[year];
    var ranks = getRanks(year, state.metric);
    var v = getValue(code, year, state.metric);
    var html = '';
    html += '<div class="stat-tile highlight"><div class="k">' + year + ' 年' + METRICS[state.metric] + '</div>' +
      '<div class="v">' + numFmt.format(v) + '<small>千元</small></div></div>';
    html += '<div class="stat-tile"><div class="k">全國排名</div><div class="v">' +
      (ranks.rank[code] ? '第 ' + numFmt.format(ranks.rank[code]) + ' 名<small>/' + numFmt.format(ranks.list.length) + '</small>' : '—') + '</div></div>';
    html += '<div class="stat-tile"><div class="k">平均數</div><div class="v">' + numFmt.format(row.avg) + '<small>千元</small></div></div>';
    html += '<div class="stat-tile"><div class="k">納稅單位</div><div class="v">' + numFmt.format(row.adm) + '<small>戶</small></div></div>';
    statsBox.innerHTML = html;

    renderTrendChart(d);

    var yearsDesc = Object.keys(d).sort().reverse();
    var t = '<table class="detail-table"><thead><tr><th>年度</th>';
    for (var f in FIELDS) t += '<th>' + FIELDS[f] + '</th>';
    t += '</tr></thead><tbody>';
    yearsDesc.forEach(function (y) {
      t += '<tr' + (y === state.year ? ' class="current"' : '') + '><td>' + y + '</td>';
      for (var f2 in FIELDS) {
        var val = d[y][f2];
        t += '<td>' + (typeof val === 'number' ? numFmt.format(val) : (val || '—')) + '</td>';
      }
      t += '</tr>';
    });
    t += '</tbody></table>';
    tableBox.innerHTML = t;
  }

  function renderTrendChart(d) {
    var labels = Object.keys(d).sort();
    var mids = [], avgs = [], q1 = [], q3 = [];
    labels.forEach(function (y) {
      mids.push(d[y].mid); avgs.push(d[y].avg);
      q1.push(d[y].mid1); q3.push(d[y].mid3);
    });
    if (trendChart) trendChart.destroy();
    var ctx = document.getElementById('trend-chart').getContext('2d');
    trendChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [
          {
            label: '_q1', data: q1,
            borderColor: 'transparent', backgroundColor: 'transparent',
            pointRadius: 0, borderWidth: 0
          },
          {
            label: '四分位距', data: q3,
            borderColor: 'transparent',
            backgroundColor: 'rgba(42, 120, 214, 0.12)',
            pointRadius: 0, borderWidth: 0,
            fill: '-1'
          },
          {
            label: '中位數', data: mids,
            borderColor: '#2a78d6', backgroundColor: '#2a78d6',
            borderWidth: 2, pointRadius: 2.5, pointHoverRadius: 5, tension: 0.25
          },
          {
            label: '平均數', data: avgs,
            borderColor: '#008300', backgroundColor: '#008300',
            borderWidth: 2, pointRadius: 2.5, pointHoverRadius: 5, tension: 0.25
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
          legend: {
            labels: {
              font: { family: '"Noto Sans TC", sans-serif', size: 11 },
              color: '#52514e',
              boxWidth: 14, boxHeight: 8,
              filter: function (item) { return item.text !== '_q1'; }
            }
          },
          tooltip: {
            filter: function (item) { return item.dataset.label !== '_q1'; },
            callbacks: {
              label: function (item) {
                return item.dataset.label + '：' + numFmt.format(item.parsed.y) + ' 千元';
              }
            }
          }
        },
        scales: {
          x: {
            grid: { display: false },
            ticks: { color: '#898781', font: { size: 10 } }
          },
          y: {
            title: { display: true, text: '千元', color: '#898781', font: { size: 10 } },
            grid: { color: '#e1e0d9' },
            ticks: { color: '#898781', font: { size: 10 } }
          }
        }
      }
    });
  }

  /* ---------- ranking panel ---------- */
  var RANK_CHUNK = 200;
  var rankRendered = 0;
  var rankFiltered = [];
  function renderRankingList() {
    var panel = document.getElementById('ranking-panel');
    if (!panel.classList.contains('open') || !salaryData) return;
    var ranks = getRanks(state.year, state.metric);
    var q = document.getElementById('ranking-filter').value.trim();
    rankFiltered = q
      ? ranks.list.filter(function (r) { return nameOf(r.code).indexOf(q) !== -1; })
      : ranks.list;
    document.getElementById('ranking-sub').textContent =
      state.year + ' 年 · ' + METRICS[state.metric] + ' · 共 ' + numFmt.format(rankFiltered.length) + ' 村里';
    document.getElementById('ranking-list').innerHTML = '';
    rankRendered = 0;
    appendRankChunk(ranks);
  }
  function appendRankChunk(ranks) {
    ranks = ranks || getRanks(state.year, state.metric);
    var listEl = document.getElementById('ranking-list');
    var frag = document.createDocumentFragment();
    var end = Math.min(rankRendered + RANK_CHUNK, rankFiltered.length);
    for (var i = rankRendered; i < end; i++) {
      var r = rankFiltered[i];
      var rk = ranks.rank[r.code];
      var btn = document.createElement('button');
      btn.className = 'rank-row' + (rk <= 3 ? ' top3' : '');
      btn.dataset.code = r.code;
      btn.innerHTML = '<span class="rank">' + rk + '</span><span class="name">' + nameOf(r.code) +
        '</span><span class="val">' + numFmt.format(r.value) + '</span>';
      frag.appendChild(btn);
    }
    rankRendered = end;
    listEl.appendChild(frag);
  }
  document.getElementById('ranking-list').addEventListener('scroll', function () {
    var el = this;
    if (el.scrollTop + el.clientHeight > el.scrollHeight - 400 && rankRendered < rankFiltered.length) {
      appendRankChunk();
    }
  });
  document.getElementById('ranking-list').addEventListener('click', function (e) {
    var btn = e.target.closest('.rank-row');
    if (btn) selectVillage(btn.dataset.code, { zoom: true });
  });
  document.getElementById('ranking-filter').addEventListener('input', function () {
    renderRankingList();
  });

  /* ---------- controls ---------- */
  function buildControls() {
    var mBox = document.getElementById('metric-chips');
    for (var m in METRICS) {
      var b = document.createElement('button');
      b.className = 'chip' + (m === state.metric ? ' active' : '');
      b.dataset.metric = m;
      b.textContent = METRICS[m];
      b.addEventListener('click', function () { stopPlay(); setMetric(this.dataset.metric); });
      mBox.appendChild(b);
    }
    var slider = document.getElementById('year-slider');
    slider.max = years.length - 1;
    slider.value = years.indexOf(state.year);
    slider.addEventListener('input', function () {
      stopPlay();
      setYear(years[parseInt(this.value, 10)]);
    });
    var cBox = document.getElementById('city-chips');
    CITIES.forEach(function (c) {
      var b = document.createElement('button');
      b.className = 'chip';
      b.textContent = c[0];
      b.addEventListener('click', function () { map.flyTo([c[1], c[2]], 13, { duration: 1 }); });
      cBox.appendChild(b);
    });
    var lBox = document.getElementById('legend-scale');
    var lHtml = '';
    COLOR_STEPS.forEach(function (s) {
      lHtml += '<div class="step"><div class="swatch" style="background:' + s.color + '"></div>' +
        '<div class="tick">' + (s.limit === Infinity ? '' : s.limit) + '</div></div>';
    });
    lBox.innerHTML = lHtml;
    document.getElementById('subtitle').textContent =
      '村里綜合所得統計 · ' + years[0] + '–' + years[years.length - 1] + ' · 財政資訊中心';
  }

  /* ---------- play ---------- */
  function stopPlay() {
    if (playTimer) {
      clearInterval(playTimer);
      playTimer = null;
      document.getElementById('play-btn').textContent = '▶';
      document.getElementById('play-btn').classList.remove('active');
    }
  }
  document.getElementById('play-btn').addEventListener('click', function () {
    if (playTimer) { stopPlay(); return; }
    var btn = this;
    btn.textContent = '⏸';
    btn.classList.add('active');
    var i = years.indexOf(state.year);
    if (i >= years.length - 1) i = -1;
    playTimer = setInterval(function () {
      i++;
      if (i >= years.length) { stopPlay(); return; }
      setYear(years[i]);
    }, 1200);
  });

  /* ---------- search ---------- */
  var searchInput = document.getElementById('search-input');
  var searchResults = document.getElementById('search-results');
  searchInput.addEventListener('input', function () {
    var q = this.value.trim();
    if (q.length < 1) { searchResults.style.display = 'none'; return; }
    var hits = [];
    for (var i = 0; i < nameIndex.length && hits.length < 20; i++) {
      if (nameIndex[i].name.indexOf(q) !== -1) hits.push(nameIndex[i]);
    }
    if (!hits.length) {
      searchResults.innerHTML = '<button disabled class="muted">查無符合的村里</button>';
    } else {
      searchResults.innerHTML = hits.map(function (h) {
        var v = getValue(h.code, state.year, state.metric);
        return '<button data-code="' + h.code + '">' + h.name +
          (v ? ' <span class="muted">' + METRICS[state.metric] + ' ' + numFmt.format(v) + ' 千元</span>' : '') +
          '</button>';
      }).join('');
    }
    searchResults.style.display = 'block';
  });
  searchResults.addEventListener('click', function (e) {
    var btn = e.target.closest('button[data-code]');
    if (!btn) return;
    searchResults.style.display = 'none';
    searchInput.value = '';
    selectVillage(btn.dataset.code, { zoom: true });
  });
  document.addEventListener('click', function (e) {
    if (!e.target.closest('.search-wrap')) searchResults.style.display = 'none';
  });

  /* ---------- buttons ---------- */
  document.getElementById('btn-ranking').addEventListener('click', function () {
    var panel = document.getElementById('ranking-panel');
    panel.classList.toggle('open');
    this.classList.toggle('active', panel.classList.contains('open'));
    renderRankingList();
  });
  document.getElementById('ranking-close').addEventListener('click', function () {
    document.getElementById('ranking-panel').classList.remove('open');
    document.getElementById('btn-ranking').classList.remove('active');
  });
  document.getElementById('detail-close').addEventListener('click', clearSelection);
  document.getElementById('btn-zoom').addEventListener('click', function () {
    if (state.code && layersByCode[state.code]) {
      map.fitBounds(layersByCode[state.code].getBounds(), { padding: [80, 80], maxZoom: 16 });
    }
  });
  document.getElementById('btn-share').addEventListener('click', function () {
    var btn = this;
    var url = window.location.href;
    (navigator.clipboard ? navigator.clipboard.writeText(url) : Promise.reject()).then(function () {
      btn.textContent = '已複製！';
      setTimeout(function () { btn.textContent = '複製分享連結'; }, 1500);
    }).catch(function () {
      window.prompt('複製此連結：', url);
    });
  });
  document.getElementById('btn-about').addEventListener('click', function () {
    document.getElementById('about-modal').classList.add('open');
  });
  document.getElementById('about-close').addEventListener('click', function () {
    document.getElementById('about-modal').classList.remove('open');
  });
  document.getElementById('about-modal').addEventListener('click', function (e) {
    if (e.target === this) this.classList.remove('open');
  });
  document.getElementById('btn-locate').addEventListener('click', function () {
    map.locate({ setView: true, maxZoom: 14 });
  });
  map.on('locationfound', function (e) {
    L.circleMarker(e.latlng, {
      radius: 7, color: '#fff', weight: 2, fillColor: '#2a78d6', fillOpacity: 1
    }).addTo(map);
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      document.getElementById('about-modal').classList.remove('open');
      searchResults.style.display = 'none';
    }
  });

  /* ---------- boot ---------- */
  Promise.all([
    fetchJSON('fia_data.json', 'data'),
    fetchJSON('cunli.json', 'topo')
  ]).then(function (results) {
    salaryData = results[0];
    var yearSet = {};
    for (var code in salaryData) {
      for (var y in salaryData[code]) yearSet[y] = true;
    }
    years = Object.keys(yearSet).sort();
    state.year = years[years.length - 1];

    document.getElementById('loading-hint').textContent = '繪製地圖中…';
    // let the browser paint the hint before the heavy topojson conversion
    setTimeout(function () {
      buildGeoLayer(results[1]);
      buildControls();
      applyHash();
      updateHash(true);
      refreshControls();
      if (pendingCode) {
        var c = pendingCode;
        pendingCode = null;
        selectVillage(c, { zoom: true, fromHash: true });
      }
      var loadingEl = document.getElementById('loading');
      loadingEl.classList.add('done');
      setTimeout(function () { loadingEl.remove(); }, 500);
    }, 30);
  }).catch(function (err) {
    document.getElementById('loading-hint').textContent = '資料載入失敗：' + err.message;
    console.error(err);
  });
})();
