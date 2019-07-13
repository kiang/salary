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

$skip = array('合計', '其他', '其  他', '合  計', '納 稅 單 位', '其 他', '合 計', '其　他', '合　計');
$trBase = array(
    '&nbsp;' => '',
);
$txtPairs = array(
    '　' => '',
    ' ' => '',
    hex2bin('f3bfbeb4') => '',
    hex2bin('ee8488') => '廍',
    hex2bin('f3bb95af') => '塭',
    hex2bin('f0a58281') => '塩',
    hex2bin('f3bfbeb5') => '獇',
    hex2bin('f3bc8d95') => '磘',
    hex2bin('f3bfbf80') => '',
    hex2bin('f0a390a4') => '',
    hex2bin('f3b9989c') => '鬮',
    '&#30936;' => '磘',
    '&#28627;' => '濓',
    '&#24269;' => '廍',
    '&#32675;' => '群',
    '&#34886;' => '眾',
    '&#33747;' => '菓',
    '&#27130;' => '槺',
    '&#40388;' => '鷄',
    '&#38682;' => '霧',
    '&#22338;' => '坂',
    '&#151681;' => '鹽',
    '\\' => '',
    '&#22633;' => '塩',
    '&#152930;' => '𥕢',
    '&#29511;' => '獇',
    '&#30940;' => '磜',
    '&#21452;' => '双',
    '&#29314;' => '犁',
    '&#22770;' => '壳',
    '&#158753;' => '𦰡',
    '&#33825;' => '',
    '&#144420;' => '',
    '&#33304;' => '舘',
    '&#144420;' => '塭',
    '&#30822;' => '硦',
    '&#21414;' => '厦',
    '&#32663;' => '羌',
    '&#21709;' => '响',
    '&#20937;' => '凉',
    '&#22752;' => '壠',
    '&#30808;' => '硘',
    '&#22356;' => '坔',
    '&#26658;' => '',
    '&#29456;' => '獇',
    'T26' => '三地門鄉',
    'T27' => '霧臺鄉',
    '?' => '？',
    '&#39;' => '',
    '&#39;T' => '',
);

$targetPath = $basePath . '/data/csv';
if(!file_exists($targetPath)) {
  mkdir($targetPath, 0777, true);
}

foreach (glob($basePath . '/raw/*') AS $rawPath) {
    $p = pathinfo($rawPath);
    $y = substr($p['filename'], 3);
    $oFh = fopen($targetPath . '/' . ($y + 1911) . '.csv', 'w');
    fputcsv($oFh, array('縣市', '鄉鎮市區', '村里', '納稅單位', '綜合所得總額', '平均數', '中位數', '第一分位數', '第三分位數', '標準差', '變異係數'));
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
                    $page = strtr($page, $trBase);
                    $lines = explode('</tr>', $page);
                    $lastLine = array();
                    foreach ($lines AS $line) {
                        $cols = explode('</td>', $line);
                        if (count($cols) !== 13) {
                            continue;
                        }
                        foreach ($cols AS $k => $v) {
                            $cols[$k] = trim(strip_tags($v));
                        }
                        if (in_array($cols[1], $skip) || in_array($cols[2], $skip)) {
                            continue;
                        }
                        if (empty($cols[1])) {
                            $cols[1] = $lastLine[1];
                        }
                        $cols[0] = $cityName;
                        unset($cols[11]);
                        unset($cols[12]);
                        $cols[1] = strtr($cols[1], $txtPairs);
                        $cols[2] = strtr($cols[2], $txtPairs);
                        fputcsv($oFh, $cols);

                        $lastLine = $cols;
                    }
                }
            }
            break;
        case 'ias':
            foreach ($cities AS $city => $cityName) {
                $pageFile = "{$rawPath}/ias165{$city}.html";
                if (file_exists($pageFile)) {
                    $page = file_get_contents($pageFile);
                    $page = strtr($page, $trBase);
                    if (false !== strpos($page, 'Big5-HKSCS') || false !== strpos($page, 'charset=big5')) {
                        $page = mb_convert_encoding($page, 'utf-8', 'big5');
                    }
                    $lines = explode('</tr>', $page);
                    $lastLine = array();
                    foreach ($lines AS $line) {
                        $cols = preg_split('/<\\/t[dh]>/', $line);
                        foreach ($cols AS $k => $v) {
                            $cols[$k] = trim(strip_tags($v));
                        }
                        if(count($cols) < 8) {
                          continue;
                        }
                        if (empty($cols[2]) || in_array($cols[1], $skip) || in_array($cols[0], $skip)) {
                          continue;
                        }

                        if (count($cols) === 10 && isset($lastLine[0])) {
                            $cols = array_merge(array($lastLine[0]), $cols);
                        }
                        unset($cols[10]);
                        $cols[0] = strtr($cols[0], $txtPairs);
                        $cols[1] = strtr($cols[1], $txtPairs);
                        fputcsv($oFh, array_merge(array($cityName), $cols));

                        $lastLine = $cols;
                    }
                }
            }
            break;
    }
}
