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
  session_start();
  include_once('../funciones.php');

  if($_GET['id']){
    $sql = 'SELECT p_nmb,p_fechaap,p_fechacierre FROM proyecciones WHERE p_id = "'.$_GET['id'].'"';
    $result = setq($sql);
    list($nmb,$fap,$fci) = $result->fetch_array();
    $accion = 'updateproyeccion&id='.$_GET['id'];
    $min = 'min="'.date("Y-m-d").'"';
    $disabled = 'disabled readonly';
    $titulo = 'Editar proyección';
    $btn = 'btn-success';
  }else{
    $accion = 'insertproyeccion';
    $titulo = 'Crear una nueva proyección';
    $disabled = '';
    $fap = date('Y-m-d');
    $fci = date('Y-m-d',strtotime('next saturday'));
    $btn = 'btn-primary';
  }
  echo'<div class="container mt-1 col-10">
    <form action="?modulo=cuentas&accion='.$accion.'" method="post" onsubmit="checksubmit();">
      
      <div class="row">
        <div class="col-12 col-md-12 alert alert-primary">
          Crear una nueva proyección
        </div>
        <div class="btn-group" data-toggle="buttons">';
        echo '</div>
      </div>';
      echo '<div class="row">
        <div class="col-12 col-md-6">
          <div class="mb-5">
            <label for"nmbac">Nombre de la proyección</label>
            <input type="text" name="nmb" maxlength="50" value="'.$nmb.'" id="nmb" class="form-control" placeholder="Nombre de la categoria" required="required" onfocus="this.select();" '.$disabled.'/>
          </div>
        </div>
        <div class="col-12 col-md-3" >
          <div class="mb-5">
            <label for"fini">Fecha de apertura <i class="icon-exclamation-circle" data-toggle="tooltip" data-placement="top" title="Al guardar la proyección, la fecha de apertura ya no podrá ser cambiada"></i></label>
            <input type="date" name="fechaap" class="form-control" value="'.$fap.'" '.$disabled.' required />
          </div>
        </div>
        <div class="col-12 col-md-3" >
          <div class="mb-5">
            <label for"fini">Fecha para pagar</label>
            <input type="date" name="fechacierre" class="form-control" value="'.$fci.'" '.$min.' required />
          </div>
        </div>
        </div>
        <div class="col-12 col-md-2">
          <center>
            <button type="submit" class="btn '.$btn.'" id="guardar"><i class="fas fa-bars" style="color: #ffffff;"></i> ';
              if(isset($_GET['id'])) echo'Actualizar Proyección';
              else echo'Crear Proyección';
            echo'</button>
          </center>
        </div>
      </div>
    </form>
  </div>';
?>