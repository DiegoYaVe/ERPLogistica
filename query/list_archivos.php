<?php
require_once('../funciones.php');
session_start();
header('Content-Type: application/json; charset=utf-8');

$idHoja = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($idHoja <= 0) { echo json_encode([]); exit; }

$sql = 'SELECT ca_id, ca_filename, ca_filepath
        FROM crm_capturaleads_archivos
        WHERE cc_id = "'.$idHoja.'"
        ORDER BY ca_id DESC';

$res = setq($sql);
$files = [];
if ($res) {
  while ($r = $res->fetch_assoc()) {
    $files[] = [
      'id' => (int)$r['ca_id'],
      'filename' => $r['ca_filename'],
      // IMPORTANTE: ca_filepath debe ser una URL relativa pública: p.ej. /uploads/prospectos/5/nombre.pdf
      'url' => $r['ca_filepath']
    ];
  }
}

echo json_encode($files);
