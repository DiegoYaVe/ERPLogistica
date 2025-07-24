<?php
require_once '../funciones.php';
ini_set('display_errors',0);

function sinacentos($cadena) {
  $buscar = ['Á','É','Í','Ó','Ú','á','é','í','ó','ú','Ñ','ñ'];
  $reemplazar = ['A','E','I','O','U','a','e','i','o','u','N','n'];
  return str_replace($buscar, $reemplazar, $cadena);
}

$estado = isset($_POST['estado']) ? trim(mb_strtoupper(sinacentos($_POST['estado']))) : '';

$sql = "SELECT cc_id FROM crm_capturaleads 
        WHERE cc_estatus = 'A' 
        AND UPPER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(cc_estado, 'Á','A'),'É','E'),'Í','I'),'Ó','O'),'Ú','U'),'á','a'),'é','e'),'í','i'),'ó','o'),'ú','u')) = '$estado'
        LIMIT 1";

$res = setq($sql);
$row = $res->fetch_array();

if ($row) {
    echo json_encode(['existe' => true, 'id' => $row['cc_id']]);
} else {
    echo json_encode(['existe' => false]);
}
?>
