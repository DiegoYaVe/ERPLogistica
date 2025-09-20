<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__.'/../funciones.php';

// --- Polyfills PHP8 para PHPMailer 5 ---
if (!function_exists('get_magic_quotes_runtime')) { function get_magic_quotes_runtime(){ return false; } }
if (!function_exists('set_magic_quotes_runtime')) { function set_magic_quotes_runtime($s){ /* noop */ } }
if (!function_exists('get_magic_quotes_gpc')) { function get_magic_quotes_gpc(){ return false; } }

// PHPMailer 5
require_once __DIR__.'/../lib/mailer/class.phpmailer.php';
require_once __DIR__.'/../lib/mailer/class.smtp.php';

// --- Session para obtener el uuid del usuario actual ---
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}

try {
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok'=>false,'msg'=>'Método no permitido']); exit;
  }

  $idHoja = isset($_POST['idHoja']) ? (int)$_POST['idHoja'] : 0;
  if ($idHoja <= 0) {
    echo json_encode(['ok'=>false,'msg'=>'idHoja inválido']); exit;
  }

  // ============================
  // (A) Cargar firma del usuario
  // ============================
  $uuid = isset($_SESSION['uid']) ? (string)$_SESSION['uid'] : '';
  $uuid = preg_replace('~[^a-zA-Z0-9_\-]~', '', $uuid); // sanea básico

  $firmaValor = '';
  if ($uuid !== '') {
    $sqlUser = "SELECT u_id, u_nmb, u_firma FROM usuarios WHERE u_id = '".$uuid."' LIMIT 1";
    //die($sqlUser);
    $rsUser  = setq($sqlUser);
    if ($rsUser && $rsUser->num_rows > 0) {
      $user = $rsUser->fetch_assoc();
      $firmaValor = (string)($user['u_firma'] ?? '');
    }
  }

  // Helper para resolver ruta local/relativa a filesystem
  $resolverFS = function(string $rutaRelOAbs) : string {
    if ($rutaRelOAbs === '') return '';
    // Si empieza con http(s), NO es filesystem
    if (preg_match('~^https?://~i', $rutaRelOAbs)) return '';
    $fs = $rutaRelOAbs;

    if ($rutaRelOAbs[0] === '/' || $rutaRelOAbs[0] === '\\') {
      // Ruta absoluta tipo /var/www/...  o \Windows\...
      $fs = $rutaRelOAbs;
      // Intento mapear si es ruta web absoluta desde DOCUMENT_ROOT
      if (is_file($fs)) return $fs;
      if (isset($_SERVER['DOCUMENT_ROOT']) && $_SERVER['DOCUMENT_ROOT'] !== '') {
        $alt = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') . $rutaRelOAbs;
        if (is_file($alt)) return $alt;
      }
    } else {
      // Ruta relativa → base = raíz del proyecto (carpeta superior a este script)
      $base = realpath(__DIR__.'/..');
      if ($base) {
        $fs = $base . DIRECTORY_SEPARATOR . $rutaRelOAbs;
      } else {
        $fs = __DIR__ . '/..' . DIRECTORY_SEPARATOR . $rutaRelOAbs;
      }
    }
    $fs = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $fs);
    return $fs;
  };

  // 1) Hoja (asunto + cuerpo)
  $sqlHoja = 'SELECT cc_estado, cc_observacion FROM crm_capturaleads WHERE cc_id = '.$idHoja.' LIMIT 1';
  $rsHoja = setq($sqlHoja);
  if (!$rsHoja || $rsHoja->num_rows === 0) {
    echo json_encode(['ok'=>false,'msg'=>'Hoja no encontrada']); exit;
  }
  $hoja = $rsHoja->fetch_assoc();
  $asunto   = 'Seguimiento DVLogitics';
  $bodyHtml = (string)($hoja['cc_observacion'] ?? '');
  if ($bodyHtml === '') {
    $bodyHtml = '<p>Estimado/a,</p><p>Seguimiento de su contacto.</p>';
  }

  // 2) Leads de ESA hoja
  $sqlLeads = '
    SELECT cl_id, cl_nmb, cl_correo
    FROM crm_leads
    WHERE cl_estatus != "X" AND cl_lead = '.$idHoja.'
      AND cl_correo <> ""';
  $res = setq($sqlLeads);
  $all = [];
  while ($r = $res->fetch_assoc()) { $all[] = $r; }

  $total = count($all);
  if ($total === 0) {
    echo json_encode(['ok'=>false,'msg'=>'No hay leads con correo en la hoja']); exit;
  }

  // 3) DISTINCT por correo
  $unique = [];
  $seen   = [];
  foreach ($all as $row) {
    $email = strtolower(trim((string)$row['cl_correo']));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) continue;
    if (isset($seen[$email])) continue;
    $seen[$email] = true;
    $unique[] = ['email' => $email, 'nombre'=> $row['cl_nmb'] ?? ''];
  }
  $unicos = count($unique);
  if ($unicos === 0) {
    echo json_encode(['ok'=>false,'msg'=>'No hay correos válidos']); exit;
  }

  // 4) Adjuntos de la hoja
  $attachments = [];       // archivos que sí existen
  $attachWarn = [];        // reportes de archivos que no se encontraron / no legibles

  $sqlFiles = 'SELECT ca_filename, ca_filepath
               FROM crm_capturaleads_archivos
               WHERE cc_id = '.$idHoja;
  $rf = setq($sqlFiles);
  if ($rf) {
    while ($f = $rf->fetch_assoc()) {
      $name = (string)($f['ca_filename'] ?? '');
      $rel  = (string)($f['ca_filepath'] ?? '');
      if ($rel === '') continue;

      // Si te guardas rutas relativas públicas tipo "/uploads/prospectos/{cc_id}/file.ext"
      // convierte a ruta de filesystem:
      $fs = $rel;

      // Si empieza con http(s) no se puede adjuntar directo (PHPMailer no descarga)
      if (preg_match('~^https?://~i', $rel)) {
        $attachWarn[] = 'No se adjunta URL (descargar primero): '.$rel;
        continue;
      }

      // Si la ruta es absoluta (empieza con /), mapea a DOCUMENT_ROOT
      if ($rel[0] === '/' || $rel[0] === '\\') {
        $fs = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/\\') . $rel;
      } else {
        // Si es relativa, intenta desde la raíz del proyecto
        $base = realpath(__DIR__.'/..'); // carpeta del proyecto (ajusta si quieres)
        $fs = $base ? $base.DIRECTORY_SEPARATOR.$rel : __DIR__.'/..'.DIRECTORY_SEPARATOR.$rel;
      }

      // Normaliza separadores en Windows
      $fs = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $fs);

      if (is_file($fs) && is_readable($fs)) {
        $attachments[] = ['path' => $fs, 'name' => ($name ?: basename($fs))];
      } else {
        $attachWarn[] = 'Archivo no encontrado o sin permisos: '.$rel.' (-> '.$fs.')';
      }
    }
  }

  // 5) SMTP (cPanel fijo como en tu código)
  $smtpHost = 'smtp-mail.outlook.com';
  $smtpUser = 'anegrete@dvlmx.com';
  $smtpPass = 'An13020105';
  $mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host        = $smtpHost;
$mail->Port        = 587;          // OBLIGATORIO en O365
$mail->SMTPSecure  = 'tls';        // STARTTLS
$mail->SMTPAutoTLS = true;         // fuerza STARTTLS si está disponible
$mail->SMTPAuth    = true;
$mail->AuthType    = 'LOGIN';      // explícito para O365
$mail->Timeout     = 20;           // opcional, evita cuelgues

// (opcional) si tu servidor tiene problemas con IPv6, fuerza IPv4:
// $mail->Host = gethostbyname('smtp.office365.com');

$mail->Username    = $smtpUser;
$mail->Password    = $smtpPass;

$mail->CharSet     = 'UTF-8';
$mail->setFrom($smtpUser, 'DVLogistics'); // el From DEBE ser la misma cuenta
$mail->isHTML(true);
// $mail->SMTPDebug  = 3; // <-- habilítalo en pruebas para ver el transcript
// $mail->Debugoutput = 'error_log';

  // Debug temporal (comenta en prod)
  // $mail->SMTPDebug  = 2;
  // $mail->Debugoutput = 'error_log';

  // Helper firma HTML según u_firma
  $construirFirma = function(PHPMailer $m, string $firmaValor) use ($resolverFS) : string {
    if ($firmaValor === '') return ''; // sin firma

    // Si es URL http/https → insertar como <img src="..."> (no embebido)
    if (preg_match('~^https?://~i', $firmaValor)) {
      $src = htmlspecialchars($firmaValor, ENT_QUOTES, 'UTF-8');
      return '<br><br><img src="'.$src.'" alt="Firma" style="max-width:600px;">';
    }

    // Si es ruta local/relativa → intentar embebido
    $fs = $resolverFS($firmaValor);
    if ($fs !== '' && is_file($fs) && is_readable($fs)) {
      $cid = 'firmaCID_'.uniqid();
      // basename opcional como nombre
      $m->addEmbeddedImage($fs, $cid, basename($fs));
      return '<br><br><img src="cid:'.$cid.'" alt="Firma" style="max-width:600px;">';
    }

    // Fallback: si no existe el archivo, no insertar nada
    return '';
  };

  // 6) Envío en lote reusando la misma conexión
  $enviados = 0; $fallidos = 0; $errores = [];

  foreach ($unique as $L) {
    try {
      $mail->clearAllRecipients();
      $mail->clearAttachments(); // limpia adjuntos y embebidos en cada vuelta

      // Adjunta TODOS los archivos de la hoja en este correo
      foreach ($attachments as $a) {
        $mail->addAttachment($a['path'], $a['name']);
      }

      // Destinatario y contenido
      $mail->addAddress($L['email'], $L['nombre']);
      $mail->Subject = $asunto;

      // Personaliza con el nombre si usas {{NOMBRE}} en la plantilla
      $personalizado = str_replace('{{NOMBRE}}', htmlspecialchars($L['nombre']), $bodyHtml);

      // Construye la firma (puede embebida o por URL)
      $firmaHtml = $construirFirma($mail, $firmaValor);

      $mail->Body    = $personalizado . $firmaHtml;
      $mail->AltBody = strip_tags($personalizado); // texto plano

      $mail->send();
      $enviados++;
      usleep(120000);
    } catch (Exception $e) {
      $fallidos++;
      $errores[] = ['email'=>$L['email'], 'error'=>$e->getMessage()];
    }
  }
  $mail->smtpClose();

  echo json_encode([
    'ok'        => true,
    'total'     => $total,
    'unicos'    => $unicos,
    'enviados'  => $enviados,
    'fallidos'  => $fallidos,
    'errores'   => $errores,
    'adjuntos'  => array_map(function($a){ return basename($a['path']); }, $attachments),
    'adj_warnings' => $attachWarn,
  ]);

} catch (Throwable $ex) {
  echo json_encode(['ok'=>false,'msg'=>'Excepción: '.$ex->getMessage()]);
}
