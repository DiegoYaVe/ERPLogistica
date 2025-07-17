<?php
session_start();
date_default_timezone_set("America/Mexico_City");
include_once("funciones.php");
if(!isset($_SESSION['emp'])) $_SESSION['emp'] = 0;


if (!isset($_SESSION['uid']) or !isset($_SESSION['db'])){
  $actual_link = url_completa();
	header("Location: logout?next=$actual_link");
}

$result = setq('SELECT DATABASE()');
list($db) = $result->fetch_row();
if ($db != $_SESSION['db']){
  $actual_link = url_completa();
  header("Location: logout?next=$actual_link");
}
// set timeout period in seconds
$inactive = 60*60*60; //minutos

if(isset($_SESSION['timeout']) ) {
  $session_life = time() - $_SESSION['timeout'];
  if($session_life > $inactive){
    $actual_link = url_completa();
    header("Location: logout?next=$actual_link");
  }
}
$_SESSION['timeout'] = time();
include('aplicacion.php');

$modulo = NULL;
$accion = NULL;
if(isset($_GET['modulo']))
  $modulo = $_GET['modulo'];
else
  $_GET['modulo'] = 'index';
if(isset($_GET['accion']))
  $accion = $_GET['accion'];
else
  $_GET['accion'] = 'index';

if (!isset($modulo)){$modulo = $_GET['modulo']; }
if (!isset($accion)){$accion = $_GET['accion']; }

include ('seguridad.php');
$grupo = busca($_SESSION['uid'],'usuarios','u_id','u_grupo');
if($_SESSION['uid'] == "ADMIN" || $grupo == "ADMIN" || $grupo == "GERENCIA"){
  if($_SESSION['uid'] == "ADMIN")
  $sql0='INSERT IGNORE INTO gruposd SET gd_grupo = "ADMIN", gd_modulo="'.strtoupper($modulo).'", gd_accion="'.strtoupper($accion).'"';

  /* elseif ($grupo == "GERENCIA")
  $sql0='INSERT IGNORE INTO gruposd SET gd_grupo = "GERENCIA", gd_modulo="'.strtoupper($modulo).'", gd_accion="'.strtoupper($accion).'"'; */

  $sql1='INSERT IGNORE INTO grupos_accion SET ga_modulo = "'.strtoupper($modulo).'",ga_accion="'.strtoupper($accion).'"';
  setq($sql0);
  setq($sql1);
}

$seguridad = new Seguridad($modulo,$accion);
$privilegio = $seguridad->privilegio();

//$privilegio = 1;

if($privilegio==0 && ($grupo != "ADMIN" && $grupo != "GERENCIA")){
    die(alert_back('Lo sentimos tu USUARIO '.$_SESSION['uid'].' no tiene acceso a este modulo.',true));
}
include_once('header.php');
?>