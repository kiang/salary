<?php

$basePath = dirname(__DIR__);
$cunliCodes = array();
$fh = fopen($basePath . '/data/cunli_code.csv', 'r');
fgetcsv($fh, 2048);
while ($line = fgetcsv($fh, 2048)) {
    $cunliCodes[$line[1] . $line[3] . $line[5]] = $line[4];
}
fclose($fh);

$codeMap = array(
    '台北市大安區？賢里' => '6300300-036',
    '台北市大安區？英里' => '6300300-037',
    '台北市大安區羣賢里' => '6300300-036',
    '台北市大安區羣英里' => '6300300-037',
    '台北市萬華區糖廍里' => '6300700-015',
    '台北市萬華區糖？里' => '6300700-015',
    '台北市南港區舊庄里' => '6300900-016',
    '台北市文山區樟？里' => '6300800-032',
    '台北市信義區三？里' => '6300200-031',
    '台北市信義區富台里' => '6300200-019',
    '宜蘭縣羅東鎮新羣里' => '1000202-006',
    '宜蘭縣五結鄉錦衆村' => '1000209-012',
    '宜蘭縣冬山鄉羣英村' => '1000208-017',
    '花蓮縣玉里鎮啓模里' => '1001503-002',
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
    '高雄市左營區合羣里' => '6400300-017',
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
    '新北市新店區五峰里' => '6500600-041',
    '新北市坪林區石𥕢里' => '6502000-004',
    '新北市板橋區公館里' => '6500100-021',
    '新北市三峽區永館里' => '6500900-006',
    '新北市土城區峰廷里' => '6501300-017',
    '新北市瑞芳區爪峰里' => '6501200-007',
    '新北市萬里區崁腳里' => '6502800-008',
    '新竹市北區臺溪里' => '1001802-044',
    '新竹縣竹東鎮上館里' => '1000402-006',
    '嘉義縣民雄鄉豐收村' => '1001005-012',
    '嘉義縣民雄鄉雙福村' => '1001005-023',
    '嘉義縣鹿草鄉豐稠村' => '1001011-005',
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
    '澎湖縣馬公市新復里' => '1001601-002',
    '澎湖縣馬公市啓明里' => '1001601-005',
    '澎湖縣馬公市蒔裡里' => '1001601-031',
    '嘉義縣中埔鄉石硦村' => '1001013-014',
    '南投縣竹山鎮硘磘里' => '1000804-005',
    '高雄市湖內區公館里' => '6402500-004',
    '台中市西區公館里' => '6600400-009',
    '台中市西區公？里' => '6600400-009',
);

$txtPairs = array(
    '頭份市' => '頭份鎮',
    '　' => '',
    ' ' => '',
    '彰化縣員林鎮' => '彰化縣員林市',
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
);


$result = array();
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


$trBase = array(
    '&nbsp;' => '',
);

$missing = array();
$skip = array('合計', '其他', '');

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
                        if (substr($cityName, 0, 6) === '桃園') {
                            $cols[1] = substr($cols[1], 0, -3) . '區';
                            $cols[2] = substr($cols[2], 0, -3) . '里';
                        }
                        $cunliKey = $cityName . $cols[1] . $cols[2];
                        $cunliKey = strtr($cunliKey, $txtPairs);
                        $cunliCode = '';
                        if (isset($cunliCodes[$cunliKey])) {
                            $cunliCode = $cunliCodes[$cunliKey];
                        } elseif (isset($codeMap[$cunliKey])) {
                            $cunliCode = $codeMap[$cunliKey];
                        }
                        if (empty($cunliCode)) {
//                            echo $cunliKey . "\n";
//                            $chars1 = $chars2 = preg_split('//u', $cunliKey);
//                            foreach ($chars2 AS $k => $v) {
//                                $chars2[$k] = bin2hex($v);
//                            }
//                            print_r(array_combine($chars1, $chars2));
                        } else {
                            if (!isset($result[$cunliCode])) {
                                $result[$cunliCode] = array();
                            }
                            $result[$cunliCode][$y + 1911] = array(
                                'adm' => intval($cols[3]),
                                'total' => intval($cols[4]),
                                'avg' => floatval($cols[5]),
                                'mid' => intval($cols[6]),
                                'mid1' => intval($cols[7]),
                                'mid3' => intval($cols[8]),
                                'sd' => floatval($cols[9]),
                                'cv' => floatval($cols[10]),
                            );
                        }
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
                    if (false !== strpos($page, 'Big5-HKSCS') || false !== strpos($page, 'charset=big5')) {
                        $page = mb_convert_encoding($page, 'utf-8', 'big5');
                    }
                    $page = strtr($page, $trBase);
                    $lines = explode('</tr>', $page);
                    $lastLine = array();
                    foreach ($lines AS $line) {
                        $cols = preg_split('/<\\/t[dh]>/', $line);
                        foreach ($cols AS $k => $v) {
                            $cols[$k] = trim(strip_tags($v));
                        }
                        $cols[0] = strtr($cols[0], $txtPairs);
                        if(in_array($cols[0], $skip) || empty($cols[2])) {
                            continue;
                        }
                        if (count($cols) === 10 && isset($lastLine[0])) {
                            $cols = array_merge(array($lastLine[0]), $cols);
                        }
                        switch ($city) {
                            case 'S':
                                $cityName = '高雄市';
                                $cunliKey = $cityName . mb_substr($cols[0], 0, -1, 'utf-8') . '區' . mb_substr($cols[1], 0, -1, 'utf-8') . '里';
                                break;
                            case 'L':
                                $cityName = '台中市';
                                $cunliKey = $cityName . mb_substr($cols[0], 0, -1, 'utf-8') . '區' . mb_substr($cols[1], 0, -1, 'utf-8') . '里';
                                break;
                            case 'R':
                                $cityName = '台南市';
                                $cunliKey = $cityName . mb_substr($cols[0], 0, -1, 'utf-8') . '區' . mb_substr($cols[1], 0, -1, 'utf-8') . '里';
                                break;
                            case 'F':
                            case 'H':
                                $cunliKey = $cityName . mb_substr($cols[0], 0, -1, 'utf-8') . '區' . mb_substr($cols[1], 0, -1, 'utf-8') . '里';
                                break;
                            default:
                                $cunliKey = $cityName . $cols[0] . $cols[1];
                        }
                        
                        $cunliKey = strtr($cunliKey, $txtPairs);
                        $cunliCode = '';
                        if (isset($cunliCodes[$cunliKey])) {
                            $cunliCode = $cunliCodes[$cunliKey];
                        } elseif (isset($codeMap[$cunliKey])) {
                            $cunliCode = $codeMap[$cunliKey];
                        }
                        if (empty($cunliCode)) {
                            $missing[$cunliKey] = $pageFile;
//                            echo "{$cunliKey}\n";
//                            print_r($cols);
                        } else {
                            if (!isset($result[$cunliCode])) {
                                $result[$cunliCode] = array();
                            }
                            $result[$cunliCode][$y + 1911] = array(
                                'adm' => intval($cols[2]),
                                'total' => intval($cols[3]),
                                'avg' => floatval($cols[4]),
                                'mid' => intval($cols[5]),
                                'mid1' => intval($cols[6]),
                                'mid3' => intval($cols[7]),
                                'sd' => floatval($cols[8]),
                                'cv' => floatval($cols[9]),
                            );
                        }

                        $lastLine = $cols;
                    }
                }
            }
            break;
    }
}

//echo var_export($missing);

file_put_contents($basePath . '/map/fia_data.json', json_encode($result));