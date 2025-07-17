<?php
  header("Cache-Control: no-cache, must-revalidate");
  date_default_timezone_set("America/Mexico_City");
  clearstatcache();
  session_start();

  include_once('../funciones.php');

  if(!isset($_GET['id'])) $_GET['id'] = NULL;

  include_once('../modulos/devoluciones.php');
  $devolucion = new modeldevoluciones();
  $devolucion->select($_GET['id']);
  $ncredito = ' checked="checked" ';
  if($devolucion->id){
    if($devolucion->ncredito == 0) $ncredito = '';
    $accion = 'update&id='.$_GET['id'].'&remision='.$devolucion->remision;
    $title = "Aplicar una ";
    $sqlcliente = 'SELECT CONCAT(c_nmb," ",c_apellidos) FROM crm_clientes INNER JOIN crm_tableros ON ct_cliente = c_id
                    INNER JOIN remisiones_devoluciones ON rd_tablero = ct_id WHERE rd_tablero = "'.$devolucion->tablero.'"';
    $resultcl = setq($sqlcliente);
    list($clientedev) = $resultcl->fetch_array();
  }
?>
  <script language="javascript">
    function checksubmit(){
      document.getElementById("guardar").value = "JD";
      document.getElementById("guardar").disabled = true;
      return true;
    }
    function cargar(){
      opener.location.reload();
      window.close();
    }
    function closewindow(){
      window.opener.location.reload();
      window.close();
    }
  </script>
<?php
  echo'<div class="container mt-1 ">
    <form action="?modulo=devoluciones&accion='.$accion.'" method="post" onsubmit="checksubmit();">
      <input type="hidden" name="empresa" value="'.$_SESSION['emp'].'" />
      <input type="hidden" name="impd" value="'.$_GET['impd'].'" />
      <div class="row">
        <div class="col-12 col-md-12 alert alert-primary">'.$title.' Devolución</div>
        <div class="btn-group" data-toggle="buttons">';
        echo '</div>
      </div>';
      echo '<div class="row">
        <div class="col-12 col-md-5">
          <div class="mb-5">
            <label for"remision">Remisión</label>
            <input type="text" id="remision" value="'.busca($devolucion->remision,'remisiones','r_id','CONCAT(r_folio," - ",r_nmb)').'" class="form-control" readonly="readonly" required/>
          </div>
        </div>
        <div class="col-6 col-md-5">
          <div class="mb-5">
            <label for"cliente">Cliente</label>
            <input type="text" id="cliente" value="'.$clientedev.'" required class="form-control" readonly="readonly"/>
          </div>
        </div>
        <div class="col-12 col-md-2" >
          <div class="mb-5">
            <label for"ncredito">Nota de cédito</label>
            <input type="checkbox" id="ncredito" name="ncredito" '.$ncredito.' class="flipswitchsn" />
          </div>
        </div>
        <div class="col-12 col-md-12">
          <div class="mb-5">
            <label for"motivo">Motivo de cancelación</label>
            <textarea name="motivo" rows="2" id="motivo" placeholder="Describe el motivo de la devolución de los productos" class="form-control" required="required" onfocus="this.select();">'.$devolucion->motivo.'</textarea>
          </div>
        </div>';
        echo '<div class="col-12 col-md-12">
          <center><button type="submit" class="btn btn-primary" id="guardar"><i class="fa fa-save"></i> GUARDAR </button></center>
        </div>
      </div>
    </form>
  </div>';
?>