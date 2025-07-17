<?php
  session_start();
  ini_set('display_errors',1);
  //include_once('../funciones.php');

  echo '<div class="">  
    <form method="post" enctype="multipart/form-data" action="?modulo=facturas&accion=serimportado&factura='.$_GET['factura'].'">
      <div class="row">
        <div class="col-12 alert alert-primary">Importar producto SAT desde formato</div>
      </div>
      <div class="row nowrap">
        <div class="col-md-12 mb-5">
          <center>
            <label>Elegir archivo</label>
            <input type="file" name="archivo" required accept="text/xml, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"  />
          </center>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12">
          <center>
            <button role="submit" class="btn btn-success mt-2"><i class="icon-download4"></i> Importar</button>
          </center>
        </div>
      </div>
    </form>
  </div>';
?>