<?php
  ini_set('display_errors', 1);
  session_start();
  include_once('../funciones.php');

  if(isset($_GET['id'])) $ordenc = $_GET['id'];
  include_once('../modulos/ordenesc.php');
  $orden = new modelordenesc();
  $orden->select($ordenc);
  $tipo = 0;
  if($orden->estatus == "P"){
    $hidden = "";
    $hdcorreo = 'hidden';
    $required = "required";
  }else{
    $hidden = "hidden";
    $tipo = 1;
    $hdcorreo = '';
    $required = "";
  }

  echo '
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
    function datoscontacto(){
      var vendedor = document.getElementById("vendedor").value;
      var correo = document.getElementById("correo"+vendedor).value;
      document.getElementById("mailvendedor").value = correo;
    }
  </script>';
    if($orden->estatus == "A"){
      $accion = "reenviarcorreo&id=$orden->id";
      $hdcorreo = 'hidden';
    }else{
      $accion = "aplicar&id=$orden->id";
    }

    $existecorreo = busca($_SESSION['uid'],'usuarios','u_id','u_mailcorp');
    if($existecorreo){
      $disabledcorreo = '';
      $checkedcorreo = 'checked';
      $alert = '';
    }else{
      $disabledcorreo = 'disabled';
      $checkedcorreo = '';
      $alert = '<div class="alert alert-danger col-md-12 text-xs-center">Necesitas tener un correo corporativo configurado para mandar los correos.</div>';
    }

  echo'<input type="hidden" id="empresa" value="'.$_SESSION['emp'].'" />
  <input type="hidden" id="tipo" value="'.$tipo.'" />
  <div class="container mt-1">
    <form action="?modulo=ordenesc&accion='.$accion.'" method="post" onsubmit="checksubmit();">
      <div class="row">
        <div class="col-12 col-md-12 alert alert-primary" style="justify-content: space-around; display: block;">Finalizar Orden de compra '.$orden->folio.' - '.busca($orden->proveedor,'proveedores','p_id','p_nmb').' 
        </div>
      </div>
      <div class="mb-5 col-md-3">
        <div class="">
          <label for"correo" class="">Enviar por correo</label><br>
          <input type="checkbox" id="checkmail" name="checkmail" class="flipswitchsn" onchange="enviarono();" '.$disabledcorreo.' '.$checkedcorreo.'  />
        </div>
      </div>
      '.$alert.'';
      
      /*$sql = 'SELECT pc_nmb,pc_correo FROM proveedores_contactos WHERE pc_proveedor = "'.$orden->proveedor.'" LIMIT 0,1';
      $result = setq($sql);
      list($nombrepc,$correopc) = $result->fetch_array();
      if(!$orden->vendedor) $orden->vendedor = $nombrepc;
      if(!$orden->mailvendedor) $orden->mailvendedor = $correopc;
      <input type="text" name="vendedor" value="'.$orden->vendedor.'" class="form-control" onfocus="this.select();" required />*/
      echo '<div class="row container" id="rowx">';
        $sqlcont = 'SELECT pc_nmb,pc_correo FROM proveedores_contactos WHERE pc_proveedor = "'.$orden->proveedor.'"';
        $resultcont = setq($sqlcont);
        $html = "";
        if($resultcont->num_rows > 0){
          $html = '<select id="vendedor" name="vendedor" class="form-control" onchange="datoscontacto();" required>';
          while($rowcont = $resultcont->fetch_array()){
            echo '
            <input type="hidden" id="correo'.$rowcont['pc_nmb'].'" value="'.$rowcont['pc_correo'].'" class="form-control"/>';
            if(!$correopc) {
              $correopc = $rowcont['pc_correo'];
              $selected = 'selected';
            }else{
              $selected = '';
            }
            $html .= '<option value="'.$rowcont['pc_nmb'].'" '.$selected.'>'.$rowcont["pc_nmb"].'</option>';
          }
          $html .= '</select>';
        }else{
          $html = '<input type="text" id="vendedor" name="vendedor" value="'.$orden->vendedor.'" class="form-control"  required  '.$required.'/>';
        }
        echo'
        <div class="col-md-4">
          <div class="mb-5">
            <label for="correo">Contacto de proveedor</label>
            '.$html.'
          </div>
        </div>
        <div class="mb-5 col-md-4">
          <div class="mb-5">
            <label for"correo">Correo proveedor</label>
            <input type="email" style="text-transform:lowercase;" id="mailvendedor" name="mailvendedor" value="'.$correopc.'" class="form-control"  required '.$required.'/>
          </div>
        </div>
        <div class="mb-5 col-md-4" '.$hidden.'>
          <div class="mb-5">
            <label for"fini">Fecha estimada de entrega</label>
            <input type="date" name="fechacom" id="fechacom" value="'.$orden->fechacom.'" class="form-control" placeholder="Cuando arriba tu compra" '.$required.' />
          </div>
        </div>
        <div class="mb-5 col-md-4" '.$hidden.'>
          <div class="mb-5">
            <label for"correo">Receptor</label>
            <input type="text" id="receptor" name="receptor" value="'.$orden->receptor.'" class="form-control" '.$required.'/>
          </div>
        </div>
        <div class="mb-5 col-md-4" '.$hidden.'>
          <div class="mb-5">
            <label for"correo">Domicilio Entrega</label>
            <input type="text" id="destino" name="destino" value="'.$orden->destino.'" class="form-control"  '.$required.'/>
          </div>
        </div>';
        echo '<div class="mb-5 col-md-12" '.$hidden.' >
          <div class="mb-5">
            <label for"correo">Observaciones</label>
            <textarea class="form-control" name="descripcion" rows="2" placeholder="Si tienes alguna anotación sobre la compra, anotala " >'.$orden->observaciones.'</textarea>
          </div>
        </div>
      </div>
    <div class="mb-5 col-md-12">
      <center><button type="submit" class="btn btn-success" id="guardar"><i class="fa fa-check"></i> Finalizar</button></center>
    </div>
    </form>
  </div>';

  echo'
  <script>
    enviarono();
    function enviarono(){
      var tipo = parseFloat(document.getElementById("tipo").value);
      if($("#checkmail").prop("checked") == true){
        document.getElementById("rowx").style="display: block;";
        document.getElementById("mailvendedor").setAttribute("required","required");
        document.getElementById("vendedor").setAttribute("required","required");

        if(tipo == 1){
          document.getElementById("fechacom").removeAttribute("required");
          document.getElementById("receptor").removeAttribute("required");
          document.getElementById("destino").removeAttribute("required");
        }else{
          document.getElementById("fechacom").setAttribute("required","required");
          document.getElementById("receptor").setAttribute("required","required");
          document.getElementById("destino").setAttribute("required","required");
        }
      }else{
        document.getElementById("vendedor").removeAttribute("required");
        document.getElementById("mailvendedor").removeAttribute("required");
        document.getElementById("fechacom").removeAttribute("required");
        document.getElementById("receptor").removeAttribute("required");
        document.getElementById("destino").removeAttribute("required");
        document.getElementById("rowx").style="display: none;";
      }    
    }
  </script>';
?>