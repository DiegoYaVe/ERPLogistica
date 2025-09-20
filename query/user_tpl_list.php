<?php
include_once('../funciones.php');
session_start();
header('Content-Type: application/json; charset=utf-8');

$u_id = isset($_GET['u_id']) ? trim($_GET['u_id']) : '';
if ($u_id === '') { echo json_encode([]); exit; }

$sql = 'SELECT id, title, html, sort_order, estatus
        FROM usuarios_email_templates
        WHERE u_id = "'.addslashes($u_id).'"
        ORDER BY sort_order, id';
$res = setq($sql);

$out = [];
if ($res) {
  while ($r = $res->fetch_assoc()) {
    $out[] = [
      'id' => (int)$r['id'],
      'title' => $r['title'],
      'html' => $r['html'],
      'sort_order' => (int)$r['sort_order'],
      'estatus' => $r['estatus']
    ];
  }
}
echo json_encode($out);
