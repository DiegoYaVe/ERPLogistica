<?php
  session_start();
  date_default_timezone_set("America/Mexico_City");
  include_once('../funciones.php');

  if(!isset($_GET['id'])) $_GET['id'] = NULL;
  include_once('../modulos/ingresos.php');
  $ingres = new Modelingresos();
  $ingres->select($_GET['id']);
  if($ingres->id){
    $accion = 'update&id='.$_GET['id'];
    $title = "Editar un ";
  }
  else{
    $accion = 'insert';
    $title = "Insertar ";
    $seldis = "selected";
    $ingres->fecha = date('Y-m-d').'T'.date('H:i:s');
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
  function confirmcondona(cxc){
    var conf = confirm("Al condonar la Cuenta por cobrar, será cancelada y no podrá ser recuperada\n¿Deseas continuar?");
    if(conf == true){
      document.getElementById("idcxccon").value = cxc;
      document.setcondonar.submit();
    }
  }
</script>
<script>
  function setcxc(){
    var clt = $("#cliente1").val();
    console.log("cliente: "+clt);
    $.ajax({
      url: "query/listcxcobrar.php",
      type: "POST",
      dataType: "html",
      data: {'clt': clt},
    })
    .done(function(datacol){
      $("#listcxc").html(datacol);
      if($("#importe").val() == ""){
        $("#importe").focus();
        $("#listcxc").prop('style','display:none');
      }
    });
  }
  function sum(id){
    cb = document.getElementById(id);

    var saldo = parseFloat(document.getElementById("monto"+id).value);
    var nsaldo = parseFloat(document.getElementById("nsaldo"+id).value);
    var maxsaldo = parseFloat(document.getElementById("nsaldo"+id).max);

    var monto = parseFloat(document.getElementById("importelote").value);
    var max = parseFloat(document.getElementById("importelote").max);
    var importe = parseFloat(document.getElementById("importe").value);
    
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
          alert("El monto a cobrar de la cuenta debe ser menor o igual a: $"+maxsaldo+",\nPor favor verifica antes de continuar");
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
          if(suma > 0){
            var nval = importe - suma;
            nsaldo = nval;
          }else {
            nsaldo = importe;
          }
          document.getElementById("nsaldo"+id).value = nsaldo.toFixed(2);
        }else {
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
    }else{
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

    var checkboxes = document.querySelectorAll("input[name='chcxc']:checked");
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
    for(let i = 0; i < values.length; i++){
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
      var c = document.getElementById("saldorest");
      if(c !== null){
        document.frmingreso.submit();
        document.getElementById("guardar").value = "JD";
        document.getElementById("guardar").disabled = true;
      }else if(parseFloat(implote) < parseFloat(impingreso)){
        var div = document.createElement('div');
        div.innerHTML = '<div class="mb-1"><label styele="display: flex;align-items: center;"><font color="red">El importe de las cuentas seleccionadas es menor al ingreso, si deseas generar un saldo restante marca la casilla siguiente</font></label>&nbsp;&nbsp;<!--<i class="icon icon-arrow-right-c" style="font-size:25px"></i>--><input style="display:inline-block;width:22px;height:22px;border: 3px solid red;" type="checkbox" id="saldorest" onclick="formenv();" /></div>';
        document.getElementById('msjerror').appendChild(div);
        document.getElementById("guardar").disabled = true;
      }else{
        document.frmingreso.submit();
        document.getElementById("guardar").value = "JD";
        document.getElementById("guardar").disabled = true;
      }
    }
    else {
      $("#cuenta").focus();
      $("#cuenta").css("border", "solid 2px red");
    }
  }
  function nuevosaldo(){
    var importe = document.getElementById('importe').value;
    var monto = parseFloat(document.getElementById("importelote").value);
    if(importe > 0){
      $("#listcxc").prop('style','display:block');
      if(parseFloat(importe) < monto) {
        document.getElementById("guardar").disabled = true;
        $("#importelote").css("border", "solid 2px red");
        document.getElementById("error").innerHTML = 'La suma de la(s) cuenta(s) es mayor al importe del egreso';
      }else {
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
      console.log("importe: "+importe);
      var saldo = parseFloat(document.getElementById("monto"+k).value);
      var nsaldo = parseFloat(document.getElementById("nsaldo"+k).value);
      var implote = parseFloat(document.getElementById("importelote").value);
      var importenuevo = parseFloat(document.getElementById("importe").value);
      if(implote == importenuevo){
        cb = document.getElementById(k);
        cb.checked = false;
        document.getElementById("monto"+k).value = parseFloat(0);
        document.getElementById("nsaldo"+k).value = parseFloat(0);
        document.getElementById("nsaldo"+k).disabled = true;
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
  echo'
  <div class="container mt-1 col-10">
    <form action="?modulo=ingresos&accion='.$accion.'" method="post" enctype="multipart/form-data" name="frmingreso" onsubmit="checksubmit();">';
      echo'<div id="list1"></div>';
      echo'
      <div class="row">
        <div class="col-12 col-md-12 alert alert-primary">'.$title.' Ingresos</div>
      </div>';
      echo '
      <div class="row">
        <div class="col-12 col-md-4" >
          <div class="mb-5">
            <label for"fini">Cuenta</label>';
            $sql = 'SELECT cu_id,cu_nmb FROM cuentas INNER JOIN cortes ON cu_id = c_cuenta
                    WHERE c_estatus = "A" ORDER BY cu_orden';
            $result = setq($sql);
            echo '
            <select name="cuenta" id="cuenta" class="form-control" required >';
              while($row = $result->fetch_array()){
                if($row['cu_id'] == $ingres->cuenta) $selcu = 'selected'; else $selcu = "";
                echo '<option value="'.$row['cu_id'].'" '.$selcu.'>'.$row['cu_nmb'].'</option>';
              }
              echo '
            </select>
          </div>
        </div>
        <div class="col-12 col-md-3">
          <div class="mb-5">
            <label for"importe">Importe del ingreso</label>
            <input type="number" min="0" step="0.01" '.$maximp.' name="importe" value="" id="importe" class="form-control number-align" placeholder="Importe del ingreso" required="required" onchange="nuevosaldo();" onfocus="this.select();" autofocus />
          </div>
        </div>
        <div class="col-12 col-md-5">
          <div class="mb-5">
            <label for"nmbac">Selecciona un cliente</label>';
            if(busca($_SESSION['uid'],'usuarios','u_id','u_grupo') == "USER"){
              $sqlac.= ' AND c_almacen = "'.busca($_SESSION['uid'],'usuarios','u_id','u_almacen').'" ';
            }
            $sql = 'SELECT c_id,c_alias,a_nmb FROM crm_clientes INNER JOIN almacenes ON a_id = c_almacen
                    WHERE 1=1 '.$sqlac.' ORDER BY c_almacen,c_nmb';
            $result = setq($sql);
            echo '
            <select class="form-control" searchable="Buscar cliente" name="cliente" id="cliente1" onchange="setcxc();" required>';
              echo '
              <option value="" disabled '.$seldis.'>Elegir Cliente</option>';
              while($row = $result->fetch_array()){
                if($row['c_id'] == $ingres->cliente) $sel = 'selected'; else $sel = "";
                echo '<option value="'.$row['c_id'].'" '.$sel.'>'.$row['c_alias'].' - '.$row['a_nmb'].'</option>';
              }
              echo '
            </select>
          </div>
        </div>
        <div class="col-6 col-md-4">
          <div class="mb-5">
            <label for"correo">Referencia</label>
            <input type="text" name="referencia" value="'.$ingres->referencia.'" id="referencia" class="form-control" placeholder="REFERENCIA DEL DEPOSITO" onfocus="this.select();" />
          </div>
        </div>
        <div class="col-6 col-md-4">
          <div class="mb-5">
            <label for"correo">Adjuntar comprobante</label>
            <input type="file" name="adjunto" id="adjunto" class="form-control" placeholder="ADJUNTAR COMPROBANTE"/>
          </div>
        </div>
        <div class="col-6 col-md-4">
          <div class="mb-5">
            <label for"correo">Observaciones</label>
            <input type="text" name="observaciones" value="'.$ingres->observaciones.'" id="observaciones" class="form-control" placeholder="Observaciones" onfocus="this.select();" />
          </div>
        </div>
        <div class="col-6 col-md-4">
          <div class="mb-5">
            <label for"correo">Fecha del ingreso</label>
            <input type="datetime-local" name="fecha" value="'.$ingres->fecha.'" id="referencia" class="form-control" onfocus="this.select();" required />
          </div>
        </div>';
          echo'
        <div class="col-12 col-md-3 excxp">
          <div class="mb-5">
            <label for"nmbac">Suma de las cuentas por cobrar</label>
            <input type="number" min="0" step="0.01" name="importelote" value="0" id="importelote" class="form-control number-align" placeholder="Importe del egreso" required="required" title="El importe debe ser menor o igual al importe del egreso" readonly/>
            <label class="font-small-2" style="color:red" id="error"></label>
          </div>
        </div>';
        echo '
        <div class="col-12 col-md-12">
          <center><button type="button" class="btn btn-primary" id="guardar" '.$disable.' onclick="cobros();"><i class="icon-bank"></i> GUARDAR </button></center>
        </div>
        <div id="msjerror" class="col-md-12 mb-5">
        </div>
      </div>
    </form>';
    echo'
      <div id="listcxc" class="mscxp" '.$style1.'>
      </div>
  </div>';
?>