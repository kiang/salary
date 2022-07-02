<?php
$basePath = dirname(__DIR__);

$fh = fopen($basePath . '/data/csv/2018.csv', 'r');
$head = fgetcsv($fh, 2048);
$pool = [];
while ($line = fgetcsv($fh, 2048)) {
    $cunli = $line[0] . $line[1] . $line[2];
    $pool[$cunli] = [
        '2018' => $line[3],
        '2020' => 0,
        'diff' => 0,
    ];
}

$fh = fopen($basePath . '/data/csv/2020.csv', 'r');
$head = fgetcsv($fh, 2048);
while ($line = fgetcsv($fh, 2048)) {
    $cunli = $line[0] . $line[1] . $line[2];
    if(isset($pool[$cunli])) {
        $pool[$cunli]['2020'] = $line[3];
        $pool[$cunli]['diff'] = $pool[$cunli]['2020'] - $pool[$cunli]['2018'];
    }
}

function cmp($a, $b)
{
    if ($a['diff'] == $b['diff']) {
        return 0;
    }
    return ($a['diff'] < $b['diff']) ? -1 : 1;
}

uasort($pool, 'cmp');

print_r($pool);