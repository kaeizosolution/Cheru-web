<?php
include_once("/home1/kaeizner/tarzanmall/cron/Classes/Config.php");
#error_reporting(E_ALL);
#ini_set('display_errors', 1);

$conf = new Config;
$constants = $conf->constants();
$dbh_master = $conf->DB_MASTER;

#print_r($dbh_master);exit;
$sql = "SELECT * FROM ec_currency";
$sth = $dbh_master->prepare($sql);
$result = $conf->select((object) ['DBH' => $dbh_master, 'STH' => $sth, 'QRY' => $sql]);
print_r($result);
/*
$sql = "INSERT INTO MyGuests SET firstname = ?, lastname = ?, email = ?";
$sth = $dbh_master->prepare($sql);
$result = $conf->insert((object) ['DBH' => $dbh_master, 'STH' => $sth, 'QRY' => $sql, 'PARAMS' => ['Neeraj', 'Diwakar', 'neeraj@example.com']]);
print $result;

$sql = "UPDATE MyGuests SET firstname = ? WHERE id = ?";
$sth = $dbh_master->prepare($sql);
$result = $conf->update((object) ['DBH' => $dbh_master, 'STH' => $sth, 'QRY' => $sql, 'PARAMS' => ['Danga', 1]]);
print $result;
*/
?>
