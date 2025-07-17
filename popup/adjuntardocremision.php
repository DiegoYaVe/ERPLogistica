<?php
ini_set('display_errors', 0);
include_once('../funciones.php');
session_start();
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 1 Jul 2000 05:00:00 GMT"); // Fecha en el pasado


//foreachdie();
$remision = $_GET['id'];
$ruta = 'img/remisiones/';
$encargado = busca($remision, 'remisiones', 'r_id', 'r_encargado');  
$folio = busca($remision, 'remisiones', 'r_id', 'r_folio');  
$estatus = busca($remision, 'remisiones', 'r_id', 'r_estatus');
$grupo = busca($_SESSION['uid'], 'usuarios', 'u_id', 'u_grupo');  


?>
<div class="container col-10">
  <div class="alert alert-primary"> Archivos para la remisión <?php echo $folio; ?></div>
    <div class="mt-4 row">
        <?php
          $sql = 'SELECT * FROM remisiones_adjuntos WHERE ra_remision = "'.$remision.'"';          
          $result = setq($sql);
          
          $maxn = $result->num_rows;
          if($maxn > 0){
            while($row = $result->fetch_array()){
              $img = array('jpg', 'jpeg', 'png', 'webp', 'WEBP');
              if(in_array($row['ra_ext'], $img)){
                $imagen = $ruta.$row['ra_nmb'].'.'.$row['ra_ext'];
              } else {
                $imagen = 'img/archivo.png';
              }
              
              ?>
                <div class="col-md-3 mb-2">
                  <a target="_blank" href="<?php echo $ruta.$row['ra_nmb'].'.'.$row['ra_ext']; ?>">
                    <img class="img-thumbnail" src="<?php echo $imagen; ?>" alt="<?php echo $row['ra_nmb'].'.'.$row['ra_ext']; ?>">
                  </a>
                  <br>
                  <?php   
                    if($_SESSION['uid'] != $encargado){
                  ?>
                  <button class="btn btn-danger border-secondary col-md-12" onclick="borrararchivo(<?php echo $row['ra_id'];?>)" role="button">
                    <i class="icon-trash2"></i> Borrar
                  </button>
                  <?php } ?>
                </div>
              <?php
            }
          } else {
            ?>
            <div class="alert alert-danger"><center> No hay archivos registrados </center></div>
            <?php
          }
          
        ?>
      </div>
    <?php  
      if(($grupo == "ADMIN" || $grupo == "GERENCIA") || ($_SESSION['uid'] == $encargado && $estatus != "FE")){
    ?>
    <form id="formimagenes" method="post" action="?modulo=remisiones&accion=setimage" enctype="multipart/form-data">      
      <input type="hidden" value="<?php echo $remision; ?>" name="remision" />
      <div class="row">
        <div class="col-md-12 mt-2 form-group"> 
          <div class="fv-row">
            <!--begin::Dropzone-->
            <div class="dropzone" id="kt_dropzonejs_example_1">
                <!--begin::Message-->
                <div class="dz-message needsclick">
                    <i class="ki-duotone ki-file-up fs-3x text-primary"><span class="path1"></span><span class="path2"></span></i>

                    <!--begin::Info-->
                    <div class="ms-4">
                        <h3 class="fs-5 fw-bold text-gray-900 mb-1">Suelta aqui los archivos relacionados a la remision.</h3>
                        <span class="fs-7 fw-semibold text-gray-400">Máximo 5 mb por archivo</span>
                    </div>
                    <!--end::Info-->
                </div>
            </div>
            <!--end::Dropzone-->
          </div>
           <input type="file" name="archivos[]" id="archivos" class="form-control" multiple hidden> <br>
          <?php
          $exticon = array("zip"=>"far fa-file-archive-o","rar"=>"far fa-file-archive-o","7z"=>"far fa-file-archive-o","pdf"=>"far fa-file-pdf","xls"=>"far fa-file-excel","xlsx"=>"far fa-file-excel","csv"=>"far fa-file-excel",
          "doc"=>"far fa-file-word","docx","far fa-file-excel","exe"=>"fas fa-laptop-code", "jpg"=>"fas fa-image", "png"=>"fas fa-image", "gif"=>"fas fa-image", "jpeg"=>"fas fa-image", "webp"=>"fas fa-image", "WEBP"=>"fas fa-image" );
          ?> 
        </div>
      </div>
      <center>
        <div class="col-12">
          <button type="submit" class="btn btn-success"><i class="fa fa-save"></i>Guardar</button>
        </div>
      <center>
    </form>
    <?php } ?>
  </div>
  
  
  
</div>

<script>
function borrararchivo(id) {
  Swal.fire({
    title: '¿Estás seguro de eliminar este archivo?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if (result.isConfirmed) {
      // El usuario confirmó la eliminación
      $.ajax({
        type: 'POST',
        url: 'query/eliminaradjuntoremision.php',
        data: {
          id: id
        },
        success: function(response) {
          // Maneja la respuesta de la eliminación, puedes mostrar un mensaje de éxito aquí
          // Si deseas cerrar FancyBox después de la eliminación, puedes hacerlo aquí
          const Toast = Swal.mixin({
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 2000,
          timerProgressBar: true,
          didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
          }
        })

        Toast.fire({
          icon: 'success',
          title: 'Archivo eliminado con éxito'
        })
          $.fancybox.close();
        },
        error: function(xhr, status, error) {
          // Maneja errores si ocurren (opcional)
          console.error('Error en la solicitud:', status, error);
        }
      });
    }
  });
}

  var drop = document.getElementById('kt_dropzonejs_example_1');
  if(drop){
    var myDropzone = new Dropzone("#kt_dropzonejs_example_1", {
    url: "modulos/guias.php", // Set the url for your upload script location
    method: "POST",
    paramName: "archivos", // The name that will be used to transfer the file
    maxFiles: 10,
    maxFilesize: 5, // MB
    addRemoveLinks: true,
    acceptedFiles: "image/jpeg,image/jpg,image/png"
  });

  
    myDropzone.on("addedfile", function(file) {
      // Verificar la extensión del archivo
      var allowedExtensions = ["jpg", "jpeg", "png"];
      var fileName = file.name.toLowerCase();
      var isImage = allowedExtensions.some(ext => fileName.endsWith(ext));

      if (!isImage) {
          // Eliminar el archivo no permitido
          myDropzone.removeFile(file);
          alert("Solo se permiten archivos con extensiones jpg, jpeg y png.");
      } else {
        var inputFiles = document.getElementById('archivos');
        var files = inputFiles.files;
        var fileList = new DataTransfer();
        for (var i = 0; i < files.length; i++) {
            fileList.items.add(files[i]);
        }

        fileList.items.add(file);
        inputFiles.files = fileList.files;
      }
    });
  }

// Escucha el evento de envío del formulario
$('#formimagenes').submit(function(event) {
  // Previene el comportamiento predeterminado del formulario (envío tradicional)
  event.preventDefault();
  
  // Crea un nuevo objeto FormData para almacenar los datos del formulario
  var formData = new FormData(this);
  
  // Realiza la solicitud AJAX
  $.ajax({
    type: 'POST', // Puedes usar 'GET' o 'POST' según tus necesidades
    url: 'query/cargaadjuntosremision.php', // URL a la que deseas enviar la solicitud
    data: formData, // Utiliza el objeto FormData para enviar los archivos y otros datos del formulario
    processData: false, // Evita que jQuery procese los datos
    contentType: false, // Evita que jQuery establezca el encabezado "Content-Type"
    success: function(response) {
      if(response == 1){
        alert("Ocurrió algún error al subir los archivos. No pudo guardarse.");
      } else if(response == 2){
        alert("Error. La extensión o el tamaño de los archivos no es correcto. Se permiten .gif, .jpg, .png y archivos de 200 kb como máximo");
      } else{
        const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true,
        didOpen: (toast) => {
          toast.addEventListener('mouseenter', Swal.stopTimer)
          toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
      })

      Toast.fire({
        icon: 'success',
        title: 'Archivos subidos con éxito'
      })
        $.fancybox.close();
      }
    },
    error: function(xhr, status, error) {
      // Maneja errores si ocurren (opcional)
      console.error('Error en la solicitud:', status, error);
    }
  });
});


  
</script>

