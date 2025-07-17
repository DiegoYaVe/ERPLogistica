<?php
include_once('../funciones.php');
/* ini_set('display_errors',1); */
session_start();

$fini = $_POST['fini'];
$ffin = $_POST['ffin'];
$motivo = $_POST['motivo'];
$sqlf = '';


$grupo = busca($_SESSION['uid'], 'usuarios', 'u_id', 'u_grupo');
if($grupo == 'ADMIN' || $grupo == 'GERENCIA'){
  $sqlf .= '';
} else{
  $sqlf .= ' AND cl_vendedor = "'.$_SESSION['uid'].'"';
}

if(!empty($motivo)){
  $sql = 'SELECT * FROM crm_leads INNER JOIN historial_perdidos ON hp_perdido = cl_id WHERE cl_estatus = "X" '.$sqlf.'';
} else{
  $sql = 'SELECT * FROM crm_leads WHERE cl_estatus = "X" '.$sqlf.'';
}
if(!empty($fini)) $sql.=' AND cl_fasigna >= "'.$fini.'"';
if(!empty($ffin)) $sql.=' AND cl_fasigna <= "'.$ffin.'"';
if(!empty($motivo)) $sql.=' AND hp_motivo = "'.$motivo.'"';
$sql.=' ORDER BY cl_fasigna DESC';
$result = setq($sql);
$arreglo = array();
$arregloexiste = array();
$arreglonoexiste = array();
$i = 0;
$arropen = array("A","I");
/* echo $sql; */
while($row = $result->fetch_array()){

    $existe = busca($row['cl_telefono'], "crm_clientes", "c_telefono1 = '".$row['cl_telefono']."' OR c_telefono2", "COUNT(*)");
    $texto = busca($_SESSION['uid'], 'usuarios', 'u_id', 'u_saludo');
    if(empty($texto)){
      $phone = "+".$row['cl_code'].$row['cl_telefono'];
    } else {
    $texto_codificado = urlencode($texto);

    $numero_de_telefono = "+".$row['cl_code'].$row['cl_telefono']; // Reemplaza con tu número de teléfono en formato internacional

    $link_whatsapp = "https://api.whatsapp.com/send?phone=$numero_de_telefono&text=$texto_codificado";

    $phone = '<a href="'.$link_whatsapp.'" target="_blank">+'.$row['cl_code'].$row['cl_telefono'].'&nbsp;
    <button style="border: 0px; background: white;" title="Contactar por WhatsApp">
        <i class="fab fa-whatsapp" style="font-size: 20px;"></i>
    </button>
    </a>';
  }
  $acciones = '
    <div style="height: 34px;">
      <div style="padding-left: 5px;">
        <a data-fancybox data-type="ajax" data-src="popup/setdetalleslead.php?id='.$row['cl_id'].'" href="javascript:;">
          <button type="button" class="btn btn-sm btn-info" data-toggle="tooltip" title="Detalles del lead"><i class="fas fa-eye"></i></button>
        </a>
      </div>
    </div>';
  
  $estatus = "Sin negociación";
  $hpmotivo = busca($row['cl_id'], 'historial_perdidos', 'hp_perdido', 'hp_motivo');
  $numasignaciones = busca($row['cl_id'], 'historial_perdidos', 'hp_vendedor IS NOT NULL AND hp_perdido', 'COUNT(*)')." veces";
  if($hpmotivo == 0){
    $motivo = "OTRO";
  } else{
    $motivo = busca($hpmotivo, 'catalogo_perdidos', 'cp_id', 'cp_nmb');
  }

  $arreglo[] = array($row['cl_nmb'],$phone, $row['cl_correo'], $estatus, $row['cl_fasigna'], $motivo, $numasignaciones, $acciones);  
}

$new_array = array("data"=>$arreglo);

echo json_encode($new_array);
?>