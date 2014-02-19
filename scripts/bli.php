<?php
$basePath = dirname(dirname(__FILE__));
$docFile = $basePath . '/docs/勞工保險投保薪資分級表(102年7月1日起適用).csv';
if(!file_exists($docFile)) {
    echo "{$docFile} not exists!";
    exit();
}
$data = array();
$linesToSkip = 2;
$fh = fopen($docFile, 'r');
while($line = fgetcsv($fh, 2048)) {
    if($linesToSkip > 0) {
        --$linesToSkip;
        continue;
    }
    $line[0] = str_replace(array('０', '１', '２', '３', '４', '５', '６', '７', '８', '９'), array(0,1,2,3,4,5,6,7,8,9), $line[0]);
    $line[0] = preg_replace('/[^0-9]*/', '', $line[0]);
    $line[3] = str_replace(array('０', '１', '２', '３', '４', '５', '６', '７', '８', '９'), array(0,1,2,3,4,5,6,7,8,9), $line[3]);
    $line[3] = preg_replace('/[^0-9]*/', '', $line[3]);
    if(empty($line[3])) {
        continue;
    }
    $data[$line[3]] = array(
        'level' => $line[0],
        'base_salary' => $line[3],
        'fee_orig' => round($line[3] * 0.085 * 0.2),
        'fee_addition' =>  round($line[3] * 0.01 * 0.2),
        'cost_company_orig' => round($line[3] * 0.085 * 0.7),
        'cost_company_addition' => round($line[3] * 0.01 * 0.7),
        'cost_government_orig' => round($line[3] * 0.085 * 0.1),
        'cost_government_addition' => round($line[3] * 0.01 * 0.1),
    );
}
file_put_contents($basePath . '/data/bli.json', json_encode($data));