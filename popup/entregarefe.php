<?php
  include('../funciones.php');
  ini_set('display_errors', 1);
  session_start();
  $corteefe = busca('1', 'cortes', 'c_estatus = "A" AND c_cuenta', 'c_id');
  $max = busca(busca('A', 'cajas', 'c_estatus', 'c_id'), 'cajasd', 'cd_estatus = "A" AND cd_caja', 'SUM(cd_efectivo)');
  $nmbcuenta = busca('1', 'cuentas', 'cu_id', 'cu_nmb');
  $saldo = saldo_actual('1')
?>
  <div class="container col-10">
    <div class="mt-5">
      <div class="col-12 alert alert-primary"> ENTREGAR EFECTIVO </div>
    <form id="caja" action="?modulo=cajas&accion=entregarefectivo" method="POST" onsubmit="bloquearbtncaja()">
      <div class="row">
        <div class="col-md-6">
          <label >Monto: </label>
          <input type="number" name="monto" id="monto" step="1" min="0" max="<?php echo $saldo; ?>" class="form-control" placeholder="Monto de efectivo a entregar" required>
        </div>
        <div class="col-md-6">
          <label >Cajero: </label>
          <input type="text" class="form-control" value="<?php echo $_SESSION['uid']; ?>" readonly> 
        </div>
        <div class="col-md-6 mt-4">
          <label >Fecha de la entrega: </label>
          <input type="text" class="form-control" value="<?php echo fecha_formato(date('Y-m-d H:i:s'),true,false); ?>" readonly>
        </div>
        <div class="col-md-6 mt-4">
          <label >Cuenta: </label>
          <select class="form-control" name="cuenta" id="cuenta">
          <?php 
            $sql = 'SELECT * FROM cuentas WHERE cu_estatus = "A" AND cu_id != "1"';
            $result = setq($sql);
            while($row = $result ->fetch_array()){
              ?>
              <option value="<?php echo $row['cu_id']; ?>"> <?php echo $row['cu_nmb']; ?></option>
              <?php
            }
          ?>
          </select>
        </div>
      </div>
      <div class="col-12 mt-4">
        <center>
        <button type="button" class="btn btn-danger " id="cerrarFancybox"><i class="fas fa-times"></i> Cancelar</button>
        <button type="submit" id="btncajaopen" class="btn btn-success"><i class="fas fa-save"></i> Guardar</button>
        </center>
      </div>
    </form>
  </div>
</div>

  <script>
    function bloquearbtncaja(){
      document.getElementById('btncajaopen').setAttribute("disabled","disabled");
    }
    $("#cerrarFancybox").click(function() {
            $.fancybox.close();
        });
  </script>
<?php
?>