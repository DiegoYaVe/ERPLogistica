<?php
  session_start();
  ini_set('display_errors',1);
  include_once('../funciones.php');

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
    <form method="post" action="?modulo=facturas&accion=insertmerca&factura='.$_GET['factura'].'">
      <div class="row">
        <div class="col-12 alert alert-primary">Agregar información de mercancia</div>
      </div>
      <div class="row nowrap">
        <div class="col-md-3 mb-5">
          <label>Bienes transportados*</label>
          '.menu_select_db('prodsat','p_claveps','CONCAT(p_claveps," - ",p_alias)',"",'BienesTransp','p_empresa = "'.$_SESSION['emp'].'"',false,false,false,true).'
        </div>
        <div class="col-md-4 mb-5">
          <label>Descripción*</label>
          <input type="text" name="Descripcion" size="40" value="" onfocus="this.select();" required="required" class="form-control"/>
        </div>
        <div class="col-md-2 mb-5">
          <label>Cantidad*</label>
          <input type="number" name="Cantidad" min="0.01" max="99999" step="0.01" value="0.01" onfocus="this.select();" required="required" class="form-control"/>
        </div>
        <div class="col-md-3 mb-5">
          <label>Unidad</label>
          '.menu_select_db('unidades','u_clavesat','CONCAT(u_clavesat," - ",u_nmb)',"",'ClaveUnidad','u_estatus = "A" AND u_empresa = "'.$_SESSION['emp'].'"',false,false,"N",false).'
        </div>
        <div class="col-md-2 mb-5">
          <label>Peso KG*</label>
          <input type="number" name="PesoEnKg" min="0.01" max="99999" step="0.001" value="0.01" onfocus="this.select();" required="required" class="form-control"/>
        </div>
        <div class="col-md-2 mb-5">
          <label>Valor mercancia</label>
          <input type="number" name="ValorMercancia" min="0.00" max="99999" step="0.01" value="0.00" onfocus="this.select();" class="form-control"/>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12">
          <center>
            <button role="submit" class="btn btn-success mt-2"><i class="fa fa-save"></i> Guardar</button>
          </center>
        </div>
      </div>
    </form>
  </div>';
?>