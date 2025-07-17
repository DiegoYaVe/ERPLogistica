<?php
  include('../funciones.php');
  ini_set('display_errors', 1);
  session_start();
?>
  <div class="container col-10">
    <div class="mt-5">
      <div class="col-12 alert alert-primary"> ABRIR CAJA </div>
    <form id="caja" action="?modulo=cajas&accion=abrircaja" method="POST" onsubmit="bloquearbtncaja()">
      <div class="row">
        <div class="col-md-6">
          <label >Efectivo Inicial: </label>
          <input type="number" name="efectivoap" id="efectivoap" step="1" min="0" max="99999999" class="form-control" placeholder="Ingresa el efectivo inicial de la caja" required>
        </div>
        <div class="col-md-6">
          <label >Cajero: </label>
          <input type="text" class="form-control" value="<?php echo $_SESSION['uid']; ?>" readonly> 
        </div>
        <div class="col-md-6 mt-4">
          <label >Fecha de Caja: </label>
          <input type="text" class="form-control" value="<?php echo fecha_formato(date('Y-m-d'),false,false); ?>" readonly>
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