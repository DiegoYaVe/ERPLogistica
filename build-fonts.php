<?php
/* ====== SHIMS para PHP 7/8 (evitan el error en makefont.php legacy) ====== */
if (!function_exists('get_magic_quotes_runtime')) {
    function get_magic_quotes_runtime() { return false; }
}
if (!function_exists('set_magic_quotes_runtime')) {
    function set_magic_quotes_runtime($new_setting) { return false; }
}

/* ====== Ruta robusta a makefont.php ====== */
$makefontPath = __DIR__ . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'fpdf'
              . DIRECTORY_SEPARATOR . 'makefont' . DIRECTORY_SEPARATOR . 'makefont.php';

if (!file_exists($makefontPath)) {
    die("No se encontró makefont.php en: $makefontPath");
}
require $makefontPath;

/* ====== Directorio de salida: lib/fpdf/font ====== */
$outDir = __DIR__ . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'fpdf' . DIRECTORY_SEPARATOR . 'font';
if (!is_dir($outDir)) {
    die("No existe el directorio de salida: $outDir");
}
chdir($outDir); // genera aquí los .php/.z

/* ====== RUTAS de tus TTF (ajústalas) ====== */
$centuryRegular = 'C:/fuentes/CenturyGothic/GOTHIC.TTF';
$centuryBold    = 'C:/fuentes/CenturyGothic/GOTHICB.TTF';

$abadiRegular   = 'C:/fuentes/Abadi/AbadiMTStd.ttf';       // cambia al archivo real
$abadiBold      = 'C:/fuentes/Abadi/AbadiMTStd-Bold.ttf';  // cambia al archivo real

/* ====== Aviso si falta alguno ====== */
foreach ([$centuryRegular,$centuryBold,$abadiRegular,$abadiBold] as $path) {
    if (!file_exists($path)) echo "⚠️ No se encontró: $path\n";
}

/* ====== Generación (usa cp1252 porque tu código hace utf8_decode) ====== */
MakeFont($centuryRegular, 'cp1252', true);
MakeFont($centuryBold,    'cp1252', true);
MakeFont($abadiRegular,   'cp1252', true);
MakeFont($abadiBold,      'cp1252', true);

echo "✅ Listo. Revisa archivos generados en: $outDir\n";
