<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 1 Jul 2000 05:00:00 GMT"); // Fecha en el pasado
session_start();
//ini_set('display_errors',1);
include_once('../funciones.php');
if(isset($_GET['id'])){
  include_once('../modulos/rolesprod.php');
  $rolesprod = new modelrolesprod();
  $rolesprod->selectempleado($_GET['id']);

  $title = "EDITAR";
  if($rolesprod->estatus == "A")
    $checked = "checked";
  else
    $checked = "";

$inpid = $_GET['id'];
$accion = 'updateempleado&id='.$_GET['id'];
}
else{
  $title = "AGREGAR";
  $checked="checked";
  $inpid = '';
  $accion = "insertempleado";
  $rolesprod->empleado = ".";
}

echo '
<style>
  #myTableG {
    width: 100%;
    border-collapse: collapse;
  }

  #myTableG th, #myTableG td {
    padding: 8px;
    text-align: left;
    border: 1px solid #ddd;
    /* background-color: #5490c6; */ /* Fondo gris claro para todas las celdas */
  }

  #myTableG tbody tr:nth-child(odd) td {
    /* background-color: #ffffff; */ /* Fondo blanco para las filas impares */
  }

  #myTableG tbody tr:nth-child(even) td {
    /* background-color: #f9f9f9; */ /* Fondo gris claro para las filas pares */
  }

  #myTableG {
    font-size: 12px; /* Cambia el tamaño de letra deseado */
  }
</style>
'; 

$sqlp = 'SELECT * FROM pr_procesos WHERE ppr_estatus = "A"';
$resultp = setq($sqlp);

echo '
    <div class="container">
    <div class="col-12 alert alert-primary">'.$title.' UN EMPLEADO</div>
      <form class="row" method="post" action="index?modulo=rolesprod&accion='.$accion.'" autocomplete="off">

        <div class="mb-5 col-md-4">
        <label>Nombre del empleado:</label>
          <input type="text" id="nmb" class="form-control focus" value="'.$rolesprod->nmb.'" placeholder="Nombre del empleado" name="nmb" onfocus="this.select();" required="required" >
        </div>

        <div class="mb-5 col-md-4">
        <label>Rol::</label>';
        $sql = 'SELECT * FROM rolesprod WHERE rp_estatus = "A" ORDER BY rp_nmb';
        $result = setq($sql);
        echo '<select name="rol" required class="form-control">';
        echo '<option disabled>Selecciona un Rol</option>';
        while($row = $result->fetch_array()){
          if($rolesprod->rol == $row['rp_id']){
            $sel = 'selected';
          } else{
            $sel = '';
          }
          echo '<option value="'.$row['rp_id'].'" '.$sel.'>'.$row['rp_nmb'].'</option>';
        }

        
echo '  </select></div>';
        echo '<div class="mb-5 col-md-4">
        <label>Estatus:</label>
          <input class= "flipswitch form-control"  type="checkbox" name="estatus" id="estatus" '.$checked.'>
        </div>';

        $grupo = busca($_SESSION['uid'], 'usuarios', 'u_id', 'u_grupo');
        $gruposper = array('ADMIN', 'PRODUCCION');
        $arrayneg = array("N","P","G","V","O","L");
        if(isset($_GET['id']) && in_array($grupo, $gruposper)){
          echo '
            <div class="mb-5 col-md-4">
              <label>Contraseña</label>
              <input  type="number" id="pass1" class="form-control" data-toggle="tooltip" data-placement="top" placeholder="Password" name="password" required="required" onchange="validapass();" readonly value="'.$rolesprod->passwordse.'">
            </div>';

            /* echo '<div class="mb-5 col-md-4">
              <label>Confirma Contraseña: (4 números)</label>
              <input  type="password" id="pass2" class="form-control" data-toggle="tooltip" data-placement="top" placeholder="Confirma contraseña" required="required" onchange="validapass();" >
            </div>'; */
        }

        echo '<div class="mb-5 col-md-12">
        <center><button type="submit" id="setempleado" class="btn btn-primary" ><i class="fa fa-save"></i> Guardar</center>
      </div>
      </div>
      </form>
    </div>';

?>
<script>
  function validapass(){
    var pass1 = document.getElementById("pass1").value;
    var pass2 = document.getElementById("pass2").value;

    if(pass1 === pass2){
      document.getElementById("setempleado").disabled = false;
    }
    else{
      document.getElementById("setempleado").disabled = true;
    }

  }
</script>