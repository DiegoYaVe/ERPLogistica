<?php
  session_start();
  //ini_set('display_errors', 1);
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
    function checkSubmitguardar() {
        document.getElementById("guardar").value = "JD";
        document.getElementById("guardar").disabled = true;
        return true;
    }
    function habilita(){
      var tipo = document.getElementById("tipo").value;
      if(tipo=="C"){
          document.getElementById("cliente").hidden=false;
          document.getElementById("clientei").hidden=false;
          document.getElementById("concesionario").hidden=true;
          document.getElementById("concesionarioi").hidden=true;
          document.getElementById("prestatario").hidden=true;
          document.getElementById("prestatarioi").hidden=true;
      }
      if(tipo=="O"){
          document.getElementById("cliente").hidden=true;
          document.getElementById("clientei").hidden=true;
          document.getElementById("concesionario").hidden=false;
          document.getElementById("concesionarioi").hidden=false;
          document.getElementById("prestatario").hidden=true;
          document.getElementById("prestatarioi").hidden=true;
          document.getElementById("proveedor").hidden=true;
          document.getElementById("proveedori").hidden=true;
      }
      if(tipo=="P"){
          document.getElementById("cliente").hidden=true;
          document.getElementById("clientei").hidden=true;
          document.getElementById("concesionario").hidden=true;
          document.getElementById("concesionarioi").hidden=true;
          document.getElementById("prestatario").hidden=false;
          document.getElementById("prestatarioi").hidden=false;
          document.getElementById("proveedor").hidden=true;
          document.getElementById("proveedori").hidden=true;
      }
      if(tipo=="S"){
          document.getElementById("cliente").hidden=false;
          document.getElementById("clientei").hidden=false;
          document.getElementById("tags").value="MAURICIO ORTUÑO FLORES";
          document.getElementById("concesionario").hidden=true;
          document.getElementById("concesionarioi").hidden=true;
          document.getElementById("prestatario").hidden=true;
          document.getElementById("prestatarioi").hidden=true;
          document.getElementById("proveedor").hidden=true;
          document.getElementById("proveedori").hidden=true;
      }
      if(tipo=="R"){
          document.getElementById("cliente").hidden=true;
          document.getElementById("clientei").hidden=true;
          document.getElementById("concesionario").hidden=true;
          document.getElementById("concesionarioi").hidden=true;
          document.getElementById("prestatario").hidden=true;
          document.getElementById("prestatarioi").hidden=true;
          document.getElementById("proveedor").hidden=false;
          document.getElementById("proveedori").hidden=false;
      }
    }
  </script>
<?php
  include_once('../funciones.php');
  $tipoc = array("O"=>"CONCESIONARIO","P"=>"PRESTAMISTA","S"=>"SERVICIO EXTRAORDINARIO");

  $accion = 'insertop';
  //$importe=1000;
  $count = busca($_SESSION['emp'],'concesionarios','c_estatus = "A" AND c_empresa','COUNT(*)');
  if($count == 0) $button = '<a href="?modulo=concesionarios&accion=index"><button type="button" class="btn btn-sm btn-warning" id="addconce"><i class="icon-plus"></i></button></a>';
  else $button = '';
  echo '<div class="container">';
    if($_GET['tablero'] != 0)
      echo '<form autocomplete="off" method=post name="pro"  action=?modulo=cxpagar&accion='.$accion.'&tablero='.$_GET['tablero'].' onsubmit="return checkSubmitguardar();">';
    else
      echo '<form autocomplete="off" method=post name="pro"  action=?modulo=cxpagar&accion='.$accion.' onsubmit="return checkSubmitguardar();">';
        echo '<div class="table">
          <table class="mb-0 table table-hover table-striped">
            <thead class="bg-light-blue bg-darken-2 text-white">
              <tr>
                <th width="20%">Tipo de orden</th>
                <th width="20%">Concepto</th>
                <th width="10%">Importe</th>
                <th width="20%">Destino '.$button.'</th>';
                if($_GET['tablero'] == 0){
                  echo'<th id="fpago">Fecha de pago</th>
                  <th id="dir">Modalidad</th>';
                }
              echo'</tr>
            </thead>
            <tbody>';
              echo '<tr>
                <td>
                  <select name="tipo" id="tipop" class="form-control" onchange="xd()" required  autofocus >';
                    $hd = 'Selecciona un concesionario';
                    $place = 'Concepto de la orden de pago';
                    if($_GET['tablero'] == 0) {
                      echo'<option value="P" '.$t3.'>PRESTAMO</option>';
                      $hd = 'Selecciona un prestamista';
                      $place = 'Concepto del prestamo';
                    }
                    else echo'<option value="O" '.$t2.' selected>ORDEN DE PAGO</option>';
                  echo'</select>
                </td>
                <td>
                  <input type="text" onfocus="this.select();" name="concepto" value="" placeholder="'.$place.'" class="form-control" required/>
                </td>
                <td width="15%">
                  <input type="number" onfocus="this.select();" name="importe" step="0.01" value="" placeholder="0.00" class="form-control" required/>
                </td>
                <td>
                  <div id="concesionarios">
                    '.menu_select_db('concesionarios', 'c_id', 'c_nmb', "", 'concesionario', 'c_estatus = "A" AND c_empresa = "'.$_SESSION['emp'].'"',false,false,false,true,$hd).'
                  </div>
                  <!-- <div id="prestarios" style="display: none;">
                    '.menu_select_db('proveedores', 'p_id', 'p_nmb', "", 'proveedores', 'p_estatus = "A" AND p_empresa = "'.$_SESSION['emp'].'"',false,false,false,true,"Selecciona un proveedor").'
                  </div> -->
                </td>';
                if($_GET['tablero'] == 0){
                  echo'<td id="fpagoch">
                    <input type="date" name="fechap" value="'.date('Y-m-d').'" class="form-control" required/>
                  </td>
                  <td id="direccionch">
                    <input class="flipswitchop" type="checkbox" name="direccion" data-toggle="toggle" id="toogle-estatus" data-size="medium" data-onstyle="success" data-offstyle="danger" data-on="Entrada" data-off="Salida">
                  </td>';
                }
              echo'</tr>';
              echo'<tr>
                <td align="center" colspan="6">
                  <center><button type="submit" class="btn btn-success" id="guardar"><i class="fa fa-save"></i>Guardar</button></center>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </form>
  </div>

  <script>
    function xd(){
      var a = document.getElementById("tipop").value;
      if(a == "P"){
        $("#fpago").css("display","table-cell"); 
        $("#fpagoch").css("display","table-cell"); 
        $("#dir").css("display","table-cell");
        $("#direccionch").css("display","table-cell");     
      }else if(a == "O"){
        $("#fpago").css("display","none"); 
        $("#fpagoch").css("display","none"); 
        $("#prestarios").css("display","none");    
        $("#dir").css("display","none"); 
        $("#direccionch").css("display","none");
      }
    }
  </script>';
?>
  