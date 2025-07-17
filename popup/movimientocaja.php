<?php
  include('../funciones.php');
  ini_set('display_errors', 1);
  session_start();

  $tipo = $_GET['tipo'];
  $caja = $_GET['id'];
  if($tipo == "I"){
    $titulo = "INGRESO";
    $accion = 'ingreso';
    $max = "999999"; 
  } else {
    $titulo = "RETIRO";
    $accion = 'gasto';
    $saldo = busca($caja, 'cajasd', 'cd_estatus = "A" AND cd_caja', 'SUM(cd_efectivo)');
    $ingresos = busca($caja, 'cajas_movimientos', 'cm_tipo = "I" AND cm_caja', 'SUM(cm_monto)');
    $retiros = busca($caja, 'cajas_movimientos', 'cm_tipo = "G" AND cm_caja', 'SUM(cm_monto)');
    $fondo = busca($caja, 'cajas', 'c_id', 'c_fondo');
    $max = $fondo + $saldo + $ingresos - $retiros;
  }

?>
  <div class="container col-10">
    <div class="mt-5">
      <div class="col-12 alert alert-primary"> INSERTAR <b><?PHP echo $titulo; ?></b> A LA CAJA </div>
      <form id="caja" action="?modulo=cajas&accion=<?PHP echo $accion; ?>&id=<?PHP echo $caja; ?>" method="POST" onsubmit="bloquearbtn()">
        <div class="row">
          <div class="col-12 col-md-4">
            <label >Monto: </label>
            <input type="number" name="monto" id="monto" step="1" min="0" max="<?php echo $max; ?>" class="form-control" placeholder="Monto de efectivo" required>
          </div>
          <!-- <div class="col-12 col-md-4" id="forpago">
              <div class="mb-5">
                <label for"correo">Forma de pago</label>
                <?php
                $sql = 'SELECT * FROM cfdi_fpago WHERE cf_referencia = "1" ORDER BY cf_nmb ASC';
                $result = setq($sql);
                echo '<select name="formapago" id="formapago" class="form-control" onchange="ingresoacaja();">';
                  while($row = $result->fetch_array()){
                    if($row['cf_nmb'] == "03") $self = 'selected'; else $self = "";
                    echo '<option value="'.$row['cf_id'].'">'.$row['cf_descripcion'].'</option>';
                  }
                ?>
                </select>
              </div>
            </div> -->
          <div class="col-12 col-md-4">
            <label >Usuario: </label>
            <input type="text" name="usuario" class="form-control" value="<?php echo $_SESSION['uid']; ?>" readonly> 
          </div>
          <div class="col-12 col-md-4">
            <label >Fecha del movimiento: </label>
            <input type="datetime-local" name="fecha" class="form-control" value="<?php echo date('Y-m-d H:i:s'); ?>">
          </div>
          <div class="col-12 col-md-8">
            <label >Descripción: </label>
            <input type="text" name="observaciones" class="form-control">
          </div>
          <div class="col-12 mt-4">
            <center>
            <button type="button" class="btn btn-danger " id="cerrarFancybox"><i class="fas fa-times"></i> Cancelar</button>
            <button type="submit" id="btnsave" class="btn btn-success"><i class="fas fa-save"></i> Guardar</button>
            </center>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

  <script>
    function bloquearbtn(){
      document.getElementById('btnsave').setAttribute("disabled","disabled");
    }
    $("#cerrarFancybox").click(function() {
            $.fancybox.close();
    });
  </script>
<?php
?>