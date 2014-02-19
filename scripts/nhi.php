<?php
$basePath = dirname(dirname(__FILE__));
$docFile = $basePath . '/docs/4395_1_保險費負擔金額表(三)公、民營事業、機構及有一定雇主之受雇者適用(102.7.1生效).csv';
if(!file_exists($docFile)) {
    echo "{$docFile} not exists!";
    exit();
}
$data = array();
$linesToSkip = 4;
$fh = fopen($docFile, 'r');
while($line = fgetcsv($fh, 2048)) {
    if($linesToSkip > 0) {
        --$linesToSkip;
        continue;
    }
    $line[2] = preg_replace('/[^0-9]*/', '', $line[2]);
    if(empty($line[2])) {
        continue;
    }
    $data[$line[2]] = array(
        'level' => $line[1],
        'base_salary' => $line[2],
        'fee1' => $line[3],
        'fee2' => $line[4],
        'fee3' => $line[5],
        'fee4' => $line[6],
        'cost_company' => $line[7],
        'cost_government' => $line[8],
    );
}
file_put_contents($basePath . '/data/nhi.json', json_encode($data));