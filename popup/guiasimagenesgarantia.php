<?php
ini_set('display_errors', 0);
include_once('../funciones.php');
session_start();
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 1 Jul 2000 05:00:00 GMT"); // Fecha en el pasado


//foreachdie();
$tipo = $_GET['tipo'];
$id = $_GET['id'];
$idembarque = $_GET['embarque'];

$ruta = 'img/garantias/embarque/';
$estatus = busca($idembarque, 'embarques', 'e_id', 'e_estatus');

$grupo = busca($_SESSION['uid'], 'usuarios', 'u_id', 'u_grupo');

$permitidos = array("ADMIN", "GERENCIA", "SUBGERENCIA", "LOGISTIC");


?>
<div class="container col-10">
  <div class="alert alert-primary"> Fotografías del artículo</div>
    <div class="mt-4 row">
        <?php
        
          $sql = 'SELECT * FROM garantias_imagenes WHERE gi_garantia = "'.$id.'" AND gi_tipo = "'.$tipo.'" ORDER BY gi_id DESC';    
          $result = setq($sql) ;
          $resultn = setq($sql) ;
          $maxn = $resultn->num_rows;
          while($row = $result->fetch_array()){
            $img = array('jpg', 'jpeg', 'png', 'webp', 'WEBP');
            if(in_array($row['gi_ext'], $img)){
              $imagen = $ruta.$row['gi_nmb'].'.'.$row['gi_ext'];
            } else {
              $imagen = 'img/archivo.png';
            }
            
            ?>
              <div class="col-md-3 mb-2">
                <a target="_blank" href="<?php echo $ruta.$row['gi_nmb'].'.'.$row['gi_ext']; ?>">
                  <img class="img-thumbnail" src="<?php echo $imagen; ?>" alt="<?php echo $row['gi_nmb'].'.'.$row['gi_ext']; ?>">
                </a>
                <br>
                <?php   
                  if($estatus != "F" && in_array($grupo, $permitidos)){
                ?>
                <button class="btn btn-danger border-secondary col-md-12" onclick="borrararchivo(<?php echo $row['gi_id'];?>, '<?php echo $tipo;?>')" role="button">
                  <i class="icon-trash2"></i> Borrar
                </button>
                <?php } ?>
              </div>
            <?php
          }
        ?>
      </div>
    <?php  
      if($estatus != "F" && in_array($grupo, $permitidos)){
    ?>
    <form id="formimagenes" method="post" action="?modulo=guias&accion=setimage" enctype="multipart/form-data">      
      <?php 
      if(isset($_GET['art'])){
      ?>
      <input type="hidden" value="<?php echo $art; ?>" name="articulo" />
      <?php
      } else{
        ?>
      <input type="hidden" value="<?php echo $id; ?>" name="guia" />
      <?php
      }
      ?>
      <input type="hidden" value="<?php echo $tipo; ?>" name="tipo" />
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
                        <h3 class="fs-5 fw-bold text-gray-900 mb-1">Suelta aqui los archivos relacionados a la variante.</h3>
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
function borrararchivo(id, tipo) {
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
        url: 'query/eliminarimagenesga.php',
        data: {
          id: id,
          tipo: tipo
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
          title: 'Imágen eliminada con éxito'
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
    addRemoveLinks: true
  });

  
    myDropzone.on("addedfile", function(file) {
    var inputFiles = document.getElementById('archivos');
    var files = inputFiles.files;
    var fileList = new DataTransfer();

    for (var i = 0; i < files.length; i++) {
      fileList.items.add(files[i]);
    }

    fileList.items.add(file);
    inputFiles.files = fileList.files;
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
    url: 'query/cargaimagenesga.php', // URL a la que deseas enviar la solicitud
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
        title: 'Imágenes subidas con éxito'
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

