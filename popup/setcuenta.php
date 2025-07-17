<?php
//ini_set('display_errors', 1);
  session_start();
  include_once('../funciones.php');
  
  //die(!isset($_GET['id']));
  if(!isset($_GET['id'])) $_GET['id'] = NULL;
  include_once('../modulos/cuentas.php');
  $mcuenta = new modelcuentas();
  $mcuenta->select($_GET['id']);
  if($mcuenta->id){
    $accion = 'update&id='.$_GET['id'];
    $title = "Registrar una ";
    $btn = "Guardar";
  }
  else{
    $accion = 'insert';
    $title = "Editar información de la";
    $btn = "Crear";
  }
  $tipocta = array("E"=>"EFECTIVO","D"=>"DEBITO","C"=>"CREDITO","A"=>"AHORRO");
  $disabled = "";

  if(!$mcuenta->tipo) $mcuenta->tipo = "E";
  if($mcuenta->tipo == "E" || $mcuenta->tipo == "A"){
    $styledisplay = "none";
    //$disabled = "disabled";
  }else{ 
    $styledisplay = "";
    //$disabled = "";
  }


  if($mcuenta->enviacotiza == "1") $checkenviar = "checked"; else $checkenviar = "";
  if($mcuenta->caja == "1") $checkcaja = "checked"; else $checkcaja = "";

?>
    <script>
      function setnumcta(){
        var tipocta = document.getElementById("tipo").value;
        //console.log("entra2");
        if(tipocta == "D" || tipocta == "C"){
          document.getElementById("divcuenta").style.display = "";
          document.getElementById("divclabe").style.display = "";
          document.getElementById("divbanco").style.display = "";
          document.getElementById("divtarjeta").style.display = "";
          document.getElementById("divcotiza").style.display = "";
          document.getElementById("divcaja").style.display = "";

          $("#clabe").attr('required',true);
          $("#banco").attr('required',true);
          $("#cuentapop").attr('required',true);
          $("#numtarjeta").attr('required',true);

        }
        else{
          document.getElementById("divcuenta").style.display = "none";
          document.getElementById("divclabe").style.display = "none";
          document.getElementById("divbanco").style.display = "none";
          document.getElementById("divtarjeta").style.display = "none";
          document.getElementById("divcotiza").style.display = "none";
          document.getElementById("divcaja").style.display = "none";

          $("#clabe").attr('required',false);
          $("#banco").attr('required',false);
          $("#cuentapop").attr('required',false);
          $("#numtarjeta").attr('required',false);

        }
      }
    </script>
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
    </script>
    <?php
echo'
<div class="container mt-1 col-9">
  <form action="?modulo=cuentas&accion='.$accion.'" method="post" onsubmit="checksubmit();">
    <div class="row">
      <div class="col-12 col-md-12 alert alert-primary">'.$title.' Cuenta</div>
      <div class="btn-group" data-bs-toggle="buttons">';
echo '</div>
    </div>';
  echo '
    <div class="row">
      <div class="col-12 col-md-4">
        <div class="mb-5">
          <label for"nmbac">Nombre de la cuenta</label>
          <input type="text" name="nmb" maxlength="50" value="'.$mcuenta->nmb.'" id="nmb" class="form-control" placeholder="Nombre de la cuenta" required="required" onfocus="this.select();" />
        </div>
      </div>
      <div class="col-12 col-md-4" >
        <div class="mb-5">
          <label for"fini">Responsable de la cuenta</label>
          <input type="text" name="propietario" maxlength="50" value="'.$mcuenta->propietario.'" id="nmb" class="form-control" placeholder="Propietario de la cuenta" required="required" onfocus="this.select();" />
        </div>
      </div>
      <div class="col-6 col-md-2">
        <div class="mb-5">
          <label for"correo">Tipo de cuenta</label>
          <select name="tipo" id="tipo" onchange="setnumcta();" class="form-control">';
        foreach($tipocta as $cta => $nmb){
          if($mcuenta->tipo == $cta) $sele = 'selected'; else $sele = "";
          echo '<option value="'.$cta.'" '.$sele.'>'.$nmb.'</option>';
        }
        echo '</select>
        </div>
      </div>
      <div class="col-6 col-md-2">
        <div class="mb-5">
          <label for"fini">Limite de uso <i class="icon-exclamation-circle"  data-toggle="tooltip" data-placement="top" title="Si es una cuenta de efectivo o débito y no tiene límite, solo pon 0, en caso de ser de crédito establece el límite de operación" data-original-title="Usar el buscador"></i></label>
          <input type="number" name="limite" min="0" max="9999999999" step="0.01" value="'.$mcuenta->limite.'" id="limite" class="form-control" placeholder="Limite de uso" required="required" onfocus="this.select();" />
        </div>
      </div>
      <div class="col-12 col-md-3" id="divclabe" style="display:'.$styledisplay.';" >
        <div class="mb-5">
          <label for"fini" id="labelclabe">CLABE</label>
          <input type="text" name="clabe" onkeyup="checkclabe();" pattern="[0-9]+" maxlength="18" minlenght="18" value="'.$mcuenta->clabe.'" id="clabe" class="form-control" placeholder="CLABE BANCARIA" onfocus="this.select();" '.$disabled.' />
        </div>
      </div>
      <div class="col-12 col-md-3"  id="divbanco"  style="display:'.$styledisplay.';" >
        <div class="mb-5">
          <label for"fini">Institución Bancaría</label>
          <input type="text" name="banco" maxlength="50" value="'.$mcuenta->banco.'" id="banco" class="form-control" placeholder="NOMBRE DEL BANCO" onfocus="this.select();" readonly '.$disabled.' />
        </div>
      </div>
      <div class="col-12 col-md-2" id="divcuenta" style="display:'.$styledisplay.';" >
        <div class="mb-5">
          <label for"nmbac">Número de cuenta</label>
          <input type="text" name="cuenta" pattern="[0-9]+" maxlength="11" value="'.$mcuenta->cuenta.'" id="cuentapop" class="form-control" placeholder="Número de cuenta" onfocus="this.select();" '.$disabled.' />
        </div>
      </div>
      <div class="col-12 col-md-3" id="divtarjeta" style="display:'.$styledisplay.';">
        <div class="mb-5">
          <label for"tj" id="tjlabel">Número de Tarjeta</label>
          <input type="text" pattern="[0-9]+" onkeyup="validacard();" maxlength="19" name="numtarjeta"  value="'.$mcuenta->numtarjeta.'" id="numtarjeta" class="form-control" placeholder="NÚMERO DE TARJETA" onfocus="this.select();" '.$disabled.' />
        </div>
      </div>
      <div class="col-6 col-md-2" id="divcotiza" style="display:'.$styledisplay.';">
        <div class="mb-5">
          <label for"fini"><i class="icon-exclamation-circle"  data-toggle="tooltip" data-placement="top" title="Enviar cuenta automáticamente en cotizaciones" data-original-title="Usar el buscador"></i>Para cotizaciones </label>
          <center>
            <input type="checkbox" class="form-control flipswitch2" name="enviacotiza" id="enviacotiza" placeholder="Envio automático" '.$checkenviar.' '.$disabled.' />
          </center>
        </div>
      </div>
      <div class="col-6 col-md-2" id="divcaja" style="display:'.$styledisplay.';">
        <div class="mb-5">
          <label for"caja"><i class="icon-exclamation-circle"  data-toggle="tooltip" data-placement="top" title="Cuenta para transferencias automáticas en caja" ></i>Terminal de caja </label>
          <center>
            <input type="checkbox" class="form-control flipswitch2" name="caja" id="caja" placeholder="Envio automático" '.$checkcaja.' '.$disabled.' />
          </center>
        </div>
      </div>

      <div class="col-12 col-md-12">
        <center><button type="submit" class="btn btn-primary" id="guardar"><i class="fas fa-university" style="color: #ffffff;"></i> '.$btn.' cuenta</button></center>
      </div>
    </div>
  </form>
</div>';

echo '<script src=https://cdn.jsdelivr.net/npm/clabe-validator@2.0/dist/clabe.min.js></script>';
//echo '<script src="assets/js/js_index.js"></script>';
?>

<script>


function checkclabe(){
  var valor = document.getElementById('clabe');
  var boton = document.getElementById("guardar");
  var label = document.getElementById("labelclabe");
  var cuentaa = document.getElementById("cuentapop");
  var nmbb = document.getElementById("banco");
  const clabeNum = valor.value;
  const clabeCheck = clabe.validate(clabeNum);
  if(clabeCheck.ok){
    nmbb.value = clabeCheck.tag.toUpperCase();
    cuentaa.value = clabeCheck.account;
    label.innerHTML = 'CLABE';
    label.style="color: black";
    valor.style="border-color: green;";
    $("#guardar").attr('disabled',false);
    $("#cuentapop").attr('readonly',true);
    
    if(document.getElementById("error")){
      document.getElementById("error").remove();
    }
  }else{
    nmbb.value = "";
    cuentaa.value = "";
    $("#cuentapop").attr('readonly',false);
    valor.style="border-color: red;";
    $("#guardar").attr('disabled',true);
    label.innerHTML = ' Erorr de CLABE. Verificar';
    label.style="color: red";
  }
}

function validacard(){
  var tarjeta = document.getElementById("numtarjeta");
  var label = document.getElementById("tjlabel");
  //console.log(card.validCard(tarjeta.value)); 
  if(card.validCard(tarjeta.value)){
    label.innerHTML = 'Número de Tarjeta';
    label.style="color: black";
    tarjeta.style="border-color: green;";
    $("#guardar").attr('disabled',false);
  }else{
    tarjeta.style="border-color: red;";
    $("#guardar").attr('disabled',true);
    label.innerHTML = 'Error. Verificar tarjeta.';
    label.style="color: red";

  }
    
}

function pos2(){
  console.log("entra");
  var tipo = document.getElementById("tipo");
  if(tipo.value == "E" || tipo.value == "A"){
    $("#clabe").attr('required',true);
    $("#banco").attr('required',true);
    $("#cuentapop").attr('required',true);
    $("#numtarjeta").attr('required',true);
  }else{
    $("#clabe").attr('required',false);
    $("#banco").attr('required',false);
    $("#cuentapop").attr('required',false);
    $("#numtarjeta").attr('required',false);
  }
}
 //pos2();
 setnumcta();


  $("#numtarjeta").keyup(function(){              
          var ta      =   $("#numtarjeta");
          letras      =   ta.val().replace(/-/g, "");
          ta.val(letras)
  }); 

  $("#numtarjeta").keyup(function(){              
          var ta      =   $("#numtarjeta");
          letras      =   ta.val().replace(/ /g, "");
          ta.val(letras)
  }); 


</script>