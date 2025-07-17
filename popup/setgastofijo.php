<?php
  session_start();
  include_once('../funciones.php');

  if(!isset($_GET['id'])) $_GET['id'] = NULL;

  include_once('../modulos/gastos.php');
  $gast = new Modelgastos();
  $gast->selectgastof($_GET['id']);
  $col = '12';
  if($gast->id){
    $accion = 'updategastof&id='.$_GET['id'];
    $title = "Editar un ";
    if($gast->estatus == "A"){
      $col = '10';
      $btn = '<div class="col-12 col-md-2">
        <center><button type="button" class="btn btn-danger" id="desc" onclick="desactivargf('.$_GET['id'].');"><i class="fa fa-times"></i> Desactivar gasto</button></center>
      </div>';
    }
  }
  else{
    $accion = 'insertgastof';
    $title = "Insertar un ";
    $seldis = "selected";
    $gast->fechaap = date('Y-m-d');
  }
  $iteracionp = array("S"=>"SEMANAL","Q"=>"QUINCENAL","M"=>"MENSUAL","U"=>"UNICA");

  echo '<script language="javascript">
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
    function desactivargf(id){
      var conf = confirm("¿Deseas desactivar el gasto fijo?");
      if(conf == true){
        document.location.href="?modulo=gastos&accion=desactivargastof&id="+id;
      }
    }
  </script>';
  echo'<div class="container mt-1 col-10">
    <form action="?modulo=gastos&accion='.$accion.'" method="post" onsubmit="checksubmit();">
      <div class="row">
        <div class="col-12 col-md-'.$col.' alert alert-primary">'.$title.' Gasto Fijo</div>
        '.$btn.'
      </div>';
      echo '<div class="row">
        <div class="col-12 col-md-3">
          <div class="mb-5">
            <label for"nmbac">Categoria del gasto fijo </label>';
            $sqlg = 'SELECT * FROM gastos_categoria WHERE gc_tipo = "F" ORDER BY gc_nmb';
            $resultg = setq($sqlg);
            echo '<select name="categoria" id="categoria" class="form-control" required >';
              while($rowg = $resultg->fetch_array()){
                if($rowg['gc_id'] == $gast->categoria) $selcat = 'selected'; else $selcat = "";
                echo '<option value="'.$rowg['gc_id'].'" '.$selcat.'>'.$rowg['gc_nmb'].'</option>';
              }
            echo '</select>
          </div>
        </div>
        <div class="col-6 col-md-4">
          <div class="mb-5">
            <label for"correo">Nombre del gasto fijo</label>
            <input type="text" name="nmb" id="nmb" class="form-control" placeholder="Nombre" value="'.$gast->nmb.'" required/>
          </div>
        </div>
        <div class="col-12 col-md-2">
          <div class="mb-5">
            <label for"nmbac">Importe del Gasto</label>
            <input type="number" min="0" step="0.01" max="9999999" name="importe" value="'.$gast->importe.'" id="importe" class="form-control number-align" placeholder="Importe del gasto fijo" required="required" onfocus="this.select();"  />
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="mb-5">
            <label for"correo">Iteración <i class="icon-exclamation-circle" data-toggle="tooltip" data-placement="top" title="Cada cuanto tiempo se repetirá"></i></label>';
            echo '<select name="iteracion" id="iteracion" class="form-control" required >';
              foreach($iteracionp as $claveit => $nmbitera){
                if($claveit == $gast->iteracion) $selcat = 'selected'; else $selcat = "";
                echo '<option value="'.$claveit.'" '.$selcat.'>'.$nmbitera.'</option>';
              }
            echo '</select>
          </div>
        </div>
        <div class="col-12 col-md-3" >
          <div class="mb-5">
            <label for"fini">Fecha proxima aparición </label>
            <input type="date" name="fechaap" id="fechaap" value="'.$gast->fechaap.'" required class="form-control" />
          </div>
        </div>';
        echo '<div class="col-12 col-md-12">
          <center><button type="submit" class="btn btn-primary" id="guardar"><i class="icon-bank"></i> GUARDAR </button></center>
        </div>
      </div>
    </form>
  </div>';
?>