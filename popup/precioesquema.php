<?php
  session_start();
  ini_set('display_errors',1);
  include_once('../funciones.php');
  $precio = $_GET['precio'];

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
  </script>';


  
  echo'
  <input type="hidden" id="empresa" value="'.$_SESSION['emp'].'" />
  <div class="container mt-1 ">
    <form action="?modulo=precios&accion=setprecioalmacen&precio='.$precio.'" method="post" enctype =  "multipart/form-data" onsubmit="checksubmit();">
      <div class="row">
        <div class="col-12 col-md-12 alert alert-primary">Elegir almacenes aplicables</div>';
          $sql = 'SELECT * FROM almacenes WHERE a_empresa = "'.$_SESSION['emp'].'" AND a_estatus = "A"';
          $result = setq($sql);
          while($row = $result->fetch_array()){
            if(busca($row['a_id'],'esquema_almacen','ea_esquema = "'.$precio.'" AND ea_almacen','COUNT(*)') > 0) $sel = "checked";
            else $sel = "";
            echo '<div class="col-6 col-md-3">
              <div class="mb-5">
                <label>'.$row['a_nmb'].'</label><br>
                <input type="checkbox" name="precioal'.$row['a_id'].'" '.$sel.'  class="flipswitchsn"  />
              </div>
            </div>';
          }
          echo '<input type="hidden" value="'.$valac.'" name="actividad" id="actividad">
        </div>';
        echo '<div class="col-12 col-md-12">
          <center><button role="submit" class="btn btn-primary" id="guardar"><i class="fa fa-save"></i> Guardar</button></center>
        </div>
      </div>
    </form>
  </div>'; 

  

////////////////////////////////////////////////////////

/*
  echo'
  <input type="hidden" id="empresa" value="'.$_SESSION['emp'].'" />
  <div class="container mt-1 ">
    <form action="?modulo=precios&accion=setprecioalmacen&precio='.$precio.'" method="post" enctype =  "multipart/form-data" onsubmit="checksubmit();">
      <div class="row">
        <div class="col-12 col-md-12 alert alert-primary">Elegir almacenes aplicables</div>
        <div class="dropdown col-md-12">
          <button class="btn btn-secondary dropdown-toggle"  type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            SELECCIONAR 
          </button>
          <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">';
          $sql = 'SELECT * FROM almacenes WHERE a_empresa = "'.$_SESSION['emp'].'" AND a_estatus = "A"';
          $result = setq($sql);
          while($row = $result->fetch_array()){
            if(busca($row['a_id'],'esquema_almacen','ea_esquema = "'.$precio.'" AND ea_almacen','COUNT(*)') > 0) $sel = "checked";
            else $sel = "";

            /*
            echo '
            <div class="col-6 col-md-3">
              <div class="mb-5">
                <label>'.$row['a_nmb'].'</label><br>
                <input type="checkbox" name="precioal'.$row['a_id'].'" '.$sel.'  class="flipswitchsn"  />
              </div>
            </div>';  */
/*
            echo '
              <li class="dropdown-item">
                  <input type="checkbox" name="precioal'.$row['a_id'].'" '.$sel.'  class="flipswitchsn"/> '.$row['a_nmb'].'
              </li>
            ';
          }

          echo '</div>';
          echo '<input type="hidden" value="'.$valac.'" name="actividad" id="actividad">
        </div>';
        echo '<div class="col-12 col-md-12">
          <center><button role="submit" class="btn btn-primary" id="guardar"><i class="fa fa-save"></i> Guardar</button></center>
        </div>
      </div>
    </form>
  </div>'; */

?>