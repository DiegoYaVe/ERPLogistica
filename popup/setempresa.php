<?php
session_start();
include_once('../funciones.php');

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

$licencia = $_GET['idlic'];
$idcliente = busca($licencia,'licencias','l_id','l_cliente');
$sql = 'SELECT * FROM crm_clientes WHERE c_id = "'.$idcliente.'"';
$result = setq($sql);
$row = $result->fetch_array();

echo '
<div class="container mt-1">
  <form action="query/nuevaempresa.php" method="POST">
    <div class="col-md-12">
    <input type="text" name="idcliente" value="'.$idcliente.'" hidden>
    <input type="text" name="idlicencia" value="'.$licencia.'" hidden>
    <div class="row">
      <div class="col-12 col-md-12 alert alert-primary text-center"><label class="text-white">Agregar Empresa</label></div>
      <div class="col-md-4 mb-5">
        <label for="">Nombre Empresa:</label>
        <input type="text" name="nmbempresa" class="form-control" id="nmbempresa" value="EMPRESA '.$row['c_nmb'].'" required>
      </div>
      <div class="col-md-4 mb-5 row">
        <label for="" id="labelsig">Siglas Empresa:</label>
        <input type="text" name="siglas" class="form-control" id="siglas" maxlength="3" placeholder="Siglas de 3 caracteres" onkeyup="verificarsiglas();" required>
      </div>
      <div class="col-md-4 mb-5">
        <label for="">Correo Administrador</label>
        <input type="email" name="correous" class="form-control" id="correous" value="'.$row['c_correo1'].'" required>
      </div>
      <div class="col-md-4 mb-5" hidden>
        <label for="">Contrato</label>
        <input type="text" class="form-control" name="contrato">
      </div>
    </div>
    <div class="col-12 col-md-12">
      <center><button type="submit" class="btn btn-primary" id="guardar"><i class="fa fa-save"></i> Crear</button></center>
    </div>
  </form>
</div>



<script>
  function verificarsiglas(){
    var sig = document.getElementById("siglas").value;
    $.ajax({
      url: "query/verificarsiglas.php",
      method: "POST",
      data: {"siglas": sig},
    }).done(function(data){
      console.log(data);
      if(data == "1"){
        $("#guardar").prop("disabled",true);
        $("#labelsig").html("Las siglas ya son usadas. Verificar.");
        $("#labelsig").css("color","red");
      }else{
        $("#guardar").prop("disabled",false);
        $("#labelsig").html("Siglas Empresa:");
        $("#labelsig").css("color","black");
      }
    });
  }
</script>
';



?>
