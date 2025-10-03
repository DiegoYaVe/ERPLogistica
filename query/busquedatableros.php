<?php
  ini_set('display_errors', 0);
session_start();
include_once('../funciones.php');

$palabra = $_POST['palabra'];
$html= '';

$sql3 = 'SELECT a_id id, a_nmb nmb, a_modelo modelo, "A" tipo FROM articulos WHERE (a_nmb LIKE "%'.$palabra.'%" OR a_cb LIKE "%'.$palabra.'%") AND (SELECT COUNT(*) FROM articulos_variantes WHERE av_articulo = a_id) < 1
UNION SELECT a_id, CONCAT(a_nmb," ",av_nmb), av_modelo, "V" FROM articulos_variantes INNER JOIN articulos ON a_id = av_articulo WHERE av_nmb LIKE "%'.$palabra.'%" OR av_modelo LIKE "%'.$palabra.'%" OR a_nmb LIKE "%'.$palabra.'%" OR av_cb LIKE "%'.$palabra.'%"';
$result3 = setq($sql3);
if($result3 -> num_rows > 0){
  $html .= '
  <table class="table mb-0" style="font-size: x-small;">
    <thead class="bg-primary text-white">
      <tr ><th colspan="5" class="text-white text-center" style="background: teal">Artículos</th></tr>
      <tr>
        <th class="">Nombre</th>
        <th class="">Precio</th>
        <th class="">Existencia</th>
      </tr>
    </thead>
    <tbody>
  ';
  while($row = $result3 -> fetch_array()){
    if($row['tipo'] == "A"){
      $precio = busca($row['id'], 'articulos_precios', 'ap_activo = "1" AND ap_modelo IS NULL AND ap_articulo', 'ap_precio');
      $existencia = existenciaModelo($row['id'], "1", "");
    } else {
      $precio = busca($row['id'], 'articulos_precios', 'ap_activo = "1" AND ap_modelo = "'.$row['modelo'].'" AND ap_articulo', 'ap_precio');
      $existencia = existenciaModelo($row['id'], "1", $row['modelo']);
    }
    $html .= '
      <tr>
        <td>'.$row['nmb'].'</td>
        <td>'.$precio.'</td>
        <td>'.$existencia.'</td>
      </tr>
    ';
  }
  $html .= '
    </tbody>
  </table>
  ';
}
$sql = 'SELECT "TABLERO" tipo, ct_id id, ct_nmb nmb, DATE(ct_fini) fecha, ct_estatus estatus FROM crm_tableros INNER JOIN crm_clientes ON c_id = ct_cliente 
WHERE ct_nmb LIKE "%'.$palabra.'%" OR c_nmb LIKE "%'.$palabra.'%" OR c_telefono1 LIKE "%'.$palabra.'%" OR c_telefono2 LIKE "%'.$palabra.'%"
UNION SELECT "REMISIÓN" tipo, r_id id, r_nmb nmb, DATE(r_falta) fecha, r_estatus estatus FROM remisiones INNER JOIN crm_clientes ON c_id = r_cliente 
WHERE r_nmb LIKE "%'.$palabra.'%" OR c_nmb LIKE "%'.$palabra.'%" OR r_folio LIKE "%'.$palabra.'%" OR r_nmb LIKE "%'.$palabra.'%" OR c_telefono1 LIKE "%'.$palabra.'%" OR c_telefono2 LIKE "%'.$palabra.'%"
UNION SELECT "COTIZACIÓN" tipo, cc_id id, cc_nmb nmb, DATE(cc_fini) fecha, cc_estatus estatus FROM crm_tableros INNER JOIN crm_clientes ON c_id = ct_cliente INNER JOIN crm_cotizaciones ON cc_tablero = ct_id
WHERE cc_nmb LIKE "%'.$palabra.'%" OR c_nmb LIKE "%'.$palabra.'%" OR cc_folio LIKE "%'.$palabra.'%" OR cc_nmb LIKE "%'.$palabra.'%" OR c_telefono1 LIKE "%'.$palabra.'%" OR c_telefono2 LIKE "%'.$palabra.'%" ORDER BY fecha DESC LIMIT 0,10
';

$result = setq($sql);
if($result -> num_rows > 0){
  $html .= '
  <table class="table mb-0" style="font-size: x-small;">
    <thead class="bg-primary text-white">
      <tr ><th colspan="5" class="bg-info text-center">Negociaciones</th></tr>
      <tr>
        <th class="">Tipo</th>
        <th class="">Nombre</th>
        <th class="">Fecha</th>
        <th class="">Estatus</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
  ';
  while($row = $result->fetch_array()){
    if($row['tipo'] == "TABLERO"){
      $countcotN = busca($row['id'], 'crm_cotizaciones', 'cc_estatus IN ("N", "D", "R")  AND cc_tablero' ,'COUNT(*)');
      $countcotL = busca($row['id'], 'crm_cotizaciones', 'cc_estatus = "L"  AND cc_tablero' ,'COUNT(*)');
      $countcotA = busca($row['id'], 'crm_cotizaciones', 'cc_estatus = "A"  AND cc_tablero' ,'COUNT(*)');
      $countcotP = busca($row['id'], 'crm_cotizaciones', 'cc_estatus = "P"  AND cc_tablero' ,'COUNT(*)');
      $countremN = busca($row['id'], 'remisiones', 'r_estatus = "N" AND r_tablero', 'COUNT(*)');
      $countremdp = busca($row['id'], 'remisiones', 'r_estatus IN ("D", "P") AND r_tablero', 'COUNT(*)');   
      $countremV = busca($row['id'], 'remisiones INNER JOIN cxcobrar ON cx_referencia = r_id', 'cx_estatus IN ("N", "A") AND r_estatus = "A" AND r_tablero' ,'COUNT(*)');
      $countremF = busca($row['id'], 'remisiones INNER JOIN cxcobrar ON cx_referencia = r_id', 'cx_estatus = "F" AND r_estatus IN ("F") AND r_tablero' ,'COUNT(*)');
      $countenvP = busca($row['id'], 'remisiones INNER JOIN guias_articulos ON ga_cotizacion = r_id', 'ga_estatus IN ("P", "A") AND r_tablero' ,'COUNT(*)');
      $countenvF = busca($row['id'], 'remisiones INNER JOIN guias_articulos ON ga_cotizacion = r_id', 'ga_estatus = "F" AND r_tablero' ,'COUNT(*)');
      $countartF = busca($row['id'], 'remisiones INNER JOIN remisionesc ON rc_remision = r_id', 'rc_estatus = "F" AND r_tablero' ,'COUNT(*)');
      $countcotC = busca($row['id'], 'crm_cotizaciones', 'cc_estatus = "C" AND cc_tablero' ,'COUNT(*)');
      $countremC = busca($row['id'], 'remisiones', 'r_estatus = "C" AND r_tablero' ,'COUNT(*)');
      if($countcotN > 0){
        $row['estatus'] = "A";
      } else if($countcotL >0){
        $row['estatus'] = "B";
      }else if($countcotP>0){
        $row['estatus'] = "C";
      }else if($countcotA>0){
        $row['estatus'] = "D";
      }else if($countremN>0){
        $row['estatus'] = "F";
      }else if($countremdp>0){
        $row['estatus'] = "G"; 
      }else if($countremV>0){
        $row['estatus'] = "H";
      }else if($countremF>0){
        $row['estatus'] = "I";
      }else if($countenvP>0){
        $row['estatus'] = "J";
      }else if($countenvF>0 || $countartF>0){
        $row['estatus'] = "K";
      } else if($row['id'] == "X"){
        $row['estatus'] = "L";
      } else if($countcotC>0){
        $row['estatus'] = "M";
      } else if($countremC>0){
        $row['estatus'] = "N";
      }
      $estatus = array("A"=>"COTIZACIÓN EN CONSTRUCCIÓN","B"=>"ESPERANDO COSTO DE LOGÍSTICA","C"=>"COTIZACIÓN PENDIENTE DE ENVÍO","D"=>"COTIZACIÓN ENVIADA","F"=>"ESPERANDO APLICACIÓN DE REMISIÓN","G"=>"ESPERANDO PRIMER ABONO","H"=>"VENDIDO-ABONADO","I"=>"ARTÍCULOS PENDIENTES DE ENVÍO","J"=>"PREPARADO PARA ENVÍO","K"=>"ARTÍCULOS ENVIADOS","L"=>"TABLERO PERDIDO", "M"=>"COTIZACIONES CANCELADAS", "N"=>"REMISIONES CANCELADAS");
      $estatusbg = array("A"=>'class="alert text-white" style="background:#F4D03F"',"B"=>'class="alert text-white" style="background:#F5B041"',"C"=>'class="alert text-white" style="background:#E67E22"',"D"=>'class="alert text-white" style="background:#58D68D',"F"=>'class="alert text-white" style="background:#C0392B"',"G"=>'class="alert text-white" style="background:#8B57E5"',"H"=>'class="alert" style="background:#45B39D"',"I"=>'class="alert" style="background:#AAB7B8"',"J"=>'class="alert text-white" style="background:#AAB7B8"',"K"=>'class="alert text-white" style="background:#8E44AD"',"L"=>'class="alert text-white" style="background:#C0392B"',"M"=>'class="alert text-white" style="background:#000000"',"N"=>'class="alert text-white" style="background:#000000"');
      $modulo = 'tableros';
      $accion = 'show';
    }elseif($row['tipo'] == "REMISIÓN"){
      $estatus = array("A" => "Vendida","P" => "Por aprobar en caja","N" => "En Captura","C" => "Cancelado", "F" => "Liquidada" , "FE" => "Liquidada y enviada");
      $estatusbg = array("A" => 'class="alert" style="background: #B0FFE7"',"P" => 'class="alert" style="background: #B7DAFF"',"N"=>'class="alert" style="background: #FFEAAB"',"C" => 'class="alert" style="background: #FFCBD0"', "F" => 'class="alert text-white" style="background: #20c997"', "FE" => 'class="alert text-white" style="background: #FFCBD0"');
      $modulo = 'remisiones';
      $accion = 'show';
    }elseif($row['tipo'] == "COTIZACIÓN"){
      $estatus = array("N"=>"En construcción","V"=>"Enviada","L"=>"En logística","A"=>"Aplicada","C"=>"Cancelada","P"=>"Pendiente de envío","R"=>"Replicada","D"=>"Definición de envíos");
      $estatusbg = array("N"=>'class="alert no-border" style="background: #ffc107"',"V"=>'class="alert no-border text-white" style="background: #007bff"',"L"=>'class="alert no-border text-white" style="background: #6c757d"',"A"=>'class="alert no-border text-white" style="background: #20c997"',"C"=>'class="alert no-border text-white" style="background: #dc3545"',"P"=>'class="alert no-border" style="background: #fd7e14"',"R"=>'class="alert no-border" style="background: #fd7e14"',"D"=>'class="alert no-border text-white" style="background: #17a2b8"');
      $modulo = 'cotizaciones';
      $accion = 'show';
    }
    

    $html .= '
      <tr>
        <td>'.$row['tipo'].'</td>
        <td>'.$row['nmb'].'</td>
        <td>'.$row['fecha'].'</td>
        <td '.$estatusbg[$row['estatus']].'>'.$estatus[$row['estatus']].'</td>
        <td><a class="btn btn-info btn-sm" href="?modulo='.$modulo.'&accion='.$accion.'&id='.$row['id'].'"><i class="fas fa-eye"></i></a></td>
      </tr>
    '; 
  }
  $html .= '
    </tbody>
  </table>
  ';
}
$sql2 = 'SELECT * FROM crm_leads WHERE cl_telefono LIKE "%'.$palabra.'%"';
$result2 = setq($sql2);
if($result2 -> num_rows > 0){
  $html .= '
  <table class="table mb-0" style="font-size: x-small;">
    <thead class="bg-primary text-white">
      <tr ><th colspan="5" class="bg-success text-center">Leads</th></tr>
      <tr>
        <th class="">Tipo</th>
        <th class="">Nombre</th>
        <th class="">Telefono</th>
        <th class="">Asignado a</th>
      </tr>
    </thead>
    <tbody>
  ';
  while($row2 = $result2 -> fetch_array()){
    $sqlcl = 'SELECT * FROM crm_clientes WHERE c_telefono1 LIKE "%'.$row2['cl_telefono'].'%" OR c_telefono2 LIKE "%'.$row2['cl_telefono'].'%"';
    $resultcl = setq($sqlcl);
    $rowcl = $resultcl->fetch_array();
    $telefono = $row2['cl_telefono'];
    $nmb = $row2['cl_nmb'];
    $asignado = $row2['cl_vendedor'];
    $id = 0;
    $texto = 'LEAD';
    $link = '?modulo=leadscapturados&accion=index&telefono='.$telefono;
    if($resultcl -> num_rows > 0){
      $nmb = $rowcl['c_nmb'].' '.$rowcl['c_apellidos'];
      $texto = 'CLIENTE';
      $link = '?modulo=clientes&accion=edit&id='.$rowcl['c_id'];
      $asignado = $rowcl['c_uregistro'];
    }
    $html .= '
      <tr>
        <td>'.$texto.'</td>
        <td>'.$nmb.'</td>
        <td>'.$telefono.'</td>
        <td>'.$asignado.'</td>
      </tr>
    '; //<td><a class="btn btn-info btn-sm" href="'.$link.'"><i class="fas fa-eye"></i></a></td>
  }
  $html .= '
    </tbody>
  </table>
  ';
} 



if($result2 -> num_rows <= 0 && $result -> num_rows <= 0 && $result3 -> num_rows <= 0)  
$html = '<div class="alert alert-danger col-12"> No se encontraron resultados </div>';



echo $html;




?>