<?php
/* ini_set('display_errors', 1); */
session_start();
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 1 Jul 2000 05:00:00 GMT"); // Fecha en el pasado
include_once('../funciones.php');
$usuario = $_SESSION['uid'];
$grupo = busca($usuario,'usuarios','u_id','u_grupo');

if(($grupo != "ADMIN" && $grupo != "GERENCIA") || isset($_GET['vendedor']))
  $vended = busca($usuario,'usuarios','u_id','CONCAT(u_nmb," ",u_apellidos)');
else $vended = "Todos los vendedores";

$vendedor = $vended;

echo '<div class="row">
        <div class="col-md-m12">
          <div class="alert alert-primary">Seguimiento de tableros abiertos de '.$vended.'</div>';
echo '<div class="table-responsive">';

  $sql = 'SELECT * FROM crm_tableros INNER JOIN crm_cotizaciones ON ct_id = cc_tablero
          WHERE cc_estatus != "C" ';
  if(($grupo != "ADMIN" && $grupo != "GERENCIA") || isset($_GET['vendedor'])){
    if(isset($_GET['vendedor'])) $usuario = $_GET['vendedor'];
    $sql.=' AND ct_agente = "'.$usuario.'" ';
  }
  if(isset($_GET['mes'])){
    $sql.= ' AND MONTH(ct_fini) = "'.$_GET['mes'].'" AND YEAR(ct_fini) = "'.date('Y').'"';
  }
  else{
    $sql.=' AND ct_estatus IN ("N","P","G","V","A") ';
  }
  $sql.=' ORDER BY ct_fini DESC,ct_hini DESC';
  $result = setq($sql);

  $tipoenvio = array("D"=>"Domicilio","C"=>"Recoger","P"=>"Por definir","O"=>"Ocurre");
  if($result->num_rows > 0){
    echo '<table class="table table-striped table-hover">
            <thead class="bg-warning">
              <tr>
                <th colspan="2">Tablero</th>
                <th>Cotización</th>
                <th>Remisión</th>
                <th>Cliente</th>
                <th>CP</th>
                <th>Estado</th>
                <th>Tipo envio</th>
                <th>Guias</th>
                <th>Articulos</th>
                <th>Venta</th>
                <th>Envio</th>
                <th>Pendiente</th>
                <th>Vendedor</th>
              </tr>
            </thead>';
    $numr = 0;
    $resulttab = setq($sql);
    $impf = 0;
    $numart = 0;
    $saldof = 0;
    $sumaenvio = 0;
    $liquidadof = 0;
    while($row = $resulttab->fetch_array()){
      $numr++;
      if($numr%2) $backtr = 'style="background:#8A8A8A;"'; else $backtr = "";

      if(!$row['cc_remision']) $remis = "-";
      else $remis = busca($row['cc_remision'],'remisiones','r_id','r_folio');

      if(!$row['cc_direnvio'] || $row['cc_direnvio'] == 0){
        $cp = '-';
        $estado = "-";
      }
      else{
        $cp = busca($row['cc_direnvio'],'crm_direcciones','cd_id','cd_cp');
        $estado = busca(busca($row['cc_envio'],'crm_direcciones','cd_id','cd_estado'),'estado','e_id','e_nmb');
      }

      $sqlte = 'SELECT COUNT(*) canti,ccc_tipoenvio FROM crm_cotizacionesc
                INNER JOIN articulos ON a_id = ccc_articulo
                INNER JOIN categorias ON cat_id = a_categoria
                WHERE ccc_cotizacion = "'.$row['cc_id'].'" AND cat_inflable = "1"
                GROUP BY ccc_tipoenvio';
      $resulte = setq($sqlte);
      $tipoen = "";
      $numen = 0;
      $artic = 0;
      while($rowe = $resulte->fetch_array()){
        if($numen > 0) $tipoen.='<br>';

        $tipoen.=$rowe['canti'].'-'.$tipoenvio[$rowe['ccc_tipoenvio']];
        $numen++;
        $artic+=$rowe['canti'];
      }

      $guias = busca($row['cc_id'],'guias_articulos','ga_cotizacion','COUNT(*)');
      $numart+=$artic;

      echo '<tr '.$backtr.'>
              <td><a target="_BLANK" href="?modulo=tableros&accion=show&id='.$row['ct_id'].'">
                    <button class="btn btn-sm btn-info"><i class="fa fa-eye"></i></button>
                  </a>
              </td>
              <td>'.$row['ct_nmb'].'</td>
              <td>'.$row['cc_folio'].'</td>
              <td>'.$remis.'</td>
              <td>'.busca($row['ct_cliente'],'crm_clientes','c_id','c_alias').'</td>
              <td>'.$cp.'</td>
              <td>'.$estado.'</td>
              <td>'.$tipoen.'</td>
              <td class="number-align">'.$guias.'</td>
              <td class="number-align">'.number_format($artic,0).'</td>';

      if(!$row['cc_remision']) echo '<td colspan="3">Sin cobros</td>';
      else{
        $sqlcx = 'SELECT SUM(cx_importe),SUM(cx_abonado) FROM cxcobrar
                  WHERE cx_tipo = "R" AND cx_referencia = "'.$row['cc_remision'].'"
                  AND cx_estatus != "C" AND cx_abonado > 0';
        $resultcx = setq($sqlcx);
        list($cximp,$cxab) = $resultcx->fetch_array();

        $envios = busca($row['cc_remision'],'remisiones','r_id','r_precioenvio');
        if(!$envios) $envios = 0;
        $sumaenvio+=$envios;
        $cximp-=$envios;

        $saldo = $cximp-$cxab+$envios;
        if($saldo < 0) $saldo = 0;

        echo '<td class="number-align">$'.number_format($cximp,2).'</td>
              <td class="number-align">$'.number_format($envios,2).'</td>
              <td class="number-align">$'.number_format($saldo,2).'</td>';

        $impf+=$cximp;
        $saldof+=$saldo;
        $liquidadof+=$cxab;
      }

      echo '<td>'.busca($row['ct_agente'],'usuarios','u_id','CONCAT(u_nmb)').'</td>
            </tr>';
    }

    echo '<tr>
            <td colspan="8"></td>
            <td colspan="2" class="number-align">'.$numart.'</td>
            <td class="number-align">$'.number_format($impf,2).'</td>
            <td class="number-align">$'.number_format($sumaenvio,2).'</td>
            <td class="number-align">$'.number_format($saldof,2).'</td>
            <td class="number-align">$'.number_format($liquidadof,2).'</td>
          </tr>';
    echo '</table>';
  }
  else
    echo '<div class="alert alert-danger">Lo siento! al parecer no cuentas con tableros abiertos</div>';

echo '</div></div></div>';
?>