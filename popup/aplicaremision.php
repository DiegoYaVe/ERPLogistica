<?php
  session_start();
  date_default_timezone_set("America/Mexico_City");
  include_once('../funciones.php');
  include_once('../modulos/remisiones.php');
  /* ini_set('display_errors', 1); */
  $mremision = new modelremisiones();
  $mremision->select($_GET['id']);
  $accion = "aplicar";
  $title = "Finalizar";
  $nmbact = $mremision->folio." DE ".busca($mremision->cliente,'crm_clientes','c_id','c_alias');
 
  $ffin = date('Y-m-d');
  $totalrem = $mremision->total;
  $dtotal = "checked";
  $imprimir = "";
  if(isset($_GET['id'])) $id = $_GET['id'];

  $limite = "0";
  $stcobro = 'style="display: none"';
  $limitecredito = busca($mremision->cliente,'crm_clientes','c_id','c_limitecredito');
  if($limitecredito > 0){
    $sumapendiente = busca($mremision->cliente,'cxcobrar','cx_empresa = "'.$_SESSION['emp'].'" AND cx_estatus IN ("A","N") AND cx_cliente','SUM(cx_importe - cx_abonado)');
    $sumacredito = $sumapendiente + $totalrem;
    if($sumacredito > $limitecredito){
      $limite = "1";
    }
  }
  if($limite == "1"){
    $dtotal = "";
    $stcb = 'disabled style="background:grey"';
    $stcobro = 'style="display: block"';
  }
  echo'
  <div class="container mt-1 col-10">
    <form action="?modulo=remisiones&accion='.$accion.'&id='.$id.'" method="post" onsubmit="checksubmit();" enctype="multipart/form-data">
      <div class="row">
        <div class="col-12 col-md-12 alert alert-primary">'.$title.' Remisión</div>
        <div class="btn-group" data-toggle="buttons">';
        echo '</div>
      </div>';
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
      </script>
      <script>
        function cxc(){
          if(document.getElementById("cxcobrar").checked){
            document.getElementById("titlecxc").style.display = "none";
            document.getElementById("cta").style.display = "none";
            document.getElementById("obscta").style.display = "none";
          }else{
            document.getElementById("titlecxc").style.display = "";
            document.getElementById("cta").style.display = "";
            document.getElementById("obscta").style.display = "";
          }
        }
        function factur(){
          if(document.getElementById("factura").checked){
            document.getElementById("titlefactur").style.display = "";
            document.getElementById("fiscales").style.display = "";
            document.getElementById("usocfdi").style.display = "";
            document.getElementById("forpago").style.display = "";
            document.getElementById("mpago").style.display = "";
            document.getElementById("mailfac").style.display = "";
          }else{
            document.getElementById("titlefactur").style.display = "none";
            document.getElementById("fiscales").style.display = "none";
            document.getElementById("usocfdi").style.display = "none";
            document.getElementById("forpago").style.display = "none";
            document.getElementById("mpago").style.display = "none";
            document.getElementById("mailfac").style.display = "none";
          }
        }
        function setproyecto(){
          if(document.getElementById("proyecto").checked){
            document.getElementById("nmbac").disabled = false;
            document.getElementById("nmbac").focus();
            document.getElementById("nmbac").select();
          }else{
            document.getElementById("nmbac").disabled = true;
          }
        }
        function ingresoacaja(){
          var fpago =document.getElementById("formapagoing").value;
          var adj = document.getElementById("adj");
          var adjunto = document.getElementById("adjunto");
          var ingreso = document.getElementById("ingresocaja");
          var cuentas = document.getElementById("cta");
          var importediv = document.getElementById("importediv");
          var fpagodiv = document.getElementById("fpagodiv");
          var obscta = document.getElementById("obscta");
          var fechadiv = document.getElementById("fechadiv");
          var obsdiv = document.getElementById("obsdiv");
          var saldos = document.getElementById("saldos");
          var comdiv = document.getElementById("comdiv");

          if(ingreso.checked){
            cuentas.hidden = true;
            importediv.hidden = true;
            fpagodiv.hidden = true;
            cuentas.hidden = true;
            obscta.hidden = true;
            fechadiv.hidden = true;
            adj.hidden = true;
            obsdiv.hidden = true;
            comdiv.hidden = true;
            saldos.hidden = true;
          }else{
            cuentas.hidden = false;
            importediv.hidden = false;
            fpagodiv.hidden = false;
            cuentas.hidden = false;
            obscta.hidden = false;
            fechadiv.hidden = false;
            adj.hidden = false;
            obsdiv.hidden = false;
            comdiv.hidden = false;
            saldos.hidden = false;
          }
          if(ingreso.checked){
            adjunto.removeAttribute("required");
          }
          else{
            adjunto.setAttribute("required", "required");
            aplicarsaldo();
          }
        }

        function aplicarsaldo(){
          var fpago =document.getElementById("formapagoing").value;
          var adj = document.getElementById("adj");
          var adjunto = document.getElementById("adjunto");
          var ingreso = document.getElementById("ingresocaja");
          var cuentas = document.getElementById("cta");
          var importediv = document.getElementById("importediv");
          var fpagodiv = document.getElementById("fpagodiv");
          var obscta = document.getElementById("obscta");
          var fechadiv = document.getElementById("fechadiv");
          var obsdiv = document.getElementById("obsdiv");
          var saldos = document.getElementById("saldos");
          var comdiv = document.getElementById("comdiv");
          if(fpago == "23"){
            cuentas.hidden = true;
            saldos.hidden = false;
            comdiv.hidden = true;
            adj.hidden = true;
            obscta.hidden = true;
            adjunto.removeAttribute("required");
            adjunto.removeAttribute("required");
            saldomaximo();
          } else {
            cuentas.hidden = false;
            saldos.hidden = true;
            comdiv.hidden = false;
            adj.hidden = false;
            obscta.hidden = false;
            importe.removeAttribute('max');
            adjunto.setAttribute("required", "required");
            adjunto.setAttribute("required", "required");
            //absorberenvio();
          }
        }  
        function absorberenvio(){
          var abs = document.getElementById('envio');
          var total = document.getElementById('enviototal');
          var empabs = document.getElementById('envioempresa');
          var envio = document.getElementById('absorber');

          if(envio.checked){
            var sub =parseFloat(total.value)-parseFloat(empabs.value);
            if(sub<0) sub = 0;
            abs.value = sub;
          } else {
            abs.value = total.value;
          }
          updimp();
        }

        function updimp(){
          var abs = document.getElementById('envio');
          var totreal = document.getElementById('totreal').value;
          var importe = document.getElementById('importe');

          var suma = 0;
          suma = parseFloat(totreal) + parseFloat(abs.value);
          importe.value = suma;
        }
        updimp();

        function saldomaximo(){
          var saldos = document.getElementById("cxsaldos");
          var importe = document.getElementById("importe");

          var indice = saldos.selectedIndex;
          var saldosel = saldos.options[indice].dataset.info;
          importe.setAttribute('max', saldosel);

          importe.value = saldosel;
        }
        
        
      </script>
      <?php
      /*
        <div class="col-4 col-md-2" >
          <div class="mb-5">
            <label for"fini">Generar proyecto</label>
            <input type="checkbox" name="proyecto" onchange="setproyecto();" '.$proyecto.' id="proyecto" class="flipswitchsn" />
          </div>
        </div>
        <div class="col-12 col-md-3">
          <div class="mb-5">
            <label for"nmbac">Nombra tu proyecto</label>
            <input type="text" name="nmbac" maxlength="50" value="'.$nmbact.'" disabled="disabled" id="nmbac" class="form-control" placeholder="Nombre de tu actividad" required="required" onfocus="this.select();" />
          </div>
        </div>
      */
      echo '<div class="row nowrap">
        <div class="col-lg-4 col-sm-4" >
          <div class="mb-5">
            <label for"fini">Fecha esperada de pago</label>
            <input type="date" name="fpago" id="fpago" min="'.date('Y-m-d').'" max="'.date('Y-m-d', strtotime('+ 10 days')).'" value="'.date('Y-m-d', strtotime('+ 10 days')).'" class="form-control" placeholder="Cuando programas tu actividad" required="required" />
          </div>
        </div>
        <div class="col-lg-4 col-sm-4">
          <div class="mb-5">
            <label for"fini">Importe de la remisión</label>
            <input type="number" value="'.number_format($totalrem,2,'.','').'" class="form-control number-align" readonly="readonly" />
          </div>
        </div>
        <div class="col-lg-4 col-sm-4">
          <div class="mb-5">
            <label for"correo">Responsable</label>';
            $sql = 'SELECT u_nuser,u_id,u_nmb,u_apellidos FROM usuarios WHERE u_estatus = "A" ORDER BY u_nuser';
            $result = setq($sql);
            echo '<select name="responsable" id="responsable" class="form-control">';
              while($row = $result->fetch_array()){
                if($row['u_nuser'] != "1"){
                  if($row['u_id'] == $mremision->encargado) $select = "selected";
                  else $select = "";
                  echo '<option value="'.$row['u_id'].'" '.$select.'>'.$row['u_nmb'].' '.$row['u_apellidos'].'</option>';
                }
              }
            echo '</select>';
          echo '</div>
        </div>';

        echo '<div class="col-lg-3 col-sm-4" hidden >
          <div class="mb-5">
            <label for"fini">Crear cuenta por cobrar</label> <br>
            <input type="checkbox" name="cxcobrar" '.$dtotal.' id="cxcobrar" onchange="cxc();" class="flipswitchsn" '.$stcb.'/>
          </div>
        </div>  
        <div class="col-lg-2 col-sm-4" hidden>
          <div class="mb-5">
            <label for"fini">Imprimir recibo</label> <br>
            <input type="checkbox" name="imprimir" '.$imprimir.' id="imprimir" class="flipswitchsn" />
          </div>
        </div>
        <!-- <div class="col-lg-2 col-sm-4" >
          <div class="mb-5">
            <label for"fini">Facturar</label> <br>
            <input type="checkbox" name="factura" id="factura" onchange="factur();" class="flipswitch2" />
          </div>
        </div> -->
        <div class="col-lg-10">
          <div class="mb-5">
            <label for"correo">Observaciones de la Remisión</label>
            <textarea class="form-control" name="descripcion" rows="2" >'.$mremision->observaciones.'</textarea>
          </div>
        </div>';
        $dobledesc = busca('1', 'configuracionesp', 'c_id', 'c_dobledesc');
        $sqlco = 'SELECT COUNT(*) FROM remisionesc INNER JOIN articulos ON rc_articulo = a_id
          WHERE rc_remision = "'.$mremision->id.'" AND rc_ligado IS NULL
          AND a_categoria IN (SELECT cat_id FROM categorias WHERE cat_inflable = "1") AND a_enviogratis = "0"';
        $resultco = setq($sqlco);
        list($numco) = $resultco -> fetch_array(); 
        $numdom = busca($mremision->id, 'remisionesc' ,'rc_ligado IS NULL AND rc_tipoenvio = "D" AND rc_remision' ,'COUNT(*)');
        echo 'numdom: '.$numdom;
        if($numdom > 0){
          $hid = "hidden";
        } else if($numco <= 0){
          $hid = "";
        } else {
          if($mremision->descuento > 0) $hid = 'hidden';
          else if($mremision->precioenvio > 0) $hid = "";
          else $hid = "hidden";
        }
        echo '<div class="col-lg-3 col-sm-4" '.$hid.'>
          <div class="mb-5">
            <label for"">Costo de envío al cliente</label>
            <input type="number"  onchange="updimp();" name="envio" id="envio" step="0.01" min="0" max="'.$mremision->precioenvio.'" value="'.$mremision->precioenvio.'" class="form-control" placeholder="costo del envío que va a pagar el cliente"/>
          </div>
        </div>';
        echo '<div class="col-lg-3 col-sm-4" '.$hid.'>
        <div class="mb-5">
          <label for"">Absorber</label>
          <input type="checkbox" name="absorber" id="absorber" onchange="absorberenvio();" class="form-control flipswitch2" />
        </div>
      </div>';
        echo '<div class="col-lg-3 col-sm-4">
          <div class="mb-5">
            <label for"">Costo de envío total</label>
            <input type="number" name="enviototal" id="enviototal" step="0.01" min="0" max="'.$mremision->precioenvio.'" value="'.$mremision->precioenvio.'" class="form-control" placeholder="costo del envío total" readonly/>
          </div>
        </div>';
        $empab = busca('1', 'configuracionesp', 'c_id', 'c_maximoenvio');
        //$categoria = busca('1', 'categorias', 'cat_inflable', 'cat_id');
        //$cotizacion = busca($mremision->id, 'crm_cotizaciones', 'cc_remision','cc_id');
        $sqlsel = 'SELECT COUNT(*) FROM
                    (SELECT * FROM remisionesc INNER JOIN articulos ON rc_articulo = a_id
                    WHERE rc_remision = "'.$mremision->id.'" AND rc_ligado IS NULL
                    AND a_categoria IN (SELECT cat_id FROM categorias WHERE cat_inflable = "1") GROUP BY rc_articulo, rc_numero)
                    as contador';
        $result= setq($sqlsel);
        list($ninf) = $result->fetch_array();
        $totxabs = $ninf*$empab; 
        echo '<div class="col-lg-3 col-sm-4" '.$hid.'>
          <div class="mb-5">
            <label for"">La empresa absobe</label>
            <input type="number" name="envioempresa" id="envioempresa" step="0.01" min="0"  value="'.$totxabs.'" class="form-control" placeholder="Costo de envío que la empresa puede absorber" readonly/>
          </div>
        </div>';
        if($hid == "hidden") $checkce = 'checked';
        else $checkce = '';
        echo '<div class="col-lg-3 col-sm-4">
          <div class="mb-5">
            <label for"">El cliente paga envío</label>
            <input type="checkbox" name="clienteenvio" id="clienteenvio" class="form-control flipswitch2" '.$checkce.'/>
          </div>
        </div>';
        //apartado de CXCobrar
        echo '<div class="col-12 col-md-12 alert alert-primary" id="titlecxc">
          Insertar abono a la remisión
        </div>
        <div class="col-12 col-md-2">
          <div class="mb-5">
            <label for"nmbac">Ingreso a caja</label>
            <input type="checkbox" id="ingresocaja" name="ingresocaja" class="form-control flipswitch2" onchange="ingresoacaja();">
          </div>
        </div>
        <div class="col-12 col-md-2" id="importediv">
          <div class="mb-5">
            <label for"nmbac">Importe del ingreso</label>
            <input type="hidden" id="totreal" value="'.round(($totalrem- $mremision->precioenvio),2).'">
            <input type="number" min="1" step="0.01" '.$rest.' name="importe" value="'.round(($totalrem- $mremision->precioenvio),2).'" id="importe" class="form-control" placeholder="Importe del ingreso" required="required" onfocus="this.select();"/>
          </div>
        </div>
        <div class="col-12 col-md-2" id="comdiv">
              <div class="mb-5">
                <label for"nmbac">Comisión del ingreso</label>
                <input type="number" min="0" step="0.01" name="comision" value="0" id="comision" class="form-control" placeholder="Comisión del ingreso" required="required" onfocus="this.select();" />
              </div>
            </div>
        <div class="col-6 col-md-4" id="fpagodiv">
          <div class="mb-5">
            <label for"correo">Forma de pago</label>';
            $sqlsaldo = 'SELECT * FROM cxcobrar WHERE cx_cliente = "'.$mremision->cliente.'" AND cx_tipo = "S"'; 
            $resultsal = setq($sqlsaldo);
            $sql = 'SELECT * FROM cfdi_fpago WHERE cf_referencia = "1" ORDER BY cf_nmb ASC';
            $result = setq($sql);
            echo '<select name="formapagoing" id="formapagoing" class="form-control" onchange="aplicarsaldo();">';
              while($row = $result->fetch_array()){
                if($row['cf_nmb'] != "98"){
                  echo '<option value="'.$row['cf_id'].'">'.$row['cf_descripcion'].'</option>';
                } else if ($row['cf_nmb'] == "98" && $resultsal ->num_rows > 0){
                  echo '<option value="'.$row['cf_id'].'">'.$row['cf_descripcion'].'</option>';
                }                    
              }
            echo '</select>'; 
          echo '</div>
        </div>
        <div class="col-6 col-md-4" id="cta">
          <div class="mb-5">
            <label for"correo">Cuenta</label>';
            $sql = 'SELECT cu_id,cu_nmb FROM cuentas INNER JOIN cortes ON cu_id = c_cuenta 
                    WHERE c_estatus = "A" 
                    ORDER BY cu_orden';
            $result = setq($sql);
            echo '<select name="cuenta" id="cuenta" class="form-control">';
              while($row = $result->fetch_array()){
                if($row['cu_id'] != "1")
                echo '<option value="'.$row['cu_id'].'">'.$row['cu_nmb'].'</option>';
              }
            echo '</select>';
          echo '</div> 
        </div>
        <div class="col-12 col-md-4" id="saldos" hidden>
          <div class="mb-5">
            <label for"fini">Saldos</label>';
            $sqlsaldo = 'SELECT * FROM cxcobrar WHERE cx_cliente = "'.$mremision->cliente.'" AND cx_tipo = "S" AND cx_estatus != "C" AND cx_estatus != "F"'; 
            $resultsal = setq($sqlsaldo);
            echo '<select name="cxsaldos" id="cxsaldos" class="form-control" onchange="saldomaximo();">';
            while($rowsal = $resultsal -> fetch_array() ){
              $saldo = $rowsal['cx_importe']-$rowsal['cx_abonado'];
              $vence = busca($rowsal['cx_referencia'], 'penalizaciones', 'p_remision', 'p_fcaduca');
              echo '<option value="'.$rowsal['cx_id'].'" data-info="'.$saldo.'">$'.number_format($saldo, 2).' Vence: '.$vence.'</option>';
            }
            echo '</select>';
          echo '</div>
        </div>
        <div class="col-6 col-md-4" id="obscta">
          <div class="mb-5">
            <label for"correo">Referencia</label>
            <input type="text" name="referencia" value="" id="referencia" class="form-control" placeholder="Referencia de pago" onfocus="this.select();" />
          </div>
        </div>
        <div class="col-3 col-md-4 col-sm-6" id="fechadiv">
          <div class="mb-5">
            <label for"correo">Fecha del ingreso</label>
            <input type="datetime-local" name="fecha" value="'.date('Y-m-d H:i:s').'" id="referencia" class="form-control" onfocus="this.select();" required />
          </div>
        </div>
        <div class="col-6 col-md-4" id="adj">
          <div class="mb-5">
            <label>Adjuntar comprobante</label>
            <input type="file" name="adjunto" id="adjunto" class="form-control" placeholder="ADJUNTAR COMPROBANTE" required/>
          </div>
        </div>
        <div class="col-8 col-md-8" id="obsdiv">
          <div class="mb-5">
            <label for"correo">Observaciones</label>
            <input type="text" name="observaciones" id="observaciones" value="INGRESO A REMISIÓN '.$mremision->folio.'" class="form-control" placeholder="Observaciones" onfocus="this.select();"/>
          </div>
        </div>
        
        ';
        //////////////Apartado de Facturación**************************
        echo '<div class="col-12 col-md-12 alert alert-primary" id="titlefactur" style="display:none">
          Opciones para emisión de factura
        </div>
        <div class="col-12 col-md-6" id="fiscales" style="display:none;">
          <div class="mb-5">
            <label for"correo">Datos fiscales</label>';
            $sqlf = 'SELECT * FROM crm_fiscales INNER JOIN crm_direcciones ON (cf_direccion = cd_id AND cf_cliente = cd_cliente)
                      WHERE cf_cliente = "'.$mremision->cliente.'" ORDER BY cf_predeterminada DESC';
            $resultf = setq($sqlf);
            echo '<select name="fiscales" id="fisc" class="form-control">'.$sqlf;
              if($resultf->num_rows == 0){
                echo '<option value="0">Registrar datos en captura de factura</option>';
              }
              while($rowf = $resultf->fetch_array()){
                echo '<option value="'.$rowf['cf_id'].'">'.$rowf['cf_nmfiscales'].' - '.$rowf['cf_rfc'].' CP '.$rowf['cd_cp'].' Regimen '.$rowf['cf_regimen'].'</option>';
              }
            echo '</select>';
          echo '</div>
        </div>';
        echo '<div class="col-12 col-md-6" id="usocfdi" style="display:none;">
          <div class="mb-5">
            <label for"correo">Uso del CFDI</label>';
            $sql = 'SELECT * FROM cfdi_uso ORDER BY cu_id DESC';
            $result = setq($sql);
            echo '<select name="uso" id="uso" class="form-control">';
              while($row = $result->fetch_array()){
                if($row['cu_id'] == "G03") $selu = 'selected'; else $selu = "";
                echo '<option value="'.$row['cu_id'].'">'.$row['cu_id'].' - '.$row['cu_descripcion'].'</option>';
              }
            echo '</select>';
          echo '</div>
        </div>';
        echo '<div class="col-6 col-md-4" id="forpago" style="display:none;">
          <div class="mb-5">
            <label for"correo">Forma de pago</label>';
            $sql = 'SELECT * FROM cfdi_fpago ORDER BY cf_nmb ASC';
            $result = setq($sql);
            echo '<select name="formapago" id="formapago" class="form-control">';
              while($row = $result->fetch_array()){
                if($row['cf_nmb'] == "03") $self = 'selected'; else $self = "";
                echo '<option value="'.$row['cf_nmb'].'">'.$row['cf_nmb'].' - '.$row['cf_descripcion'].'</option>';
              }
            echo '</select>';
          echo '</div>
        </div>';
        echo '<div class="col-6 col-md-4" id="mpago" style="display:none;">
          <div class="mb-5">
            <label for"correo">Método de pago</label>
            <select name="metodopago" id="metodopago" class="form-control">
              <option value="PUE" selected> PUE-Pago en una sola exhibición</option>
              <option value="PPD"> PPD-Pago en Parcialidades o diferido</1option>
            </select>';
          echo '</div>
        </div>';
        echo '<div class="col-6 col-md-4" id="mailfac" style="display:none;">
          <div class="mb-5">
            <label for"correo">Correo para envío</label>
            <input type="mail" name="correofac" value="" id="correofac" class="form-control" placeholder="Correo para envío de la factura" onfocus="this.select();" />
          </div>
        </div>';
        echo '<div class="col-12 col-md-12">
          <center><button role="submit" class="btn btn-primary" id="guardar"><i class="fa fa-save"></i> Finalizar</button></center>
        </div>
      </div>
    </form>
  </div>
  
  ';
?>