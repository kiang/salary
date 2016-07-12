<?php

$tmpPath = dirname(__DIR__) . '/tmp/income';
if (!file_exists($tmpPath)) {
    mkdir($tmpPath, 0777, true);
}

$targetPath = __DIR__ . '/income';
if (!file_exists($targetPath)) {
    mkdir($targetPath, 0777, true);
}

$result = array();
$cities = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'M', 'N', 'O', 'P', 'Q', 'T', 'U', 'V', 'W', 'X', 'Z');

/*
 *     [3] => 納稅單位
  [4] => 綜合所得總額
  [5] => 平均數
  [6] => 中位數
  [7] => 第一分位數
  [8] => 第三分位數
  [9] => 標準差
  [10] => 變異係數
 */

for ($i = 103; $i > 99; $i--) {
    foreach ($cities AS $city) {
        $pageFile = $tmpPath . '/' . $i . '_' . $city;
        if (!file_exists($pageFile)) {
            file_put_contents($pageFile, file_get_contents("http://www.fia.gov.tw/public/Attachment/isa{$i}/{$i}_165-{$city}.html"));
        }
        $page = file_get_contents($pageFile);
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
            if ($cols[2] === '合　計' || $cols[2] === '其　他') {
                continue;
            }
            if (empty($cols[1])) {
                $cols[1] = $lastLine[1];
            }
            if (!isset($result[$city])) {
                $result[$city] = array();
            }
            if (!isset($result[$city][$cols[1]])) {
                $result[$city][$cols[1]] = array();
            }
            if (!isset($result[$city][$cols[1]][$cols[2]])) {
                $result[$city][$cols[1]][$cols[2]] = array();
            }
            $result[$city][$cols[1]][$cols[2]][$i + 1911] = array(
                '納稅單位' => intval($cols[3]),
                '綜合所得總額' => intval($cols[4]),
                '平均數' => floatval($cols[5]),
                '中位數' => intval($cols[6]),
                '第一分位數' => intval($cols[7]),
                '第三分位數' => intval($cols[8]),
                '標準差' => floatval($cols[9]),
                '變異係數' => floatval($cols[10]),
            );
            $lastLine = $cols;
        }
    }
}

file_put_contents(dirname(__DIR__) . '/map/fia_data.json', json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
