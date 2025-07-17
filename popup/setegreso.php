<?php
  header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
  header("Expires: Sat, 1 Jul 2000 05:00:00 GMT"); // Fecha en el pasado
  session_start();
  include_once('../funciones.php');
  date_default_timezone_set("America/Mexico_City");
  if(!isset($_GET['id'])) $_GET['id'] = NULL;
  include_once('../modulos/egresos.php');
  $egres = new Modelegresos();
  $egres->select($_GET['id']);
  if($egres->id){
    $accion = 'update&id='.$_GET['idcxp'];
    $title = "Editar un ";
  } 
  else{
    $accion = 'insert';
    $title = "Insertar un ";
    $seldis = "selected";
    $egres->fecha = date('Y-m-d').'T'.date('H:i:s');
  }
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
    function confirmcondona(cxp){
      var conf = confirm("Al condonar la Cuenta por pagar, será cancelada y no podrá ser recuperada\n¿Deseas continuar?");
      if(conf == true){
        document.getElementById("idcxpcon").value = cxp;
        document.setcondonar.submit();
      }
    }
  </script>
  <script>
    $("#cuenta").focus();
    function setminasaldo(){
      var cuentw = document.getElementById("cuenta").value;
      var importe = document.getElementById("maximp").value;
      var saldo = document.getElementById("saldo" + cuentw).value;
      $("#cuenta").css("border", "");
      document.getElementById("errorc").innerHTML = '';
      if(parseFloat(saldo) >= parseFloat(importe)){
        document.getElementById("importe").max = importe;
      }else{
        document.getElementById("importe").max = saldo;
      }

      if(document.getElementById("importe").value !== ""){
        if(document.getElementById("proveedor1").value === ""){
          $("#proveedor1").focus();
        }
      }else $("#importe").focus();
    }
    function setcxp(){
      var prv = $("#proveedor1").val();
      $.ajax({
        url: "query/listcxpagar.php",
        type: "POST",
        dataType: "html",
        data: {'prv': prv},
      })
      .done(function(datacol){
        $("#listcxp").html(datacol);
        if($("#importe").val() == ""){
          $("#importe").focus();
          $("#listcxp").prop('style','display:none');
        }
      });
    }
    function sum(id){
      cb = document.getElementById(id);

      var saldo = parseFloat(document.getElementById("monto"+id).value);
      var nsaldo = parseFloat(document.getElementById("nsaldo"+id).value);
      var maxsaldo = parseFloat(document.getElementById("nsaldo"+id).max);

      var monto = parseFloat(document.getElementById("importelote").value);
      var importe = parseFloat(document.getElementById("importe").value);
      var max = parseFloat(document.getElementById("importelote").max);
      
      var suma = parseFloat(monto);
      var valor = 0;

      if(cb.checked == true){
        document.getElementById("nsaldo"+id).disabled = false;
        if(nsaldo < saldo){
          var resta = saldo - nsaldo;
          valor = (resta);
          suma -= (valor);
        }else if(nsaldo > saldo){
          if(nsaldo > maxsaldo){
            alert("El monto a pagar de la cuenta debe ser menor o igual a: $"+maxsaldo+",\nPor favor verifica antes de continuar");
            document.getElementById("nsaldo"+id).value = saldo.toFixed(2);
            return;
          }else{
            valor = nsaldo - saldo;
            suma += valor;
          }
        }else{
          valor = (nsaldo);
          var sm = suma+valor;
          if(nsaldo > importe || sm > importe){
            if(suma > 0) {
              var nval = importe - suma;
              nsaldo = nval;
            }
            else nsaldo = importe;
            document.getElementById("nsaldo"+id).value = nsaldo.toFixed(2);
          }else{
            var res = importe - suma;
            if(parseFloat(maxsaldo) <= parseFloat(res)){
              nsaldo = maxsaldo;
            }else{
              var res2 = maxsaldo - (maxsaldo - res);
              nsaldo = parseFloat(res2);
            }
            document.getElementById("nsaldo"+id).value = nsaldo.toFixed(2);
          }
          
          suma += (nsaldo);
        }
        document.getElementById("monto"+id).value = nsaldo.toFixed(2);
      }else{
        document.getElementById("nsaldo"+id).disabled = true;
        document.getElementById("nsaldo"+id).value = 0;
        document.getElementById("monto"+id).value = 0;
        if(suma > 0)
          valor = (nsaldo);
        else valor = 0;
        suma -= (valor);
      }

      if(suma > 0) document.getElementById("guardar").disabled = false;
      else document.getElementById("guardar").disabled = true;
      document.getElementById("importelote").value = parseFloat(suma.toFixed(2));
      if(suma <= max){
        $("#importelote").css("border", "");
        document.getElementById("error").innerHTML = '';
      }
      else{
        document.getElementById("guardar").disabled = true;
        $("#importelote").css("border", "solid 2px red");
        document.getElementById("error").innerHTML = 'La suma de la(s) cuenta(s) es mayor al importe del egreso';
      }
    }
    function cobros(){
      var values = [];
      var saldos = [];
      
      var divlist = document.getElementById("list");
      divclass = document.getElementsByClassName("lotecxp");
      if(divclass.length > 0) divlist.remove();

      var checkboxes = document.querySelectorAll("input[name='chcxp']:checked");
      
      checkboxes.forEach((checkbox) => {
        var saldo = document.getElementById("nsaldo"+checkbox.id).value;
        if(saldo > 0){
          values.push(checkbox.value);
          saldos.push(saldo);
        }
      });
      var divl = document.createElement('div');
      divl.setAttribute('id', 'list');
      divl.setAttribute('style', 'display:none');
      document.getElementById('list1').appendChild(divl);
      for(var i = 0; i < values.length; i++){
        var div = document.createElement('div');
        div.setAttribute('class', 'lotecxp');
        div.setAttribute('style', 'display:none');
        div.innerHTML = '<div><input type="hidden" name="idlist[]" id="idlist'+i+'" value="'+values[i]+'"/><input type="hidden" name="saldolist[]" id="saldolist'+i+'" value="'+saldos[i]+'"/></div>';
        document.getElementById('list').appendChild(div);
      }
      
      var cuenta = document.getElementById("cuenta").value;
      var implote = document.getElementById("importelote").value;
      var impingreso = document.getElementById("importe").value;
      if(cuenta !== "") {
        var saldo = document.getElementById("saldo" + cuenta).value;
        if(parseFloat(saldo) >= parseFloat(implote)){
          var bandera = 1;
        }else{
          var bandera = 0;
        }
        var c = document.getElementById("saldorest");
        if(c !== null){
          document.frmegreso.submit();
          document.getElementById("guardar").value = "JD";
          document.getElementById("guardar").disabled = true;
        }else if(parseFloat(implote) < parseFloat(impingreso) && bandera == 1){
          var div = document.createElement('div');
          div.innerHTML = '<div class="mb-1"><label styele="display: flex;align-items: center;"><font color="red">El importe de las cuentas seleccionadas es menor al egreso, si deseas generar un saldo restante marca la casilla siguiente</font></label>&nbsp;&nbsp;<input style="display:inline-block;width:22px;height:22px;border: 3px solid red;" type="checkbox" id="saldorest" onclick="formenv();"/> </div>';
          document.getElementById('msjerror').appendChild(div);
          document.getElementById("guardar").disabled = true;
        }else if(parseFloat(implote) == parseFloat(impingreso) && bandera == 1){
          document.frmegreso.submit();
          document.getElementById("guardar").value = "JD";
          document.getElementById("guardar").disabled = true;
        }else if(bandera == 0){
          var div = document.createElement('div');
          div.innerHTML = '<label><font color="red">El importe del egreso debe ser menor o igual al saldo de la cuenta seleccionada ($'+saldo+').</font></label>';
          document.getElementById('msjerror').appendChild(div);
          document.getElementById("guardar").disabled = true;
        }
      }
      else {
        $("#cuenta").focus();
        $("#cuenta").css("border", "solid 2px red");
        document.getElementById("errorc").innerHTML = 'Selecciona una cuenta';
      }
    }
    function nuevosaldo(){
      var importe = document.getElementById('importe').value;
      var monto = parseFloat(document.getElementById("importelote").value);
      if(importe > 0){
        $("#listcxp").prop('style','display:block');
        if(parseFloat(importe) < monto) {
          document.getElementById("guardar").disabled = true;
          $("#importelote").css("border", "solid 2px red");
          document.getElementById("error").innerHTML = 'La suma de la(s) cuenta(s) es mayor al importe del egreso';
        }else{
          document.getElementById("guardar").disabled = false;
          $("#importelote").css("border", "");
          document.getElementById("error").innerHTML = '';
        }
        document.getElementById('importelote').max = importe;
      }
      document.getElementById("fondear").disabled = false;
    }
    function formenv(){
      cb = document.getElementById("saldorest");
      if(cb.checked)document.getElementById("guardar").disabled = false;
      else document.getElementById("guardar").disabled = true;
    }
    function fondear(){
      $("#importelote").css("border", "");
      document.getElementById("error").innerHTML = '';
      document.getElementById("fondear").disabled = true;
      document.getElementById("guardar").disabled = false;
      var importe = parseFloat(document.getElementById("importe").value);
      document.getElementById("importelote").value = parseFloat(0);
      var monto = parseFloat(document.getElementById("importelote").value);
      var suma = parseFloat(monto);
      cbfon = document.getElementById("fondear");
      var valcb = parseFloat(document.getElementById("fondear").value);
      for(var l=1;l<=valcb;l++){
        var nsaldo = parseFloat(document.getElementById("nsaldo"+l).value);
        var maxsaldo = parseFloat(document.getElementById("nsaldo"+l).max);
        if(nsaldo !== maxsaldo){
          document.getElementById("nsaldo"+l).value = maxsaldo;
          document.getElementById("monto"+l).value = maxsaldo;
        }
        document.getElementById(l).checked = false;
        document.getElementById("nsaldo"+l).disabled = true;
      }
      for(var k=1;k<=valcb;k++){
        var saldo = parseFloat(document.getElementById("monto"+k).value);
        var nsaldo = parseFloat(document.getElementById("nsaldo"+k).value);
        var implote = parseFloat(document.getElementById("importelote").value);
        var imponew = parseFloat(document.getElementById("importe").value);
        if(implote == imponew){
          document.getElementById("monto"+k).value = parseFloat(0);
          document.getElementById("nsaldo"+k).value = parseFloat(0);
          document.getElementById("nsaldo"+k).disabled = true;
          cb = document.getElementById(k);
          cb.checked = false;
        }else{
          if (saldo <= importe){
            var sumimp = parseFloat(suma + saldo);
            if(sumimp <= importe){
              suma += saldo;
              document.getElementById("importelote").value = parseFloat(suma.toFixed(2));
              document.getElementById("nsaldo"+k).disabled = false;
              cb = document.getElementById(k);
              cb.checked = true;
            }else if(suma < importe){
              var resta = parseFloat(importe - suma);
              sumimp = suma + resta;
              if (sumimp == importe){
                suma += resta;
                document.getElementById("importelote").value = parseFloat(suma.toFixed(2));
                document.getElementById("monto"+k).value = parseFloat(resta.toFixed(2));
                document.getElementById("nsaldo"+k).value = parseFloat(resta.toFixed(2));
                document.getElementById("nsaldo"+k).disabled = false;
                cb = document.getElementById(k);
                cb.checked = true;
              }else return;
            }else return;
          }else if (saldo > importe && suma < importe){
              restimp = importe - suma;
              suma += parseFloat(restimp);
              document.getElementById("importelote").value = parseFloat(suma.toFixed(2));
              document.getElementById("monto"+k).value = parseFloat(restimp.toFixed(2));
              document.getElementById("nsaldo"+k).value = parseFloat(restimp.toFixed(2));
              document.getElementById("nsaldo"+k).disabled = false;
              cb = document.getElementById(k);
              cb.checked = true;
          }else return;
        }
      }
    }
  </script>
<?php
  $sqlac = "";
  $maximp = ' max="99999999" ';
  if(isset($_GET['idcxp'])){
    $disable = '';
    $sql = 'SELECT cp_idref,cp_id,cp_observaciones,cp_tipo,cp_proveedor,cp_importe,cp_abonado
            FROM cxpagar WHERE cp_id = "'.$_GET['idcxp'].'"';
    $result = setq($sql);
    list($cxref,$cxid,$cxobservaciones,$cxtipo,$cxproveedor,$cximporte,$cxabonado) = $result->fetch_array();
    if($cxtipo == "G") $desc = busca($cxref,'gastos','g_id','g_folio');
    elseif($cxtipo == "C") $desc = busca($cxref,'ordenesc','o_id','o_folio');
    else $desc = $cxobservaciones;
    $sqlac.= ' AND p_id = "'.$cxproveedor.'" ';
    $egres->proveedor = $cxproveedor;
    $egres->monto = $cximporte-$cxabonado;
    if($egres->monto < 0) $egres->monto = 0;

    $maximp = ' max = "'.$egres->monto.'" ';

    if(isset($_GET['from'])) $from = '&from=gastos';
    $accion.='&idcxp='.$_GET['idcxp'].$from;
    $style = "";
    $style1 = 'style="display:none"';
   
    $focus1 = ' onfocus="this.select();" autofocus';
    $col = "col-md-10";
    $req = '';
  }else { 
    $focus1 = ' onfocus="this.select();" autofocus';
    $disable = 'disabled';
    $style = 'style="display:none"';
    $style1 = "";
    $col = "col-md-12";
    $req = 'required';
  }
  $storigen = '';
  $btnorigen = 'type="button" onclick="cobros();"';
  $change = 'onchange="setcxp();"';
  echo'<div class="container mt-1 col-10">
    <form action="?modulo=egresos&accion='.$accion.'" method="post" enctype="multipart/form-data" name="frmegreso" onsubmit="checksubmit();">
      <input type="hidden" id="final" value="0"/>';
      if(!isset($_GET['idcxp'])) echo'<div id="list1"></div>';
      echo'<div class="row">
        <div class="'.$col.' alert alert-primary">'.$title.' Egreso</div>';
        if($_GET['idcxp'])
          echo '<div class="col-md-2 valign-middle" >
            <button type="button" class="btn btn-danger" onclick="confirmcondona('.$_GET['idcxp'].')">
              <i class="fas fa-times-circle"></i> Condonar egreso
            </button>
          </div>';
      echo '</div>';
      $sqleg = 'SELECT e_id FROM egresos WHERE e_estatus IN ("A","N") AND e_proveedor = "'.$egres->proveedor.'"
                ORDER BY e_fecha ASC LIMIT 1';
      $resulteg = setq($sqleg);
      list($ideg) = $resulteg->fetch_array();
      if($ideg){
        echo '<div class="row">
          <div class="col-md-12 alert alert-whitee text-xs">
            El proveedor seleccionado tiene saldo disponible de un egreso anterior
            &nbsp;<a href="?modulo=egresos&accion=show&id='.$ideg.'">
              <button type="button" class="btn-sm btn-success"><i class="fas fa-eye"></i> Ver</button>
            </a>
          </div>
        </div>';
      }
      echo '<div class="row">
        <div class="col-12 col-md-4 excxp">
          <div class="mb-5">
            <label for"fini">Cuenta  </label> <label class="font-small-2" style="color:red" id="errorc"></label>';
            $sql = 'SELECT cu_id,cu_nmb FROM cuentas INNER JOIN cortes ON cu_id = c_cuenta
                    WHERE c_estatus = "A" AND cu_estatus = "A" ORDER BY cu_orden';
            $result = setq($sql);
            while($rowi = $result->fetch_array()){
              $saldo = saldo_actual($rowi['cu_id']);
              echo '<input type="hidden" id="saldo'.$rowi['cu_id'].'" value="'.$saldo.'">';
            }
            $result = setq($sql);
            echo '<select name="cuenta" id="cuenta" class="form-control" required onchange="setminasaldo()" autofocus>
              <option value="" disabled selected>Elige una cuenta</option>';
              while($row = $result->fetch_array()){
                if($row['cu_id'] == $egres->cuenta) $selcu = 'selected'; else $selcu = "";
                echo '<option value="'.$row['cu_id'].'" '.$selcu.'>'.$row['cu_nmb'].' - $ '.number_format(saldo_actual($row['cu_id']),2).'</option>';
              }
            echo '</select>
          </div>
        </div>
        <div class="col-12 col-md-3 excxp">
          <div class="mb-5">
            <label for"nmbac">Importe del egreso</label>
            <input type="hidden" value="'.$egres->monto.'" id="maximp"/>
            <input type="number" min="0" step="0.01" '.$maximp.' name="importe" value="'.$egres->monto.'" id="importe" class="form-control number-align" placeholder="Importe del egreso" required="required"  title="El importe debe ser menor o igual al saldo disponible" onchange="nuevosaldo();" />
          </div>
        </div>
        <div class="col-12 col-md-5">
          <div class="mb-5">
            <label for"nmbac">Selecciona un Proveedor</label>';
            if(isset($_GET['idcxp']) && ($cxtipo == "O" || $cxtipo == "P")){
              echo '<select class="form-control" searchable="Selecciona a tu proveedor" name="proveedor" id="proveedor1" required >';
                if($_GET['idcxp']){
                  $sql = 'SELECT p_id,p_alias FROM proveedores WHERE p_predeterminado = "1" ORDER BY p_nmb';
                  $result = setq($sql) or die($sql);
                  echo '<option value="" disabled '.$seldis.'>Elegir Proveedor</option>';
                  while($row = $result->fetch_array()){
                    if($row['p_id'] == $egres->proveedor) $sel = 'selected'; else $sel = "";
                    echo '<option value="'.$row['p_id'].'" '.$sel.'>'.$row['p_alias'].'</option>';
                  }
                }else{
                  echo '<option value="'.$_SESSION['emp'].'">'.busca(1,'empresas','e_id','e_nmb').'</option>';
                }
              echo '</select>';
            }else{
              $sql = 'SELECT p_id,p_alias FROM proveedores WHERE p_estatus = "A" /*'.$sqlac.' */ ORDER BY p_nmb';
              $result = setq($sql) or die($sql);
              echo '<select class="form-control" searchable="Buscar proveedor" name="proveedor" '.$change.' id="proveedor1"  required autofocus>';
                echo '<option value="" disabled '.$seldis.'>Elegir Proveedor</option>';
                while($row = $result->fetch_array()){
                  if($row['p_id'] == $egres->proveedor) $sel = 'selected'; else $sel = "";
                  echo '<option value="'.$row['p_id'].'" '.$sel.'>'.$row['p_alias'].'</option>';
                }
              echo '</select>
              <label class="font-small-2" style="color:red" id="errorp"></label>';
            }
          echo '</div>
        </div>
        <div class="col-6 col-md-4 excxp">
          <div class="mb-5">
            <label for"correo">Referencia</label>
            <input type="text" name="referencia" value="'.$egres->referencia.'" id="referencia" class="form-control" placeholder="REFERENCIA DEL DEPOSITO" onfocus="this.select();" />
          </div>
        </div>
        <div class="col-6 col-md-4 excxp">
          <div class="mb-5">
            <label for"correo">Adjuntar comprobante</label>
            <input type="file" name="adjunto" id="adjunto" class="form-control" placeholder="ADJUNTAR COMPROBANTE"/>
          </div>
        </div>
        <div class="col-6 col-md-4 excxp">
          <div class="mb-5">
            <label for"correo">Observaciones</label>
            <input type="text" name="observaciones" value="'.$egres->observaciones.'" id="observaciones" class="form-control" placeholder="Observaciones" onfocus="this.select();" '.$req.'/>
          </div>
        </div>
        <div class="col-6 col-md-4 excxp">
          <div class="mb-5">
            <label for"correo">Fecha del egreso</label>
            <input type="datetime-local" name="fecha" value="'.$egres->fecha.'" id="referencia" class="form-control" onfocus="this.select();" required />
          </div>
        </div>';
        if(isset($_GET['idcxp'])){
          echo '<div class="col-6 col-md-4">
            <div class="mb-5">
              <label for"correo">Aplicar a Cuenta por cobrar</label>
              <input type="hidden" name="cxcobrar" value="'.$cxid.'" />
              <input type="text" value="'.$desc.'" class="form-control" disabled />
            </div>
          </div>
          <div class="col-12 col-md-12 excxp" >
            <center>
              <button type="submit" class="btn btn-primary" id="guardar" '.$disable.'><i class="fas fa-university"></i> GUARDAR </button>
            </center>
          </div>';
        }else{
          echo'<div class="col-12 col-md-3 excxp" '.$storigen.'>
            <div class="mb-5">
              <label for"nmbac">Suma de las cuentas por pagar</label>
              <input type="number" min="0" step="0.01" name="importelote" value="0" id="importelote" class="form-control number-align" placeholder="Importe del egreso" required="required" title="El importe debe ser menor o igual al saldo disponible" readonly/>
              <label class="font-small-2" style="color:red" id="error"></label>
            </div>
          </div>
          <div class="col-12 col-md-12 excxp" >
            <center>
              <button '.$btnorigen.' class="btn btn-primary" id="guardar" '.$disable.'><i class="fas fa-university"></i> GUARDAR </button>
            </center>
          </div>
          <div id="msjerror" class="col-md-12 mb-5">
          </div>';
        }
      echo '</div>
    </form>';
    echo'<div id="listcxp" class="mscxp" '.$style1.'>
    </div>
  </div>
  </div>';
?>