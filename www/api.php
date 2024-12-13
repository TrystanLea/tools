<?php

error_reporting(E_ALL);
ini_set('display_errors', 'on');

header('Content-Type: application/json');

$ids = get("ids");
$start = (float) get("start");
$end = (float) get("end");
$interval = (int) get("interval");
$average = (int) get("average",false,0);
$delta = (int) get("delta",false,0);
$skipmissing = (int) get("skipmissing",false,0);
$limitinterval = (int) get("limitinterval",false,0);
$timeformat = get('timeformat',false,'unixms');
$dp = (int) get('dp',false,-1);

if (!in_array($timeformat,array("unix","unixms","excel","iso8601","notime"))) {
    return array('success'=>false, 'message'=>'Invalid time format');
}

$url = "http://emoncms.org/feed/data.json?ids=$ids&start=$start&end=$end&interval=$interval&average=$average&delta=$delta&skipmissing=$skipmissing&limitinterval=$limitinterval&timeformat=$timeformat&dp=$dp";
$md5_url = md5($url);

// if cache exists load from cache
$cache_file = "cache/$md5_url.json";

if (file_exists($cache_file)) {
    $data = file_get_contents($cache_file);
    echo $data;
    return;
} else {
    // if cache does not exist get data from emoncms
    $data = file_get_contents($url);
    // Save output to cache folder
    // file_put_contents($cache_file,$data);
    echo $data;
}

function get($index,$error_if_missing=false,$default=null)
{
    $val = $default;
    if (isset($_GET[$index])) {
        $val = rawurldecode($_GET[$index]);
    } else if ($error_if_missing) {
        header('Content-Type: text/plain');
        die("missing $index parameter");
    }
    if(!is_null($val)){
    $val = stripslashes($val);
	}
    return $val;
}
