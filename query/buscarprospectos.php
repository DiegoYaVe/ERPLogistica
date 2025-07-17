<?php
/* ini_set('display_errors',1); */
include_once('../funciones.php');

$lead = $_POST['lead'];
$sql = 'SELECT COUNT(*) FROM crm_leads WHERE cl_lead = "' . $lead . '" AND cl_estatus = "P" AND cl_fasigna = "'.date("Y-m-d").'"';
$result = setq($sql);
list($number) = $result->fetch_array();

echo $number;
?>