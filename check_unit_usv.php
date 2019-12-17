<?php
/*
Plugin / Script for Nagios / Icinga2, checking Unit USV.

V4n1X (C)2019

Version: master
*/
$host = $argv[1];
$community = "public";

$critical = false;
$warning = false;

$output = "";

$upsESystemStatus = snmpget($host, $community, ".1.3.6.1.4.1.935.10.1.1.2.1.0");
$upsESystemStatus = str_replace("INTEGER: ", "", $upsESystemStatus);

if(!$upsESystemStatus) {
    fwrite(STDOUT, "KRITISCH: Verbindung konnte nicht hergestellt werden.");
  	exit(2);
}

if($upsESystemStatus == 7) {
  $error = getSystemStatus($upsESystemStatus);
  $critical = true;
  $output .= "Status: " . $error . " - ";
}

if($upsESystemStatus != 4) {
  $error = getSystemStatus($upsESystemStatus);
  $warning = true;
  $output .= "Status: " . $error . " - ";
}

$upsESystemTemperature = snmpget($host, $community, ".1.3.6.1.4.1.935.10.1.1.2.2.0");
$upsESystemTemperature = str_replace("INTEGER: ", "", $upsESystemTemperature);

$upsESystemTemperature = $upsESystemTemperature * 0.1;

if($upsESystemTemperature > 45.0) {
  $critical = true;
  $output .= "Hohe Temperatur (" . $upsESystemTemperature . "°C)" . " - ";
}

if($upsESystemTemperature < 45.0 && $upsESystemTemperature > 43.0) {
  $warning = true;
  $output .= "Erhöhte Temperatur (" . $upsESystemTemperature . "°C)" . " - ";
}

$upsEBatteryStatus = snmpget($host, $community, ".1.3.6.1.4.1.935.10.1.1.3.1.0");
$upsEBatteryStatus = str_replace("INTEGER: ", "", $upsEBatteryStatus);

if($upsEBatteryStatus != 2) {
  $error = getBatteryStatus($upsEBatteryStatus);
  $critical = true;
  $output .= "Batterie: " . $error;
}

$upsEBatteryEstimatedMinutesRemaining = snmpget($host, $community, ".1.3.6.1.4.1.935.10.1.1.3.3.0");
$upsEBatteryEstimatedMinutesRemaining = str_replace("INTEGER: ", "", $upsEBatteryEstimatedMinutesRemaining);

$output = rtrim($output, " - ");

if($critical) {
  fwrite(STDOUT, $output);
	exit(2);
}

if($warning) {
  fwrite(STDOUT, $output);
	exit(1);
}

fwrite(STDOUT, "Status: " . getSystemStatus($upsESystemStatus) . " - Batteriestatus: " . getBatteryStatus($upsEBatteryStatus) . " - Temperatur: " . $upsESystemTemperature . "°C - Verbleibend: " . $upsEBatteryEstimatedMinutesRemaining . " Minuten.");
exit(0);


function getSystemStatus($code) {

  $status = "";

  switch ($code) {
    case 1:
    $status = "POWER-UP (STARTET)";
    break;

    case 2:
    $status = "STANDBY";
    break;

    case 3:
    $status = "BYPASS";
    break;

    case 4:
    $status = "Line (Normal)";
    break;

    case 5:
    $status = "Batteriebetrieb";
    break;

    case 6:
    $status = "Batterie-Test";
    break;

    case 7:
    $status = "DEFEKT";
    break;

    case 8:
    $status = "CONVERTER";
    break;

    case 9:
    $status = "ECO/Sparmodus";
    break;

    case 10:
    $status = "Herunterfahren...";
    break;

    case 11:
    $status = "ON-BOOSTER";
    break;

    case 12:
    $status = "ON-REDUCER";
    break;

    case 13:
    $status = "UNBEKANNT";
    break;

  }

  return $status;

}


function getBatteryStatus($code) {

  $status = "";

  switch ($code) {
    case 1:
    $status = "Unbekannt";
    break;

    case 2:
    $status = "Normal";
    break;

    case 3:
    $status = "Schwach";
    break;

    case 4:
    $status = "Verbraucht";
    break;

    case 5:
    $status = "Entladung";
    break;

    case 6:
    $status = "Fehler";
    break;


  }

  return $status;

}

?>
