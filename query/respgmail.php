<?php
ini_set('display_errors',0);
//include('db.php');
include('../funciones.php');

  

$resp = $_POST['cgm'];
$html = "";

$sql = 'SELECT u_gmail FROM usuarios WHERE u_id = "'.$resp.'"';
$result = setq($sql);
$row = $result->fetch_array();
$html = $row['u_gmail'];


echo $html;
?>