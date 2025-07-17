<?php
session_start();
//ini_set('display_errors',1);
include_once('../funciones.php');
?>
    <div class="card mt-3">
        <div class="row">
            <center><h4>Artículos disponibles para embarcar por paquetería</h4></center>
        </div>
    <div class="card-body">
    <div class=" col-12 table-responsive">
      <center><table width="100%" class="mb-0 table table-hover table-striped" id="myTableP">
      <thead class="bg-light-blue bg-darken-2 ">
        <tr>
          <th><b>Forma de envío</b></th>
          <th><b>Cantidad</b></th>
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
          url: "query/datatableartembarque.php",
          type: "POST",
          datatype: "json"
        },
        pageLength: "50",
        responsivePriority: 1,
      });
    }
    
    mandar(1);
  </script>