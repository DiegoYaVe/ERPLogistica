<?php
// query/save_hoja.php
include_once('../funciones.php');
session_start();
header('Content-Type: application/json; charset=utf-8');

try {
  // Helpers simples
  function val($k,$d=null){ return isset($_POST[$k]) ? trim($_POST[$k]) : $d; }
  function b01($v){ return (isset($v) && (string)$v !== '' && (int)$v === 1) ? 1 : 0; } // normaliza 0/1

  $modo   = strtolower(val('modo','insert')); // insert | update
  $estado = val('estado','');
  $fini   = val('fini', ''); // datetime-local: YYYY-MM-DDTHH:mm:ss
  $ugen   = val('ugen', $_SESSION['uid'] ?? '');
  $obs    = val('observacion', ''); // HTML TinyMCE
  $intl   = b01(val('internacional', 0)); // <-- NUEVO: 0 nacional / 1 internacional

  if ($modo !== 'insert' && $modo !== 'update') {
    echo json_encode(['ok'=>false,'msg'=>'Modo inválido']); exit;
  }

  // Parse de fecha/hora desde input type="datetime-local"
  $cc_fini = null; // DATE
  $cc_hini = null; // TIME
  if (!empty($fini) && strpos($fini, 'T') !== false) {
    list($cc_fini, $cc_hini) = explode('T', $fini, 2);
    // Quita milisegundos si vienen
    if (strlen($cc_hini) > 8) $cc_hini = substr($cc_hini, 0, 8);
  }

  if ($modo === 'insert') {
    // Inserta crm_capturaleads
    // cc_hfin -> NULL, cc_estatus -> A
    $sql = sprintf(
      'INSERT INTO crm_capturaleads 
        (cc_fini, cc_ugen, cc_hini, cc_hfin, cc_estatus, cc_observacion, cc_estado, cc_internacional)
       VALUES ("%s","%s","%s",NULL,"A","%s","%s",%d)',
      $cc_fini,
      $ugen,
      $cc_hini,
      addslashes($obs),
      addslashes($estado),
      $intl                      // <-- NUEVO
    );
    $ok = setq($sql);
    if ($ok === false) { echo json_encode(['ok'=>false,'msg'=>'No se pudo insertar la hoja']); exit; }

    // id generado
    $res = setq('SELECT MAX(cc_id) AS id FROM crm_capturaleads');
    $row = $res ? $res->fetch_assoc() : null;
    $cc_id = $row ? intval($row['id']) : 0;

    // Subir archivos (si vienen)
    handle_media_uploads($cc_id);

    echo json_encode(['ok'=>true,'id'=>$cc_id]); exit;
  }

  if ($modo === 'update') {
    $cc_id = intval(val('idHoja', 0));
    if ($cc_id <= 0) { echo json_encode(['ok'=>false,'msg'=>'idHoja inválido']); exit; }

    // Actualiza campos básicos; si quieres impedir edición de fecha/usuario, omite esos SET
    $sets = [];
    if ($cc_fini) $sets[] = 'cc_fini = "'.$cc_fini.'"';
    if ($cc_hini) $sets[] = 'cc_hini = "'.$cc_hini.'"';
    if ($ugen)    $sets[] = 'cc_ugen = "'.$ugen.'"';
    $sets[] = 'cc_estado = "'.addslashes($estado).'"';
    $sets[] = 'cc_observacion = "'.addslashes($obs).'"';
    $sets[] = 'cc_internacional = '.$intl;              // <-- NUEVO

    $sql = 'UPDATE crm_capturaleads SET '.implode(', ',$sets).' WHERE cc_id = '.$cc_id.' LIMIT 1';
    $ok = setq($sql);
    if ($ok === false) { echo json_encode(['ok'=>false,'msg'=>'No se pudo actualizar la hoja']); exit; }

    // Subir archivos nuevos (si vienen)
    handle_media_uploads($cc_id);

    echo json_encode(['ok'=>true]); exit;
  }

} catch (Throwable $e) {
  echo json_encode(['ok'=>false,'msg'=>$e->getMessage()]); 
  exit;
}

/**
 * Guarda uploads múltiples en /uploads/prospectos/{cc_id}/ y registra en DB
 */
function handle_media_uploads(int $cc_id): void {
  if (empty($_FILES['mediaFiles']) || !isset($_FILES['mediaFiles']['name'])) return;

  $baseDir = '/uploads/prospectos/' . $cc_id . '/';           // URL relativa pública
  $absDir  = rtrim($_SERVER['DOCUMENT_ROOT'],'/'). $baseDir;  // ruta física
  if (!is_dir($absDir)) @mkdir($absDir, 0775, true);

  $names = $_FILES['mediaFiles']['name'];
  $tmp   = $_FILES['mediaFiles']['tmp_name'];
  $errs  = $_FILES['mediaFiles']['error'];
  $sizes = $_FILES['mediaFiles']['size'];

  for ($i=0; $i < count($names); $i++){
    if ($errs[$i] !== UPLOAD_ERR_OK) continue;
    $safeName = preg_replace('/[^a-zA-Z0-9._-]/','_', $names[$i]);
    $final = uniqid()."_".$safeName;
    if (move_uploaded_file($tmp[$i], $absDir.$final)) {
      $sql = sprintf(
        'INSERT INTO crm_capturaleads_archivos (cc_id, ca_filename, ca_filepath, ca_uploaded_by, ca_created_at)
         VALUES (%d, "%s", "%s", "%s", NOW())',
        $cc_id,
        addslashes($safeName),
        addslashes($baseDir.$final),
        addslashes($_SESSION['uid'] ?? '')
      );
      setq($sql);
    }
  }
}
