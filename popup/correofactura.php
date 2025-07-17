<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 1 Jul 2000 05:00:00 GMT"); // Fecha en el pasado
session_start();
  include_once('../funciones.php');
 
echo '
<script language="javascript">
function checksubmit(){
  document.getElementById("enviarcorreo").value = "JD";
  document.getElementById("enviarcorreo").disabled = true;
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
function enable(){
  if(document.getElementById("enaex").checked){
      document.getElementById("correoex").disabled = false;
      document.getElementById("correoex").focus();
      document.getElementById("enaex").setAttribute("name","exena");
  }
  else{
      document.getElementById("correoex").disabled = true;
      document.getElementById("enaex").setAttribute("name","correo");
  }
}
</script>

';  

echo'<form action="mailer.php" method="get" target="_BLANK" class="table-responsive" onsubmit="checksubmit();">
<div class="container mt-1 ">
    <input type="hidden" name="empresa" value="'.$_SESSION['emp'].'" />
    <div class="row">
      <div class="col-12 col-md-12 alert alert-primary">Enviar factura por correo '.busca($_GET['factura'],'facturas','f_id','f_folio').'</div>
      <div class="btn-group" data-toggle="buttons">';
echo '</div>
    </div>';
    $cliente = busca($_GET['factura'],'facturas','f_id','f_cliente');
    /*
      <tr><td>'.busca($cliente,'crm_clientes','c_id','c_correo1').'</td>
      <td><input type="radio" class="custom-control-input"name="correo" value="'.busca($cliente,'crm_clientes','c_id','c_correo1').'" checked /></td></tr>';

    */
  echo '

      <table width="100%" class="table table-primary"><thead class="bg-primary text-white">
        <tr><th colspan="2">Elija el correo</th></tr></thead>
          <input type="hidden" name="factura" value="'.$_GET['factura'].'" />
          <input type="hidden" name="id" value="'.$_GET['factura'].'" />';
  $sql = 'SELECT cf_correo1,cf_correo2 correo FROM crm_fiscales WHERE cf_cliente = "'.$cliente.'"';
  $result = setq($sql) or die($sql);
  $idc = 0;
  while($row = $result->fetch_array()){
    $correos1[] = $row['cf_correo1'];
    $correos2[] = $row['cf_correo2'];
  }
  $correos1mod = array_unique($correos1);
  $fincorreos1 = array_filter($correos1mod);
  //var_dump($fincorreos1);
  $correos2mod = array_unique($correos2);
  $fincorreos2 = array_filter($correos2mod);
  //var_dump($fincorreos2);

  foreach($fincorreos1 as $c1){
    $idc++;
    if($idc == 1) $chec = "checked"; else $chec = "";
    echo '
    <tr>
      <td width="50%">
      <label class="display-inline-block custom-control custom-radio ml-1">
        <input type="radio" class="custom-control-input" name="correo" value="'.$c1.'" onchange="enable();" '.$chec.' />
        <span class="custom-control-indicator"></span>
        <span class="custom-control-description ml-0">' . $c1 . '</span>
      </label></td>
    </tr>';
    
  }

  foreach($fincorreos2 as $c2){
    $idc++;
    if($idc == 1) $chec = "checked"; else $chec = "";
    echo '
    <tr>
      <td width="50%">
      <label class="display-inline-block custom-control custom-radio ml-1">
        <input type="radio" class="custom-control-input" name="correo" value="'.$c2.'" onchange="enable();" '.$chec.' />
        <span class="custom-control-indicator"></span>
        <span class="custom-control-description ml-0">' . $c2 . '</span>
      </label></td>
    </tr>';
    
  }
  echo '<td>
            <label class="display-inline-block custom-control custom-radio ml-1">
              <input type="radio" class="custom-control-input" name="correo" id="enaex" onchange="enable();" />
              <span class="custom-control-indicator"></span>
              <span class="custom-control-description ml-0"><input class="form-control" type="email" style="text-transform:lowercase;" name="correo" placeholder="Correo Extra" id="correoex"  disabled /></span>
            </label>
        </td>';
  echo '<tr><td colspan="2"><input type="submit" class="btn btn-success" id="enviarcorreo" value="Enviar Correo" /></td></tr>';
  echo '
    </div>
  </form>
</div>';
?>