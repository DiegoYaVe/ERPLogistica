<?php
session_start();
//ini_set('display_errors',1);
include_once('../funciones.php');

$id = $_GET['id'];

$sqlv = 'SELECT * FROM garantias_articulos WHERE ga_id = "'.$id.'"';
/* echo $sqlv; */
$resultv = setq($sqlv);
$rowgi = $resultv->fetch_array();

$remision = $rowgi['ga_remision'];
$articulo = $rowgi['ga_articulo'];
$modelo = $rowgi['ga_modelo'];
$client = $rowgi['ga_cliente'];

$cliente = busca($client, 'crm_clientes', 'c_id', 'c_nmb');
$cliente .= " ".busca($client, 'crm_clientes', 'c_id', 'c_apellidos');
$nmba = '';
if(empty($rowgi['ga_rcid'])){
  $folio = $remision;
  $nmba = $articulo." ".$modelo;
} else{
  $folio = busca($remision, 'remisiones', 'r_id', 'r_folio');
  $nmba = busca($articulo, "articulos", "a_id", "a_nmb");
  $var = busca($articulo, 'articulos_variantes', 'av_modelo = "' . $modelo . '" AND av_articulo', 'COUNT(*)');
  if ($var > 0) {
    $nmba .= " " . busca($articulo, 'articulos_variantes', 'av_modelo = "' . $modelo . '" AND av_articulo', 'av_nmb');
  }
}

$nmba .= "&nbsp;&nbsp;";

echo '
    <div class="container">
    <div class="col-12 alert alert-primary">ASIGNACIÓN DE GUÍA AL ARTÍCULO EN GARANTÍA: '.$nmba.'</div>
      <form class="row" method="post" action="index?modulo=guias&accion=insertguiaga" enctype="multipart/form-data">
      <input type="hidden" value="'.$id.'" name="id">
        <div class="mb-5 col-md-4">
        <label>Guía:</label>
          <input type="text" class="form-control focus" name="guia" id="guia" placeholder="Ingrese una guía" required>
        </div>
        <div class="mb-5 col-md-4">
        <label>Remisión:</label>
          <input type="text" class="form-control" value="'.$folio.'" readonly>
        </div>
        <div class="mb-5 col-md-4">
        <label>Cliente:</label>
          <input type="text" class="form-control" value="'.$cliente.'" readonly>
        </div>';
        
        ?>
    <div class="mb-5 col-md-12">
      <label>Evidencia:</label>

      <div class="fv-row">
        <!--begin::Dropzone-->
        <div class="dropzone" id="kt_dropzonejs_example_1">
          <!--begin::Message-->
          <div class="dz-message needsclick">
            <i class="ki-duotone ki-file-up fs-3x text-primary"><span class="path1"></span><span
                class="path2"></span></i>

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
      <input type="hidden" value="<?php echo $remision; ?>" name="remision">
      <input type="file" name="archivos[]" id="archivos" class="form-control" multiple hidden> <br>
      <?php
      $exticon = array(
        "zip" => "far fa-file-archive-o",
        "rar" => "far fa-file-archive-o",
        "7z" => "far fa-file-archive-o",
        "pdf" => "far fa-file-pdf",
        "xls" => "far fa-file-excel",
        "xlsx" => "far fa-file-excel",
        "csv" => "far fa-file-excel",
        "doc" => "far fa-file-word",
        "docx",
        "far fa-file-excel",
        "exe" => "fas fa-laptop-code",
        "jpg" => "fas fa-image",
        "png" => "fas fa-image",
        "gif" => "fas fa-image",
        "jpeg" => "fas fa-image",
        "webp" => "fas fa-image",
        "WEBP" => "fas fa-image"
      );
      ?>
    </div>
    <?php 
        echo '<div class="mb-5 col-md-12">
          <center><button type="submit" class="btn btn-primary" ><i class="fa fa-save"></i> Guardar</center>
        </div>
      </form>
    </div>';

?>
<script>

  var drop = document.getElementById('kt_dropzonejs_example_1');
  if (drop) {
    var myDropzone = new Dropzone("#kt_dropzonejs_example_1", {
      url: "modulos/guias.php", // Set the url for your upload script location
      method: "POST",
      paramName: "archivos", // The name that will be used to transfer the file
      maxFiles: 10,
      maxFilesize: 5, // MB
      addRemoveLinks: true
    });


    myDropzone.on("addedfile", function (file) {
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
</script>