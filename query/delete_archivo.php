<?php
require_once('../funciones.php');
session_start();
header('Content-Type: application/json; charset=utf-8');

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
if ($id <= 0) { echo json_encode(['ok'=>false,'msg'=>'id inválido']); exit; }

// Obtiene ruta
$sql = 'SELECT ca_filepath FROM crm_capturaleads_archivos WHERE ca_id = "'.$id.'" LIMIT 1';
$res = setq($sql);
if (!$res || $res->num_rows === 0) { echo json_encode(['ok'=>false,'msg'=>'no existe']); exit; }
$row = $res->fetch_assoc();

// borra archivo físico si es una ruta local (relativa)
$relative = $row['ca_filepath'];            // p.ej. /uploads/prospectos/5/archivo.pdf
$absolute = $_SERVER['DOCUMENT_ROOT'].$relative;
if (is_file($absolute)) @unlink($absolute);

// borra registro
$sqlDel = 'DELETE FROM crm_capturaleads_archivos WHERE ca_id = "'.$id.'"';
setq($sqlDel);

echo json_encode(['ok'=>true]);
