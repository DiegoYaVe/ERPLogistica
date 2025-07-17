<?php
  session_start();
  date_default_timezone_set("America/Mexico_City");
  include_once('../funciones.php');

  include_once('../modulos/remisiones.php');
  $mremision = new modelremisiones();
  $mremision->select($_GET['idrem']);

  include_once('../modulos/clientes.php');
  $mcliente = new modelclientes();
  
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
  </script>';

  if(!isset($_GET['idrem'])) $_GET['idrem'] = "0";

  $diva = ' checked="checked" ';
  $dtotal = ' checked="checked" ';
  if($mremision->id){
    $cliente = $mremision->cliente;
    $mcliente->select($mremision->cliente);
    $nmbact = $mremision->nmb;
    $ffin = $mremision->fliquidacion;
    $accion = 'updateremision&idremision='.$mremision->id;
    $title = "Editar";
    $btnText = 'Actualizar';
    $responsable = $mremision->encargado;
    $nmbresponsable = busca($responsable, 'usuarios', 'u_id', 'CONCAT(u_nmb, " ",u_apellidos)');
    $descuento = $mremision->descuento;
    $almacen = $mremision->almacen;
    if(!$mremision->faplica) $faplica= date('Y-m-d');
    else $faplica = date("Y-m-d",strtotime($mremision->faplica));
    $sqlidr = ' AND ct_id = "'.$mremision->tablero.'" ';
    if($mremision->diva == 0) $diva = '';
  }else{
    $mcliente->select($_GET['cliente']);
    if(!isset($nmbact)) $nmbact = "Remisión para ".$mcliente->nmb;
    $almacen = $mcliente->almacen;
    $ffin = date('Y-m-d');
    $faplica = date('Y-m-d');
    $responsable = $_SESSION['uid'];
    $nmbresponsable = busca($responsable, 'usuarios', 'u_id', 'CONCAT(u_nmb, " ",u_apellidos)');
    $accion = 'insert';
    $title = "Registrar";
    $btnText = 'Crear';
    $descuento = 0;
    $sqlidr = "";
    $cliente = $_GET['cliente'];
    if(isset($_GET['tablero'])){
      $mremision->tablero = $_GET['tablero'];
      $nmbact = busca($mremision->tablero,'crm_tableros','ct_id','ct_nmb');
    }
  }

  if(isset($_GET['read'])) {
    $read = 'readonly';
    $title = 'Información de la remisión';
    $di = 'hidden';
    $accion = 'updateremision&idremision='.$mremision->id;
  }

  if(busca($_SESSION['uid'],'usuarios','u_id','u_grupo') == "USER")
    $almacenuser = ' AND a_id = "'.busca($_SESSION['uid'],'usuarios','a_id','a_almacen').'" ';
  else $almacenuser = "";

  echo'
  <div class="container mt-1 col-10">
    <form action="?modulo=remisiones&accion='.$accion.'" method="post" onsubmit="checksubmit();">
      <input type="hidden" name="empresa" value="'.$_SESSION['emp'].'" />
      <input type="hidden" name="cliente" value="'.$cliente.'" />
      <div class="row">
        <div class="col-12 col-md-12 alert alert-primary">'.$title.' Remisión</div>
        <div class="btn-group" data-toggle="buttons"></div>
      </div>
      <div class="row">
        <div class="col-12 col-md-3 ">
          <div class="mb-5">
            <label for"nmbac">Alias de tu Remisión</label>
            <input type="text" name="nmbac" maxlength="50" value="'.$nmbact.'" id="nmbac" class="form-control" placeholder="Nombre de tu actividad" required="required" onfocus="this.select();" '.$read.'/>
          </div>
        </div>
        <div class="col-12 col-md-2" >
          <div class="mb-5">
            <label for"fini">Fecha liquidación</label>
            <input type="date" name="ffin" id="ffin" value="'.$ffin.'" class="form-control" placeholder="Cuando programas tu actividad" required="required" '.$leer.'/>
          </div>
        </div>
        <div class="col-6 col-md-2">
          <div class="mb-5">
            <label for"correo">Responsable</label>';
            //if(!isset($_GET['read'])){
              $grupo = busca($_SESSION['uid'], 'usuarios', 'u_id', 'u_grupo');
              if($grupo == "ADMIN" || $grupo == "GERENCIA"){
                echo '<select class="form-control" id="responsable" name="responsable">';
                $sqlu ='SELECT * FROM usuarios WHERE u_estatus = "A"';
                $resultu = setq($sqlu);
                while($rowu = $resultu -> fetch_array()){
                  if($rowu['u_id'] != "ADMIN"){
                    if($responsable == $rowu['u_id']) $sel = 'selected';
                    else $sel = '';
                    echo '<option value="'.$rowu['u_id'].'" '.$sel.'>'.$rowu['u_nmb'].' '.$rowu['u_apellidos'].'</option>';
                  }
                }
                echo '</select>';
              }
              else 
              echo '<input type="text" value="'.$nmbresponsable.'" class="form-control" disabled/>'; 
            //}
            /* else 
              echo '<input type="text" value="'.$responsable.'" class="form-control" '.$read.'/>'; */
          echo '</div>
        </div>
        <div class="col-6 col-md-3">
          <div class="mb-5">
            <label for"correo">Almacen</label>';
            if(!isset($_GET['read']))
              echo menu_select_db('almacenes','a_id','a_nmb',$almacen,'almacen','a_vendible = "1" AND a_estatus = "A"'.$almacenuser,false,false,false,true,"Selecciona un almacen");
            else{
              echo '<input type="text" value="'.busca($almacen,'almacenes','a_id','a_nmb').'" class="form-control" '.$read.'/>';
              echo '<input type="hidden" value="'.$almacen.'" name="almacen">';
            }
              
          echo '</div>
        </div>
        ';
        if($grupo == "ADMIN" || $grupo == "GERENCIA") $leer = '';
        else $leer = 'readonly';
        echo '<div class="col-6 col-md-2">
          <div class="mb-5">
            <label for"fini">Fecha de aplicación</label>
            <input type="date" name="faplica" value="'.$faplica.'" id="faplica" class="form-control" placeholder="Fecha de aplicación" required="required" onfocus="this.select();" data-toggle="tooltip" title="Fecha de aplicación" '.$leer.'/>
          </div>
        </div>
       <!-- <div class="col-12 col-md-8" '.$di.'>
          <div class="mb-5">
            <label for"correo">Tablero</label>';
            /* $sqlt = 'SELECT * FROM crm_tableros WHERE ct_cliente = "'.$mcliente->id.'" AND ct_estatus IN ("N","P","G","L","A")
                    '.$sqlidr.' ORDER BY ct_fcierre DESC,ct_folio DESC';
            $resultt = setq($sqlt);
            echo '<select class="form-control" name="tablero" required="required" '.$read.'>';
              echo '<option value="XXX">Nuevo tablero</option>';
              while($row = $resultt->fetch_array()){
                if($row['ct_id'] == $mremision->tablero) $selt = 'selected'; else $selt = "";
                echo '<option value="'.$row['ct_id'].'" '.$selt.'>'.$row['ct_folio'].' - '.$row['ct_nmb'].' - '.date('d-m-Y',strtotime($row['ct_fcierre'])).'</option>';
              }
            echo '</select>'; */
            
          echo '</div>
        </div> -->
        <div class="col-12 col-md-2" '.$di.'>
          <div class="mb-5">
            <label for"fini">Desglosar IVA</label>
            <input type="checkbox" name="diva" '.$diva.' class="flipswitchsn"/>
          </div>
        </div>';
        if(isset($_GET['read'])){
          echo'</div>
          <div class="row"> 
          <div class="col-6 col-md-3" >
            <div class="mb-5">
              <label for"fini">Fecha esperada de pago</label>
              <input type="date" value="'.date("Y-m-d",strtotime($mremision->faplica)).'" class="form-control" placeholder="Cuando programas tu actividad" required="required" '.$read.'/>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="mb-5">
              <label for"fini">Importe de la remisión</label>
              <input type="number" value="'.number_format($mremision->total,2,'.','').'" class="form-control number-align" '.$read.' />
            </div>
          </div>
          <div class="col-6 col-md-2">
          <div class="mb-5">
            <label for"fini">% Descuento</label>
            <input type="number" name="descuento" min="0" max="100" step="1" value="'.$descuento.'" id="descuento" class="form-control" placeholder="Porcentaje de descuento" required="required" onfocus="this.select();" data-toggle="tooltip" data-placement="right" title="Expresar descuento en porcentaje" '.$read.'/>
          </div>
        </div>';
          if($mremision->estatus == "A" || $mremision->estatus == "F" || $mremision->estatus == "FE"){
            if($mremision->estatus == "FE") $leer = 'readonly';
            echo '<div class="col-6 col-md-3">
              <div class="mb-5">
                <label for"fini">Costo de envío</label>
                <input type="number" step="0.01" name="envio" value="'.number_format($mremision->precioenvio,2,'.','').'" class="form-control number-align" '.$leer.'/>
              </div>
            </div>';
          }
        }
        echo'<div class="col-12 col-md-12 alert alert-primary">Información del cliente</div>
        <div class="col-12 col-md-4">
          <div class="mb-5">
            <label for"destino">Cliente</label>
            <input type="text" name="destino" id="destino" value="'.$mcliente->nmb.' '.$mcliente->apellidos.'" class="form-control" placeholder="Persona para contactar" readonly="readonly"  />
          </div>
        </div>
        <div class="col-6 col-md-4">
          <div class="mb-5">
            <label for"telefono">Número telefónico</label>
            <input type="tel" name="telefono" id="telefono" value="'.$mcliente->telefono1.'" class="form-control" placeholder="Telefóno del contacto" readonly="readonly" />
          </div>
        </div>
        <div class="col-6 col-md-4">
          <div class="mb-5">
            <label for"correo">Correo destino</label>
            <input type="email" style="text-transform:lowercase;" name="correo" id="correo" value="'.$mcliente->correo1.'" class="form-control" placeholder="correo de la contacto" readonly="readonly" />
          </div>
        </div>';
        //if(!isset($_GET['read']))
          echo'<div class="col-12 col-md-12">
            <center><button type="submit" class="btn btn-primary" id="guardar"><i class="fa fa-save"></i> '.$btnText.'</button></center>
          </div>';
      echo'</div>
    </form>
  </div>';
?>