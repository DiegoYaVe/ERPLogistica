<?php
  session_start();
  ini_set('display_errors',1);
  include_once('../funciones.php');

  if(isset($_GET['idu'])){
    $sql = 'SELECT cpu_Calle,cpu_NumeroExterior,cpu_NumeroInterior,cpu_Colonia,cpu_Localidad,cpu_Municipio,cpu_Estado,cpu_Pais,cpu_CodigoPostal
              FROM carta_porte_ubicaciones WHERE cpu_factura = "'.$_GET['factura'].'" AND cpu_id = "'.$_GET['idu'].'"';
    $result = setq($sql);
    list($Calle,$NumeroExterior,$NumeroInterior,$Colonia,$Localidad,$Municipio,$Estado,$Pais,$CodigoPostal) = $result->fetch_array();

    $style = 'style="display:block"';
    $accion = '?modulo=facturas&accion=insertcpdom&factura='.$_GET['factura'].'&idubica='.$_GET['idu'].'';
    $btn = '<button role="submit" class="btn btn-info"><i class="icon-reload"></i> Actualizar</button>';
  }else{
    if(busca($_GET['factura'],'carta_porte_ubicaciones','cpu_factura','COUNT(*)') == 1){        
      $sqlcld = 'SELECT c_rfc,c_nmb,c_calle,c_nume,c_numi,c_colonia,c_municipio,c_estado,c_pais,c_cp
                FROM facturas INNER JOIN clientes ON f_cliente = c_id
                WHERE f_id = "'.$_GET['factura'].'"';
      $resultcld = setq($sqlcld);
      list($rfc,$nmb,$calle,$nume,$numi,$colonia,$municipio,$estado,$pais,$cp) = $resultcld->fetch_array();
    }else{
      $rfc = NULL;
      $nmb = NULL;
      $calle = NULL;
      $nume = NULL;
      $numi = NULL;
      $colonia = NULL;
      $municipio = NULL;
      $estado = NULL;
      $pais = NULL;
      $cp = NULL;
    }

    $style = 'style="display:none"';
    $accion = '?modulo=facturas&accion=insertubica&factura='.$_GET['factura'].'';
    $btn = '<button role="submit" class="btn btn-success"><i class="fa fa-save"></i> Guardar ubicación</button>';
  }

  $tipoubica = array("Destino"=>"Destino");
  $fechaor = busca($_GET['factura'],'carta_porte_ubicaciones','cpu_TipoUbicacion = "Origen" AND cpu_factura','cpu_FechaHoraSalidaLlegada');

  if($CodigoPostal){
    $sql = 'SELECT cpc_colonia,cpc_nmb FROM carta_porte_colonias WHERE cpc_cp = "'.$CodigoPostal.'"';
    $result = setq($sql) or die($sql);
    while($row = $result->fetch_array()){
      if($Colonia == $row['cpc_colonia']) $selcol = "selected"; else $selcol = "";
      $htmlcol.='<option value="'.$row['cpc_colonia'].'" '.$selcol.'>'.$row['cpc_colonia'].' - '.$row['cpc_nmb'].'</option>';
    }
  }

?>
  <script>
    function includedir(){
      if(document.getElementById("indireccion").checked == true){
        document.getElementById("direccion").style.display = "";
        document.getElementById("cptpais").value = "MEX";
        document.getElementById("cptcp").focus();
      }else{
        document.getElementById("direccion").style.display = "none";
      }
    }
  </script>
  <script>
    function setcp(){
      var cp = $("#cptcp").val();
      $.ajax({
        url: '../query/buscarcolonia.php',
        type: 'POST',
        dataType: 'html',
        data: {'cp': cp},
      })
      .done(function(respuesta){
        if(respuesta != ""){
          var direccion = JSON.parse(respuesta);
          var local = direccion[0].localidad;
          var muni = direccion[0].municipio;
          var estad = direccion[0].estado;
          $('#cptlocalidad').val(local);
          $('#cptmunicipio').val(muni);
          $('#cptestado').val(estad);
        }
      });
      $.ajax({
        url: '../query/coloniascodigo.php',
        type: 'POST',
        dataType: 'html',
        data: {'cp': cp},
      })
      .done(function(datacol){
        $("#cptcolonia").html(datacol);
      });
      $('#cptcolonia').focus();
    }
  </script>
<?php

  echo '<div class="container">  
    <form action="'.$accion.'" method="post">';
      if(!isset($_GET['idu'])){
        echo'<div class="row">
          <div class="col-12 alert alert-primary">Agregar información de destino</div>
        </div>
        <div class="row nowrap">
          <div class="col-md-2 mb-5">
            <label>Tipo</label>
            '.menu_select_array($tipoubica,"Destino",'TipoUbicacion',true).'
          </div>
          <div class="col-md-1 mb-5">
            <label>ID</label>
            <input type="number" name="IDUbicacion" step="1" max="3" min="1" value="1" class="form-control" />
          </div>
          <div class="col-md-3 mb-5">
            <label>RFC Remitente</label>
            <input type="text" name="RFCRemitenteDestinatario" autofocus="autofocus" value="'.$rfc.'" required="required" class="form-control text-uppercase" />
          </div>
          <div class="col-md-3 mb-5">
            <label>Nombre Remitente</label>
            <input type="text" name="NombreRFC" value="'.$nmb.'" class="form-control" />
          </div>
          <div class="col-md-3 mb-5">
            <label>Fecha y hora de llegada</label>
            <input type="datetime-local" name="FechaHoraSalidaLlegada" min="'.str_replace(' ',"T",$fechaor).'" value="'.str_replace(" ","T",date('Y-m-d H:00',strtotime($fechaor.'+ 1 hour'))).'" step="any"  required="required" class="form-control" />
          </div>
          <div class="col-md-2 mb-5">
            <label>Distancia</label>
            <div class="input-group">
              <input type="number" name="DistanciaRecorrida" step="0.01" min="0" max="99999" value="0" required="required" class="form-control" />
              <span class="input-group-addon" id="basic-addon2">KM</span>
            </div>
          </div>
          <div class="col-md-3">
            <div class="mb-5">
              <label>Incluir dirección</label><br>
              <input type="checkbox" name="domicilio" id="indireccion" onchange="includedir();" class="flipswitchsn"/>
            </div>
          </div>
        </div>';
      }
      echo'<div class="row nowrap"  id="direccion">
        <div class="col-12 col-md-12 alert alert-primary">Dirección de la ubicación</div>
        <div class="col-md-2 mb-5">
          <label>Código postal*</label>
          <input type="text" name="CodigoPostal" maxlenght="12" id="cptcp" value="'.$CodigoPostal.'" required="required" class="form-control" onchange="setcp();" autofocus />
        </div>
        <div class="col-md-3 mb-5">
          <label>Colonia</label>
          <select name="Colonia" id="cptcolonia" placeholder="Seleccionar colonia" class="form-control">'.$htmlcol.'</select>
        </div>
        <div class="col-md-3 mb-5">
          <label>Calle</label>
          <input type="text" name="calle" value="'.$Calle.'" class="form-control"  />
        </div>
        <div class="col-md-2 mb-5">
          <label>Exterior</label>
          <input type="text" name="NumeroExterior" value="'.$NumeroExterior.'" class="form-control"  />
        </div>
        <div class="col-md-2 mb-5">
          <label>Interior</label>
          <input type="text" name="NumeroInterior" value="'.$NumeroInterior.'" class="form-control"  />
        </div>
        <div class="col-md-3 mb-5">
          <label>Localidad</label>
          <input type="text" name="Localidad" value="'.$Localidad.'" id="cptlocalidad" class="form-control" readonly/>
        </div>
        <div class="col-md-3 mb-5">
          <label>Municipio</label>
          <input type="text" name="Municipio" value="'.$Municipio.'" id="cptmunicipio" class="form-control" required="required" readonly/>
        </div>
        <div class="col-md-3 mb-5">
          <label>Estado*</label>
          <input type="text" name="Estado" value="'.$Estado.'" id="cptestado" required="required" class="form-control" required="required" readonly/>
        </div>
        <div class="col-md-3 mb-5">
          <label>Pais*</label>
          <input type="text" name="Pais" maxlenght="3" value="'.$Pais.'" id="cptpais" required="required" class="form-control" required="required" readonly/>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12">
          <center>
            '.$btn.'
          </center>
        </div>
      </div>
    </form>
  </div>';
?>