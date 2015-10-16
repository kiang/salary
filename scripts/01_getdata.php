<?php

$tmpPath = dirname(__DIR__) . '/tmp/data.gov.tw';
if (!file_exists($tmpPath)) {
    mkdir($tmpPath, 0777, true);
}

$targetPath = __DIR__ . '/csv';
if (!file_exists($targetPath)) {
    mkdir($targetPath, 0777, true);
}

$cunliCodes = array();
$fh = fopen(dirname(__DIR__) . '/data/cunli_code.csv', 'r');
fgetcsv($fh, 2048);
while ($line = fgetcsv($fh, 2048)) {
    $cunliCodes[$line[1] . $line[3] . $line[5]] = $line[4];
}
fclose($fh);

$codeMap = array(
    '宜蘭縣宜蘭市大道里' => '',
    '宜蘭縣宜蘭市和睦里' => '',
    '宜蘭縣宜蘭市昇平里' => '',
    '宜蘭縣宜蘭市民生里' => '',
    '宜蘭縣宜蘭市新興里' => '',
    '宜蘭縣宜蘭市大東里' => '',
    '宜蘭縣宜蘭市中正里' => '',
    '宜蘭縣宜蘭市鄂王里' => '',
    '宜蘭縣宜蘭市慶和里' => '',
    '宜蘭縣羅東鎮新羣里' => '1000202-006',
    '宜蘭縣五結鄉錦衆村' => '1000209-012',
    '宜蘭縣冬山鄉羣英村' => '1000208-017',
    '宜蘭縣蘇澳鎮岳明里' => '',
    '宜蘭縣大同鄉土場村' => '',
    '花蓮縣玉里鎮啓模里' => '1001503-002',
    '南投縣竹山鎮硘  里' => '1000804-005',
    '南投縣名間鄉廍下村' => '1000806-012',
    '屏東縣東港鎮下廍里' => '1001303-017',
    '屏東縣東港鎮興臺里' => '1001303-005',
    '屏東縣里港鄉三廍村' => '1001309-012',
    '屏東縣新園鄉瓦磘村' => '1001317-001',
    '屏東縣林邊鄉崎峰村' => '1001319-007',
    '屏東縣南州鄉溪州村' => '1001320-001',
    '屏東縣佳冬鄉羗園村' => '1001321-010',
    '苗栗縣苑裡鎮山腳里' => '1000502-014',
    '苗栗縣苑裡鎮上館里' => '1000502-021',
    '苗栗縣竹南鎮公館里' => '1000504-018',
    '桃園市桃園區永和里' => '',
    '桃園市蘆竹區大華里' => '',
    '高雄市左營區自立里' => '',
    '高雄市左營區合羣里' => '6400300-017',
    '高雄市左營區復興里' => '',
    '高雄市左營區自治里' => '',
    '高雄市左營區自勉里' => '',
    '高雄市三民區港北里' => '',
    '高雄市鳳山區誠正里' => '',
    '高雄市鳳山區海風里' => '',
    '高雄市岡山區臺上里' => '6401900-020',
    '高雄市岡山區爲隨里' => '6401900-031',
    '高雄市梓官區茄典里' => '6402900-014',
    '高雄市阿蓮區峰山里' => '6402300-003',
    '高雄市內門區內豐里' => '6403500-008',
    '高雄市那瑪夏區達卡努瓦' => '6403800-001',
    '雲林縣斗六市崙峰里' => '1000901-018',
    '雲林縣西螺鎮公館里' => '1000904-027',
    '雲林縣北港鎮公館里' => '1000906-011',
    '雲林縣麥寮鄉瓦磘村' => '1000913-003',
    '雲林縣元長鄉瓦磘村' => '1000917-018',
    '雲林縣四湖鄉箔子村' => '1000918-016',
    '雲林縣四湖鄉箔東村' => '1000918-021',
    '雲林縣水林鄉欍埔村' => '1000920-021',
    '新北市新店區五峰里' => '6500600-041',
    '新北市石碇區碧山里' => '',
    '新北市坪林區石𥕢里' => '6502000-004',
    '新北市板橋區公館里' => '6500100-021',
    '新北市三峽區永館里' => '6500900-006',
    '新北市樹林區猐寮里' => '6500700-007',
    '新北市樹林區西山里' => '',
    '新北市土城區峰廷里' => '6501300-017',
    '新北市瑞芳區爪峰里' => '6501200-007',
    '新北市萬里區崁腳里' => '6502800-008',
    '新竹市北區臺溪里' => '1001802-044',
    '新竹縣竹東鎮上館里' => '1000402-006',
    '嘉義縣民雄鄉豐收村' => '1001005-012',
    '嘉義縣民雄鄉雙福村' => '1001005-023',
    '嘉義縣鹿草鄉豐稠村' => '1001011-005',
    '嘉義縣中埔鄉鹽館村' => '1001013-002',
    '嘉義縣中埔鄉石  村' => '1001013-014',
    '嘉義縣竹崎鄉文峰村' => '1001014-018',
    '嘉義縣梅山鄉瑞峰村' => '1001015-015',
    '彰化縣彰化市下廍里' => '1000701-002',
    '彰化縣彰化市磚磘里' => '1000701-030',
    '彰化縣彰化市寶廍里' => '1000701-059',
    '彰化縣彰化市臺鳳里' => '1000701-071',
    '彰化縣員林市大峰里' => '1000710-030',
    '彰化縣秀水鄉陜西村' => '1000707-005',
    '彰化縣埔鹽鄉廍子村' => '1000714-003',
    '彰化縣埔心鄉舊館村' => '1000715-012',
    '彰化縣埔心鄉南館村' => '1000715-013',
    '彰化縣埔心鄉新館村' => '1000715-014',
    '彰化縣埔心鄉埤腳村' => '1000715-020',
    '彰化縣芳苑鄉頂廍村' => '1000723-013',
    '台中市東區富臺里' => '6600200-014',
    '台中市沙鹿區犂分里' => '6601300-010',
    '台中市大安區龜売里' => '6602200-004',
    '台中市霧峰區丁臺里' => '6602600-018',
    '台北市大安區羣賢里' => '6300300-036',
    '台北市大安區羣英里' => '6300300-037',
    '台北市萬華區糖廍里' => '6300700-015',
    '台北市大同區文昌里' => '',
    '台北市南港區舊庄里' => '6300900-016',
    '臺東縣綠島鄉公館村' => '1001411-001',
    '臺東縣達仁鄉臺坂村' => '1001415-001',
    '台南市中西區赤崁里' => '6703700-005',
    '台南市後壁區菁豐里' => '6700500-007',
    '台南市麻豆區榖興里' => '6700700-001',
    '台南市麻豆區晉江里' => '6700700-004',
    '台南市佳里區溪洲里' => '6701200-011',
    '台南市新化區山腳里' => '6701800-016',
    '台南市新化區𦰡拔里' => '6701800-018',
    '台南市山上區玉峰里' => '6702200-005',
    '台南市龍崎區石𥕢里' => '6703000-008',
    '台南市永康區𥂁洲里' => '6703100-029',
    '澎湖縣馬公市新復里' => '1001601-002',
    '澎湖縣馬公市啓明里' => '1001601-005',
    '澎湖縣馬公市蒔裡里' => '1001601-031',
);

$txtPairs = array(
    '　' => '',
    '桃園縣' => '桃園市',
    '彰化縣員林鎮' => '彰化縣員林市',
    '臺中市' => '台中市',
    '臺北市' => '台北市',
    '臺南市' => '台南市',
);

$pages = array(
    'http://data.gov.tw/search/site/%E7%B6%9C%E5%90%88%E6%89%80%E5%BE%97%E7%A8%85%E6%89%80%E5%BE%97%E7%B8%BD%E9%A1%8D%E5%90%84%E7%B8%A3%E5%B8%82%E9%84%89%E9%8E%AE%E6%9D%91%E9%87%8C%E7%B5%B1%E8%A8%88%E5%88%86%E6%9E%90%E8%A1%A8',
    'http://data.gov.tw/search/site/%E7%B6%9C%E5%90%88%E6%89%80%E5%BE%97%E7%A8%85%E6%89%80%E5%BE%97%E7%B8%BD%E9%A1%8D%E5%90%84%E7%B8%A3%E5%B8%82%E9%84%89%E9%8E%AE%E6%9D%91%E9%87%8C%E7%B5%B1%E8%A8%88%E5%88%86%E6%9E%90%E8%A1%A8?page=1'
);

$prefix = '<h2  property="dc:title" datatype=""><a href="/node/';
$prefixLength = strlen($prefix);
$nodeBaseUrl = 'http://data.gov.tw/node/';
$fiaBaseUrl = 'http://www.fia.gov.tw/public/Attachment/';
$arrToSkip = array(
    '其　他',
    '合　計',
);
$result = array();

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

            $fh = fopen($csvFile, 'r');
            fgetcsv($fh, 2048);
            $header = fgetcsv($fh, 2048);
            while ($line = fgetcsv($fh, 2048)) {
                if (in_array($line[1], $arrToSkip)) {
                    continue;
                }
                $cunliKey = $parts[1] . $line[0] . $line[1];
                $cunliKey = strtr($cunliKey, $txtPairs);
                if (false !== strpos($parts[1], '桃園')) {
                    $cunliKey = mb_substr($cunliKey, 0, -1, 'utf-8') . '里';
                }
                $cunliCode = '';
                if (isset($cunliCodes[$cunliKey])) {
                    $cunliCode = $cunliCodes[$cunliKey];
                } elseif (isset($codeMap[$cunliKey])) {
                    $cunliCode = $codeMap[$cunliKey];
                }
                if (!empty($cunliCode)) {
                    $result[$cunliCode] = array(
                        'adm' => $line[2],
                        'total' => $line[3],
                        'avg' => $line[4],
                        'mid' => $line[5],
                        'mid1' => $line[6],
                        'mid3' => $line[7],
                        'sd' => $line[8],
                        'cv' => $line[9],
                    );
                }
            }
        }
        $pos = strpos($page, $prefix, $posEnd);
    }
}

file_put_contents(dirname(__DIR__) . '/map/data.json', json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
