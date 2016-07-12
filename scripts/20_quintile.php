<?php

$tmpPath = dirname(__DIR__) . '/tmp/fia.gov.tw';
if (!file_exists($tmpPath)) {
    mkdir($tmpPath, 0777, true);
}

$targetPath = __DIR__ . '/quintile';
if (!file_exists($targetPath)) {
    mkdir($targetPath, 0777, true);
}

$result = array();

for($i = 103; $i > 99; $i--) {
  $pageFile = $tmpPath . '/' . $i;
  if(!file_exists($pageFile)) {
    file_put_contents($pageFile, file_get_contents("http://www.fia.gov.tw/public/Attachment/isa{$i}/{$i}_6-3-2.html"));
  }
  $page = file_get_contents($pageFile);
  $lines = explode('</tr>', $page);
  foreach($lines AS $line) {
    $cols = explode('</td>', $line);
    foreach($cols AS $k => $v) {
      $cols[$k] = trim(strip_tags($v));
    }
    if(isset($cols[1]) && substr($cols[1], -6) === '分位') {
      if(!isset($result[$cols[1]])) {
        $result[$cols[1]] = array();
      }
      $result[$cols[1]][$i + 1911] = array(
        '納稅單位' => $cols[2],
        '綜合所得總額' => $cols[3] * 1000,
        '所得淨額' => $cols[4] * 1000,
        '應納稅額' => $cols[6] * 1000,
        '稅後所得' => $cols[8] * 1000,
        '平均綜合所得總額' => round($cols[3] * 1000 / $cols[2]),
        '平均所得淨額' => round($cols[4] * 1000 / $cols[2]),
        '平均應納稅額' => round($cols[6] * 1000 / $cols[2]),
        '平均稅後所得' => round($cols[8] * 1000 / $cols[2]),
      );
    }
  }
}

foreach($result AS $k => $v) {
  ksort($result[$k]);
}

file_put_contents(dirname(__DIR__) . '/quintile/data.json', json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
