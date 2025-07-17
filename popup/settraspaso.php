<?php
session_start();
//ini_set('display_errors', 1);
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
function checkSubmitguardar() {
    document.getElementById("guardar").value = "JD";
    document.getElementById("guardar").disabled = true;
    return true;
}
function setminasaldo(){
  var cuentw = document.getElementById("origen").value;

  var saldo = document.getElementById("saldo" + cuentw).value;
  document.getElementById("importe").max = saldo;
}

</script>
<?php
  include_once('../funciones.php');
  $accion = 'insertop';
  $importe=1000;

  $sql = 'SELECT cu_id,cu_nmb FROM cuentas INNER JOIN cortes ON cu_id = c_cuenta
          WHERE c_estatus = "A" ORDER BY cu_orden';
  $result = setq($sql);
  while($rowi = $result->fetch_array()){
    $saldo[$rowi['cu_id']] = saldo_actual($rowi['cu_id']);

    echo '<input type="hidden" id="saldo'.$rowi['cu_id'].'" value="'.$saldo.'">';
  }

  echo '<form autocomplete="off" method=post name="pro"  action="?modulo=cuentas&accion=inserttraspaso" onsubmit="return checkSubmitguardar();">';

  echo '<div class="row">
          <div class="col-12 col-md-12 alert alert-primary">Agregar traspaso entre cuentas</div>';
  echo '<div class="col-12 col-md-4">
            <div class="mb-5">
              <label for"nmbac">Origen</label>';
  $result = setq($sql);
  echo '<select name="origen" id="origen" class="form-control" required onchange="setminasaldo()" >';
  echo '<option value="" disabled selected>Elige una cuenta origen</option>';
  while($row = $result->fetch_array()){
    if($saldo[$row['cu_id']] > 0)
      echo '<option value="'.$row['cu_id'].'" '.$selcu.'>'.$row['cu_nmb'].' - $ '.number_format(saldo_actual($row['cu_id']),2).'</option>';
  }
  echo '</select>
        </div>
      </div>';

  echo '<div class="col-12 col-md-4">
            <div class="mb-5">
              <label for"nmbac">Destino</label>';
  $result = setq($sql);
  echo '<select name="destino" id="destino" class="form-control" required >';
  echo '<option value="" disabled selected>Elige una cuenta destino</option>';
  while($row = $result->fetch_array()){
    echo '<option value="'.$row['cu_id'].'" '.$selcu.'>'.$row['cu_nmb'].'</option>';
  }
  echo '</select>
        </div>
      </div>';
  echo '<div class="col-12 col-md-3">
        <div class="mb-5">
          <label for"nmbac">Importe del traspaso</label>
          <input type="number" min="0" step="0.01" '.$maximp.' name="importe" value="0" id="importe" class="form-control number-align" placeholder="Importe del egreso" required="required" onfocus="this.select();" title="El importe debe ser menor o igual al saldo disponible" autofocus />
        </div>
      </div>';

  echo '<div class="col-12 col-md-12">
        <div class="mb-5">
          <label for"correo">Descripcion</label>
          <input type="text" name="observaciones" value="" id="observaciones" class="form-control" placeholder="Observaciones" onfocus="this.select();" required />
        </div>
      </div>';

  echo '<div class="col-12 col-md-12">
        <center><button type="submit" class="btn btn-primary" id="guardar"><i class="icon-bank"></i> GUARDAR </button></center>
      </div>
    </div>
  </form>
</div>';

?>