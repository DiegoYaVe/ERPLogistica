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
  session_start();
  include_once('../funciones.php');

  if(!isset($_GET['id'])) $_GET['id'] = NULL;
  include_once('../modulos/gastos.php');
  $gast = new Modelgastos();
  $gast->select($_GET['id']);
  if($gast->id){
    $accion = 'update&id='.$_GET['id'];
    $title = "Aplicar un ";
    $disable = 'readonly';
    if($gast->tipo == "V") {
      $accion = 'autorizarv&id='.$_GET['id'];
      $selV = 'selected';
      $styV = 'style="display:none"';
      $v = 1;
    }else {
      $selG = 'selected';
      $styG = 'style="display:none"';
      $g = 1;
    }
  }
  else{
    $accion = 'insert';
    $title = "Insertar un ";
    $seldis = "selected";
    $gast->fecha = date('Y-m-d').'T'.date('H:i:s');
  }
?>
  <script>
    function setminasaldo(){
      var cuentw = document.getElementById("cuenta").value;
      var saldo = document.getElementById("saldo" + cuentw).value;
      document.getElementById("importe").max = saldo;
    }
    function settipo(){
      var tipog = document.getElementById("tipog").value;
      if(tipog == "G"){
        setminasaldo();
        document.getElementById("congasto").style.display = "";
        document.getElementById("convale").style.display = "none";
        document.getElementById("conceptog").required = true;
        document.getElementById("conceptov").required = false;
      }
      else{
        document.getElementById("importe").max = "999999";
        document.getElementById("congasto").style.display = "none";
        document.getElementById("convale").style.display = "";
        document.getElementById("conceptog").required = false;
        document.getElementById("conceptov").required = true;
      }
    }
  </script>
<?php
  $sqlac = "";
  $sqlminc = 'SELECT cu_id,cu_nmb,c_id FROM cuentas INNER JOIN cortes ON cu_id = c_cuenta
              WHERE cu_empresa = "'.$_SESSION['id'].'" AND c_estatus = "A" ORDER BY cu_orden';
  $result = setq($sqlminc);
  $sqlmn = $sqlminc.' LIMIT 0,1';
  $resultnc = setq($sqlmn);
  list($cuentasel) = $resultnc->fetch_array();
  $maximp = saldo_actual($cuentasel);
  
  echo'<div class="container mt-1 ">
    <form action="?modulo=gastos&accion='.$accion.'" method="post" enctype="multipart/form-data" onsubmit="checksubmit();">
      <input type="hidden" name="empresa" value="'.$_SESSION['emp'].'" />
      <div class="row">
        <div class="col-12 col-md-12 alert alert-primary">'.$title.' Gasto</div>
        <div class="btn-group" data-toggle="buttons">';
        echo '</div>
      </div>';
      echo '<div class="row">
        <div class="col-12 col-md-3">
          <div class="mb-5">
            <label for"nmbac">Tipo de Gasto <i class="icon-exclamation-circle" data-toggle="tooltip" data-placement="top" title="Gasto: El saldo se descuenta al momento - Vale: Entra a planeación de cuentas por pagar y se puede editar"></i></label>';
            echo'<select class="form-control" searchable="Seleccionar tipo de gasto" onchange="settipo();" name="tipo" id="tipog" required >
              <option value="G">Gasto</option>
              <option value="V">Vale</option>
            </select>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="mb-5">
            <label for"correo">Descripcion</label>
            <input type="text" name="descripcion" value="'.$gast->descripcion.'" id="descripcion" required class="form-control" placeholder="Observaciones" onfocus="this.select();" />
          </div>
        </div>
        <div class="col-12 col-md-3">
          <div class="mb-5">
            <label for"nmbac">Importe del Gasto  <i class="icon-exclamation-circle" data-toggle="tooltip" data-placement="top" title="Si es gasto, el importe no debe superar el saldo de la cuenta"></i></label>
            <input type="number" min="0" step="0.01" max="'.$maximp.'" name="importe" value="'.$gast->monto.'" id="importe" class="form-control number-align" placeholder="Importe del gasto" required="required" onfocus="this.select();" title="El importe debe ser menor o igual al saldo disponible" autofocus />
          </div>
        </div>
        <div class="col-12 col-md-3" >
          <div class="mb-5">
            <label for"fini">Cuenta <i class="icon-exclamation-circle" data-toggle="tooltip" data-placement="top" title="Solo se muestran cuentas con saldo"></i> </label>';
            $sql = 'SELECT cu_id,cu_nmb FROM cuentas INNER JOIN cortes ON cu_id = c_id
                    WHERE c_estatus = "A" AND cu_empresa = "'.$_SESSION['emp'].'" ORDER BY cu_orden';
            $result = setq($sql);
            while($rowi = $result->fetch_array()){
              $saldo[$rowi['cu_id']] = saldo_actual($rowi['cu_id']);
              echo '<input type="hidden" id="saldo'.$rowi['cu_id'].'" value="'.$saldo[$rowi['cu_id']].'">';
            }
            $result = setq($sql);
            echo '<select name="cuenta" id="cuenta" class="form-control" required onchange="setminasaldo()" >';
              echo '<option value="" disabled selected>Elige una cuenta</option>';
              while($row = $result->fetch_array()){
                if($saldo[$row['cu_id']] > 0){
                  if($row['cu_id'] == $gast->cuenta) $selcu = 'selected'; else $selcu = "";
                  echo '<option value="'.$row['cu_id'].'" '.$selcu.'>'.$row['cu_nmb'].' - $ '.number_format(saldo_actual($row['cu_id']),2).'</option>';
                }
              }
            echo '</select>';
          echo '</div>
        </div>
        <div class="col-6 col-md-4" id="congasto">
          <div class="mb-5">
            <label for"correo">Concepto del gasto</label>';
            $sqlg = 'SELECT * FROM gastos_categoria WHERE gc_empresa = "'.$_SESSION['emp'].'" AND gc_tipo = "G" ORDER BY gc_nmb';
            $resultg = setq($sqlg);
            echo '<select name="conceptog" id="conceptog" class="form-control" required >';
              while($rowg = $resultg->fetch_array()){
                echo '<option value="'.$rowg['gc_id'].'">'.$rowg['gc_nmb'].'</option>';
              }
            echo '</select>';
          echo '</div>
        </div>
        <div class="col-6 col-md-4" id="convale" style="display:none">
          <div class="mb-5">
            <label for"correo">Concepto del Vale</label>';
            $sqlg = 'SELECT * FROM gastos_categoria WHERE gc_empresa = "'.$_SESSION['uid'].'" AND gc_tipo = "V" ORDER BY gc_nmb';
            $resultg = setq($sqlg);
            echo '<select name="conceptov" id="conceptov" class="form-control">';
              echo '<option value="" disabled selected>Elige una categoria</option>';
              while($rowg = $resultg->fetch_array()){
                echo '<option value="'.$rowg['gc_id'].'">'.$rowg['gc_nmb'].'</option>';
              }
            echo '</select>';
          echo '</div>
        </div>
        <div class="col-6 col-md-4">
          <div class="mb-5">
            <label for"correo">Fecha del egreso</label>
            <input type="datetime-local" name="fecha" value="'.$gast->fecha.'" id="referencia" class="form-control" onfocus="this.select();" required />
          </div>
        </div>
        <div class="col-6 col-md-4">
          <div class="mb-5">
            <label for"correo">Adjuntar comprobante <i class="icon-exclamation-circle" data-toggle="tooltip" data-placement="top" title="Comprobante de gasto/vale"></i> </label>
            <input type="file" name="adjunto" id="adjunto" class="form-control" placeholder="ADJUNTAR COMPROBANTE"/>
          </div>
        </div>';
        if(isset($_GET['idcxp'])){
          echo '<div class="col-6 col-md-4">
            <div class="mb-5">
              <label for"correo">Aplicar a Cuenta por cobrar</label>
              <input type="hidden" name="cxcobrar" value="'.$cxid.'" />
              <input type="text" value="'.$desc.'" class="form-control" disabled />
            </div>
          </div>';
        }
        echo '<div class="col-12 col-md-12">
          <center><button type="submit" class="btn btn-primary" id="guardar"><i class="icon-bank"></i> Finalizar</button></center>
        </div>
      </div>
    </form>
  </div>';
?>