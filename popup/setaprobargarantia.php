<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 1 Jul 2000 05:00:00 GMT"); // Fecha en el pasado
session_start();
ini_set('display_errors',0);
include_once('../funciones.php');
$id = $_GET['id'];

$sqlv = 'SELECT * FROM garantias_articulos WHERE ga_id = "'.$id.'"';
/* echo $sqlv; */
$resultv = setq($sqlv);
$rowgi = $resultv->fetch_array();

if(isset($_GET['tipo'])){
  $tipo = "&tipo=".$_GET['tipo'];
  $readon = " readonly";
  $descripcion = $rowgi['ga_descripcion'];
  $txtBtn = 'Guardar';
} else{
  $tipo = '';
  $readon = "";
  $descripcion = '';
  $txtBtn = 'Guardar';
}

if(isset($_GET['save'])){
  $tipo .= '&save=1';
}

$link = '';
if(isset($_GET['link'])){
  $link = '&link=1';
}

$estatus = $rowgi['ga_estatus'];

if($estatus != "C"){
  $motivocancelacion = "";  
} else {
  $motivocancelacion = $rowgi['ga_motivocancela'];
}


$remision = $rowgi['ga_remision'];
$articulo = $rowgi['ga_articulo'];
$modelo = $rowgi['ga_modelo'];

if(empty($rowgi['ga_rcid'])){
  $remision = $rowgi['ga_remision'];
  $nmba = $rowgi['ga_articulo']." ".$rowgi['ga_modelo'];
  $folio = $remision;
} else{
  $folio = busca($remision, 'remisiones', 'r_id', 'r_folio');
  $nmba = busca($articulo, "articulos", "a_id", "a_nmb");
  $var = busca($articulo, 'articulos_variantes', 'av_modelo = "' . $modelo . '" AND av_articulo', 'COUNT(*)');
  if ($var > 0) {
    $nmba .= " " . busca($articulo, 'articulos_variantes', 'av_modelo = "' . $modelo . '" AND av_articulo', 'av_nmb');
  }
}

$nmba .= "&nbsp;&nbsp;REMISIÓN: ".$folio;

if(isset($_GET['fa'])){
  $modulo = 'fabricagarantias';
} else{
    $modulo = 'prodgarantias';
}

?> 
<div class="container">
  <div class="col-12 alert alert-primary">ARTÍCULO: <?php echo $nmba; ?></div>
  <form class="row" id="formulario" method="post" action="index?modulo=<?php echo $modulo; ?>&accion=insertgarantia&id=<?php echo $id.$tipo.$link; ?>" enctype="multipart/form-data" id="formsetgarantia">
  <?php
  if($estatus == "R" && $_GET['origen'] == "R"){

    $rem = $rowgi['ga_remision'];

    $direnvio = busca($rem, 'crm_cotizaciones', 'cc_remision', 'cc_direnvio');
    

    $cp = busca($direnvio, 'crm_direcciones', 'cd_id', 'cd_cp');
    $cob = busca($cp, 'paqueterias_cobertura', 'pc_paqueteria = "1" AND pc_cp', 'pc_tipo');

    $selectvald = "'D'";
    $selectvalc = "'C'";
    $selectvalp = "'P'";
    $selectvalo = "'O'";


    //INICIO DE CHECKBOXES PARA DEFINICIÓN DE ENVÍOS
  $retorno = '
  <div class="row" style="background: white;">
  <div class="card-body">
  <center>
  <div class="table-responsive col-12">
    <table class="table table-hover" id="myTableG">
    <thead class="thead-active bg-primary text-white">
      <tr>
        <th colspan="5" width="67%">Tipo de envío</th>
      </tr>
    </thead>
  <tbody>';
  $retorno .= '
  <td>
      <input class="form-check-input" onclick="selectval('.$selectvald.');" type="radio" name="tipo" id="domicilio" value="D" checked>
      <label class="form-check-label" for="domicilio">A domicilio</label>
  </td>&nbsp;&nbsp;
  <td>
      <input class="form-check-input" onclick="selectval('.$selectvalc.');" type="radio" name="tipo" id="recoge" value="C">
      <label class="form-check-label" for="recoge">Recoge cliente</label>
  </td>&nbsp;&nbsp;
  <!-- <td>
      <input class="form-check-input" onclick="selectval('.$selectvalp.');" type="radio" name="tipo" id="pordefinir" value="P">
      <label class="form-check-label" for="pordefinir">Por definir</label>
  </td>&nbsp;&nbsp; -->'
  . ($cob == "0" ? '
  <td>
      <input class="form-check-input" onclick="selectval('.$selectvalo.');" type="radio" name="tipo" id="ocurre" value="O">
      <label class="form-check-label" for="ocurre">Ocurre</label>    
  </td>
      ' : '');
  $retorno .= '
  <td>
    <input style="font-size: 12px;" class="form-control" type="date" name="fechaEnvio" id="fechaEnvio" value="' . date("Y-m-d") . '" required>
  </td>';


//Select de la entrega a ocurre o domicilio
$selectdom =
' <select style="font-size: 12px;" class="form-control"  name="paqueteriaDom" id="paqueteriaDom">';
/* $sql = 'SELECT * FROM paqueterias_sucursales WHERE ps_sucursal IN (SELECT DISTINCT(pc_sucursal) FROM paqueterias_cobertura WHERE pc_plaza IN (SELECT pc_plaza FROM paqueterias_cobertura WHERE pc_cp = "'.$cp.'")) AND ps_ocurre = "1"'; */
$sqldom = 'SELECT * FROM paqueterias WHERE p_estatus = "A" AND p_adomicilio = 1';
$resultdom = setq($sqldom);
if ($resultdom->num_rows < 1) {
$selectdom .= '<option value="">SIN RESULTADOS</option>';
} else {
while ($rowdom = $resultdom->fetch_array()) {
  if ($paq == $rowdom['p_id']) {
    $indicador = ' selected';
  } else {
    $indicador = '';
  }
  $selectdom .= '<option value="' . $rowdom['p_id'] . '" ' . $indicador . '>' . $rowdom['p_nmb'] . '</option>';
}
}
$selectdom .= '</select>';

$selectp =
' <select hidden style="font-size: 12px;" class="form-control" onchange="paqueteriaOcurre2();" name="paqueteriaOcurre" id="paqueteriaOcurre">';
/* $sql = 'SELECT * FROM paqueterias_sucursales WHERE ps_sucursal IN (SELECT DISTINCT(pc_sucursal) FROM paqueterias_cobertura WHERE pc_plaza IN (SELECT pc_plaza FROM paqueterias_cobertura WHERE pc_cp = "'.$cp.'")) AND ps_ocurre = "1"'; */
$sqlp = 'SELECT * FROM paqueterias WHERE p_estatus = "A" AND p_ocurre = 1';
$resultp = setq($sqlp);
if ($resultp->num_rows < 1) {
$selectp .= '<option value="">SIN RESULTADOS</option>';
} else {
while ($rowp = $resultp->fetch_array()) {
  if ($paq == $rowp['p_id']) {
    $indicador = ' selected';
  } else {
    $indicador = '';
  }
  $selectp .= '<option value="' . $rowp['p_id'] . '" ' . $indicador . '>' . $rowp['p_nmb'] . '</option>';
}
}
$selectp .= '</select>';

$select =
' <td style="width: 200px;">';
if ($cob == "0") {
$select .= $selectp;
$select .= '
<select style="font-size: 12px;" class="form-control" hidden  name="sucursalOcurre" id="sucursalOcurre">';
/* $sql = 'SELECT * FROM paqueterias_sucursales WHERE ps_sucursal IN (SELECT DISTINCT(pc_sucursal) FROM paqueterias_cobertura WHERE pc_plaza IN (SELECT pc_plaza FROM paqueterias_cobertura WHERE pc_cp = "'.$cp.'")) AND ps_ocurre = "1"'; */
$sql = 'SELECT * FROM paqueterias_sucursales INNER JOIN paqueterias ON p_id = ps_paqueteria WHERE ps_plaza IN (SELECT DISTINCT(ps_plaza) FROM paqueterias_sucursales WHERE ps_sucursal IN (SELECT pc_sucursal FROM paqueterias_cobertura WHERE pc_cp = "' . $cp . '")) AND ps_ocurre="1" AND p_estatus = "A" AND p_ocurre = 1;';
$result = setq($sql);
if ($result->num_rows < 1) {
  $select .= '<option value="">SIN RESULTADOS</option>';
} else {
  while ($row = $result->fetch_array()) {
    if ($sucu == $row['ps_id']) {
      $indicadorS = ' selected';
    } else {
      $indicadorS = '';
    }
    $select .= '<option value="' . $row['ps_id'] . '" ' . $indicadorS . '>' . $row['ps_sucursal'] . ' - ' . $row['ps_nmb'] . '</option>';
  }
}
$select .= '</select>';
} else {
$select .= "";
}
$select .= $selectdom;
$select .= '</td>
      ';
$retorno .= $select;

//Fin del select de la entrega a ocurre o domicilio
          $retorno .= '
        </tbody>
      </table>
      </form>
    </div>
  </center>
  </div>';
    //FIn DE CHECKBOXES PARA DEFINICIÓN DE ENVÍOS
echo $retorno;

  }
  ?>
    <div class="mb-5 col-md-12">
      <label>Descripción:</label>
      <textarea id="descripcion" rows="4" name="descripcion" class="form-control" <?php echo $readon; ?>
        placeholder="Ingrese una descripción del artículo que ingresa a garantía" required="required"><?php echo $descripcion; ?></textarea>
    </div>
    <?php 
    if(!empty($motivocancelacion)){
    ?>
    <div class="mb-5 col-md-12">
      <label>Motivo de cancelación:</label>
      <textarea class="form-control" readonly><?php echo $motivocancelacion; ?></textarea>
    </div>
    <?php
    }
    ?>
    <?php if($estatus == "A" && $_GET['origen'] == "R"){?>
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
    }
      ?>
      <div class="mt-4 row">
        <?php
          $sql = 'SELECT * FROM garantias_imagenes WHERE gi_garantia = "'.$id.'" AND gi_tipo = "G"';    
          $result = setq($sql) ;
          $resultn = setq($sql) ;
          $maxn = $resultn->num_rows;
          while($row = $result->fetch_array()){
            $img = array('jpg', 'jpeg', 'png', 'webp', 'WEBP');
            if(in_array($row['gi_ext'], $img)){
              $imagen = "img/garantias/".$row['gi_nmb'].'.'.$row['gi_ext'];
            } else {
              $imagen = 'img/archivo.png';
            }
            
            ?>
              <div class="col-md-3 mb-2">
                <a target="_blank" href="<?php echo $imagen; ?>">
                  <img class="img-thumbnail" src="<?php echo $imagen; ?>" alt="<?php echo $row['gi_nmb'].'.'.$row['gi_ext']; ?>">
                </a>
                <br>
                <?php if($estatus == "A"){?>
                <button type="button" class="btn btn-danger border-secondary col-md-12" onclick="borrararchivo(<?php echo $row['gi_id'];?>, '<?php echo $tipo;?>')" role="button">
                  <i class="icon-trash2"></i> Borrar
                </button>
                <?php }?>
              </div>
            <?php
          }
        ?>
      </div>

    <?php if($estatus == "A" && $_GET['origen'] == "R"){?>
    <div class="mb-5 col-md-12">
      <center>
      <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> <?php echo $txtBtn; ?>
      &nbsp; &nbsp; 
      <button type="button" class="btn btn-warning" onclick="sendprod(<?php echo $id; ?>);"><i class="fas fa-paper-plane"></i> Enviar a producción
      </center>
    </div>
    <?php } else if($estatus == "P" && $_GET['origen'] == "P"){
    ?>
    <div class="mb-5 col-md-12">
      <center>
      <button type="button" class="btn btn-primary" onclick="aprobarprod(<?php echo $id; ?>);"><i class="fas fa-check"></i> Aceptar producto
      &nbsp; &nbsp; 
      <button type="button" class="btn btn-danger" onclick="cancelarprod(<?php echo $id; ?>);"><i class="far fa-times-circle"></i> No aceptar
      </center>
      <input type="hidden" id="motivocancela" name="motivocancela" value="">
    </div>
    <?php
    } else if($estatus == "E" && $_GET['origen'] == "P"){
      ?>
    <div class="mb-5 col-md-12">
      <center>
      <button type="button" class="btn btn-primary" onclick="sendtofabrica(<?php echo $id; ?>);"><i class="fas fa-check"></i> Enviar producto a la fábrica
      </center>
    </div>
    <?php
    } else if($estatus == "R" && $_GET['origen'] == "R"){
      ?>
    <div class="mb-5 col-md-12">
      <center>
      <button type="button" class="btn btn-primary" onclick="confirmarecibido(<?php echo $id; ?>);"><i class="fas fa-check"></i> Confirmar de recibido
      </center>
    </div>
    <?php
    }
    ?>
  </form>
</div>

<script>

function paqueteriaOcurre2() {
    var idPaqueteria = document.getElementById('paqueteriaOcurre').value;
    var data = {
      idPaqueteria: idPaqueteria,
      cp: "<?php echo $cp; ?>"
    };
    $.ajax({
      url: 'query/selectsucursales.php',
      method: 'POST',
      dataType: 'json',
      data: data, // Los datos que quieres enviar
      success: function (data) {
        // La función que se ejecuta cuando la consulta AJAX es exitosa
        var select = $('#sucursalOcurre');

        // Limpia las opciones actuales en el select
        select.empty();

        if (data.length === 0 || data === 1 || data === 2) {
          // Si no hay resultados o el valor es 1 o 2, agrega una opción "Sin resultados"
          select.append($('<option></option>')
            .attr('value', '')
            .text('SIN RESULTADOS'));
        } else {
          // Si hay resultados, llena el select con las opciones obtenidas de la consulta
          $.each(data, function (key, value) {
            select.append($('<option></option>')
              .attr('value', value.id)
              .text(value.nombre));
          });
        }
      }
    });
  }

  function selectval(val){
    var paqdom = document.getElementById("paqueteriaDom");
    var paqocurre = document.getElementById("paqueteriaOcurre");
    var selsuc = document.getElementById("sucursalOcurre");
    if(val == "D"){
      paqdom.removeAttribute("hidden");
      paqdom.setAttribute("required", true);

      paqocurre.setAttribute("hidden", true);
      paqocurre.removeAttribute("required");
      selsuc.setAttribute("hidden", true);
      selsuc.removeAttribute("required");
    } else if (val == "O"){
      paqdom.setAttribute("hidden", true);
      paqdom.removeAttribute("required");
      paqocurre.removeAttribute("hidden");
      paqocurre.setAttribute("required", true);
      selsuc.removeAttribute("hidden");
      selsuc.setAttribute("required", true);
    } else{
      paqdom.setAttribute("hidden", true);
      paqdom.removeAttribute("required");
      paqocurre.setAttribute("hidden", true);
      paqocurre.removeAttribute("required");
      selsuc.setAttribute("hidden", true);
      selsuc.removeAttribute("required");
    }
  }

  function sendprod(k) {
  // Accede al elemento del formulario por su ID
  var formulario = document.getElementById("formulario");

  // Verifica si los elementos requeridos están vacíos
  var elementosRequeridos = formulario.querySelectorAll('[required]');
  var hayCamposVacios = false;

  elementosRequeridos.forEach(function(elemento) {
    if (elemento.value.trim() === '') {
      hayCamposVacios = true;
      // Puedes resaltar los campos vacíos para que el usuario los vea.
      elemento.style.border = '2px solid red';
    }
  });

  if (hayCamposVacios) {
    alert("Por favor, complete todos los campos requeridos.");
  } else {
    // Cambia el valor del atributo "action" del formulario
    formulario.action = "?modulo=<?php echo $modulo; ?>&accion=sendproduccion&id=" + k+"<?php echo $link?>";

    // Envía el formulario
    formulario.submit();
  }
}


async function cancelarprod(k) {
  // Accede al elemento del formulario por su ID
  var formulario = document.getElementById("formulario");
  var motivocancela = document.getElementById("motivocancela");

  const { value: text } = await Swal.fire({
    input: "textarea",
    inputLabel: "Message",
    inputPlaceholder: "Especifica el motivo",
    inputAttributes: {
      "aria-label": "Especifica el motivo de cancelación"
    },
    showCancelButton: true
  });

  if (text === null) {
    // El usuario presionó "Cancelar", no hagas nada.
    return;
  }

  if (text.trim() === '') {
    // El usuario no proporcionó un motivo, muestra un mensaje de error y llama la función de nuevo.
    Swal.fire("Por favor, especifique un motivo de cancelación.");
    cancelarprod(k);
    return;
  }

  // Realiza la acción solo si el campo de texto no está vacío
  motivocancela.value = text;
  // Verifica si los elementos requeridos están vacíos
  var elementosRequeridos = formulario.querySelectorAll('[required]');
  var hayCamposVacios = false;

  elementosRequeridos.forEach(function(elemento) {
    if (elemento.value.trim() === '') {
      hayCamposVacios = true;
      // Puedes resaltar los campos vacíos para que el usuario los vea.
      elemento.style.border = '2px solid red';
    }
  });

  if (hayCamposVacios) {
    alert("Por favor, complete todos los campos requeridos.");
  } else {
    // Cambia el valor del atributo "action" del formulario
    formulario.action = "?modulo=<?php echo $modulo; ?>&accion=cancelarprod&id="+k;

    // Envía el formulario
    formulario.submit();
  }
}


  function aprobarprod(k){

    // Accede al elemento del formulario por su ID
    var formulario = document.getElementById("formulario");

    // Verifica si los elementos requeridos están vacíos
    var elementosRequeridos = formulario.querySelectorAll('[required]');
    var hayCamposVacios = false;

    elementosRequeridos.forEach(function(elemento) {
      if (elemento.value.trim() === '') {
        hayCamposVacios = true;
        // Puedes resaltar los campos vacíos para que el usuario los vea.
        elemento.style.border = '2px solid red';
      }
    });

    if (hayCamposVacios) {
      alert("Por favor, complete todos los campos requeridos.");
    } else {
      // Cambia el valor del atributo "action" del formulario
      formulario.action = "?modulo=<?php echo $modulo; ?>&accion=aprobarprod&id="+k;

      // Envía el formulario
      formulario.submit();
    }

  }

  function sendtofabrica(k){
    // Accede al elemento del formulario por su ID
    var formulario = document.getElementById("formulario");

    // Verifica si los elementos requeridos están vacíos
    var elementosRequeridos = formulario.querySelectorAll('[required]');
    var hayCamposVacios = false;

    elementosRequeridos.forEach(function(elemento) {
      if (elemento.value.trim() === '') {
        hayCamposVacios = true;
        // Puedes resaltar los campos vacíos para que el usuario los vea.
        elemento.style.border = '2px solid red';
      }
    });

    if (hayCamposVacios) {
      alert("Por favor, complete todos los campos requeridos.");
    } else {
      // Cambia el valor del atributo "action" del formulario
      formulario.action = "?modulo=<?php echo $modulo; ?>&accion=sendtofabrica&id="+k;

      // Envía el formulario
      formulario.submit();
    }
  }

  function confirmarecibido(k){
    // Accede al elemento del formulario por su ID
    var formulario = document.getElementById("formulario");

    // Verifica si los elementos requeridos están vacíos
    var elementosRequeridos = formulario.querySelectorAll('[required]');
    var hayCamposVacios = false;

    elementosRequeridos.forEach(function(elemento) {
      if (elemento.value.trim() === '') {
        hayCamposVacios = true;
        // Puedes resaltar los campos vacíos para que el usuario los vea.
        elemento.style.border = '2px solid red';
      }
    });

    if (hayCamposVacios) {
      alert("Por favor, complete todos los campos requeridos.");
    } else {
      // Cambia el valor del atributo "action" del formulario
      formulario.action = "?modulo=<?php echo $modulo; ?>&accion=confirmarecibido&id="+k+"<?php echo $link?>";

      // Envía el formulario
      formulario.submit();
    }
  }

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
          url: 'query/eliminarimagenesgarantias.php',
          data: {
            id: id,
            tipo: tipo
          },
          success: function (response) {
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
          error: function (xhr, status, error) {
            // Maneja errores si ocurren (opcional)
            console.error('Error en la solicitud:', status, error);
          }
        });
      }
    });
  }

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