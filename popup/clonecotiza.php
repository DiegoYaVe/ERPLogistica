<?php
session_start();
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
function desglose(){
  chtotal = document.getElementById("total");
  chiva = document.getElementById("iva");
  if(chtotal.checked){
    chiva.removeAttribute("disabled");
    chiva.removeAttribute("style");
    document.getElementById("descuento").value = 0;
    chiva.checked = false;
  }else {
    $("#iva").prop("disabled","true");
    $("#iva").prop("style","background:grey");
    chiva.checked = false;
    document.getElementById("descuento").value = 0;
  }
}
function setcliente(){
  var client = document.getElementById("setclone").value;
  if(client == "C"){
    document.getElementById("iddestino").style.display = "";
    document.getElementById("idtelefono").style.display = "";
    document.getElementById("idcorreo").style.display = "";
    document.getElementById("idcliente").style.display = "none";
    document.getElementById("idtablero").style.display = "none";
    document.getElementById("idfinal").style.display = "none";
    document.getElementById("tab").style.display = "none";
  }
  else{
    document.getElementById("iddestino").style.display = "none";
    document.getElementById("idtelefono").style.display = "none";
    document.getElementById("idcorreo").style.display = "none";
    document.getElementById("idcliente").style.display = "";
    document.getElementById("cliente").focus();
    document.getElementById("idtablero").style.display = "";
    document.getElementById("idfinal").style.display = "";
    document.getElementById("tab").style.display = "";
  }
}
</script>
    <script>
    $(document).ready(function() {
        $('#cliente').on('keyup', function() {
          var key = $(this).val();
//          var empresa = $('#empresa').val();
          var dataString = 'cliente='+key;
        $.ajax({
          type: "POST",
          url: "query/suggestclientes.php",
          data: dataString,
          success: function(data) {
            //Escribimos las sugerencias que nos manda la consulta
            $('#suggestions-block').fadeIn(1000).html(data);
            //Al hacer click en alguna de las sugerencias
            $('.suggest-element').on('click', function(){
              //Obtenemos la id unica de la sugerencia pulsada
              var id = $(this).attr('id');
              //Editamos el valor del input con data de la sugerencia pulsada
              $('#cliente').val($('#'+id).attr('data'));
              //Hacemos desaparecer el resto de sugerencias
              $('#suggestions-block').fadeOut(1000);
              buscartab();
//              alert('Has seleccionado el '+id+' '+$('#'+id).attr('data'));
              return false;
            });
          }
        });
      });
    });
    </script>

<?php
//session_start();
include_once('../funciones.php');
$tablero = $_GET['tablero'];
include_once('../modulos/cotizaciones.php');
$mcotiza = new modelcotizaciones();
$mcotiza->select($_GET['idcotiza']);


  if(isset($_GET['idcotiza'])) $_GET['idcotiza'] = "0";

  $diva = ' checked="checked" ';
  $dtotal = ' checked="checked" ';
  if($mcotiza->id){

    $nmbcliente = $mcotiza->destino;
    $telcliente = $mcotiza->teldestino;
    $mailcliente = $mcotiza->maildestino;
    $ffin = date('Y-m-d',strtotime($mcotiza->ffin.' + 15 days'));
    $nmbact = $mcotiza->nmb.' CLON';
    $responsable = $mcotiza->responsable;

    if($mcotiza->diva == 0) $diva = '  ';
    //if($mcotiza->mtotal == 0) $dtotal = ' ';
    if($mcotiza->mtotal == 0) {
      $dtotal = '';
      $disablediva = 'disabled';
      $style = 'style="background:grey"';
    }
    $accion = 'clonar&idcotiza='.$mcotiza->id;
    $title = "Clonar";
  }


echo'

<div class="container mt-1 col-10">
  <form action="?modulo=cotizaciones&accion='.$accion.'&tablero='.$tablero.'" method="post" onsubmit="checksubmit();">
    <div class="row">
      <div class="btn-group" data-toggle="buttons">';
echo '</div>
    </div>';

  echo '
    <div class="row">
      <div class="col-12 col-md-12 alert alert-primary">Destino</div>
      <div class="col-12 col-md-3">
        <div class="mb-5">
          <label for"destino">Elegir cliente</label>
          <select name="setclone" id="setclone" requiered class="form-control" onchange="setcliente();">
            <option value="C">Mismo cliente</option>
            <option value="O">Otro cliente</option>
          </select>
        </div>
      </div>
      <div class="col-12 col-md-3" id="iddestino">
        <div class="mb-5">
          <label for"destino">Dirigido a</label>
          <input type="text" name="destino" id="destino" value="'.$nmbcliente.'" class="form-control" placeholder="Persona para contactar"  />
        </div>
      </div>
      <div class="col-6 col-md-3" id="idtelefono">
        <div class="mb-5">
          <label for"telefono">Número telefónico</label>
          <input type="tel" name="telefono" id="telefono" value="'.$telcliente.'" class="form-control" placeholder="Telefóno del contacto" />
        </div>
      </div>
      <div class="col-6 col-md-3" id="idcorreo">
        <div class="mb-5">
          <label for"correo">Correo destino</label>
          <input type="email" style="text-transform:lowercase;" name="correo" id="correo" value="'.$mailcliente.'" class="form-control" placeholder="correo de la contacto" />
        </div>
      </div>';
    //Div para nuevo tablero
    echo '<div class="col-6 col-md-3" id="idcliente" style="display:none;">
            <label>Cliente: </label>
            <div class="mb-5">
              <input type="text" name="cliente" id="cliente" placeholder="Nombre o alias del cliente" class="search_query form-control"  onchange="buscartab();">
            </div>
            <div id="suggestions-block"></div>
          </div>
          <div class="col-md-2" id="tab" style="display:none;">
            <label>Tablero</label>
            <select name="tablero" id="tablero" class="form-control">
              <option value="">Nuevo Tablero</option>
            </select>
          </div>          
          <div class="col-6 col-md-2" id="idtablero" style="display:none;">
            <label>Nombre proyecto: </label>
            <div class="mb-5">
              <input type="text" name="nmbt" id="nmbt" placeholder="Nombre del tablero/proyecto" class="form-control" onfocus="this.select();" >
            </div>
          </div>
          <div class="col-6 col-md-2" id="idfinal" style="display:none;">
          <label>Fecha inicio tablero: </label>
            <div class="mb-5">
              <div class="position-relative has-icon-left">
                <input type="datetime-local" id="finit" name="finit" class="form-control" value="'.date('Y-m-d').'T'.date('H:i:s').'" step="any" >
                <div class="form-control-position">
                  <i class="icon-calendar5"></i>
                </div>
              </div>
            </div>
          </div>
          ';
    echo '<div class="col-12 col-md-12 alert alert-primary">'.$title.' cotización</div>';
    echo '<div class="col-12 col-md-3 ">
        <div class="mb-5">
          <label for"nmbac">Alias de tu cotización</label>
          <input type="text" name="nmbac" maxlength="50" value="'.$nmbact.'" id="nmbac" class="form-control" placeholder="Nombre de tu actividad" required="required" onfocus="this.select();" />
        </div>
      </div>
    <!-- 
      <div class="col-12 col-md-2" >
        <div class="mb-5">
          <label for"fini">Vigencia</label>
          <input type="date" name="ffin" id="ffin" value="'.$ffin.'" class="form-control" placeholder="Cuando programas tu actividad" required="required" />
        </div>
      </div> -->
      <div class="col-12 col-md-2" >
        <div class="mb-5">
          <label for"fini">Mostrar total</label>
          <input type="checkbox" name="total" id="total" '.$dtotal.' class="flipswitchsn" onchange="desglose();" />
        </div>
      </div>
      <div class="col-12 col-md-2" >
        <div class="mb-5">
          <label for"fini">Desglosar IVA</label>
          <input type="checkbox" name="iva" id="iva" '.$diva.' '.$disablediva.' '.$style.' class="flipswitchsn" />
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="mb-5">
          <label for"correo">Responsable</label>
          '.menu_select_db('usuarios','u_id','u_nmb',$responsable,'responsable','u_estatus = "A" AND u_empresa = "'.$_SESSION['emp'].'"',false,false,false,true,"Responsable").'
        </div>
      </div>
      <div class="col-12 col-md-12">
        <div class="mb-5">
          <label for"correo">Descripción de la cotización</label>
          <textarea class="form-control" name="descripcion" rows="2" >'.$descripcion.'</textarea>
        </div>
      </div>

      <div class="col-12 col-md-12">
        <center><button role="submit" class="btn btn-primary" id="guardar"><i class="far fa-clone" style="color: #ffffff;"></i> Clonar</button></center>
      </div>
    </div>
  </form>
</div>


<script>
  function buscartab(){
    var id = document.getElementById("cliente").value;
    $.ajax({
      url: "query/buscartab.php",
      method: "POST",
      data: {"cliente": id,},
    }).success(function(data){
      $("#tablero").html(data);
    });
  }
</script>';
?>
