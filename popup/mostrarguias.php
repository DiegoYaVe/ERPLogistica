<?php 
session_start();
//ini_set('display_errors', 1);
include_once('../funciones.php');
?>
  <div class="col-12">
  <div class="card">
  <div class="col-12 alert alert-primary"><center><h3>Guías creadas</h3></center></div>
  <div class="card-body" id="listaGuias">
    <div class="col-12 table-responsive">
      <center>
        <table width="100%" class="mb-0 table table-hover table-striped" id="myTableGuias">
        <thead class="bg-light-blue bg-darken-2 ">
          <tr>
            <th><b>No. guía</b></th>
            <th><b>Generó</b></th>
            <th><b>Paqueteria</b></th>
            <th><b>Asignados</b></th>
            <th><b>Estatus</b></th>
            <th><b>Fecha creación</b></th>
            <th><b>Fecha final</b></th>
            <th><b></b></th>
          </tr>
        </thead>
        <tbody>
        </tbody>
      </table>
      </div>
    </div>
    </div>
  </div>

  <script>
      function mandar(){
        //var table = $("#myTableGuias").DataTable();
        $("#myTableGuias").DataTable().clear().draw();
        $("#myTableGuias").DataTable().destroy();
  
        $("#myTableGuias").DataTable( {
          paging: true,
          scrollY: 400,
          processing: true,
          serverside: true,
          language: {
              url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
          },
          ajax: {
            url: "query/datatableguiasxremision.php",
            type: "POST",
            datatype: "json",
            data:{ 
              
            }
          },
          pageLength: "50",
          responsivePriority: 1,
          columnDefs: [ 
            {
                targets: 7, // Índice de la séptima columna (0 basado en índices)
                width: "20%",
            }
        ],
        });
      }

      mandar();

      function deleteGuia(guia){
        var idguia = document.getElementById("idguia").value;
        var btnCancelar = document.getElementById("btnCancelar");
        var btnAtras = document.getElementById("btnAtras");
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
              toast.addEventListener('mouseenter', Swal.stopTimer)
              toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
          })
          Swal.fire({
          title: 'Atención',
          text: "¿Estás seguro de eliminar esta guía?. Los artículos asociados serán retirados de la guía.",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Estoy seguro',
          cancelButtonText: 'Cancelar'
        }).then((result) => {
          if (result.isConfirmed) {

            $.ajax({
              url: 'query/eliminarguia.php',
              method: 'POST',
              data: {
                id: guia
              },
              success: function(data) {
                if(data == 1){
                  if(guia == idguia){
                    btnAtras.click();
                    btnCancelar.click();
                  }
                  mandar();
                  Toast.fire({
                    icon: 'success',
                    title: 'La guía se eliminó satisfactoriamente'
                  })
                } else{
                  Toast.fire({
                    icon: 'error',
                    title: 'Error 704. Ocurrió un error al eliminar la guía'
                  })
                  /* alert("Error 704"); */
                }
              },
              error: function() {
                  alert('Error 709');
              }
            });
          }
        })
      }
      
    </script>