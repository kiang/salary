<?php
$basePath = dirname(__DIR__);
$cunli = json_decode(file_get_contents($basePath . '/map/20180330.json'), true);
$ref = array();
foreach($cunli['objects']['20180330']['geometries'] AS $obj) {
  $ref[$obj['properties']['VILLCODE']] = $obj['properties'];
}
$json = json_decode(file_get_contents($basePath . '/map/fia_data.json'), true);
$stack = array();
foreach($json AS $cunliCode => $lv1) {
  foreach($lv1 AS $year => $lv2) {
    if($year === 2018) {
      if(!isset($stack[$lv2['mid']])) {
        $stack[$lv2['mid']] = array();
      }
      $stack[$lv2['mid']][$cunliCode] = isset($ref[$cunliCode]) ? $ref[$cunliCode]['COUNTYNAME'] . $ref[$cunliCode]['TOWNNAME'] . $ref[$cunliCode]['VILLNAME'] : '';
    }
  }
}

krsort($stack);
foreach($stack AS $income => $lv1) {
  foreach($lv1 AS $cunliCode => $cunliName) {
    echo "{$income},{$cunliName},https://goo.gl/nz3N1T#2018/mid/{$cunliCode}\n";
  }
}
