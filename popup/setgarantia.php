<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 1 Jul 2000 05:00:00 GMT"); // Fecha en el pasado
session_start();
//ini_set('display_errors',1);
include_once('../funciones.php');
$id = $_GET['ids'];

if(isset($_GET['tipo'])){
  $tipo = "&tipo=".$_GET['tipo'];
} else{
  $tipo = '';
}

$sqlv = 'SELECT rc_articulo, rc_modelo, rc_remision FROM remisionesc WHERE rc_id = "'.$id.'"';
/* echo $sqlv; */
$resultv = setq($sqlv);
list($articulo, $modelo, $remision) = $resultv->fetch_array();

$folio = busca($remision, 'remisiones', 'r_id', 'r_folio');
$cliente = busca($remision, 'remisiones', 'r_id', 'r_cliente');

$nmba = busca($articulo, "articulos", "a_id", "a_nmb");
$var = busca($articulo, 'articulos_variantes', 'av_modelo = "' . $modelo . '" AND av_articulo', 'COUNT(*)');
if ($var > 0) {
  $nmba .= " " . busca($articulo, 'articulos_variantes', 'av_modelo = "' . $modelo . '" AND av_articulo', 'av_nmb');
}

$nmba .= "&nbsp;&nbsp;REMISIÓN: ".$folio;

?>
<div class="container">
  <div class="col-12 col-md-12 alert alert-primary">ARTÍCULO: <?php echo $nmba; ?><br>INGRESE UNA DIRECCIÓN DEL CLIENTE</div>
  <form class="row" method="post" action="index?modulo=remisiones&accion=insertgarantia&id=<?php echo $id.$tipo."&garantia=1"; ?>"
    enctype="multipart/form-data" id="formsetgarantia">
    <input type="hidden" value="<?php echo $cliente; ?>" name="cliente" id="cliente">
    <!-- INICA FORMULARIO DE DIRECCIÓN -->
    <?php
      /* if(empty($direnvio) || $direnvio == 0){ */
        
      $idcotiza = busca($remision, 'crm_cotizaciones', 'cc_remision', 'cc_id');
      $direnvio = busca($idcotiza, 'crm_cotizaciones', 'cc_id', 'cc_direnvio');
      /* if($direnvio == 0){
        $direccion = "NO CAPTURADA";
      } else{
        $valord = 1;// SI hay una direccion registrada
        $cp = busca($direnvio, 'crm_direcciones', 'cd_id', 'cd_cp');
        $direccion = busca($direnvio, 'crm_direcciones INNER JOIN estado ON cd_estado = e_id', 'cd_id', 'CONCAT(cd_calle," ",cd_nume," ",cd_numi,", ",cd_colonia,", ",cd_municipio,", ",e_nmb,", C.P.:",cd_cp)');
      } */
    echo '<div class="row nowrap">';    
      echo '
      <!-- <div class="col-12 col-md-12 alert alert-primary" id="titulodir">Ingrese una dirección del cliente</div> -->';
        echo '
        <div id="camposdir">
        <div class="row" id="selectdir">
          <div class="col-12 col-md-6">
            <div class="mb-5">
              <label for"cp">Seleccionar dirección</label>
              <select id="direcciones" name="direcciones" onchange="cambiardir();" class="form-control">
                <option value="YYY" disabled> Seleccionar una dirección </option>';
                $i = 0;
                $sqldir = 'SELECT * FROM crm_direcciones WHERE cd_cliente = "'.$cliente.'"';
                $result = setq($sqldir);
                while($row = $result -> fetch_array()){
                  if($direnvio == $row['cd_id'] || $row['cd_predeterminada'] == "1") {
                    $seld = 'selected';
                  }else{
                    $seld = '';
                  }
                  echo '<option value="'.$row['cd_id'].'" '.$seld.' >'.$row['cd_calle'].' '.$row['cd_nume'].' '.$row['cd_numi'].' '.$row['cd_colonia'].' '.$row['cd_municipio'].' '.busca($row['cd_estado'], 'estado', 'e_id', 'e_nmb').' '.$row['cd_cp'].'</option>';
                }
              echo '
                <option value="XXX" '.$seldir.'> Nueva dirección</option>
              </select> 
            </div>
          </div>';
          if($direnvio){
            $sqlget = 'SELECT cd_cp, cd_colonia, cd_municipio, cd_estado, cd_calle, cd_nume, cd_numi, cd_pais FROM crm_direcciones WHERE cd_id ="'.$direnvio.'"';
            $resultget = setq($sqlget);
            list($cp, $colonia, $municipio, $estadoh, $calle, $nume, $numi, $pais) = $resultget->fetch_array();
            $estado =busca($estadoh, 'estado', 'e_id', 'e_nmb');
          } else {
            $cp="";
            $colonia="";
            $municipio="";
            $estadoh="";
            $estado="";
            $calle="";
            $nume="";
            $numi="";
            $pais="";
          }  
          
          if($pais == "146" || $pais == "MEXICO"){
            $checked = "";
            $hid2 = " hidden";
            $col = "4";
            $req = "required";
            $flagcountry = 0;
          } else {
            if(empty($pais)){
              $checked = "";
              $hid2 = " hidden";
              $req = "required";
              $col = "4";
              $flagcountry = 0;
            } else{
              $checked = "checked";
              $hid = " hidden";
              $hid2 = "";
              $req = "";
              $col = "8";
              $flagcountry = 1;
            }
          }
          echo '<div class="col-6 col-md-6">
            <div class="mb-5">
              <label for"correo">Observaciones de envío</label>
              <input type="text" name="observadir" maxlenght="100" id="observadir" value="'.$observadir.'" class="form-control" placeholder="Indicaciones" />
              <!-- <input type="hidden" name="cliente" value="'.$rowcc['r_cliente'].'"/> -->
              <input type="hidden" name="remision" value="'.$_GET['id'].'"/>
            </div>
          </div>
        </div>
        ';
        echo '<div class="row" id="formdir">
          <input type="hidden" id="countdir">
          <div class="col-12 col-md-3" id="divcp" '.$hid.'>
            <div class="mb-5">
              <label >Código postal</label>
              <input type="text" value="'.$cp.'" class="form-control" onchange="colonia();" id="cp" name="cp"  '.$disall.' '.$req.'>
              <div id="nameError" class="error-message"></div>
            </div>
          </div>
          <div class="col-12 col-md-5" id="divcolonia" '.$hid.'>
            <div class="mb-5">
              <label >Colonia</label>
              <select id="col" name="col" value="'.$colonia.'" class="form-control"  '.$disall.' '.$req.'>
                <option value="" disabled selected>Seleccione la colonia</option>
              </select>
              <input type="hidden" value="'.$colonia.'" name="coldesc" id="coldesc">
            </div>
          </div> 
          <div class="col-12 col-md-4" id="divestado" '.$hid.'>
            <div class="mb-5">
              <label >Estado</label>
              <input type="text" value="'.$estado.'" class="form-control" id="estado" name="estado"  '.$disall.' '.$req.'>
              <input type="hidden" value="'.$estadoh.'" class="form-control" id="estadoh" name="estadoh" >
            </div>
          </div>
          <div class="col-12 col-md-4" id="divmunicipio" '.$hid.'>
            <div class="mb-5">
              <label >Municipio</label>
              <select id="municipio" value = "'.$municipio.'" name="municipio" class="form-control"  '.$disall.' '.$req.'>
                <option value="" disabled selected>Seleccione el municipio</option>
              </select>
              <input type="hidden" value="'.$municipio.'" name="munidesc" id="munidesc">
            </div>
          </div>
          <div class="col-12 col-md-'.$col.'" id="divcalle">
            <div class="mb-5">
              <label id="lblcalle">Calle</label>
              <input type="text" value="'.$calle.'" class="form-control" id="calle" name="calle"  '.$disall.' required>
            </div>
          </div>
          <div class="col-12 col-md-4" id="divpais" '.$hid2.'>
          <label id="lblpais">País</label>
            <select class="form-control" id="pais" name="pais">';
              $sqlpais = 'SELECT * FROM paises ORDER BY p_id ASC;';
              $resultpais = setq($sqlpais);
              if($resultpais->num_rows == 0){
                echo '<option value="">No hay elementos disponibles</option>';
              } else{
                while($rowpais = $resultpais->fetch_array()){
                  echo '<option value="'.$rowpais['p_id'].'">'.$rowpais['p_nmb'].'</option>';
                }
              }
            echo '</select>
          </div>
          <div class="col-12 col-md-2" id="divnume" '.$hid.'>
            <div class="mb-5">
              <label >Número exterior</label>
              <input type="text" value="'.$nume.'" class="form-control" id="exterior" name="exterior"  '.$disall.'>
            </div>
          </div>
          <div class="col-12 col-md-2" id="divnumi" '.$hid.'>
            <div class="mb-5">
              <label >Número interior</label>
              <input type="text" value="'.$numi.'" class="form-control" id="interior" name="interior" '.$disall.'>
            </div>
          </div>
          <div class="col-12 col-md-12">
          <div class="col-md-2 col-2">
            <label id="lblestatusp">Envío al extranjero</label>
              <input class="flipswitch form-control" type="checkbox" onchange="envioextranjero();" name="estatusp" id="estatusp" '.$checked.'>
              </div>
          </div>
        </div>
        </div>';
      echo '<div class="col-12 col-md-12" id="btnsave">
      </div>
    </div>
        ';

        //FINALIZA FORMULARIO DE DIRECCIÓN
        ?>

        
    <div class="mb-5 col-md-12">
      <label>Descripción:</label>
      <textarea id="descripcion" rows="4" name="descripcion" class="form-control"
        placeholder="Ingrese una descripción del artículo que ingresa a garantía" required="required"></textarea>
    </div>

    <!-- INICIA COSTO DE ENVÍO -->
    <?php
        if ($flagcountry == 1) {
          $moneda = 'USD$';
          $tamanio = 6;
          $text = " (USD)";
          $hidd = "";
          $reqq = "required";
        } else {
          $moneda = 'MXN$';
          $tamanio = 12;
          $text = "";
          $hidd = "hidden";
          $reqq = "";
        }
        ?>
        <div class="col-<?php echo $tamanio; ?> col-md-<?php echo $tamanio; ?>" id="divprecio">
          <h2><label for="precio" id="lblprecio">Costo de reparación<?php echo $text; ?></label></h2>
          <div class="input-group mb-3">
              <div class="input-group-prepend">
                  <span class="input-group-text" id="spnprecio"><?php echo $moneda; ?></span>
              </div>
              <input type="number" name="precio" min="0" step="0.01" id="precio" class="form-control" placeholder="Costo del envío de la reparación" required="required"/>
          </div>
      </div>
      <div class="col-6 col-md-6" id="divtcambio" <?php echo $hidd; ?>>
          <h2><label for="precio">Tipo de cambio</label></h2>
          <div class="input-group mb-3">
              <div class="input-group-prepend">
                  <span class="input-group-text">MXN$</span>
              </div>
              <input type="number" name="tcambio" min="0" step="0.01" id="tcambio" class="form-control" placeholder="Tipo de cambio de la moneda" <?php echo $reqq; ?>/>
          </div>
      </div>
        <!-- FINALIZA COSTO DE ENVÍO-->

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

    <div class="mb-5 col-md-12">
      <center><button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Guardar</center>
    </div>
  </form>
  </div>

<script>

  //Inician funciones de la subida de archivos
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
          url: 'query/eliminarimagenes.php',
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

  //Finaliza funciones de la subida de archivos



//Funciones de la dirección
  function cambiardescuento(num){
  var pordiv = document.getElementById('porcentajediv');
  var montodiv = document.getElementById('montodiv');
  var btnporcentaje = document.getElementById('btnporcentaje');
  var btnmonto = document.getElementById('btnmonto');
  var descuento = document.getElementById('descuento');
  var monto = document.getElementById('impdescuento');

  if(num == 0){
    btnporcentaje.hidden = true;
    btnmonto.hidden = false;
    pordiv.hidden = false;
    montodiv.hidden = true;
    monto.value="0";
  } else {
    btnporcentaje.hidden = false;
    btnmonto.hidden = true;
    pordiv.hidden = true;
    montodiv.hidden = false;
    monto.value="0";
  }
}
function colonia(){
  var selcol = document.getElementById("col");
  var coldesc = document.getElementById("coldesc").value;
  var estado = document.getElementById("estado");
  var estadoh = document.getElementById("estadoh");
  var cp = document.getElementById("cp");
  var municipio = document.getElementById("municipio");
  var munidesc = document.getElementById("munidesc").value;
  var nameError = document.getElementById("nameError");

  for (let i = selcol.options.length - 1; i > 0; i--) {
    selcol.remove(i);
  }
  selcol.value ="";
  for (let i = municipio.options.length - 1; i > 0; i--) {
    municipio.remove(i);
  }
  municipio.value ="";
  estado.value = "";
  estado.disabled = false;
  estadoh.value = "";
  if(cp.value.length == 5){
    $.ajax({
      type: "POST",
      url: "query/getcolonias.php",
      data: {"cp": cp.value},
      success: function(data) {
        
        var datax = JSON.parse(data);
        if(data == '{"cobertura":[null]}'){
          nameError.innerHTML = "El código postal ingresado no existe.";
        } else {
          nameError.innerHTML = "";
        }
        for(var i = 0; i<datax['colonias'].length; i++){
          const nuevaOpcion = document.createElement('option');
          nuevaOpcion.value = datax['colonias'][i]; 
          nuevaOpcion.text = datax['colonias'][i];
          selcol.append(nuevaOpcion);
        }
        for(var i = 0; i<datax['municipios'].length; i++){
          const nuevaOpcion = document.createElement('option');
          nuevaOpcion.value = datax['municipios'][i]; 
          nuevaOpcion.text = datax['municipios'][i]; 
          municipio.append(nuevaOpcion);
          if(datax['municipios'][i] == munidesc){
            nuevaOpcion.selected = true;
          } 
        }
        if(coldesc != ""){
          selcol.value = coldesc;
        } else {
          selcol.value = datax['colonias'][0];
        }
        if(munidesc != ""){
          municipio.value = munidesc;
        } else {
          municipio.value = datax['municipios'][0];
        }
        
        estado.value = datax['estados'][0];
        estadoh.value = datax['estadosid'][0];
        estado.disabled = true;
      }
    });
  }
}
colonia();
function cambiardir(){
  var seldir = document.getElementById("direcciones");
  var selcol = document.getElementById("col");
  var estado = document.getElementById("estado");
  var estadoh = document.getElementById("estadoh");
  var cp = document.getElementById("cp");
  var municipio = document.getElementById("municipio");
  var nume = document.getElementById("exterior");
  var numi = document.getElementById("interior");
  var nameError = document.getElementById("nameError");
  var munidesc = document.getElementById("munidesc");
  var coldesc = document.getElementById("coldesc");
  var calle = document.getElementById("calle");
  if(seldir.value == ""){
    for (let i = selcol.options.length - 1; i > 0; i--) {
      selcol.remove(i);
    }
    selcol.value ="";
    for (let i = municipio.options.length - 1; i > 0; i--) {
      municipio.remove(i);
    }
    cp.value="";
    municipio.value ="";
    estado.value="";
    estadoh.value="";
    
    municipio.value="";
    nume.value="";
    calle.value="";
    numi.value="";
    estado.disabled = false;
    cp.focus();
  } else {
    $.ajax({
      type: "POST",
      url: "query/getdireccion.php",
      data: {"direccion": seldir.value},
      success: function(data) {
        var datax = JSON.parse(data);
        console.log(datax);
        munidesc.value = datax['municipio'];
        coldesc.value = datax['colonia'];
        cp.value = datax['cp'];
        calle.value = datax['calle'];
        numi.value = datax['numi'];
        nume.value = datax['nume'];
        pais.value = datax['pais'];
        colonia();
        if(pais.value != "146"){
          envioextranjero2(true, pais.value);
        } else{
          envioextranjero2(false, pais.value);
        }
      }
    });
  }
}  
function guardarform(){
/*   var form = document.getElementById("setcotiza");
  var elements = form.elements;

  for (var i = 0; i < elements.length; i++) {
    if (!elements[i].checkValidity()) {
      alert("Error al capturar sus datos, verifique su información para continuar.");
      elements[i].focus();
      event.preventDefault(); 
      return;
    }
  }

  form.submit();
*/
}
function setdireccion(tipo = true){
  /* var check = document.getElementById('setdir'); */
  var campos = document.getElementById('camposdir');
  var seldir = document.getElementById("direcciones");
  var titulodir = document.getElementById("titulodir");
  var btnsave = document.getElementById("btnsave");

  campos.hidden = tipo;
  titulodir.hidden = tipo;
  btnsave.hidden = tipo;
}

function envioextranjero(){
  var estatusp = document.getElementById("estatusp");

  var selcol = document.getElementById("col");
  var estado = document.getElementById("estado");
  var cp = document.getElementById("cp");
  var municipio = document.getElementById("municipio");
  var nume = document.getElementById("exterior");
  var numi = document.getElementById("interior");
  var lblcalle = document.getElementById("lblcalle");
  var calle = document.getElementById("calle");
  var pais = document.getElementById("pais");
  
  var divpais = document.getElementById("divpais");
  var divcol = document.getElementById("divcolonia");
  var divestado = document.getElementById("divestado");
  var divcp = document.getElementById("divcp");
  var divmunicipio = document.getElementById("divmunicipio");
  var divnume = document.getElementById("divnume");
  var divnumi = document.getElementById("divnumi");

  var divtcambio = document.getElementById("divtcambio");
  var divprecio = document.getElementById("divprecio");
  var lblprecio = document.getElementById("lblprecio");
  var spnprecio = document.getElementById("spnprecio");
  var tcambio = document.getElementById("tcambio");

  if(estatusp.checked){
    divtcambio.removeAttribute("hidden");
    divprecio.classList.remove("col-md-12");
    divprecio.classList.add("col-md-6");
    lblprecio.innerHTML = "Costo de reparación (USD)";
    spnprecio.innerHTML = "USD$";
    tcambio.setAttribute("required", true);

    selcol.removeAttribute("required");
    estado.removeAttribute("required");
    cp.removeAttribute("required");
    municipio.removeAttribute("required");
    nume.removeAttribute("required");
    numi.removeAttribute("required");
    pais.setAttribute("required", true);
    pais.value = "75";

    divcol.setAttribute("hidden", true);
    divestado.setAttribute("hidden", true);
    divcp.setAttribute("hidden", true);
    divmunicipio.setAttribute("hidden", true);
    divnume.setAttribute("hidden", true);
    divnumi.setAttribute("hidden", true);
    divpais.removeAttribute("hidden");

    divcalle.classList.remove("col-md-4");
    divcalle.classList.add("col-md-8");

    //calle.removeAttribute("required");
    lblcalle.innerHTML = "Dirección: ";
  } else{
    divtcambio.setAttribute("hidden", true);
    divprecio.classList.remove("col-md-6");
    divprecio.classList.add("col-md-12");
    lblprecio.innerHTML = "Costo de reparación (MXN)";
    spnprecio.innerHTML = "MXN$";
    tcambio.removeAttribute("required");

    selcol.setAttribute("required", true);
    estado.setAttribute("required", true);
    cp.setAttribute("required", true);
    municipio.setAttribute("required", true);
    nume.setAttribute("required", true);
    numi.setAttribute("required", true);
    pais.removeAttribute("required");
    //calle.removeAttribute("required");
    lblcalle.innerHTML = "Calle: ";

    divcol.removeAttribute("hidden");
    divestado.removeAttribute("hidden");
    divcp.removeAttribute("hidden");
    divmunicipio.removeAttribute("hidden");
    divnume.removeAttribute("hidden");
    divnumi.removeAttribute("hidden");
    divpais.setAttribute("hidden", true);

    divcalle.classList.remove("col-md-8");
    divcalle.classList.add("col-md-4");
  }
}

function envioextranjero2(variable, paisx){
  var estatusp = document.getElementById("estatusp");

  var selcol = document.getElementById("col");
  var estado = document.getElementById("estado");
  var cp = document.getElementById("cp");
  var municipio = document.getElementById("municipio");
  var nume = document.getElementById("exterior");
  var numi = document.getElementById("interior");
  var lblcalle = document.getElementById("lblcalle");
  var calle = document.getElementById("calle");
  var pais = document.getElementById("pais");
  
  var divpais = document.getElementById("divpais");
  var divcol = document.getElementById("divcolonia");
  var divestado = document.getElementById("divestado");
  var divcp = document.getElementById("divcp");
  var divmunicipio = document.getElementById("divmunicipio");
  var divnume = document.getElementById("divnume");
  var divnumi = document.getElementById("divnumi");

  if(variable){
    selcol.removeAttribute("required");
    estado.removeAttribute("required");
    cp.removeAttribute("required");
    municipio.removeAttribute("required");
    nume.removeAttribute("required");
    numi.removeAttribute("required");
    pais.setAttribute("required", true);
    pais.value = paisx;

    divcol.setAttribute("hidden", true);
    divestado.setAttribute("hidden", true);
    divcp.setAttribute("hidden", true);
    divmunicipio.setAttribute("hidden", true);
    divnume.setAttribute("hidden", true);
    divnumi.setAttribute("hidden", true);
    divpais.removeAttribute("hidden");

    divcalle.classList.remove("col-md-4");
    divcalle.classList.add("col-md-8");

    //calle.removeAttribute("required");
    lblcalle.innerHTML = "Dirección: ";
    estatusp.checked = true;
  } else{
    selcol.setAttribute("required", true);
    estado.setAttribute("required", true);
    cp.setAttribute("required", true);
    municipio.setAttribute("required", true);
    nume.setAttribute("required", true);
    numi.setAttribute("required", true);
    pais.removeAttribute("required");
    //calle.removeAttribute("required");
    lblcalle.innerHTML = "Calle: ";
    pais.value = paisx;

    divcol.removeAttribute("hidden");
    divestado.removeAttribute("hidden");
    divcp.removeAttribute("hidden");
    divmunicipio.removeAttribute("hidden");
    divnume.removeAttribute("hidden");
    divnumi.removeAttribute("hidden");
    divpais.setAttribute("hidden", true);

    divcalle.classList.remove("col-md-8");
    divcalle.classList.add("col-md-4");
    estatusp.checked = false;
  }
}

//setdireccion(true); //Quitamos el atributo hidden

// FINALIZA FUNCIONES DE LA DIRECCION
</script>