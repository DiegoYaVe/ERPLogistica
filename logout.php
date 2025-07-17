<?php
session_start();
if($_SESSION['eid']) $url='&produccion=1';
else $url = ''; 

$userlog = $_SESSION['uid'];
$vars = explode("?",$_SERVER['REQUEST_URI']);
$numv = count($vars)-1;
//die($numv.'<< '.$vars[$numv]);
$cont = $vars[$numv].'&lusr='.$userlog;

$_SESSION=array();
session_destroy();
header("Location: login?next=$cont.$url");
?>