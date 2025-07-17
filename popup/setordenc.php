<?php
//ini_set('display_errors', 1);
session_start();
include_once('../funciones.php');

if(isset($_GET['id'])) $ordenc = $_GET['id'];
include_once('../modulos/ordenesc.php');
$orden = new modelordenesc();
$orden->select($ordenc);
echo '
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

'; 
  if($orden->id){
    $accion = 'update&id='.$orden->id;
    $title = "Editar";
    $responsable = $orden->solicita;
    $fechacom = $orden->fechacom;
    $descripcion = $orden->observaciones;
    $proveedor = $orden->proveedor;
    $almacen = $orden->almacen;
  }
  else{
    $responsable = $_SESSION['uid'];
    $accion = 'insert';
    $title = "Registrar";
    $descuento = 0;
    $fechacom = date('Y-m-d',strtotime('+ 3 days'));
    $sdesc = "checked";
    $sindescuento = "checked";
    $sindescuento = "checked";
    $almacen = "1";
  }

if($orden->estatus == "P") $tipo = "checked"; else $tipo = "";
if($orden->diva == "1") $diva = "checked"; else $diva = "";

echo'
<input type="hidden" id="empresa" value="'.$_SESSION['emp'].'" />

<div class="container mt-1 ">
  <form action="?modulo=ordenesc&accion='.$accion.'" method="post" onsubmit="checksubmit();">
    <div class="row">
      <div class="col-12 col-md-12 alert alert-primary">'.$title.' Orden de compra</div>
      <div class="btn-group" data-toggle="buttons">';

  if(isset($_GET['tablero'])) echo '<input type="hidden" name="tablero" value="'.$_GET['tablero'].'" />';

echo '</div>
    </div>';
  echo '
    <div class="row nowrap">
      <div class="col-12 col-md-3">
        <div class="mb-5">
          <label for"nmbac">Proveedor</label>';
  $sql = 'SELECT * FROM proveedores WHERE p_empresa = "'.$_SESSION['emp'].'" AND p_estatus = "A"';
  $result = setq($sql);
  echo '<select name="proveedor" id="proveedor" required="required" class="form-control">';
  echo '<option value="" disabled >Elegir un proveedor</option>';
  while($row = $result->fetch_array()){
    if($row['p_id'] == $proveedor) $sel = 'selected'; else $sel = "";
    echo '<option value="'.$row['p_id'].'" '.$sel.'>'.$row['p_alias'].'</option>';
  }
  echo '</select></div>
      </div>
       <div class="col-12 col-md-3" >
        <div class="mb-5">
          <label for"fini">Fecha estimada de entrega</label>
          <input type="date" name="fechacom" id="fechacom" value="'.$fechacom.'" class="form-control" placeholder="Cuando arriba tu compra" required="required" />
        </div>
      </div>
      <div class="col-6 col-md-2">
        <div class="mb-5">
          <label for"correo">Solicitante</label>
          '.menu_select_db('usuarios','u_id','u_nmb',$responsable,'solicita','u_estatus = "A" AND u_id != "ADMIN"',false,false,false,true,"Responsable").'
        </div>
      </div>
      
      <div class="col-md-2 mb-5">
        <label>Tipo</label> 
        <input type="checkbox" id="tipo1" name="tipo" '.$tipo.' class="flipswitchoc" onchange="checkrequi();"/>
      </div>

      <div class="col-md-2 mb-5">
        <label>Almacen</label>
        '.menu_select_db('pr_almacenes','pa_id','pa_nmb',$almacen,'almacen','pa_estatus = "A"',false,false,false,true,"Almacen").'
      </div>
      <div class="col-md-2 mb-5" id="divadiv" style="display: none;">
        <label>Desplegar IVA</label> 
        <input type="checkbox"  name="diva" '.$diva.' class="flipswitch" />
      </div>
      ';


  echo '<div class="col-12 col-md-12">
        <div class="mb-5">
          <label for"correo">Observaciones</label>
          <textarea class="form-control" name="descripcion" rows="2" placeholder="Si tienes alguna anotación sobre la compra, anotala " >'.$descripcion.'</textarea>
        </div>
      </div>

      <div class="col-12 col-md-12">
        <center><button role="submit" class="btn btn-primary" id="guardar"><i class="fa fa-save"></i> Guardar</button></center>
      </div>
    </div>
  </form>
</div>';
?>
<script>
  checkrequi();
  function checkrequi(){
    if($('#tipo1').prop("checked") == true ){
      $('#divadiv').css("display", "block");
      $('#divadiv').prop("disabled", false);
    }else{
      $('#divadiv').css("display", "none");
      $('#divadiv').prop("disabled", true);
    }
  }
  
</script>
<?php
?>
