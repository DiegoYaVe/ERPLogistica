<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once('../funciones.php');

$u_id = isset($_GET['u_id']) ? $_GET['u_id'] : (isset($_SESSION['uid'])? $_SESSION['uid'] : '');

if (!$u_id) {
  echo json_encode(['ok'=>false,'msg'=>'u_id requerido']); exit;
}

$sql = "SELECT id, title FROM usuarios_email_templates 
        WHERE u_id = '".esc($u_id)."' AND estatus = 'A'
        ORDER BY sort_order ASC, id ASC";
$res = setq($sql);

$items = [];
while ($row = $res->fetch_assoc()) {
  $items[] = ['id'=>(int)$row['id'], 'title'=>$row['title']];
}
echo json_encode(['ok'=>true, 'items'=>$items]);

function esc($s){ return addslashes($s); }
