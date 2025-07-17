<?php
  //ini_set('display_errors', 1);
  session_start();
  include_once('../funciones.php');
  $tablero = $_GET['tablero'];
  include_once('../modulos/cotizaciones.php');
  $mcotiza = new modelcotizaciones();
  $mcotiza->select($_GET['idcotiza']);
  

  echo '<script language="javascript">
    function checksubmit(){
      document.getElementById("sendcotiza").value = "JD";
      document.getElementById("sendcotiza").disabled = true;
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
  </script>';
 
  $title = "Finalizar cotización y enviar por correo";

  $diva = ' checked="checked" ';
  $dtotal = ' checked="checked" ';

  if($mcotiza->diva == 0) $diva = '  ';
  //if($mcotiza->mtotal == 0) $dtotal = ' ';
  if($mcotiza->mtotal == 0) {
    $dtotal = '';
    $disablediva = 'disabled';
    $style = 'style="background:grey"';
  }
  if($mcotiza->estatus == "N") $accion= 'enviaryvender&idcotiza='.$mcotiza->id.'&tablero='.$tablero;
  else $accion = 'finalcaptura&idcotiza='.$mcotiza->id;

  

  $cliente = busca($mcotiza->tablero,'crm_tableros','ct_id','ct_cliente');
  $mcotiza->cliente = $cliente;

  $precio = busca($mcotiza->id,'crm_cotizacionesd','(cdm_precio = "0" OR cdm_cantidad = "0") AND cdm_cotizacion','COUNT(*)');
  if($precio > 0) $divalert = '<div class="col-12 col-md-12 alert alert-danger">Existen productos con precio 0 en la cotización</div>';
  else $divalert = '';
  
  $count = busca($mcotiza->id,'crm_cotizacionesd','cdm_cotizacion','COUNT(*)');
  $btn = '';
  if($count == 0) {
    $divalert = '<div class="col-12 col-md-12 alert alert-danger">No existen productos capturados en la cotización.</div>';
    $btn = 'disabled';
  }

  $peso = 0;
  $cons = 'SELECT a_id, a_peso, cdm_cantidad, a_tipoprod FROM crm_cotizacionesd INNER JOIN articulos ON a_id = cdm_articulo WHERE cdm_cotizacion = "'.$_GET['idcotiza'].'"';
  $resultd = setq($cons);
  while($rowd = $resultd -> fetch_array()){
    if($rowd['a_tipoprod'] == "M"){
      $hijos = 'SELECT a_peso, ac_cantidad FROM articulos_combos INNER JOIN articulos ON a_id = ac_ahijo WHERE ac_articulo = "'.$rowd['a_id'].'"';
      $resulth = setq($hijos);
      $pesosum = 0;
      while($rowh = $resulth -> fetch_array()){
        $pesosum += $rowh['a_peso']*$rowh['ac_cantidad'];
      }
    } else {
      $pesosum = $rowd['a_peso'];  
    }
    if($pesosum > 60) $peso = 1;
  }
  

  echo'
  <div class="container mt-1 col-10">
    <form action="?modulo=cotizaciones&accion='.$accion.'&tablero='.$tablero.'" method="post" onsubmit="checksubmit();">
      <div class="row">
        '.$divalert.' 
        <div class="col-12 col-md-12 alert alert-primary">'.$title.'</div>
        <div class="btn-group" data-toggle="buttons">';
        echo '</div>
      </div>';
      echo '<input type="hidden" name="folio" value="'.$mcotiza->folio.'">';
      echo '<input type="hidden" name="nmbac" value="'.$mcotiza->nmb.'">';
      echo '<input type="hidden" name="descripcion" value="'.$mcotiza->descripcion.'">';
      echo '<input type="hidden" name="descuento" value="'.$mcotiza->descuento.'">';
      echo '<input type="hidden" name="importe" value="'.$mcotiza->importe.'">';
      echo '<input type="hidden" name="probabilidad" value="'.$mcotiza->probabilidad.'">';
      echo '<input type="hidden" name="envio" value="'.$envio.'">';
      //echo 'peso: '.$peso;
      ?>
      <script>
        function enviarmail(){
          if(document.getElementById("checkmail").checked){
            document.getElementById("divnmbagente").style.display = "";
            document.getElementById("divnmbagenteceo").style.display = "";
            document.getElementById("divpuestoagente").style.display = "";
            document.getElementById("divdestino").style.display = "";
            document.getElementById("divtelefono").style.display = "";
            document.getElementById("divcorreo").style.display = "";
            document.getElementById("divcopiamail").style.display = "";
            document.getElementById("divmensaje").style.display = "";
            document.getElementById("divopciones1").style.display = "";
            document.getElementById("divopciones3").style.display = "";
            document.getElementById("destino").setAttribute("required", "required");
            document.getElementById("correo").setAttribute("required", "required");
            document.getElementById("sendcotiza").innerHTML = '<i class="fa fa-paper-plane"></i> Finalizar Cotización y enviar correo';
          }
          else{
            document.getElementById("divnmbagente").style.display = "none";
            document.getElementById("divnmbagenteceo").style.display = "none";
            document.getElementById("divpuestoagente").style.display = "none";
            document.getElementById("divdestino").style.display = "none";
            document.getElementById("divtelefono").style.display = "none";
            document.getElementById("divcorreo").style.display = "none";
            document.getElementById("divcopiamail").style.display = "none";
            document.getElementById("divmensaje").style.display = "none";
            document.getElementById("divopciones1").style.display = "none";
            document.getElementById("divopciones3").style.display = "none";
            document.getElementById("correo").removeAttribute("required");
            document.getElementById("destino").removeAttribute("required");
            document.getElementById("sendcotiza").innerHTML = '<i class="fas fa-flag-checkered"></i> Finalizar Captura de Cotización';
          }
        }
      </script>
      <?php
      $grupo = busca($_SESSION['uid'], 'usuarios', 'u_id', 'u_grupo');
      if($grupo != "ADMIN") {
        $sel = 'style="display: none;"';
        $inp = 'style="display: block;"';
      }else {
        $sel = 'style="display: block;"';
        $inp = 'style="display: none;"';
      }
      echo '<div class="row">
        <div class="col-sm-6 col-md-3">
          <label for"nmbac">Cliente</label><br>
          '.busca($cliente,'crm_clientes','c_id','c_nmb').'
        </div>
        <div class="col-sm-6 col-md-1">
          <label for"nmbac">Importe</label><br>
          <b>$'.number_format($mcotiza->importe,2).'</b>
        </div>
        <div class="col-sm-6 col-md-2">
          <div class="mb-5">
            <label for"correo">Fecha de cierre</label>
            <input type="date" name="ffin" id="ffin" value="'.$mcotiza->ffin.'" class="form-control" required />
          </div>
        </div>
        <div class="col-12 col-md-2" >
          <div class="mb-5">
            <label for"fini">Mostrar total</label>
            <input type="checkbox" id="total" name="total" '.$dtotal.' class="flipswitchsn" onchange="desglose();"/>
          </div>
        </div>
        <div class="col-12 col-md-2" >
          <div class="mb-5">
            <label for"fini">Desglosar IVA</label>
            <input type="checkbox" name="iva" id="iva" '.$diva.' '.$disablediva.' '.$style.' class="flipswitchsn" />
          </div>
        </div>
        <div class="col-6 col-md-2">
          <div class="mb-5">
            <label for"correo">Responsable</label>
            <select class="form-control" searchable="Responsable del tablero" name="responsable" '.$read.' '.$sel.'>';
            $sqlu = 'SELECT u_nuser,u_id,u_nmb FROM usuarios WHERE u_estatus = "A"';
            $resultu = setq($sqlu);
            while($rowu = $resultu->fetch_array()){
              if($mcotiza->responsable == $rowu['u_id']) $seldis = "selected"; else $seldis = "";
              echo '<option value="'.$rowu['u_id'].'" '.$seldis.'>'.$rowu['u_id'].'</option>';
            }
            echo '</select>
            <input name="responsable" class="form-control" type="text" value="'.$mcotiza->responsable.'" '.$inp.' disabled> 
          </div>
        </div>
        <div class="col-6 col-md-3" >
          <div class="mb-5">
            <label for"fini">Almacen</label>
            '.menu_select_db('almacenes','a_id','a_nmb',$mcotiza->almacen,'almacen','a_estatus = "A"',false,false,false,true,"SELECCIONA ALMACEN").'
          </div>
        </div>
        ';
        $existecorreo = busca($_SESSION['uid'],'usuarios','u_id','u_mailcorp');
        if(!empty($existecorreo)){
          $disabled = '';
          $chcorreo = 'checked';
        }
        else{
          $disabled = 'disabled';
          $chcorreo = '';
          $tooltip = 'data-toggle="tootlip" title="No puedes enviar la cotización por correo hasta que el correo corporativo esté configurado"';
          echo '<script>
            enviarmail();
          </script>';
        }
        if($mcotiza -> estatus == "N"){
           $hiddencor = '';
           $titlemail = 'Enviar remisión al cliente';
        }else {
          $hiddencor = 'hidden';
          $titlemail = 'Enviar cotización por correo electrónico';
        }
        echo '
        <div class="row">
          <div class="col-12 col-md-12 alert alert-primary">
            '.$titlemail.'
            <input type="checkbox" style="width:20px;height:20px" id="checkmail" name="checkmail" onchange="enviarmail()" '.$chcorreo.' '.$tooltip.' '.$disabled. '/>
          </div>
          <div class="col-sm-6 col-md-3" id="divnmbagente">
            <div class="mb-5">
              <label for"correo">Nombre Remitente</label>
              <input type="text" name="nmbagente" id="nmbagente" value="'.$mcotiza->nmbagente.'" class="form-control" placeholder="Nombre del remitente" required />
            </div>
          </div>
          <div class="col-sm-6 col-md-2" id="divpuestoagente">
            <div class="mb-5">
              <label for"correo">Puesto Remitente</label>
              <input type="text" name="puestoagente" id="puestoagente" value="'.$mcotiza->puestoagente.'" class="form-control" placeholder="Puesto a mostrar del agente"  required />
            </div>
          </div>
          <div class="col-12 col-md-3" id="divdestino">
            <div class="mb-5">
              <label for"destino">Dirigido a</label>
              <input type="text" name="destino" id="destino" value="'.$mcotiza->destino.'" class="form-control" placeholder="Persona para contactar" required  />
            </div>
          </div>
          <div class="col-6 col-md-2" id="divcorreo">
            <div class="mb-5">
              <label for"correo">Correo destino</label>
              <input type="email" style="text-transform:lowercase;" name="correo" id="correo" value="'.$mcotiza->maildestino.'" class="form-control" placeholder="correo de la contacto" required/>
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
          <div class="col-sm-6 col-md-2" id="divnmbagenteceo">
            <div class="mb-5">
              <label for"correo">Agente</label>
              <select class="form-control" searchable="Responsable del tablero" name="responsable" '.$read.' '.$sel.'>';
              $sqlu = 'SELECT u_nuser,u_id,u_nmb FROM usuarios WHERE u_estatus = "A"';
              $resultu = setq($sqlu);
              while($rowu = $resultu->fetch_array()){
                if($mcotiza->responsable == $rowu['u_id']) $seldis = "selected"; else $seldis = "";
                echo '<option value="'.$rowu['u_id'].'" '.$seldis.'>'.$rowu['u_id'].'</option>';
              }
              echo '</select>
              <input name="responsable" class="form-control" type="text" value="'.$mcotiza->responsable.'" '.$inp.' disabled> 
            </div>
          </div>
          <div class="col-6 col-md-3"  id="divopciones1">
            <div class="mb-5">
              <label for"fini">Mostrar tabla de productos</label>
              <input type="checkbox" name="tablaprod" checked class="flipswitchsn" />
            </div>
          </div>
          <div class="col-6 col-md-3"  id="divopciones3">
            <div class="mb-5">
              <label for"fini">Mostrar cuentas bancarias</label>
              <input type="checkbox" name="cuentas" class="flipswitchsn" />
            </div>
          </div>';
          echo'<div class="col-12 col-md-12" id="divmensaje">
            <div class="mb-5">
              <label for"correo">Cuerpo del Correo</label>
              <textarea class="form-control" name="mensaje" rows="6" >'.$mcotiza->mensaje.'</textarea>
            </div>
          </div>';
          if(busca($mcotiza->cliente,'clientes','c_idc','COUNT(*)') > 0){
            echo '<div class="col-12 col-md-12 alert alert-primary">Dirección de envio</div>
            <div class="col-12 col-md-8">
              <div class="mb-5">
                <label for"destino">Dirección de envio</label>';
                $sqlde = 'SELECT * FROM crm_direcciones INNER JOIN estado ON cd_estado = e_id
                          WHERE cd_cliente = "'.$mcotiza->cliente.'" ORDER BY cd_predeterminada DESC, cd_id DESC';
                $resultde = setq($sqlde);
                echo'<select name="direnvio" requiered class="form-control">
                  <option value="" >Enviar sin dirección asignada</option>';
                  while($row = $resultde->fetch_array()){
                    if($mcotiza->direnvio == $row['cd_id']) $seld = 'selected'; else $seld = "";
                    echo '<option value="'.$row['cd_id'].'" '.$seld.' >'.$row['cd_calle'].' '.$row['cd_nume'].' '.$row['cd_numi'].' '.$row['cd_colonia'].' '.$row['cd_municipio'].' '.$row['e_nmb'].' '.$row['cd_cp'].'</option>';
                  }
                echo '</select>
              </div>
            </div>
            <div class="col-6 col-md-4">
              <div class="mb-5">
                <label for"correo">Observaciones de envio</label>
                <input type="text" name="observadir" maxlenght="100" id="observadir" value="'.$mcotiza->observadir.'" class="form-control" placeholder="correo de la contacto" />
              </div>
            </div>
          ';
          }
        echo '</div>
        <div class="col-12 col-md-12">
          <center><button role="submit" id="sendcotiza" '.$btn.' class="btn btn-primary"><i class="fa fa-paper-plane"></i> '.$title.'</button></center>
        </div>
      </div>    
    </form>
  </div>';
?>