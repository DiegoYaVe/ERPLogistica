<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once('../funciones.php');

$id   = isset($_GET['id']) ? intval($_GET['id']) : 0;
$u_id = isset($_GET['u_id']) ? $_GET['u_id'] : (isset($_SESSION['uid'])? $_SESSION['uid'] : '');

if ($id<=0 || !$u_id) {
  echo json_encode(['ok'=>false,'msg'=>'Parámetros inválidos']); exit;
}

$sql = "SELECT html FROM usuarios_email_templates 
        WHERE id=".$id." AND u_id='".esc($u_id)."' LIMIT 1";
$res = setqemojis($sql);

if ($row = $res->fetch_assoc()) {
  // html viene tal cual lo guardaste (con emojis si usas setqemojis)
  echo json_encode(['ok'=>true, 'html'=>$row['html']]);
} else {
  echo json_encode(['ok'=>false,'msg'=>'No existe la plantilla']);
}

function esc($s){ return addslashes($s); }
