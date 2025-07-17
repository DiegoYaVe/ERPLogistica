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
//ini_set('display_errors', 1);
session_start();
include_once('../funciones.php');
include_once('../modulos/cotizaciones.php');
$mcotiza = new modelcotizaciones();
$mcotiza->select($_GET['cotizacion']);

if($mcotiza->tabla == 0) $dtabla= ' '; else $dtabla= 'checked';
if($mcotiza->firma == 0) $dfirma= ' '; else $dfirma= 'checked';
if($mcotiza->cuentas == 0) $dcuentas = ' '; else $dcuentas= 'checked';

$cliente = busca($mcotiza->tablero,'crm_tableros','ct_id','ct_cliente');


echo'<input type="hidden" id="empresa" value="'.$_SESSION['emp'].'" />

<div class="container mt-1 ">
  <form action="?modulo=cotizaciones&accion=sendmailcotiza&id='.$mcotiza->id.'" method="post" onsubmit="checksubmit();">
    <div class="row">
      <div class="col-12 col-md-12 alert alert-primary">Reenviar cotización por correo</div>';

  echo '      <div class="col-sm-6 col-md-3" id="divnmbagente">
        <div class="mb-5">
          <label for"correo">Nombre Remitente</label>
          <input type="text" name="nmbagente" id="nmbagente" value="'.$mcotiza->nmbagente.'" class="form-control" placeholder="Nombre del remitente" required />
        </div>
      </div>

      <div class="col-sm-6 col-md-3" id="divpuestoagente">
        <div class="mb-5">
          <label for"correo">Puesto Remitente</label>
          <input type="text" name="puestoagente" id="puestoagente" value="'.$mcotiza->puestoagente.'" class="form-control" placeholder="Puesto a mostrar del agente"  required />
        </div>
      </div>

      <div class="col-12 col-md-3" id="divdestino">
        <div class="mb-5">
          <label for"destino">Dirigido a</label>
          <input type="text" name="destino" id="destino" value="'.$mcotiza->destino.'" class="form-control" placeholder="Persona para contactar"  />
        </div>
      </div>
      <div class="col-6 col-md-3" id="divcorreo">
        <div class="mb-5">
          <label for"correo">Correo destino</label>
          <input type="email" style="text-transform:lowercase;" name="correo" id="correo" value="'.$mcotiza->maildestino.'" class="form-control" placeholder="correo de la contacto" />
        </div>
      </div>
      <div class="col-6 col-md-2" id="divtelefono">
        <div class="mb-5">
          <label for"telefono">Número telefónico</label>
          <input type="tel" name="telefono" id="telefono" value="'.$mcotiza->teldestino.'" class="form-control" placeholder="Telefóno del contacto" />
        </div>
      </div>

      <div class="col-6 col-md-3"  id="divcopiamail">
        <div class="mb-5" >
          <label for"correo">Copiar a <i class="icon-exclamation-circle" data-toggle="tooltip" data-placement="top" title="Separar por comas si es mas de uno, si no quieres enviar copias dejar el campo en blanco" ></i> </label>
          <input type="text" name="copiamail" id="copiamail" value="'.$mcotiza->copiamail.'" class="form-control" placeholder="correo de la contacto"  />
        </div>
      </div>

      <!-- <div class="col-6 col-md-2"  id="divopciones1">
        <div class="mb-5">
          <label for"fini">Mostrar tabla de productos</label>
          <input type="checkbox" name="tablaprod" '.$dtabla.' class="flipswitchsn" />
        </div>
      </div> -->
      <!-- <div class="col-6 col-md-2"  id="divopciones2">
        <div class="mb-5">
          <label for"fini">Mostrar firma la del asesor</label>
          <input type="checkbox" name="firma" '.$dfirma.' class="flipswitchsn" />
        </div>
      </div> -->
      <div class="col-6 col-md-2"  id="divopciones3">
        <div class="mb-5">
          <label for"fini">Mostrar cuentas bancarias</label>
          <input type="checkbox" name="cuentas" '.$dcuentas.' class="flipswitchsn" />
        </div>
      </div>

      <div class="col-12 col-md-12" id="divmensaje">
        <div class="mb-5">
          <label for"correo">Cuepo del Correo</label>
          <textarea class="form-control" name="mensaje" rows="6" >'.$mcotiza->mensaje.'</textarea>
        </div>
      </div>';

  echo '</div>';


  echo '
      <div class="col-12 col-md-12">
        <center><button role="submit" class="btn btn-primary" id="guardar"><i class="icon-envelope-o"></i> Enviar correo </button></center>
      </div>
    </div>
  </form>
</div>';
?>