<?php

$basePath = dirname(__DIR__);

$cities = array(
    'A' => '台北市',
    'B' => '台中市',
    'C' => '基隆市',
    'D' => '台南市',
    'E' => '高雄市',
    'F' => '新北市',
    'G' => '宜蘭縣',
    'H' => '桃園市',
    'J' => '新竹縣',
    'K' => '苗栗縣',
    'L' => '臺中縣',
    'M' => '南投縣',
    'N' => '彰化縣',
    'P' => '雲林縣',
    'Q' => '嘉義縣',
    'R' => '臺南縣',
    'S' => '高雄縣',
    'T' => '屏東縣',
    'U' => '花蓮縣',
    'V' => '臺東縣',
    'X' => '澎湖縣',
    //'Y' => '陽明山',
    'W' => '金門縣',
    'Z' => '連江縣',
    'I' => '嘉義市',
    'O' => '新竹市',
);

$txtPairs = array(
    '頭份市' => '頭份鎮',
    '　' => '',
    ' ' => '',
    '彰化縣員林鎮' => '彰化縣員林市',
    'T26' => '三地門鄉',
    'T27' => '霧臺鄉',
    '&#39;' => '',
    '&#39;T' => '',
);

$trBase = array(
    '&nbsp;' => '',
);

$missing = array();
$skip = array('合計', '其他', '');
$errorPool = array();

foreach (glob($basePath . '/raw/*') AS $rawPath) {
    $p = pathinfo($rawPath);
    $y = substr($p['filename'], 3);
    switch (substr($p['filename'], 0, 3)) {
        case 'isa':
            foreach ($cities AS $city => $cityName) {
                switch ($y) {
                    case '99':
                        $pageFile = "{$rawPath}/isa165-{$city}.html";
                        break;
                    default:
                        $pageFile = "{$rawPath}/{$y}_165-{$city}.html";
                        break;
                }
                if (file_exists($pageFile)) {
                    $page = file_get_contents($pageFile);
                    $lines = explode('</tr>', $page);
                    $currentArea = false;
                    foreach ($lines AS $line) {
                        $cols = explode('</td>', $line);
                        if (count($cols) !== 13) {
                            continue;
                        }
                        foreach ($cols AS $k => $v) {
                            $cols[$k] = trim(strip_tags($v));
                        }
                        if(false === $currentArea) {
                          $currentArea = $cityName . strtr($cols[1], $txtPairs);
                          if(!isset($result[$currentArea])) {
                            $result[$currentArea] = array();
                          }
                        }
                        if ($cols[2] === '合　計') {
                          $result[$currentArea][$y + 1911] = array(
                              'adm' => intval($cols[3]),
                              'total' => intval($cols[4]),
                              'avg' => floatval($cols[5]),
                              'mid' => intval($cols[6]),
                              'mid1' => intval($cols[7]),
                              'mid3' => intval($cols[8]),
                              'sd' => floatval($cols[9]),
                              'cv' => floatval($cols[10]),
                          );
                          $currentArea = false;
                        }
                    }
                }
            }
            break;
        case 'ias':
            foreach ($cities AS $city => $cityName) {
                $pageFile = "{$rawPath}/ias165{$city}.html";
                if (file_exists($pageFile)) {
                    $page = file_get_contents($pageFile);
                    if (false !== strpos($page, 'Big5-HKSCS') || false !== strpos($page, 'charset=big5')) {
                        $page = mb_convert_encoding($page, 'utf-8', 'big5');
                    }
                    $page = strtr($page, $trBase);
                    $lines = explode('</tr>', $page);
                    $lastLine = array();
                    $currentArea = false;
                    foreach ($lines AS $line) {
                        $cols = preg_split('/<\\/t[dh]>/', $line);
                        foreach ($cols AS $k => $v) {
                            $cols[$k] = trim(strip_tags($v));
                        }
                        if(empty($cols[2]) || $cols[1] === '納 稅 單 位') {
                          continue;
                        }
                        if(false === $currentArea) {
                          $currentArea = $cityName . strtr($cols[0], $txtPairs);
                          if(!isset($result[$currentArea])) {
                            $result[$currentArea] = array();
                          }
                        }
                        $cols[0] = strtr($cols[0], $txtPairs);
                        if ($cols[0] === '合計') {
                            $result[$currentArea][$y + 1911] = array(
                              'adm' => intval($cols[1]),
                              'total' => intval($cols[2]),
                              'avg' => floatval($cols[3]),
                              'mid' => intval($cols[4]),
                              'mid1' => intval($cols[5]),
                              'mid3' => intval($cols[6]),
                              'sd' => floatval($cols[7]),
                              'cv' => floatval($cols[8]),
                            );
                            $currentArea = false;
                        }
                    }
                }
            }
            break;
    }
}

$fh = fopen($basePath . '/04_fia_city.csv', 'w');
fputcsv($fh, array('area', 'year', 'adm', 'total', 'avg', 'mid', 'mid1', 'mid3', 'sd', 'cv'));
foreach($result AS $area => $lv1) {
  ksort($lv1);
  foreach($lv1 AS $year => $lv2) {
    fputcsv($fh, array_merge(array($area, $year), $lv2));
  }
}

file_put_contents($basePath . '/map/fia_data.json', json_encode($result));
