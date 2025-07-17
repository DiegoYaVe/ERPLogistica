<?php
//ini_set('display_errors', 1);
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 1 Jul 2000 05:00:00 GMT"); // Fecha en el pasado
include_once('../funciones.php');

//foreachdie();
$art = $_GET['articulo'];
$modelo = $_GET['modelo'];
$nmb = busca($art, 'articulos','a_id','a_nmb');
if(isset($_GET['modelo'])) {
  $titulo = "Editar variante de ";
  $accion = "updatevariante";
  $precio = busca($modelo, 'articulos_precios', 'ap_articulo = "'.$art.'" AND ap_modelo', 'ap_precio');
}else {
  $titulo = "Insertar variante de ";
  $accion = "insertvariante";
  $modelo = "";
  $precio = "0";
}

$sql = 'SELECT * FROM articulos_variantes WHERE av_articulo = "'.$art.'" AND av_modelo = "'.$modelo.'"';
$result = setq($sql);
$row = $result -> fetch_array();

echo '
<div class="container col-10">
  <div class="alert alert-primary">'.$titulo.$nmb.'</div>
    <form method="post" action="?modulo=articulos&accion='.$accion.'" name="newvariante" id="newvariante" enctype="multipart/form-data">
      <input type="hidden" value="'.$art.'" name="articulo" />
      <input type="hidden" value="'.$modelo.'" name="modelo" />
      <div class="row">';
        if($accion == 'insertvariante')
          echo '
          <div class="col-sm-2 col-md-1">
            <div class="mb-5">
              <label class="font-weight-bold font-weight-bold">Generar</label>
              <center>
                <button type="button" role="button" onclick="generacb();" class="btn-sm bg-black" >
                  <i class="fas fa-barcode fa-lg" style="color: #ffffff;"></i>
                </button>
              </center>
            </div>
          </div>';
          if(isset($_GET['modelo'])) $dis = 'readonly="readonly"';
          else $dis = "";
        echo '
        <div class="col-md-4">
          <label>Código de barras:</label>
          <input type="text" id="cb" value="'.$row['av_cb'].'" class="form-control" placeholder="Código de barras" name="cb" required="required" '.$dis.'>
        </div>
        <div class="col-md-4">
          <label>Nombre:</label>
          <input type="text" id="nmb" value="'.$row['av_nmb'].'" class="form-control" placeholder="Nombre de la variante" name="nmb" required="required" >
        </div>
        <div class="col-md-3">
          <label>Modelo:</label>
          <input type="text" style="font-size:1rem" id="modelo" value="'.$modelo.'" class="form-control" placeholder="Modelo único" name="modelo" required="required" '.$dis.'/>
        </div>
       ';
        
        echo '
        <div class="col-md-3 mt-2">
          <label>Precio de venta:</label>
          <input type="text" style="font-size:1rem" id="precio" value="'.$precio.'" class="form-control" placeholder="Precio de venta" name="precio" required="required"/>
        </div>
        <div class="col-md-6 mt-2">
          <label>Descripción:</label>
          <input type="text" id="descripcion" value="'.$row['av_descripcion'].'" class="form-control" placeholder="Descripción" name="descripcion" required="required" >
        </div>
        <div class="col-sm-6 col-xs-12 col-md-3 mt-2">
          <div class=""> 
            <label>Estatus de la variante</label>';
            if($row['av_estatus']){
              if($row['av_estatus'] == "A") $checked = "checked";
              else $checked = "";
            } else $checked ="checked";
            echo '
            <input class= "flipswitch form-control" type="checkbox" name="estatus" id="estatus" '.$checked.'>
          </div> 
        </div>
        <div class="col-sm-12 col-md-12 mt-2">
          <div class="mb-5">
            <label class="font-weight-bold">Link en página web</label>
            <input class="form-control" type="text" value="'.$row['av_url'].'" name="url" id="url" placeholder="Link a la página web" >
          </div>
        </div>
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
           <input type="file" name="archivos[]" id="archivos" class="form-control" multiple hidden> <br>';
          $exticon = array("zip"=>"far fa-file-archive-o","rar"=>"far fa-file-archive-o","7z"=>"far fa-file-archive-o","pdf"=>"far fa-file-pdf","xls"=>"far fa-file-excel","xlsx"=>"far fa-file-excel","csv"=>"far fa-file-excel",
          "doc"=>"far fa-file-word","docx","far fa-file-excel","exe"=>"fas fa-laptop-code", "jpg"=>"fas fa-image", "png"=>"fas fa-image", "gif"=>"fas fa-image", "jpeg"=>"fas fa-image", "webp"=>"fas fa-image", "WEBP"=>"fas fa-image" );
          $sql = 'SELECT * FROM articulos_descargas WHERE ad_articulo = "'.$art.'" AND ad_modelo = "'.$row['av_modelo'].'"';
          $result = setq($sql);
          while($row = $result->fetch_array()){
            echo '
            <div class="btn-group" role="group">
              <a href="img/'.$row['ad_ruta'].'" target="_blank" class="btn btn-secondary btn-sm mb-1"><i class="'.$exticon[$row['ad_ext']].'"></i> '.$row['ad_archivo'].'</a>
              <button type="button" class="btn btn-sm btn-danger mb-1" onclick="borrararchivo('.$row['ad_id'].')"><i class="fas fa-trash" style="color: #ffffff;"></i></button>
            </div>
            ';
          }
          echo ' 
        </div>
      </div>
      <center>
        <div class="col-12">
          <button type="submit" class="btn btn-success"><i class="fa fa-save"></i>Guardar</button>
        </div>
      <center>
    </form>
  </div>
</div>';

?>
<script>
  function generacb(){
    $.ajax({
      url: 'query/getcb.php',
      type: 'POST',
      dataType: 'html',
      data: {},
    })
    .done(function(respuesta){
      console.log(respuesta);
      $('#cb').val(respuesta);
    });
    $('#nmb').focus();
  }
  function borrararchivo(id){
    var c = confirm("¿Estás seguro de eliminar este archivo?");
    if(c){
      window.location.href="?modulo=articulos&accion=borrararchivo&id="+id;
    }
  } 
  var myDropzone = new Dropzone("#kt_dropzonejs_example_1", {
    url: "modulos/articulos.php", // Set the url for your upload script location
    method: "POST",
    paramName: "archivos", // The name that will be used to transfer the file
    maxFiles: 10,
    maxFilesize: 5, // MB
    addRemoveLinks: true,
    acceptedFiles: "image/*",
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
  
</script>

