<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 1 Jul 2000 05:00:00 GMT"); // Fecha en el pasado
?>
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
/* ini_set('display_errors', 1); */
session_start();
include_once('../funciones.php');

$id = $_GET['id'];

//MODAL DE LOS VENDEDORES
echo '   
<div class="card">
<div class="card-body" id="listaGuias">
<div class="col-12 table-responsive">
<form autocomplete="off" action="?modulo=prospectos&accion=insertorden" method="post" id="ordenvendedores" class="">
  <center>
  <h4>Estatus de los vendedores</h4>
  <br>
  <table width="100%" class="mb-0 table table-hover table-striped" id="myTable">
  <thead class="bg-light-blue bg-darken-2 ">
    <tr>
      <th><b>Vendedor</b></th>
      <th><b>Estatus</b></th>
    </tr>
  </thead>
  <tbody>';
  echo '
  </tbody>
  </table>
  </form>
  <div clas="col-12">
  <button type="submit" class="btn btn-sm btn-primary">
    <i class="fas fa-save"></i> Guardar
  </button>
  <input type="hidden" name="id" value="'.$id.'">
  </div>  
  </div>
</div>
</div>';

echo '
<script>
  //var table = $("#myTable").DataTable();
  $("#myTable").DataTable().clear().draw();
  $("#myTable").DataTable().destroy();


  $("#myTable").DataTable( {
    paging: true,
    
    processing: true,
    serverside: true,
    language: {
        url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
    },
    ajax: {
      url: "query/datatableordenvendedores.php",
      type: "POST",
      datatype: "json",
    },
    pageLength: "50",
    responsivePriority: 1
  });

</script>';