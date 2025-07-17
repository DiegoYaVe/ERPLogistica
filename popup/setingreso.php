<?php
  //ini_set('display_errors',1);
  session_start();
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
    $title = "Insertar un ";
    $seldis = "selected";
    $ingres->fecha = date('Y-m-d').'T'.date('H:i:s');
  }
  $sqlac = "";
  $maximp = ' max="99999999" ';
  if(isset($_GET['idcxc'])){
    $sql = 'SELECT cx_referencia,cx_id,cx_observaciones,cx_tipo,cx_cliente,cx_importe,cx_abonado
            FROM cxcobrar WHERE cx_id = "'.$_GET['idcxc'].'"';
    $result = setq($sql);
    list($cxref,$cxid,$cxobservaciones,$cxtipo,$cxcliente,$cximporte,$cxabonado) = $result->fetch_array();
    if($cxtipo == "R") $desc = busca($cxref,'remisiones','r_id','r_folio');
    else $desc = $cxobservaciones;

    $sqlac.= ' AND c_id = "'.$cxcliente.'" ';
    $ingres->cliente = $cxcliente;

    $ingres->monto = $cximporte-$cxabonado;
    if($ingres->monto < 0) $ingres->monto = 0;

    $maximp = ' max = "'.$ingres->monto.'" ';

    $accion.='&idcxc='.$_GET['idcxc'];
    $req = '';
  }else $req = 'required="required"';

  echo '<script language="javascript">
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
  </script>';

  echo'<div class="container mt-1 col-10">
    <form action="?modulo=ingresos&accion='.$accion.'" method="post" enctype="multipart/form-data" onsubmit="checksubmit();">
      
      <div class="row">
        <div class="col-10 col-md-10 alert alert-primary">'.$title.' Ingreso</div>';
        if($_GET['idcxc'])
          echo'<div class="col-2 col-md-2 valign-middle">
            <button type="button" class="btn btn-sm btn-danger" onclick="confirmcondona('.$_GET['idcxc'].')">
              <i class="fas fa-times-circle" style="color: #ffffff;"></i> Condonar pago
            </button>
          </div>';
      echo'</div>';
      if(isset($_GET['idcxc'])){
        $sqlig = 'SELECT i_id FROM ingresos WHERE i_estatus IN ("A","N") AND i_cliente = "'.$ingres->cliente.'"
                  ORDER BY i_fecha ASC LIMIT 1';
        $resultig = setq($sqlig);
        list($idig) = $resultig->fetch_array();
        if($idig){
          echo '<div class="row">
            <div class="col-md-12 alert alert-whitee text-xs">
              El cliente seleccionado tiene un saldo disponible de un ingreso anterior
              &nbsp;<a href="?modulo=ingresos&accion=show&id='.$idig.'">
                <button type="button" class="btn-sm btn-success"><i class="icon-eye4"></i> Ver</button>
              </a>
            </div>
          </div>';
        }
      }
      echo '<div class="row">
        <div class="col-12 col-md-5">
          <div class="mb-5">
            <label for"nmbac">Selecciona un cliente</label>';
            if(busca($_SESSION['uid'],'usuarios','u_id','u_grupo') == "USER"){
              $sqlac.= ' AND c_almacen = "'.busca($_SESSION['uid'],'usuarios','u_id','u_almacen').'" ';
            }
            if($cxtipo != "P"){
              $sql = 'SELECT c_id,c_alias FROM crm_clientes 
                      WHERE 1=1 '.$sqlac.' ORDER BY c_nmb';    
              $result = setq($sql);
              echo '<select class="form-control" searchable="Buscar cliente" name="cliente" required >';
                echo '<option value="" disabled '.$seldis.'>Elegir Cliente</option>';
                while($row = $result->fetch_array()){
                  if($row['c_id'] == $ingres->cliente) $sel = 'selected';
                  elseif($row['c_id'] == 1){
                    $sel = 'checked';
                  }
                  else $sel = "";
                  echo '<option value="'.$row['c_id'].'" '.$sel.'>'.$row['c_alias'].'</option>';
                }
              echo '</select>';
            }else{
              $sql = 'SELECT * FROM concesionarios WHERE  c_id = "'.$ingres->cliente.'"';
              $result = setq($sql);
              echo '<select class="form-control" searchable="Buscar cliente" name="cliente" required >';
                echo '<option value="" disabled '.$seldis.'>Elegir Cliente</option>';
                while($row = $result->fetch_array()){
                  if($row['c_id'] == $ingres->cliente) $sel = 'selected'; else $sel = "";
                  echo '<option value="'.$row['c_id'].'" '.$sel.'>'.$row['c_nmb'].'</option>';
                }
              echo '</select>';
            }
          echo '</div>
        </div>
        <div class="col-12 col-md-3">
          <div class="mb-5">
            <label for"nmbac">Importe del ingreso</label>
            <input type="hidden" id="maximp" value="'.$ingres->monto.'" />
            <input type="number" min="0" step="0.01" '.$maximp.' name="importe" value="'.$ingres->monto.'" id="nmb" class="form-control number-align" placeholder="Importe del ingreso" required="required" onfocus="this.select();" />
          </div>
        </div>
        <div class="col-12 col-md-4" >
          <div class="mb-5">
            <label for"fini">Cuenta</label>';
            $sql = 'SELECT cu_id,cu_nmb FROM cuentas INNER JOIN cortes ON cu_id = c_cuenta
                    WHERE cu_estatus = "A" ORDER BY cu_orden';
            $result = setq($sql);
            echo '<select name="cuenta" id="cuenta" class="form-control" required >';
              while($row = $result->fetch_array()){
                if($row['cu_id'] == $ingres->cuenta) $selcu = 'selected'; else $selcu = "";
                echo '<option value="'.$row['cu_id'].'" '.$selcu.'>'.$row['cu_nmb'].'</option>';
              }
            echo '</select>';
          echo '</div>
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
            <input type="text" name="observaciones" value="'.$ingres->observaciones.'" id="observaciones" class="form-control" placeholder="Observaciones" onfocus="this.select();" '.$req.'/>
          </div>
        </div>
        <div class="col-6 col-md-4">
          <div class="mb-5">
            <label for"correo">Fecha del ingreso</label>
            <input type="datetime-local" name="fecha" value="'.$ingres->fecha.'" id="referencia" class="form-control" onfocus="this.select();" required />
          </div>
        </div>';
        if(isset($_GET['idcxc'])){
          echo '<div class="col-6 col-md-4">
            <div class="mb-5">
              <label for"correo">Aplicar a Cuenta por cobrar</label>
              <input type="hidden" name="cxcobrar" value="'.$cxid.'" />
              <input type="text" value="'.$desc.'" class="form-control" disabled />
            </div>
          </div>';
        }
        echo '<div class="col-12 col-md-12">
          <center><button type="submit" class="btn btn-primary" id="guardar"><i class="fas fa-university" style="color: #ffffff;"></i> GUARDAR </button></center>
        </div>
      </div>
    </form>
  </div>';
?>