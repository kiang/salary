<?php
$basePath = dirname(__DIR__);
$cunli = json_decode(file_get_contents($basePath . '/map/cunli.json'), true);
$ref = array();
foreach($cunli['objects']['20150401']['geometries'] AS $obj) {
  $ref[$obj['properties']['VILLAGE_ID']] = $obj['properties'];
}
$json = json_decode(file_get_contents($basePath . '/map/fia_data.json'), true);

$result = $sort1 = $sort2 = array();
foreach($json AS $cunliCode => $lv1) {
  if(isset($ref[$cunliCode])) {
    if(!isset($result[$ref[$cunliCode]['C_Name']])) {
      $result[$ref[$cunliCode]['C_Name']] = array(
        'midUp' => 0,
        'midDown' => 0,
        'avgUp' => 0,
        'avgDown' => 0,
      );
    }
    $mid = $lv1[2016]['mid'] - $lv1[2014]['mid'];
    if($mid > 0) {
      $result[$ref[$cunliCode]['C_Name']]['midUp'] += 1;
    } else {
      $result[$ref[$cunliCode]['C_Name']]['midDown'] += 1;
    }
    if(!isset($sort1[$mid])) {
      $sort1[$mid] = 0;
    }
    ++$sort1[$mid];
    $avg = $lv1[2016]['avg'] - $lv1[2014]['avg'];
    if($avg > 0) {
      $result[$ref[$cunliCode]['C_Name']]['avgUp'] += 1;
    } else {
      $result[$ref[$cunliCode]['C_Name']]['avgDown'] += 1;
    }
    if(!isset($sort2[$avg])) {
      $sort2[$avg] = 0;
    }
    ++$sort2[$avg];
  }
}

$fh = fopen(__DIR__ . '/result.csv', 'w');
fputcsv($fh, array('縣市', '中位數上升', '中位數下降', '平均數上升', '平均數下降'));
foreach($result AS $city => $data) {
  fputcsv($fh, array_merge(array($city), $data));
}

ksort($sort1);
ksort($sort2);

$fh = fopen(__DIR__ . '/mid.csv', 'w');
foreach($sort1 AS $v => $count) {
  fputcsv($fh, array($v, $count));
}

$fh = fopen(__DIR__ . '/avg.csv', 'w');
foreach($sort2 AS $v => $count) {
  fputcsv($fh, array($v, $count));
}
