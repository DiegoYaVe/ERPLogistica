
<?php

  //set headers to NOT cache a page
  header("Cache-Control: no-cache, must-revalidate"); //HTTP 1.1
  header("Pragma: no-cache"); //HTTP 1.0
  header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

  
  session_start();
  
  include_once('../funciones.php');

  if(!isset($_GET['id'])) $_GET['id'] = NULL;
  include_once('../modulos/departamentosw.php');
  $depto = new modeldepartamentosw();
  $depto->selectcat($_GET['id']);
  if($depto->id){
    $accion = 'updatecategoria&id='.$_GET['id'];
    $title = "Registrar un ";
  }
  else{
    $accion = 'insertcategoria';
    $title = "Editar información de ";
  }

  $_SESSION['emp'] = 1;

echo'
<div class="container mt-1 ">
  <form action="?modulo=departamentosw&accion='.$accion.'" method="post" onsubmit="checksubmit();">
    <input type="hidden" name="empresa" value="'.$_SESSION['emp'].'" />
    <div class="row">
      <div class="col-12 col-md-12 alert alert-primary">'.$title.' Categoría</div>
      <div class="btn-group" data-toggle="buttons">';
echo '</div>
    </div>';

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
  echo '
    <div class="row">
      <div class="col-md-4 mb-5">
        <label>Categoría en JDCEO</label>
        <select class="form-control" name="catceo" id="catceo" onchange="buscarnmbcat();" required >
        <option value="">Selecciona una categoría de JDCEO</option>';
        $sql = 'SELECT * FROM categorias WHERE cat_estatus = "A" AND cat_empresa = "'.$_SESSION['emp'].'" ';
        $result = setq($sql);
        while($row = $result->fetch_Array()){
          if($depto->catceo == $row['cat_id']) $selected = 'selected'; else $selected = '';
          echo '<option value="'.$row['cat_id'].'" '.$selected.'>'.$row['cat_nmb'].'</option>';
        }
        echo '
        </select>
      </div>
      <div class="col-md-4">
        <div class="mb-5">
          <label for"nmbac">Nombre de la Categoría</label>
          <input type="text" name="nmbac" maxlength="50" value="'.$depto->nmb.'" id="nmbac" class="form-control" placeholder="Nombre de tu Departamento" required="required" onfocus="this.select();" />
        </div>
      </div>
     <div class="col-12 col-md-12">
        <center><button type="submit" class="btn btn-primary" id="guardar"><i class="fa fa-save"></i> Guardar</button></center>
      </div>
    </div>
  </form>
</div>';

echo '
<script>
 function buscarnmbcat(){
  var idcat = document.getElementById("catceo").value;
  $.ajax({
    url: "query/buscarnmbcat.php",
    method: "POST",
    data: {"id" : idcat},
  })
  .done(function(data) {
    console.log(data);
    document.getElementById("nmbac").value = data;
  });

 }
</script>';
?>