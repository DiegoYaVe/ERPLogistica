<?php
include_once('../funciones.php');
session_start();
header('Content-Type: application/json; charset=utf-8');

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$u_id = isset($_POST['u_id']) ? trim($_POST['u_id']) : '';
if ($id <= 0 || $u_id === '') { echo json_encode(['ok'=>false]); exit; }

$sql = 'DELETE FROM usuarios_email_templates WHERE id='.$id.' AND u_id="'.addslashes($u_id).'" LIMIT 1';
$r = setq($sql);
echo json_encode(['ok' => $r !== false]);
