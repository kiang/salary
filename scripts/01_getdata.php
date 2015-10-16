<?php

$tmpPath = dirname(__DIR__) . '/tmp/data.gov.tw';
if (!file_exists($tmpPath)) {
    mkdir($tmpPath, 0777, true);
}

$targetPath = __DIR__ . '/csv';
if (!file_exists($targetPath)) {
    mkdir($targetPath, 0777, true);
}

$pages = array(
    'http://data.gov.tw/search/site/%E7%B6%9C%E5%90%88%E6%89%80%E5%BE%97%E7%A8%85%E6%89%80%E5%BE%97%E7%B8%BD%E9%A1%8D%E5%90%84%E7%B8%A3%E5%B8%82%E9%84%89%E9%8E%AE%E6%9D%91%E9%87%8C%E7%B5%B1%E8%A8%88%E5%88%86%E6%9E%90%E8%A1%A8',
    'http://data.gov.tw/search/site/%E7%B6%9C%E5%90%88%E6%89%80%E5%BE%97%E7%A8%85%E6%89%80%E5%BE%97%E7%B8%BD%E9%A1%8D%E5%90%84%E7%B8%A3%E5%B8%82%E9%84%89%E9%8E%AE%E6%9D%91%E9%87%8C%E7%B5%B1%E8%A8%88%E5%88%86%E6%9E%90%E8%A1%A8?page=1'
);

$prefix = '<h2  property="dc:title" datatype=""><a href="/node/';
$prefixLength = strlen($prefix);
$nodeBaseUrl = 'http://data.gov.tw/node/';
$fiaBaseUrl = 'http://www.fia.gov.tw/public/Attachment/';

foreach ($pages AS $pageUrl) {
    $tmpPageFile = $tmpPath . '/' . md5($pageUrl);
    if (!file_exists($tmpPageFile)) {
        file_put_contents($tmpPageFile, file_get_contents($pageUrl));
    }
    $page = file_get_contents($tmpPageFile);
    $pos = strpos($page, $prefix);
    while (false !== $pos) {
        $pos += $prefixLength;
        $posEnd = strpos($page, '</a>', $pos);
        $part = substr($page, $pos, $posEnd - $pos);
        $parts = explode('">綜合所得稅所得總額各縣市鄉鎮村里統計分析表-縣市別：', $part);

        $tmpNodeFile = $tmpPath . '/node_' . $parts[0];
        if (!file_exists($tmpNodeFile)) {
            file_put_contents($tmpNodeFile, file_get_contents($nodeBaseUrl . $parts[0]));
        }
        $node = file_get_contents($tmpNodeFile);
        $nodePos = strpos($node, $fiaBaseUrl);
        if (false !== $nodePos) {
            $nodePosEnd = strpos($node, '&', $nodePos);
            $csvFile = $targetPath . '/' . $parts[1] . '.csv';
            if (!file_exists($csvFile)) {
                file_put_contents($csvFile, file_get_contents(substr($node, $nodePos, $nodePosEnd - $nodePos)));
            }
        }
        $pos = strpos($page, $prefix, $posEnd);
    }
}