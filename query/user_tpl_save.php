<?php
include_once('../funciones.php');
session_start();
header('Content-Type: application/json; charset=utf-8');

$u_id = isset($_POST['u_id']) ? trim($_POST['u_id']) : '';
if ($u_id === '') { echo json_encode(['ok'=>false,'msg'=>'u_id requerido']); exit; }

/* 1) Obtén templates desde POST o, si viene vacío, intenta leer raw JSON */
$templatesRaw = $_POST['templates'] ?? null;

if ($templatesRaw === null) {
  // ¿vino como JSON crudo?
  $raw = file_get_contents('php://input');
  if ($raw) {
    $body = json_decode($raw, true);
    if (json_last_error() === JSON_ERROR_NONE && isset($body['templates'])) {
      $templatesRaw = $body['templates'];
      if (isset($body['u_id']) && !$u_id) $u_id = trim($body['u_id']);
    }
  }
}

/* 2) Normaliza a arreglo PHP */
if (is_string($templatesRaw)) {
  $templates = json_decode($templatesRaw, true);
} else {
  $templates = $templatesRaw; // podría ya ser array si usaste FormData.append('templates[]', ...)
}

if (!is_array($templates)) {
  echo json_encode(['ok'=>false,'msg'=>'payload inválido']); exit;
}

/* 3) Guarda / actualiza */
$idsEnviados = [];
foreach ($templates as $idx => $tpl) {
  $id    = isset($tpl['id']) ? (int)$tpl['id'] : 0;
  $t     = addslashes($tpl['title'] ?? '');
  $h     = addslashes($tpl['html'] ?? '');
  $order = isset($tpl['sort_order']) ? (int)$tpl['sort_order'] : $idx;
  $est   = ($tpl['estatus'] ?? 'A') === 'I' ? 'I' : 'A';

  if ($id > 0) {
    $sql = 'UPDATE usuarios_email_templates
            SET title="'.$t.'", html="'.$h.'", sort_order='.$order.', estatus="'.$est.'"
            WHERE id='.$id.' AND u_id="'.addslashes($u_id).'" LIMIT 1';
    setq($sql);
    $idsEnviados[] = $id;
  } else {
    $sql = 'INSERT INTO usuarios_email_templates (u_id, title, html, sort_order, estatus)
            VALUES ("'.addslashes($u_id).'", "'.$t.'", "'.$h.'", '.$order.', "'.$est.'")';
    setq($sql);
    $res = setq('SELECT LAST_INSERT_ID() AS id');
    if ($res) { $r = $res->fetch_assoc(); if ($r) $idsEnviados[] = (int)$r['id']; }
  }
}

echo json_encode(['ok'=>true,'ids'=>$idsEnviados]);
