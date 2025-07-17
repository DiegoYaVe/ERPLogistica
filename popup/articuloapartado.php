<?php
session_start();
//ini_set('display_errors',1);
include_once('../funciones.php');

$articulo = $_GET['id'];
$nmba = busca($articulo, "articulos", "a_id", "a_nmb");
if(isset($_GET['model'])){
    $modelo = $_GET['model'];
    $nmba .= " ".busca($articulo, 'articulos_variantes', 'av_modelo = "'.$modelo.'" AND av_articulo', 'av_nmb');
}

?>
    <div class="card mt-3">
        <div class="row">
            <center><h4>Remisiones con el artículo apartado</h4></center>
            <center><h4>"<?php echo $nmba; ?>"</h4></center>
        </div>
    <div class="card-body">
    <div class=" col-12 table-responsive">
      <center><table width="100%" class="mb-0 table table-hover table-striped" id="myTableP">
      <thead class="bg-light-blue bg-darken-2 ">
        <tr>
          <th><b>Remisión</b></th>
          <th><b>Cliente</b></th>
          <th><b>Vendedor</b></th>
          <th><b>No. artículos</b></th>
          <th><b>Estatus</b></th>
          <th><b>Tipo envío</b></th>
          <th><b>Fecha de aplicación</b></th>
        </tr>
      </thead>
      <tbody>
      </tbody>
      </table>
      </div>
    </div>
    </div>

    <script>
    function mandar(id){
      //var table = $("#myTable").DataTable();
      $("#myTableP").DataTable().clear().draw();
      $("#myTableP").DataTable().destroy();
      
      var windowHeight = $(window).height();

      $("#myTableP").DataTable( {
        paging: true,
        scrollY: windowHeight * 0.5,
        processing: true,
        serverside: true,
        ordering: false,
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
        },
        ajax: {
          url: "query/datatableartremision.php",
          type: "POST",
          datatype: "json",
          data: {
            articulo: "<?php echo $_GET['id']; ?>",
            model: "<?php echo $_GET['model']; ?>"
          }
        },
        pageLength: "50",
        responsivePriority: 1,
      });
    }
    
    mandar(1);
  </script>