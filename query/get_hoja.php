<?php
require_once('../funciones.php');
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) { echo json_encode(['ok'=>false,'msg'=>'id inválido']); exit; }

$sql = 'SELECT cc_id, cc_estado, cc_fini, cc_ugen, cc_observacion, cc_internacional
        FROM crm_capturaleads
        WHERE cc_id = "'.$id.'"
        LIMIT 1';
$res = setq($sql);
if (!$res || $res->num_rows===0) { echo json_encode(['ok'=>false,'msg'=>'no existe']); exit; }
$r = $res->fetch_assoc();

echo json_encode([
  'ok'=>true,
  'idHoja' => $r['cc_id'],
  'estado' => $r['cc_estado'],
  'fini' => date('Y-m-d\TH:i:s', strtotime($r['cc_fini'])),
  'ugen' => $r['cc_ugen'],
  'observacionHTML' => $r['cc_observacion'],
  'internacional' => (int)$r['cc_internacional'],
]);
