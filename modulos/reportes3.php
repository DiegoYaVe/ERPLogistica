<?php
//ini_set('display_errors','1');
$GLOBALS['menu'] = "Reportes";
class Reportes{
  var $model;
  var $view;

function Modulo(){ // constructor
  $this->model = new modelreportes($obj); // crea modelo
}
function index() {
  $this->view = new viewreportes($this->model);
  $this->view->showreportes();
}
function compras(){
  $this->view = new viewreportes($this->model);
  $this->view->compras();
  //include('reportes/comprasproveedor.php');
}

function existencias(){
  $this->view = new viewreportes($this->model);
  $this->view->existencias();
  //include('reportes/comprasproveedor.php');
}

function ventasd(){
  if(!isset($_POST['desde'])) $_POST['desde'] = date('Y-m-d');
  if(!isset($_POST['hasta'])) $_POST['hasta'] = date('Y-m-d');
  if(!isset($_POST['estatus'])) $_POST['estatus'] = "N";
  if(!isset($totaldes)) $totaldes = NULL;

  $this->view = new viewreportes($this->model);
  $this->view->ventasd($_POST['desde'],$_POST['hasta'],$_POST['estatus']);
  //include('reportes/comprasproveedor.php');
}

function facturacion(){
  if(!isset($_POST['fini'])) $_POST['fini'] = date('Y-m-01');
  if(!isset($_POST['ffin'])) $_POST['ffin'] = date('Y-m-d');
  if(!isset($_POST['cliente'])) $_POST['cliente'] = NULL;

  $this->view = new viewreportes($this->model);
  $this->view->facturacion($_POST['fini'],$_POST['ffin'],$_POST['cliente']);
  //include('reportes/comprasproveedor.php');
}
function conversiones(){
  $this->view = new viewreportes($this->model);
  $this->view->conversiones();
}


}
class modelreportes{

}

class viewreportes{
  var $model;

function viewreportes($model) {
  $this->model = $model;
}

function showreportes(){
  echo '<center><table  class="lista"  width="100%"><thead>
  <tr><th colspan="4"><b>Reportes</b></th></tr>
  <tr><th><b>Inventario</b></th>
  <th><b>Otros</b></th>
  <th><b>Ventas</b></th></tr></thead><tbody>
  <tr>
    <td><a href="?modulo=reportes&accion=compras">Compras</a></td>
    <td><a href="?modulo=reportes&accion=movdiario">Movimientos diarios por artículo</a></td>
    <td><a href="?modulo=reportes&accion=ventasd">Detalle de ventas</a></td>
  </tr>
  <tr>
    <td><a href="?modulo=reportes&accion=existencias">Existencias</a></td>
    <td><a href="?modulo=reportes&accion=facturacion">Facturación</a></td>
    <td><a href="?modulo=reportes&accion=creditos">Ventas a Crédito</a></td>
  </tr>
  <tr>
    <td><a href="?modulo=reportes&accion=combos">Combos</a></td>
    <td><a href="?modulo=reportes&accion=rgastos">Gastos</a></td>
    <td><a href="?modulo=reportes&accion=ventaspt">Ventas por ticket</a></td>
  </tr>
  <tr>
    <td><a href="?modulo=reportes&accion=ventasprodsat">Ventas por producto SAT</a></td>
    <td><a href="?modulo=reportes&accion=saldoscliente">Saldos de cliente</a></td>
    <td><a href="?modulo=reportes&accion=ventastotales">Ventas por Articulo</a></td>
  </tr>
  <td><a href="?modulo=reportes&accion=conversiones">Conversiones Por Mailing</a></td>
  ';

  echo '</tbody></table>';
}


function compras(){
  if (isset($_POST['fini']))
      $fini = $_POST['fini'];
  else
      $fini = date('Y-m-') . '01';

  if (isset($_POST['ffin']))
      $ffin = $_POST['ffin'];
  else
      $ffin = date('Y-m-d');
   $totalGen=0;
   $totalGenn=0;
   $totaldesc=0;

  echo '<form name="fechas" method="post"><center><table width="70%" border="0">
  <thead><tr>
  <th><b>Fecha inicial</b> </th>
  <td><input type="date" name="fini" value="'.$fini.'" /></td>
  <td>&nbsp;</td>
  <th><b>Fecha final</b> </th>
  <td><input type="date" name="ffin" value="'.$ffin.'" /></td>
  <td>&nbsp;</td>
  <td><b><input name="enviar" type="submit" value="" id="actualizar" class="botont"/></b></td>
  <td><a href="reportes/xlscomprasproveedor.php?fi='.$fini.'&ff='.$ffin.'" target=""_SELF"><input type="button" value="" id="descargaexcel" class="botont"></a></td>
  </tr></thead>
  </table></form>';

  echo '<center><table border="0" class="lista" width="100%"><thead>';

          echo '
          <tr><th colspan="8">Compras</th></tr>
          <tr>
          <th >Recepcion</th>
          <th>Fecha</th>
          <th >Articulo</th>
          <th>Costo</th>
          <th>Cant.</th>
          <th>Total</th>
          <th>Proveedor</th>
          </tr></thead><tbody>';

  $total= 0;

  $admin="";
  /*if(dbexists('sqlserv',true)){  //ethan
  $admin=' r_documento!="R" AND';
  } */

     $sql = 'SELECT * FROM recepciones INNER JOIN recepcionesd ON r_id=rd_recepcion
          INNER JOIN articulos ON rd_articulo=a_id WHERE '.$admin.'  DATE(r_fechagen) BETWEEN "'.$fini.'" AND "'.$ffin.'"
          ORDER BY r_id DESC';
      $result = mysql_query($sql) or die(mysql_error());

  if(mysql_num_rows($result)>0){
            $total=0;
            $totaln=0;
    $np=mysql_num_rows($result);
   while ($row = mysql_fetch_array($result)) {
    if( $row['r_estatus']=="A"){
         echo '
         <tr>';
         echo'<th ><a href=?modulo=recepciones&accion=showrecepcion&id='.$row['r_id'].'  target="_blank"> '.$row['r_id'].'</a></th>';
         echo'<td>'.$row['r_fechagen'].'</td>
         <td><a href=?modulo=articulos&accion=show&id='.$row['a_id'].'  target="_blank">'.busca($row['a_id'],'articulos','a_id','a_nmb').'</a></td>
         <td><p align="right">$'.number_format($row['rd_costo']/$row['rd_cantidad'],2).'</p></td>
         <td>'.number_format($row['rd_cantidad'],2).'</td>

         <td><p align="right">$'.number_format( ( $row['rd_costo']),2).'</p></td>
         <td>'.busca(busca($row['a_id'],'articulos','a_id','a_proveedor'),'proveedores','p_id','p_nmb').'</td>
         </tr>';
     $total+=$row['rd_costo'];
     $totaln+=$row['rd_cantidad'];
     $totaldesc+=$row['rd_descuento'];
     }
     $np--;
      }

   }
   $totalGen+=$total;
   $totalGenn+=$totaln;

       echo '
      <tr>
      <td colspan="4"><p align="right"><b>TOTALES: </td>
      <td><p align="center"><b>'.number_format( $totalGenn,2).'</p></td>
      <td><p align="right"><b>$'.number_format( $totaldesc,2).'</p></td>
      <td><p align="right"><b>$'.number_format( $totalGen-$totaldesc,2).'</p></td>
      </tr>
      </tfoot>';
    echo '</table>';
}

function existencias(){
  if (!isset($_POST['marca'])) $_POST['marca'] = NULL;
  if (!isset($_POST['categoria'])) $_POST['categoria'] = NULL;
  if (!isset($_POST['articulo'])) $_POST['articulo'] = NULL;
  if (!isset($_POST['almacen'])) $_POST['almacen'] = NULL;

    //Sección: E1 Encabezado - Botones de acción
    echo '<div class="page-title-actions row">';

    //Sección: E2 Encabezado - Filtros
    ?>
    <div class="col-2 col-md-1 ml-1">
      <button type="button" class="btn btn-info"  data-toggle="collapse" data-target="#filter-panel">
        <span class="glyphicon glyphicon-cog"></span><i class="fa fa-search5"></i> Filtrar
      </button>
    </div>
    <div id="filter-panel" class="col-md-10 collapse filter-panel">
      <div class="panel panel-default">
        <div class="panel-body">
          <form class="form-inline" role="form" method="post" >
            <div class="mb-5 mr-2">
              <input type="text" class="form-control" id="pref-search" name="nmb" value="<?php echo $_POST['articulo'] ?>" placeholder="Buscar por nombre">
            </div><!-- form group [search] -->
            <div class="mb-5 mr-2">
              <select id="pref-orderby" class="form-control" name="categoria" placeholder="Categoria">
                <option value="">TODAS LAS CATEGORIAS</option>
                <?php
                  $sql = 'SELECT * FROM categorias WHERE cat_estatus = "A" ORDER BY cat_nmb';
                  $result = setq($sql);
                  while($row = $result->fetch_array()){
                    if($_POST['categoria'] == $row['cat_id']) $sel = "selected"; else $sel = "";
                ?>
                    <option value="<?php echo $row['cat_id'] ?>" <?php echo $sel; ?>><?php echo $row['cat_nmb'] ?></option>
                <?php
                  }
                ?>
              </select>
            </div> <!-- form group [order by] -->
            <div class="mb-5 mr-2">
              <select id="pref-orderby" class="form-control" name="grupo" placeholder="Grupo">
                <option value="">TODAS LAS MARCAS</option>
                <?php
                  $sql = 'SELECT * FROM marcas WHERE m_estatus = "A" ORDER BY m_nmb';
                  $result = setq($sql);
                  while($row = $result->fetch_array()){
                    if($_POST['marca'] == $row['m_id']) $sel = "selected"; else $sel = "";
                ?>
                    <option value="<?php echo $row['m_id'] ?>" <?php echo $sel; ?>><?php echo $row['m_nmb'] ?></option>
                <?php
                  }
                ?>
              </select>
            </div> <!-- form group [order by] -->
            <div class="mb-5 mr-2">
              <select id="pref-orderby" class="form-control" name="almacen" placeholder="almacen">
                <option value="">TODOS LAS ALMACENES</option>
                <?php
                  $sql = 'SELECT * FROM almacenes WHERE a_estatus = "A" ORDER BY a_nmb';
                  $result = setq($sql);
                  while($row = $result->fetch_array()){
                    if($_POST['almacen'] == $row['a_id']) $sel = "selected"; else $sel = "";
                ?>
                    <option value="<?php echo $row['a_id'] ?>" <?php echo $sel; ?>><?php echo $row['a_nmb'] ?></option>
                <?php
                  }
                ?>
              </select>
            </div> <!-- form group [order by] -->
            <div class="mb-5">
            <button type="submit" class="btn btn-info">
              <span class="glyphicon glyphicon-record"></span> <i class="fa fa-redo"></i> Actualizar
            </button>
            <a href="?modulo=reportes&accion=existencias"><button type="button" class="btn btn-warning">
              <span class="glyphicon glyphicon-record"></span> <i class="icon-android-cancel"></i> Limpiar
            </button></a>
            </div>
          </form>
        </div>
      </div>
    </div>

<?php
$sql2 = '';
if ($_POST['categoria']) {
    $sql2.='AND a_categoria = "' . $_POST['categoria'] . '" ';
}
if ($_POST['articulo']) {
    $sql2.='AND a_nmb LIKE "%' . $_POST['articulo'] . '%" OR a_modelo LIKE "%' . $_POST['articulo'] . '%" OR a_cb LIKE "%' . $_POST['articulo'] . '%" ';
}
if ($_POST['marca']) {
    $sql2.='AND a_marca = "' . $_POST['marca'] . '" ';
}

$admin="";

$sqlal = 'SELECT * FROM almacenes where a_estatus="A"';
if($_POST['almacen']) $sqlal.=' AND a_id = "'.$_POST['almacen'].'" ';
$resultal = setq($sqlal) or die($sqlal);

while ($rowal = $resultal->fetch_array()) {
  $sqlex = 'SELECT SUM(e_cantidad) existencia,a_nmb articulo,a_id id,a_sku sku,a_marca marca,a_categoria categoria,e_almacen,a_cb
            FROM existencias INNER JOIN articulos ON a_id = e_articulo
            WHERE a_estatus = "A" '.$admin.' AND e_almacen = "' . $rowal['a_id'] . '"
            ' . $sql2 . '
            GROUP BY a_id ORDER BY a_nmb';
  $resultex = setq($sqlex) or die($sqlex);

  $sqlcomp = 'SELECT SUM(e_cantidad) FROM existencias INNER JOIN articulos ON a_id = e_articulo
              WHERE a_estatus = "A" '.$admin.' AND  e_almacen = "' . $rowal['a_id'] . '"
              ' . $sql2 . '
              GROUP BY a_id ORDER BY a_nmb';
  $resultcomp = setq($sqlcomp) or die($sqlcomp);
  $exi = $resultcomp->fetch_array();
    echo '<div class="table-responsive">
        <table border="0" class="table table table-hover table-striped" >
        <thead class="thead-inverse">
        <tr>
          <th colspan="5">Existencias del almacen ' . $rowal['a_nmb'] . ' "' . $ven . '"</th>
          <th colspan="3">
            <a target="_BLANK" href="formats/pdfexistencias.php?marca='.$_POST['marca'].'&categoria='.$_POST['categoria'].'&almacen='.$_POST['almacen'].'">
              <button class="btn btn-danger"><i class="fa fa-file-pdf"></i> Descargar reporte</button>
            </a>
          </th>
        </tr>
        <tr>
        <th width="15%">ALMACEN</th>
        <th width="20%">ARTÍCULO</th>
        <th width="10%">CB</th>
        <th width="15%">CATEGORIA</th>
        <th width="10%">MARCA</th>
        <th width="10%">SKU</th>
        <th width="10%">COSTO</th>
        <th width="10%">EXISTENCIA</th>';

    echo '</tr></thead><tbody>';
  if ($exi >= 1) {
    if ($rowal['a_vendible'] == 1) $ven = 'vendible';
    else $ven = 'No vendible';


    while ($row = $resultex->fetch_array()) {
      $sql = 'SELECT ap_costo FROM articulos_precios WHERE ap_articulo = "'.$row['id'].'" AND ap_activo = "1"';
      $result = setq($sql) or die($sql);
      list($costo) = $result->fetch_array();

      echo '<tr>
              <td>'.busca($row['e_almacen'],'almacenes','a_id','a_nmb').'</td>
              <th style="text-align:right;">' . $row['articulo'] . '</th>
              <td>' . $row['a_cb'] . '</td>
              <td>' . busca($row['categoria'], 'categorias', 'cat_id', 'cat_nmb') . '</td>
              <td>' . busca($row['marca'], 'marcas', 'm_id', 'm_nmb') . '</td>
              <td>' . $row['sku'] . '</td>
              <td style="text-align:right;">$ ' . number_format($costo, 2) . '</td>
              <td>' . number_format($row['existencia'], 2) . '</td>';
        echo'</tr>';
        }
    }
}
  echo '<tbody></table></div></div>';

}

function ventasd($fini,$ffin,$estatus){

$sqlc = 'SELECT DISTINCT(t_cajero) cajero FROM tickets
        WHERE DATE(t_fcreo) BETWEEN "'.$_POST['desde'].'" AND "'.$_POST['hasta'].'" ';

  if(isset($_POST['cancelados'])){
    $sqlc.='  AND t_estatus IN ("A","C") ';
    $checkcan = "checked";
  }
  else {
    $sqlc.='  AND t_estatus = "A" ';
    $checkcan = "";
  }


  if(isset($_POST['remisiones'])){
    $checkrem = "checked";
    $postrem = '&remision=1';

  }
  else{
    $sqlc.='  AND t_TR = "T" ';
    $checkrem = "";
    $postrem = '';
  }

//die($sqlc);
$resultc = mysql_query($sqlc) or die(mysql_error());
$fila = 0;
$cajeros = '';

for($i=1;$i<=mysql_num_rows($resultc);$i++){
  if(isset($_POST['cajero'.$i])) {
   $cajeros.='"'.$_POST['cajero'.$i].'",';
  }
}

$cajeros = substr($cajeros,0,-1);
if(empty($cajeros)) $cajas = '';
else $cajas = 'AND t_cajero IN ('.$cajeros.')';

echo '<div class="blue"><center><table width="90%"  CELLSPACING="0" border="0">
<form action="'.$_SERVER["REQUEST_URI"].'" method="post">
<thead><tr>
<td>Mostrar Cancelados <input type="checkbox" name="cancelados" '.$checkcan.' onchange="submit();" ></td>
<td>Ver remisiones <input type="checkbox" name="remisiones" '.$checkrem.' onchange="submit();" ></td>
<th>Desde</th>
<td><input type="date" name="desde" value="'.$_POST['desde'].'"></td>
<td style="padding-left:100px;">&nbsp;</td>
<th>Hasta</th>
<td><input type="date" name="hasta" value="'.$_POST['hasta'].'" ></td>
<td><input name="enviar" type="submit" value="" id="actualizar" class="botont"/></td>
<td style="padding-left:100px;"></td>';        ?>
<td ><a href="reportes/xlsventasd.php?fi=<?php echo $_POST['desde'] ?>&ff=<?php echo $_POST['hasta'] ?>&c=<?php echo $cajeros.$postrem ?>" target=_SELF ><input type="button" value="" id="descargaexcel" class="botont"></a></td>
<?php echo'</tr></thead></table><table  width="90%"><thead>';

echo '<tr><th colspan="11">Detalles de Venta</th></tr><tr>';
echo '<tr><th colspan="11">Cajeros disponibles</th></tr></thead><tr>';
while($rowc = mysql_fetch_array($resultc)){
  $fila++;
  if($fila == 1 or $fila%5 == 0) echo '</tr><tr>';
  echo '<td  style="text-align:right;">'.$rowc['cajero'].'</td>';
  if(isset($_POST['cajero'.$fila]))
  echo '<td width="10px"><input type="checkbox" name="cajero'.$fila.'" value="'.$rowc['cajero'].'" checked onChange="submit();"></td>';
  else
  echo '<td width="10px"><input type="checkbox" name="cajero'.$fila.'" value="'.$rowc['cajero'].'" onChange="submit();"></td>';
}

echo '</tr></table></center><div>';

$sqlal = 'SELECT * FROM tickets WHERE t_fcreo
          BETWEEN "'.$_POST['desde'].' 00:00:01" AND "'.$_POST['hasta'].' 23:59:59"
           '.$cajas.' ';
if(isset($_POST['cancelados'])) $sqlal.=' AND t_estatus IN ("A","P","C") ';
else $sqlal.=' AND t_estatus IN ("A","P") ';

if(!isset($_POST['remisiones'])) $sqlal.='  AND t_TR = "T" ';
else $sqlal.='  AND t_TR = "R" ';

$sqlal.= ' AND (t_corteprev IS NULL OR t_corteprev = "")
          ORDER BY t_id';
$resultal = mysql_query($sqlal) or die($sqlal);


echo '<center><table width="100%" class="lista">
<thead>
<tr><th colspan=10>Tickets de Venta</th></tr>
<tr>
<th>Ticket</th>
<th>Fecha</th>
<th>Estatus</th>
<th>Cajero</th>
<th>Vendedor</th>
<th>Cliente</th>
<th>Efectivo</th>
<th>TDC</th>
<th>Transfer.</th>
<th>Deposito</th>
<th>Descuento</th>
<th>Total Ticket</th>
</tr></thead><tbody>';

$totalefectivo = 0;
$totalvales = 0;
$totaltdc = 0;
$totaldep= 0;

$total = 0;
$totalm = 0;
while($rowal = mysql_fetch_array($resultal)){

if($rowal['t_estatus'] == "A"){ $color = "#00CC33"; $nmb = "Cobrado"; }
elseif($rowal['t_estatus'] == "C"){ $color = "#CC0000"; $nmb = "Cancelado"; }
else { $color = "#CC9900"; $nmb = "Crédito"; }

$monto = busca($rowal['t_id'],'ticketsd','td_ticket','SUM(td_cantidad*td_precio)')-$rowal['t_descuento'];

echo '<tr>
<th>'.$rowal['t_folio'].'</th>
<td>'.$rowal['t_fcreo'].'</td>
<td style="background:'.$color.';">'.$nmb.'</td>
<td>'.$rowal['t_cajero'].'</td>
<td>'.$rowal['t_ucreo'].'</td>
<td>'.busca($rowal['t_cliente'],'clientes','c_id','c_nmb').'</td>
<td style="text-align:right;">$ '.number_format($rowal['t_efectivo'],2).'</td>
<td style="text-align:right;">$ '.number_format($rowal['t_tdc'],2).'</td>
<td style="text-align:right;">$ '.number_format($rowal['t_vales'],2).'</td>
<td style="text-align:right;">$ '.number_format($rowal['t_deposito'],2).'</td>
<td style="text-align:right;">$ '.number_format($rowal['t_descuento'],2).'</td>
<td style="text-align:right;">$ '.number_format($monto,2).'</td>

</tr>';
$totalefectivo+=$rowal['t_efectivo'];
$totalvales+=$rowal['t_vales'];
$totaltdc+=$rowal['t_tdc'];
$totaldep+=$rowal['t_deposito'];
$totaldes+=$rowal['t_descuento'];
$total=$totalefectivo+$totalvales+$totaltdc+$totaldep-$totaldes;
$totalm+=$monto;
}
echo '<tfoot>
        <tr>
          <td colspan="5" style="text-align:right;">Totales individuales</td>
          <td style="text-align:right; font-size:13px;">$ '.number_format($totalefectivo,2).'</td>
          <td style="text-align:right; font-size:13px;">$ '.number_format($totaltdc,2).'</td>
          <td style="text-align:right; font-size:13px;">$ '.number_format($totalvales,2).'</td>
          <td style="text-align:right; font-size:13px;">$ '.number_format($totaldep,2).'</td>
          <td style="text-align:right; font-size:13px;">$ '.number_format($totaldes,2).'</td>
          <td style="text-align:right; font-size:13px;">$ '.number_format($totalm,2).'</td>
        </tr></tfood>';

$sqlal = 'SELECT * FROM tickets WHERE t_fcreo
          BETWEEN "'.$_POST['desde'].' 00:00:01" AND "'.$_POST['hasta'].' 23:59:59"
          AND t_estatus IN ("A","P") '.$cajas.'
          AND (t_corteprev IS NOT NULL AND t_corteprev != "")
          ORDER BY t_id';
$resultal = mysql_query($sqlal) or die($sqlal);

echo '<center><table width="100%" class="lista">
<thead>
<tr><th colspan=10>Tickets de Ruta</th></tr>
<tr>
<th>Ticket</th>
<th>Fecha</th>
<th>Estatus</th>
<th>Cajero</th>
<th>Cliente</th>
<th>Efectivo</th>
<th>Vales</th>
<th>TDC</th>
<th>Descuento</th>
<th>Total</th>
</tr></thead><tbody>';

$totalefectivo = 0;
$totalvales = 0;
$totaltdc = 0;

$total = 0;
while($rowal = mysql_fetch_array($resultal)){
$monto = busca($rowal['t_id'],'ticketsd','td_ticket','SUM(td_cantidad*td_precio)')-$rowal['t_descuento'];

if($rowal['t_estatus'] == "A"){ $color = "#00CC33"; $nmb = "Cobrado"; }
else { $color = "#CC9900"; $nmb = "Crédito"; }

echo '<tr>
<th>'.$rowal['t_id'].'</th>
<td>'.$rowal['t_fcreo'].'</td>
<td style="background:'.$color.';">'.$nmb.'</td>
<td>'.$rowal['t_cajero'].'</td>
<td>'.busca($rowal['t_cliente'],'clientes','c_id','c_nmb').'</td>
<td style="text-align:right;">$ '.number_format($rowal['t_efectivo'],2).'</td>
<td style="text-align:right;">$ '.number_format($rowal['t_vales'],2).'</td>
<td style="text-align:right;">$ '.number_format($rowal['t_tdc'],2).'</td>
<td style="text-align:right;">$ '.number_format($rowal['t_descuento'],2).'</td>
<td style="text-align:right;">$ '.number_format($monto,2).'</td>
</tr>';
$totalefectivo+=$rowal['t_efectivo'];
$totalvales+=$rowal['t_vales'];
$totaltdc+=$rowal['t_tdc'];
$totaldes+=$rowal['t_descuento'];
$total=$totalefectivo+$totalvales+$totaltdc-$totaldes;
}
echo '<tfoot><tr><td colspan="5" style="text-align:right;">Totales individuales</td>
      <td style="text-align:right; font-size:11px;">$ '.number_format($totalefectivo,2).'</td><td style="text-align:right;">$ '.number_format($totalvales,2).'</td><td style="text-align:right;">$ '.number_format($totaltdc,2).'</td><td style="text-align:right;">$ '.number_format($totaldes,2).'</td><td style="text-align:right;">$ '.number_format($total,2).'</td></tr></tfood>';

  //imprimo abonos de clientes

  if(empty($cajeros)) $cajasabono = '';
  //else $cajasabono = 'AND pd_usuario IN ('.$cajeros.')';
  else $cajasabono = 'AND pd_banco IN (SELECT c_corte FROM cajas WHERE c_cajero IN ('.$cajeros.'))';

  $sqlal = 'SELECT * FROM cargos_cliente WHERE DATE(cc_fecha)
  BETWEEN "'.$_POST['desde'].'" AND "'.$_POST['hasta'].'"
  AND cc_estatus = "A" '.$cajasabono;

  $sqlal = 'SELECT * FROM pagos INNER JOIN pagosd ON p_id = pd_pago
  WHERE DATE(p_fecha) BETWEEN "'.$_POST['desde'].'" AND "'.$_POST['hasta'].'"
  AND p_estatus != "C"
    '.$cajasabono;

  $resultal = mysql_query($sqlal) or die($sqlal);

  echo '<center><table width="100%" class="lista">
  <thead>
  <tr><th colspan="8">Abonos de Cliente</th></tr>
  <tr>
  <th>Cliente</th>
  <th>Descripción</th>
  <th>Fecha</th>
  <th>Cajero</th>
  <th>Efectivo</th>
  <th>Transferencia</th>
  <th>Deposito</th>
  <th>TDC</th>
  </tr></thead><tbody>';

  $totalefectivoab = 0;
  $totalvalesab = 0;
  $totaltdcab = 0;
  $totaltrans = 0;
  $totaldepo = 0;

  while($rowal = mysql_fetch_array($resultal)){
  $v=0;
  $e=0;
  $t=0;
  $d=0;
  $tr=0;

  echo '<tr>
  <th>'.busca($rowal['p_cliente'],'clientes','c_id','c_nmb').'</th>
  <td>'.$rowal['p_descripcion'].'</td>
  <td>'.$rowal['p_fecha'].'</td>
  <td>'.busca($rowal['p_usuario'],'usuarios','u_id','u_nmb').'</td>';
  if($rowal['pd_formap']=="1"){$e=$rowal['pd_monto'];}
  if($rowal['pd_formap']=="6"){$tr=$rowal['pd_monto'];}
  if($rowal['pd_formap']=="11"){$t=$rowal['pd_monto'];}
  if($rowal['pd_formap']=="10"){$d=$rowal['pd_monto'];}
  if($rowal['cc_formapago']=="V"){$v=$rowal['cc_importe'];}

  echo'<td style="text-align:right;">$ '.number_format($e,2).'</td>
  <td style="text-align:right;">$ '.number_format($tr,2).'</td>
  <td style="text-align:right;">$ '.number_format($d,2).'</td>
  <td style="text-align:right;">$ '.number_format($t,2).'</td>

  </tr>';
  $totalefectivoab+=$e;
  $totalvalesab+=$v;
  $totaltdcab+=$t;
  $totaltrans+=$tr;
  $totaldepo+=$d;
  }
  $total+=$totalefectivoab+$totalvalesab+$totaltdcab+$totaltrans+$totaldepo;
  echo '<tfoot>
          <tr>
            <td colspan="4" style="text-align:right;">Totales individuales</td>
            <td style="text-align:right;">$ '.number_format($totalefectivoab,2).'</td>
            <td style="text-align:right;">$ '.number_format($totaltrans,2).'</td>
            <td style="text-align:right;">$ '.number_format($totaldepo,2).'</td>
            <td style="text-align:right;">$ '.number_format($totaltdcab,2).'</td>
          </tr>';
  echo '<tr><td colspan="3" style="text-align:right;">Importe total</td><td colspan="4"><center>$ '.number_format($total,2).'</center></td></tr></tfoot>';
  echo '</tbody></table>';


}

function facturacion($fini,$ffin,$cliente,$tipo){
  $checkf = "";
  $checkp = "";
  if($tipo == "F") $checkf = "checked";
  else $checkp = "checked";
    //Sección: E1 Encabezado - Botones de acción
  echo '<div class="page-title-actions row">';
    //Sección: E2 Encabezado - Filtros
    ?>
    <div class="col-4 col-md-1 ml-1">
      <button type="button" class="btn btn-info"  data-toggle="collapse" data-target="#filter-panel">
        <span class="glyphicon glyphicon-cog"></span><i class="fa fa-search5"></i> Filtrar
      </button>
    </div>
    <div id="filter-panel" class="col-md-9 collapse filter-panel">
      <div class="panel panel-default">
        <div class="panel-body">
          <form class="form-inline" role="form" method="post" action="?modulo=reportes&accion=facturacion">
            <div class="mb-5 mr-2">
              <span>Cliente</span>
              <input type="text" class="form-control" id="pref-search" name="cliente" value="<?php echo $cliente ?>" placeholder="Buscar por cliente">
            </div><!-- form group [search] -->
            <div class="mb-5 mr-2">
              <span>Fecha inicial</span>
              <input type="date" name="fini" class="form-control" value="<?php echo $fini ?>" />
            </div><!-- form group [search] -->
            <div class="mb-5 mr-2">
              <span>Fecha Final</span>
              <input type="date" name="ffin" class="form-control" value="<?php echo $ffin?>" />
            </div><!-- form group [search] -->
            <div class="mb-5">
            <button type="submit" class="btn btn-info">
              <span class="glyphicon glyphicon-record"></span> <i class="fa fa-redo"></i> Actualizar
            </button>
            <a href="?modulo=reportes&accion=facturacion"><button type="button" class="btn btn-warning">
              <span class="glyphicon glyphicon-record"></span> <i class="icon-android-cancel"></i> Limpiar
            </button></a>
            </div>
          </form>
        </div>
      </div>
    </div>

    </div>
  </div>
  <div class="main-card mb-3 card">
    <div class="card-body row">
    <?php
  $this->nmbmetodopago = array("PUE" => "PAGO EN UNA SOLA EXHIBICION", "PPD" => "PAGO EN PARCIALIDADES O DIFERIDO", "NO IDENTIFICADO" => "No Identificado");

  $sql = 'SELECT * FROM facturas
          WHERE f_ftimbro BETWEEN DATE("'.$fini.'") AND DATE("'.date('Y-m-d',strtotime($ffin." + 1 day")).'")
          AND f_estatus IN ("T","C")';
  if($estatus) $sql.=' AND f_estatus = "'.$estatus.'" ';
  if($cliente) $sql.=' AND f_cliente IN (SELECT c_id FROM crm_clientes WHERE
                      (c_nmb LIKE "%'.$cliente.'%" OR c_apellidos LIKE "%'.$cliente.'%" OR c_alias LIKE "%'.$cliente.'%"))
                      OR f_cliente IN (SELECT cf_cliente FROM crm_fiscales WHERE cf_razonsocial LIKE "%'.$cliente.'%") ';
  $sql.='ORDER BY f_ftimbro DESC';
  $resultmonto = setq($sql) or die($sql);

  echo '<div class="table">
          <table  class="mb-0 table table-hover table-striped">
            <thead class="bg-light-blue bg-darken-2 text-white">
            <tr><th width="5%"></th>
            <th width="5%">Factura</th>
            <th width="8%">RFC</th>
            <th width="15%">Cliente</th>
            <th width="13%">Fecha</th>
            <th width="10%">Estatus</th>
            <th width="10%">Método</th>
            <th width="10%">Forma</th>
            <th>Subtotal</th>
            <th>IVA</th>
            <th>Total</th>
          </tr></thead>';

  $totsubtotal = 0;
  $totiva = 0;
  $totretiva = 0;
  $totisr = 0;
  $sumtot = 0;
  while($row = $resultmonto->fetch_array()){
    echo '<tr>
            <th>
              <a href="?modulo=facturas&accion=showfactura&id='.$row['f_id'].'" target="_BLANK">
                <button class="btn btn-primary"><i class="icon-hand-pointer-o"></i></button>
              </a>
            </th>
            <td>'.$row['f_folio'].'</td>
            <td>'.busca($row['f_fiscales'],'crm_fiscales','cf_id','cf_rfc').'</td>
            <td>'.busca($row['f_fiscales'],'crm_fiscales','cf_id','cf_razonsocial').'</td>';

      $total = $row['f_subtotal']+$row['f_iva']-$row['f_isr']-$row['f_retiva'];

      if($row['f_estatus'] == "T"){ $estatus = "VIGENTE"; $stl = 'bg-success'; }
      elseif($row['f_estatus'] == "C"){ $estatus = "CANCELADA"; $stl = 'bg-danger'; }

      echo '<td>'.date('d-m-y',strtotime($row['f_ftimbro'])).'</td>';
      echo '<td class="'.$stl.'">'.$estatus.'</td>
            <td class="text-medium">'.$row['f_metodopago'].'</td>
            <td class="text-medium">'.busca($row['f_fpago'],'cfdi_fpago','cf_id','cf_descripcion').'</td>';
      echo '<td class="number-align">'.number_format($row['f_subtotal'],2).'</td>';
      echo '<td class="number-align">'.number_format($row['f_iva'],2).'</td>';
      echo '<td class="number-align">'.number_format($total,2).'</td>';

      $totsubtotal+=$row['f_subtotal'];
      $totiva+=$row['f_iva'];
      $totretiva+=$row['f_retiva'];
      $totisr+=$row['f_isr'];
      $sumtot+=$total;

    echo '</tr>';
     }
  echo '<tfoot><tr><td colspan="8" style="text-align:right;font-size: 15pt;">Total </td>
        <td style="font-size: 12pt;text-align:center;">$'.number_format($totsubtotal,2).'</td>
        <td style="font-size: 12pt;text-align:center;">$'.number_format($totiva,2).'</td>
        <td style="font-size: 12pt;text-align:center;">$'.number_format($sumtot,2).'</td>
        </tr>';
     echo '</tfoot>';
}

function creditos($fini,$ffin,$cliente){
  echo '<form name="fechas" method="post"><center><table width="100%" border="0">
  <thead><tr>
  <th><b>Fecha inicial</b> </th>
  <td><input type="date" name="fini" value="'.$fini.'" /></td>
  <td>&nbsp;</td>
  <th><b>Fecha final</b> </th>
  <td><input type="date" name="ffin" value="'.$ffin.'" /></td>
  <td>&nbsp;</td>
  <th><b>Cliente</b> </th>
  <td>'.menu_select_db('clientes','c_id','c_nmb',$cliente,'cliente','c_estatus = "1"').'</td>
  <td>&nbsp;</td>
  <td>&nbsp;</td>
  <td>
    <input name="enviar" style="width:80px;" type="submit" value="" id="actualizar" class="botont"/>
    <a href="xlscreditos.php?tipo='.$tipo.'&fini='.$fini.'&ffin='.$ffin.'&cliente='.$cliente.'">
      <input name="xls" style="width:80px;" type="button" value="" id="descargaexcel" class="botont"/>
    </a>
    </td>

  </tr></thead>
  </table></form>';

  echo '<table width="100%" class="listam" border="0">
        <thead>
          <tr>
            <th>Ticket</th>
            <th>Cliente</th>
            <th>Fecha</th>
            <th>Vendedor</th>
            <th>Monto</th>
            <th>Abonos</th>
            <th>Estatus</th>
          </tr>
        </thead>';


  $sql = 'SELECT t_id,t_cliente,t_fcreo,t_ucreo,t_estatus FROM tickets WHERE DATE(t_fcreo) BETWEEN "'.$fini.'" AND "'.$ffin.'" AND t_estatus IN ("R","P")
          UNION
          SELECT t_id,t_cliente,t_fcreo,t_ucreo,t_estatus FROM tickets as tick WHERE  DATE(t_fcreo) BETWEEN "'.$fini.'" AND "'.$ffin.'"
          AND t_id IN (SELECT DISTINCT(pt_ticket) FROM pagos_ticket INNER JOIN pagos ON p_id = pt_pago
          WHERE p_estatus = "A" AND pt_ticket = t_id)
          ORDER BY t_fcreo DESC';
  $result = mysql_query($sql) or die($sql);
  $totalvc = 0;
  $totalab = 0;
  while($row = mysql_fetch_array($result)){
    $montoc = busca($row['t_id'],'ticketsd','td_ticket','SUM(td_precio*td_cantidad)');
    $totalvc+=$montoc;
    if($row['t_estatus'] == "P"){ $cole = '#FFCC33'; $nmbe = "Pendiente"; }
    else{ $cole = '#00CC33'; $nmbe = "Pagado"; }
    echo '<tr>
            <th>'.$row['t_id'].'</th>
            <td>'.busca($row['t_cliente'],'clientes','c_id','c_nmb').'</th>
            <td>'.date('d-m-Y',strtotime($row['t_fcreo'])).'</th>
            <td>'.$row['t_ucreo'].'</th>
            <td>'.number_format($montoc,2).'</th>
            <td>&nbsp;</td>
            <td style="background:'.$cole.';">'.$nmbe.'</td>
          </tr>';
    $sqlp = 'SELECT * FROM pagos_ticket INNER JOIN pagos ON p_id = pt_pago
            WHERE pt_ticket = "'.$row['t_id'].'" AND p_estatus = "A"
            ORDER BY p_fecha DESC';
    $resultp = mysql_query($sqlp) or die($sqlp);
  //    $resultpn = mysql_query($sqlp) or die($sqlp);
    $sumabo = 0;
    $numabos = 0;
    while($rowp = mysql_fetch_array($resultp)){
      $sumabo+=$rowp['pt_monto'];
      $saldo=$montoc-$sumabo;
      $numabos++;

      echo '<tr>
              <td colspan="3">&nbsp;</td>
              <th>Abono</th>
              <td>'.date('d-m-Y',strtotime($rowp['p_fecha'])).'</td>
              <td>'.number_format($rowp['pt_monto'],2).'</td>
              <td>'.number_format($saldo,2).'</td>
            </tr>';
    }
    $totalab+=$sumabo;
    if($numabos >0)
    echo '<tr><th colspan="7">&nbsp;</th></tr>';

  }
  $saldof = $totalvc-$totalab;
  echo '<tr>
          <td colspan="3">&nbsp;</td>
          <td>Totales</td>
          <td>'.number_format($totalvc,2).'</td>
          <td>'.number_format($totalab,2).'</td>
          <td>'.number_format($saldof,2).'</td>
        </tr>';

}

function ventaspt($fini,$ffin,$estatus){
  if($estatus == "A"){ $esta = "selected"; $estc = "";}
  elseif($estatus == "C"){ $esta = ""; $estc = "selected";}

  echo '<form name="fechas" method="post"><center><table width="100%" border="0">
  <thead><tr>
  <th><b>Fecha inicial</b> </th>
  <td><input type="date" name="fini" value="'.$fini.'" /></td>
  <td>&nbsp;</td>
  <th><b>Fecha final</b> </th>
  <td><input type="date" name="ffin" value="'.$ffin.'" /></td>
  <th><b>Estatus</b> </th>
  <td><select name="estatus">
        <option value="T" '.$esta.'>Aplicados y créditos</option>
        <option value="A" '.$esta.'>Solo Aplicados</option>
        <option value="P" '.$esta.'>Solo Créditos</option>
        <option value="C" '.$estc.'>Cancelados</option>
      </select></td>
  <td>&nbsp;</td>
  <td>
    <input name="enviar" style="width:80px;" type="submit" value="" id="actualizar" class="botont"/>
    <a href="reportes/xlsventaspt.php?tipo='.$tipo.'&fini='.$fini.'&ffin='.$ffin.'&estatus='.$estatus.'">
      <input name="xls" style="width:80px;" type="button" value="" id="descargaexcel" class="botont"/>
    </a>
    </td>

  </tr></thead>
  </table></form>';

  echo '<table width="100%" class="listam" border="0">
        <thead>
          <tr>
            <th align="center">Ticket</th>
            <th align="center">Código de barras </th>
            <th align="center">Articulo </th>
            <th align="center">Cantidad</th>
            <th align="center">Precio</th>
            <th align="center">Total</th>
            <th align="center">Descuento</th>
          </tr>
        </thead><tbody>';


  $sql = 'SELECT t_id FROM tickets WHERE DATE(t_fcreo) BETWEEN "'.$fini.'" AND "'.$ffin.'"';
  if($estatus){
    if($estatus == "T") $sql.= ' AND t_estatus IN ("A","P")' ;
    else $sql.= ' AND t_estatus = "'.$estatus.'"' ;
  }
  $result=mysql_query($sql) or die($sql);
  $totc = 0;
  $totp = 0;
  while($rowt = mysql_fetch_array($result)){
    $sqltd = 'SELECT * FROM ticketsd WHERE td_ticket = "'.$rowt['t_id'].'" ORDER BY td_id DESC';
    $resultd = mysql_query($sqltd);

    while($row = mysql_fetch_array($resultd)){
      $precio = ($row['td_cantidad']*$row['td_precio']);
      $totc+=$row['td_cantidad'];
      $totp+=$precio;

      if($row['td_descuento']>0){$desc="Si";}else{$desc="No";}
      echo '<tr><th align=center><a target="_BLANK" href="?modulo=tickets&accion=showtickets&id='.$rowt['t_id'].'">'.$rowt['t_id'].'</a></th>';
      echo '<td align=center>'.$row['td_articulo'].'</a></td>';
      echo '<td align=center>'.busca($row['td_articulo'],'articulos','a_id','a_nmb').'</td>';
      echo '<td style="text-align:right;">'.number_format($row['td_cantidad'],0).'</td>';
      echo '<td style="text-align:right;">$'.number_format($row['td_precio'],2).'</td>';
      echo '<td style="text-align:right;">$'.number_format($precio,2).'</td>';
      echo '<td style="text-align:right;">'.$desc.' - $ '.$row['td_descuento'].'</td></tr>';
    }
  }
  echo '</tbody><tfoot><tr>
          <td colspan="2">&nbsp;</td>
          <td style="text-align:right;">Total</td>
          <td>'.number_format($totc,2).'</td>
          <td>&nbsp;</td>
          <td>'.number_format($totp,2).'</td>
        </tr></tfoot></table>';

}

function rgastos(){
  if(!isset($_POST['desde'])) $_POST['desde'] = date('Y-m-01');
  if(!isset($_POST['hasta'])) $_POST['hasta'] = date('Y-m-d');

  echo '<center><table class="lista" width="100%" border="0">
  <form method="post">
  <thead><tr>
  <th>Desde</th>
  <td><input type="date" name="desde" value='.$_POST['desde'].' ></td>
  <td style="padding-left:10px;">&nbsp;</td>
  <th>Hasta</th>
  <td><input type="date" name="hasta" value="'.$_POST['hasta'].'" ></td>
  <th>Concepto</th>
  <td>'.menu_select_db('conceptosg','c_id','c_nmb',$_POST['concepto'],'concepto','c_estatus = 1').'</td>
  <td style="padding-left:10px;"><input name="enviar" type="submit" value="" id="actualizar" class="botont"/></td>';
  echo '<td ><a href="xlsrgastos.php?fi='.$_POST['desde'].'&ff='.$_POST['hasta'].'&c='.$_POST['concepto'].'" target=_SELF ><input type="button" value="" id="descargaexcel" class="botont"></a></td>';

  echo '</tr></thead></form></table>';

  if($_POST['concepto']) $sqlg = ' AND g_concepto = "'.$_POST['concepto'].'" ';
  $sql = 'SELECT * FROM gastos
          WHERE DATE(g_fechagen) BETWEEN "'.$_POST['desde'].'" AND "'.$_POST['hasta'].'" '.$sqlg;
  $result = mysql_query($sql) or die($sql);
  $sum = 'SELECT SUM(g_monto) FROM gastos
          WHERE DATE(g_fechagen) BETWEEN "'.$_POST['desde'].'" AND "'.$_POST['hasta'].'" '.$sqlg;
  $sumresult = mysql_query($sum) or die($sum);
  list($gastos) = mysql_fetch_array($sumresult);


  echo '<center><table border="0" width="90%" class="lista"><thead>
  <tr><th colspan="6">Gastos</th>
  <tr><th>Genero</th><th>Caja</th><th>Fecha que se Genero</th><th>Monto</th><th>Descripcion</th><th>Concepto</th></tr></thead><tbody>';

  while($row = mysql_fetch_array($result)){

   $fecha = explode(' ', $row['g_fechagen']);

  echo '<tr><th>'.$row['g_ugen'].'</th><td>'.$row['g_caja'].'</td><td>'.cambiar_fecha($fecha[0],'').$fecha[1].'</td><td><b>$ '.number_format($row['g_monto'],2).'</b></td><td>'.$row['g_descripcion'].'</td><td>'.busca($row['g_concepto'],'conceptosg','c_id','c_nmb').'</td></tr>';

  }

    echo '<tr><td></td><td></td><th>Total</th><td><b>$ '.number_format($gastos,2).'</b></td></tr>';
  echo '</table>';
}

function saldoscliente(){
  echo '<a href="xlsrsaldos.php" target=_SELF ><input type="button" value="" id="descargaexcel" class="botont"></a>';

  $sqlc = 'SELECT * FROM clientes WHERE c_estatus = "1" ORDER BY c_nmb';
  $resultc = mysql_query($sqlc) or die($sqlc);
  echo '<center><table class="lista" width="75%">';
  echo '<tr>
          <th>No. Cliente</th>
          <th>Nombre</th>
          <th>Saldo</th>
        </tr>';

  while($row = mysql_fetch_array($resultc)){
    $sqltc = 'SELECT t_id,t_cliente,t_fcreo,t_ucreo,t_estatus FROM tickets
              WHERE t_cliente = "'.$row['c_id'].'" AND t_estatus IN ("R","P")';
    $resultcrtc = mysql_query($sqltc) or die($sqltc);
    $saldo = 0;
      while($rowtdc = mysql_fetch_array($resultcrtc)){
      $abonosc = busca($rowtdc['t_id'],'pagos_ticket','pt_ticket','SUM(pt_monto)');
      $cargoc = busca($rowtdc['t_id'],'ticketsd','td_ticket','SUM(td_precio*td_cantidad)');
      $saldo+=$cargoc;
      $saldo-=$abonosc;
    }
    if($saldo > 0) $cols = "#CC0000"; else $cols = "#00CC33";

    echo '<tr>
            <th>'.$row['c_id'].'</th>
            <td>'.$row['c_nmb'].'</td>
            <td style="text-align:right;background:'.$cols.';color:#FFFFFF">$'.number_Format($saldo,2).'</td>
          </tr>';
  }
  echo '</table></center>';

}

function ventastotales(){
  if(!isset($_POST['desde'])) $_POST['desde'] = date('Y-m-01');
  if(!isset($_POST['hasta'])) $_POST['hasta'] = date('Y-m-d');
  if(!isset($_POST['order'])) $_POST['order'] = "V";

  if(!isset($_POST['ventas0'])) $ventas0 = ""; else $ventas0 = "checked";

  if(!isset($_POST['existencia']) OR $_POST['existencia'] == 0){
    $_POST['existencia'] = "0";
    $e0='Selected';
    $sqle = '';
    $sqle2 = '';
  }
  elseif($_POST['existencia'] == "1"){
    $e1='Selected';
    $sqle = 'AND td_se = "0"';
    $sqle2 = 'AND cd_se = "0"';
  }
  else{
    $e2='Selected';
    $sqle = 'AND td_se = "1"';
    $sqle2 = 'AND cd_se = "1"';
  }
  if(!isset($totaldes)) $totaldes = NULL;

  if($_POST['order'] == "N"){ $odn = "checked"; $odv = ""; $order = "a_nmb ASC"; }
  elseif($_POST['order'] == "V"){ $odn = ""; $odv = "checked"; $order = "cantidad DESC, ptotal DESC"; }
    echo '<center><table width="80%" border="0">
       <thead>
     <form method="post">
  <th>Artículo</th><td><input type="text" name="articulo" value="'.$_POST['articulo'].'" placeholder="Nombre o fragmento del producto" size="30" onfocus="this.select();" ></td>
  <th>Desde</th><td><input type="date" name="desde" value="'.$_POST['desde'].'" ></td>
  <th>Hasta</th>
  <td><input type="date" name="hasta" value="'.$_POST['hasta'].'">
  </tr><tr>
  <td>Ordenar por</td>

  <td><b>Por Nombre</b>  <input type="radio" name="order" value="N" '.$odn.' />
      <b>Más vendidos</b>  <input type="radio" name="order" value="V" '.$odv.' /></td>
  <td></td>

  </td><td style="padding-left:0px;"><input name="enviar" type="submit" value="" id="actualizar" class="botont"/></td>
  <td ><a href="reportes/xlsventastotales.php?desde='.$_POST['desde'].'&hasta='.$_POST['hasta'].'&departamento='.$_POST['departamento'].'&existencia='.$_POST['existencia'].'&concepto='.$_POST['concepto'].'&order='.$_POST['order'].'" target=_SELF ><input type="button" value="" id="descargaexcel" class="botont"></a></td>
  </form></thead></table>';

    $sqlc2 = '';
    $concept = '';

  if($_POST['articulo']){
    $sqlc2.=' AND a_nmb LIKE "%'.$_POST['articulo'].'%"';
  }

  //sumo vendido en pv
  $sqlsum = 'SELECT SUM(td_cantidad) as cantidad,SUM(td_precio*td_Cantidad) as ptotal , td_articulo, td_precio, a_id,a_nmb,a_sku,a_proveedor,a_inventariado,a_marca
             FROM articulos INNER JOIN ticketsd ON a_id = td_articulo
             INNER JOIN tickets ON ticketsd.td_ticket = tickets.t_id
             WHERE tickets.t_estatus = "A"
             AND DATE(t_fcreo) BETWEEN "'.$_POST['desde'].'" AND "'.$_POST['hasta'].'" '.$sqlc2.'
             GROUP BY a_id
             ORDER BY '.$order;
  $resultsum = mysql_query($sqlsum) or die(mysql_error());

  echo '<center><table width="100%" border="0" class="listam">';
  echo '<thead>
  <tr><th colspan="7">Detalles de ventas por articulos '.$concept.'</th></tr>
  <tr>
    <th>Codigo de Barras</th>
    <th>SKU</th>
    <th>Articulo</th>
    <th>Proveedor</th>
    <th>Inventariado</th>
    <th>Cantidad</th>
    <th>Total</th>
  </tr></thead><tbody> ';

  $costototal=0;
  $preciototal=0;
  $cantidadtotal=0;
  $numa = 0;
  while($rowc = mysql_fetch_array($resultsum)){
    $numa++;
    $articulo=$rowc['td_articulo'];
    echo '<tr><th>'.$rowc['td_articulo'].'</th>
              <td>'. $rowc['a_sku'].'</td><b>
              <td>'.$rowc['a_nmb'].'</td></b>
              <td><center><b>'.busca($rowc['a_proveedor'],'proveedores','p_id','p_nmb').'</b></center></td>
              ';
    if(busca($articulo,'articulos','a_id','a_inventariado')==1){
      echo'<td><center>SI</center></td>';
    }
    else{
      echo'<td><center>NO</center></td>';
    }

   echo '<td style="text-align:right;">'.number_format($rowc['cantidad'],2).'</td>
         <td style="text-align:right;">$'.number_format($rowc['ptotal'],2).'</td>
         </tr>';

    $preciototal += $rowc['ptotal'];//+$rowsumcot['ctotal'];
    $cantidadtotal += $rowc['cantidad'];//+$rowsumcot['cantidadcot'];
  }

  echo '<tfoot><tr><td colspan="5" style="text-align:right;">Totales</td>
  <td style="text-align:right;">'.$cantidadtotal.'</td>
  <td style="text-align:right;">$'.number_format($preciototal,2).'</td></tr></tfoot>';

  echo '</tbody></table>';
}

function combos($fini,$ffin){
  echo '<form name="fechas" method="post"><center><table width="100%" border="0">
  <thead><tr>
  <th><b>Fecha inicial</b> </th>
  <td><input type="date" name="fini" value="'.$fini.'" /></td>
  <td>&nbsp;</td>
  <th><b>Fecha final</b> </th>
  <td><input type="date" name="ffin" value="'.$ffin.'" /></td>
  <td>&nbsp;</td>
  <td>
    <input name="enviar" style="width:80px;" type="submit" value="" id="actualizar" class="botont"/>

    </td>

  </tr></thead>
  </table></form>';


  $sql = 'SELECT * FROM tickets WHERE DATE(t_fcreo) BETWEEN "'.$fini.'" AND "'.$ffin.'" AND t_estatus = "A"
          AND t_id IN (SELECT DISTINCT(tc_ticket) FROM ticketsc)';
  $resultt=mysql_query($sql) or die($sql);
  $totc = 0;
  $totp = 0;
  while($rowt = mysql_fetch_array($resultt)){
    $precio =  $rowt['t_efectivo']+$rowt['t_tdc']+$rowt['t_vales']+$rowt['t_deposito'];
    echo '<table width="100%" class="listam" border="0">
          <thead>
            <tr>
              <th align="center">Ticket</th>
              <td>
                <a target="_BLANK" href="?modulo=tickets&accion=showtickets&id='.$rowt['t_id'].'">
                  '.$rowt['t_id'].'
                </a>
              </td>
              <th align="center">Fecha</th>
              <td>'.fecha_formato($rowt['t_fcreo'],true,true).'</td>
              <th align="center">Precio</th>
              <td>'.number_format($precio,2).'</td>
            </tr>
            <tr>
              <th colspan="2">Producto vendido</th>
              <th colspan="2">Insumo</th>
              <th colspan="2">Cantidad</th>
            </tr>
          </thead><tbody>';

    $sqlc = 'SELECT td_articulo,tc_articulo,tc_cantidad
             FROM ticketsd INNER JOIN ticketsc ON (td_ticket = tc_ticket AND td_id = tc_idt)
             WHERE tc_ticket = "'.$rowt['t_id'].'"';
    $resultc = mysql_query($sqlc) or die($sqlc);
    while($rowc = mysql_fetch_array($resultc)){
      echo '<tr><th align=center colspan="2">'.busca($rowc['td_articulo'],'articulos','a_id','a_nmb').'</th>';
      echo '<td align=center colspan="2">'.busca($rowc['tc_articulo'],'articulos','a_id','a_nmb').'</td>';
      echo '<td style="text-align:right;" colspan="2">'.$rowc['tc_cantidad'].'</td></tr>';
    }
  }

}

function ventasprodsat($fini,$ffin){
  if($estatus == "A"){ $esta = "selected"; $estc = "";}
  elseif($estatus == "C"){ $esta = ""; $estc = "selected";}

  echo '<form name="fechas" method="post"><center><table width="100%" border="0">
  <thead><tr>
  <th><b>Fecha inicial</b> </th>
  <td><input type="date" name="fini" value="'.$fini.'" /></td>
  <td>&nbsp;</td>
  <th><b>Fecha final</b> </th>
  <td><input type="date" name="ffin" value="'.$ffin.'" /></td>
  <td><input name="enviar" style="width:80px;" type="submit" value="" id="actualizar" class="botont"/></td>
  </tr></thead>
  </table></form>';

  $sql = 'SELECT DISTINCT(d_producto) FROM facturasd WHERE d_factura
          IN (SELECT f_id FROM facturas WHERE f_estatus = "T" AND DATE(f_ftimbro) BETWEEN "'.$fini.'" AND "'.$ffin.'")';
  $resultf = mysql_query($sql);
  while($rowf = mysql_fetch_array($resultf)){
    $sqlf = 'SELECT * FROM facturasd INNER JOIN facturas ON f_id = d_factura
              WHERE d_producto = "'.$rowf['d_producto'].'" AND f_estatus = "T" AND DATE(f_ftimbro)
              BETWEEN "'.$fini.'" AND "'.$ffin.'"';
    $result = mysql_query($sqlf);
    echo '<table width="100%" class="listam" border="0">
        <thead>
          <tr><th colspan="7">'.$rowf['d_producto'].' - '.busca($rowf['d_producto'],'productos','p_claveps','p_nmb').'</th></tr>
          <tr>
            <th align="center">Factura</th>
            <th align="center">Cantidad</th>
            <th align="center">Concepto</th>
            <th align="center">Unitario</th>
            <th align="center">Importe</th>
            <th align="center">IVA</th>
            <th align="center">Total</th>
          </tr>
        </thead><tbody>';
    $importeprod = 0;
    $ivaprod = 0;
    $totalprod = 0;
    while($row = mysql_fetch_array($result)){
      $importe = $row['d_cantidad']*$row['d_costo'];
      $iva = $importe*0.16;
      $total = $importe+$iva;

      echo '<tr><th align=center>'.$row['f_folio'].'</th>';
      echo '<td align=center>'.number_format($row['d_cantidad'],0).'</a></td>';
      echo '<td align=center>'.$row['d_concepto'].'</td>';
      echo '<td style="text-align:right;">'.number_format($row['d_costo'],2).'</td>';
      echo '<td style="text-align:right;">$'.number_format($importe,2).'</td>';
      echo '<td style="text-align:right;">$'.number_format($iva,2).'</td>';
      echo '<td style="text-align:right;">$'.number_format($total,2).'</td>';
      $importeprod+=$importe;
      $totalprod+=$total;
      $ivaprod+=$iva;
    }
    echo '<tr><td colspan="3">&nbsp;</td><td>Totales</td>
            <td>$'.number_format($importeprod,2).'</td>
            <td>$'.number_format($ivaprod,2).'</td>
            <td>$'.number_format($totalprod,2).'</td>
          </tr>';
    echo '</table><br>';
  }
}

function conversiones(){

  echo '
  <div class="bg-white">
  <h4 class="card-title p-1">Mailing-Conversiones</h4>
    <div class="row">
      <div class="col-md-12">
      </div>
      <div class="col-md-7">
        <div class="card-block chartjs">
          <canvas id="myChart" style=""></canvas>
        </div>
        <div class="alert alert-success alert-dismissible fade in" role="alert" bis_skin_checked="1">
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
          <strong>Presiona sobre cualquier punto de la grafica para ver más detalles.
        </div>
      </div>
      <div id="divcampana" class="col-md-5">

      </div>
      <div id="divtabla" class="col-md-12"> <!-- -->
      </div> <!-- -->
    </div>
  </div>';


}
}
?> 

<script src="js/jquery.min.js" type="text/javascript"></script>


<script>

$.ajax({
  url: "query/mailingrafica.php",
  type: "POST",
  dataType: 'json',
  success: function(rtnData) {
    $.each(rtnData, function(dataType, data) {
      //console.log(data.datasets);
      //var ctx = document.getElementById("myChart").getContext('2d');
      var myChart = new Chart(document.getElementById("myChart"), {
        type: data.type,
        //type: 'bar',
        data: {
            datasets: data.datasets,
            labels: data.labels
        },
        options:  {
          scales: 
            {
              x : {
                display: true,
                title: {
                  display: true,
                  text: 'Total',
                }

              },
              y: {
                display: true,
                title: {
                  display: true,
                  text: 'Fecha',
                }
              },
            },
            responsive: true,
            title: {
                display: true,
                text: data.title
            }
        }
      });
      
      document.getElementById("myChart").onclick = function(evt) {
        var activePoints = myChart.getElementAtEvent(evt)[0];
        var firstPoint = activePoints;
        var indexpoint = activePoints._datasetIndex;
        var label = myChart.data.labels[firstPoint._index];
        var value = myChart.data.datasets[firstPoint._datasetIndex].data[firstPoint._index];
        if (firstPoint !== undefined) {

          $.ajax({
            url: "query/tabladatosmail.php",
            type: "POST",
            data: {'fecha' : label,
                  'index' : indexpoint},
          })
          .done(function(data){
            var div = document.getElementById('divtabla');
            var grafcamp = document.getElementById('divcampana');
            div.innerHTML = data; 
            grafcamp.innerHTML = '<div class="card-block chartjs"> <canvas id="myChart2"></canvas> </div>'; 
            $.ajax({
              url: "query/graficacamp.php",
              method: "POST",
              dataType: 'json',
              data: {'fecha' : label,
                    'index' : indexpoint},

              })/// FIN AJAX NUEVA GRAFICA
              .done(function(datagraf) {
                //console.log('pinto nueva grafica');// HASTA AQUI FUNCIONA BIEN
                $.each(datagraf, function(dataType, data) {
                  var myChart = new Chart(document.getElementById("myChart2"), {
                    //type: data.type,
                    type: 'pie',
                    data: {
                        datasets: data.datasets,
                        labels: data.labels
                    },
                    options:  {
                      scales: 
                        {
                          x : {
                            display: true,
                            title: {
                              display: true,
                              text: 'Total',
                            }

                          },
                          y: {
                            display: true,
                            title: {
                              display: true,
                              text: 'Fecha',
                            }
                          },
                        },
                        responsive: true,
                        title: {
                            display: true,
                            text: data.title
                        }
                    }
                  });
                });
                

              }); //// FIN DONE

          }); // FIN DONE FUNCTION
        
        }else{


        } // FIN IF SI NO ES INDEFINIDO

      };// FIN FUNCION ON CLICK


    });// FIN DEL EACH
  }, // FIN DEL SUCCESS PRINCIPAL
  error: function(rtnData) {
      alert('error' + rtnData);
  }
});// FIN AJAX PRINCIPAL
 






//myChart.canvas.onclick = clickpa;



</script>


