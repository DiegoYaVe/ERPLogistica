<?php
ini_set('display_errors',1);
include('../funciones.php');

$fecha = $_POST['fecha'];
$index = $_POST['index'];

if($index == "0"){

  $sql = 'SELECT * FROM mailing_envios INNER JOIN mailing_campanas ON mc_id = me_campana INNER JOIN mailing_campanasd ON md_id = me_correo 
  INNER JOIN registros_distribuidores ON rd_id = me_registro WHERE me_fecha = "'.$fecha.'" LIMIT 0,20';
  $result = setq($sql);         
  $respuesta = 
  '
  <div class="table-responsive" bis_skin_checked="1">
    <table class="table table-hover  table-bordered table-striped mb-0">
      <thead class="bg-primary text-white">
        <tr>
            <th>Campaña</th>
            <th>Correo</th>
            <th>Origen</th>
            <th>Registro</th>
            <th>Fecha</th>
        </tr>
      </thead>
      <tbody>';
  
  while($row = $result->fetch_array()){
  if($row['me_origen'] == "RD"){
    $sqlRD = 'SELECT * FROM registros_distribuidores WHERE rd_id = "'.$row['me_registro'].'"';
    $resultRD = setq($sqlRD);
    $rowRD = $resultRD->fetch_array();
    $nmb = $rowRD['rd_nmb'];
    $origen = "REGISTRO DISTRIBUIDORES";
  }elseif($row['me_origen'] == "CL"){
    $sqlRD = 'SELECT * FROM clientes WHERE c_id = "'.$row['me_registro'].'"';
    $resultRD = setq($sqlRD);
    $rowRD = $resultRD->fetch_array();
    $nmb = $rowRD['c_nmb'];
    $origen = "CLIENTES";
  }

  $respuesta .= '
        <tr>
            <tH scope="row">'.$row['mc_nmb'].'</th>
            <td>'.$row['md_asunto'].'</td>  
            <td>'.$origen.'</td>
            <td>'.$nmb.'</td>
            <td>'.fecha_formato($row['me_fecha'],false,false).'</td>
        </tr>';
  
      }
  
  $respuesta .= '
      </tbody>
    </table>
  </div>
  
  ';

}elseif($index == "1"){

  $sql = 'SELECT * FROM campanas_conversiones INNER JOIN registros_distribuidores ON rd_id = cc_distribuidor  INNER JOIN mailing_campanas ON mc_id = cc_campana 
  INNER JOIN mailing_campanasd ON md_id = cc_campanad WHERE cc_fecha = "'.$fecha.'" AND cc_estatus = "N" AND cc_fuente = "M" LIMIT 0,20';
  $result = setq($sql);         
  $respuesta = 
  '
  <div class="table-responsive" bis_skin_checked="1">
    <table class="table table-hover table-bordered table-striped mb-0">
      <thead class="bg-primary text-white">
        <tr>
            <th>Distribuidor</th>
            <th>Fecha</th>
            <th>Fuente</th>
            <th>Campana</th>
            <th>Mail</th>
        </tr>
      </thead>
      <tbody>';
  
  while($row = $result->fetch_array()){
  if($row['cc_fuente'] == "M") $fuente = 'Mailing';
  else { }
  $respuesta .= '
        <tr>
            <th scope="row">'.$row['rd_nmb'].'</th>
            <td>'.fecha_formato($row['cc_fecha'],false,false).'</td>
            <td>'.$fuente.'</td>
            <td>'.$row['mc_nmb'].'</td>
            <td>'.$row['md_asunto'].'</td>
        </tr>';
  
      }
  
  $respuesta .= '
      </tbody>
    </table>
  </div>
  
  ';

}elseif($index == "2"){

  //$sql = 'SELECT cc_distribuidor, cc_fconversion, cc_fuente, cc_campana, cc_campanad FROM campanas_conversiones WHERE cc_fconversion = "'.$fecha.'" AND cc_estatus = "A" AND cc_fuente = "M" LIMIT 0,20';
  $sql = 'SELECT * FROM campanas_conversiones INNER JOIN registros_distribuidores ON rd_id = cc_distribuidor  INNER JOIN mailing_campanas ON mc_id = cc_campana 
  INNER JOIN mailing_campanasd ON md_id = cc_campanad WHERE cc_fconversion = "'.$fecha.'" AND cc_estatus = "A" AND cc_fuente = "M" LIMIT 0,20';
  $result = setq($sql); 
  $respuesta = 
  '
  <div class="table-responsive" bis_skin_checked="1">
    <table class="table table-hover mb-0">
      <thead>
        <tr>
            <th>Distribuidor</th>
            <th>Fecha Conversion</th>
            <th>Fuente</th>
            <th>Campana</th>
            <th>Mail</th>
        </tr>
      </thead>
      <tbody>';
  
  while($row = $result->fetch_array()){
  if($row['cc_fuente'] == "M") $fuente = 'Mailing';
  else { }
  $respuesta .= '
        <tr>
            <th scope="row">'.$row['rd_nmb'].'</th>
            <td>'.fecha_formato($row['cc_fconversion'],false,false).'</td>
            <td>'.$fuente.'</td>
            <td>'.$row['mc_nmb'].'</td>
            <td>'.$row['md_asunto'].'</td>
        </tr>';
  
      }
  
  $respuesta .= '
      </tbody>
    </table>
  </div>
  
  ';

}




echo $respuesta;




?>