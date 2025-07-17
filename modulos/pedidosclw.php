<?php
  //ini_set("display_errors",1);
  $GLOBALS['menu'] = "WEB";
  class pedidosclw{
    var $model;
    var $modelD;
    var $view;
    function __construct(){
      $this->model = new modelpedidosclw(isset($obj));
      $this->modelD = new modelpedidosclwDetalle(isset($obj));
    }
    function index(){
      if(!isset($_REQUEST['estatus'])) $_REQUEST['estatus'] = "P";
      if(!isset($_REQUEST['w'])) $web = 1;
      else $web = $_REQUEST['w'];

      if(!isset($_REQUEST['page'])) $_REQUEST['page'] = 0;

      $this->model->result($_REQUEST['pedido'],$_REQUEST['cliente'],$_REQUEST['apellidos'],$_REQUEST['email'],$_REQUEST['estatus'],$_REQUEST['page'],20,$web,$_GET['clienteid']);
      $this->view = new viewpedidosclw($this->model);
      $this->view->browse($_REQUEST['pedido'],$_REQUEST['cliente'],$_REQUEST['apellidos'],$_REQUEST['email'],$_REQUEST['estatus'],$_REQUEST['page'],20,$web);
    }
    function editpedido(){
      if(!isset($_GET['id'])) $_GET['id'] = NULL;
      if(!isset($_REQUEST['empresa'])) $_REQUEST['empresa'] = NULL;
      if(!isset($_REQUEST['cliente'])) $_REQUEST['cliente'] = NULL;

      $this->model->select($_GET['id']);
      $this->view = new viewpedidosclw($this->model);
      $this->view->edit($_REQUEST['empresa']);
    }
    function insertguiapedido(){
      $this->model->insertguiapedido($_GET['id'],$_POST['guia'],$_POST['paqueteria'],$_POST['nopaq']);

      redirect('?modulo=pedidosclw&accion=show&id='.$_GET['id']);
    }
    function deleteguiapedido(){
      $this->model->deleteguiapedido($_GET['id']);

      redirect('?modulo=pedidosclw&accion=show&id='.$_GET['pedido']);
    }
    function enviarpedido(){
      $this->model->enviarpedido($_GET['id']);

      redirect('?modulo=pedidosclw&accion=show&id='.$_GET['id']);
    }
    function recepcionpedido(){
      $this->model->recepcionpedido($_GET['id']);

      redirect('?modulo=pedidosclw&accion=show&id='.$_GET['id']);
    }
    function insert(){
      $sqlid = 'SELECT MAX(p_id) FROM pedidoscl';
      $resultid = setq($sqlid) or die($sqlid);
      list($id) = mysql_fetch_array($resultid);
      $id++;

      $this->model->setdata($id,$_POST['cliente'],$_POST['observaciones'],$_POST['empresa']);
      $this->model->insert();

      header("Location: ?modulo=pedidosclw&accion=show&id=".$this->model->id);
    }
    function show(){
      $showd = "0";
      if(!isset($_GET['id'])) $_GET['id'] = NULL;
      if(!isset($_REQUEST['producto'])) $_REQUEST['producto'] = NULL;
      if(!isset($_REQUEST['cantidad'])) $_REQUEST['cantidad'] = NULL;
      if(isset($_POST['showd'])) $showd = "1";
      $this->model->select($_GET['id']);
      $this->view = new viewpedidosclw($this->model);
      $this->view->show($_REQUEST['producto'],$_REQUEST['cantidad'],$showd);
    }
    function update(){
      $this->model->setdata($_POST['id'],$_POST['cliente'],$_POST['observaciones'],$_POST['empresa']);
      $this->model->update();

      header("Location: ?modulo=pedidosclw&accion=show&id=".$this->model->id);
    }
    function insertarti(){
      $pedido = $_REQUEST['pedido'];
      $producto = codigobarrascb($_REQUEST['producto']);
      if(!isset($_POST['color']))
        $_POST['color'] = 0;
      if(!isset($_POST['talla']))
        $_POST['talla'] = 0;

      $cantidad = $_REQUEST['cantidad'];
      $talla = $_POST['talla'];
      $color = $_POST['color'];

      $descontinuado = busca($producto,'productos_tallasc','pt_talla = "'.$talla.'" AND pt_color = "'.$color.'" AND pt_producto','pt_descontinuado');
      $agotado = busca(busca($producto,'productos','p_id','p_marca'),'marcas','m_id','m_inventagot');

      $existencia = existenciaptc($producto,$talla,$color);

      $idexiste = busca($pedido,'pedidoscld','pd_producto = "'.$producto.'" AND pd_color = "'.$color.'" AND pd_talla = "'.$talla.'" AND pd_pedido','pd_id');
      if($idexiste){
        $ant = busca($idexiste,'pedidoscld','pd_pedido = "'.$pedido.'" AND pd_id','pd_cantidad');
        if($existencia < ($cantidad+$ant)){
          if($agotado == "A" AND $descontinuado == 0){
            $this->modelD->updatedetalle($pedido,$idexiste,$producto,$cantidad,$color,$talla,true);
          }
          elseif(($existencia - $ant) > 0){
            $cantidad = ($existencia - $ant);
            $this->modelD->updatedetalle($pedido,$idexiste,$producto,$cantidad,$color,$talla,true);
            echo '<script>alert("Existencias Insuficientes solo se agregaran '.$cantidad.' unidades");</script>';
          }
          else{
            echo '<script>alert("Existencias Insuficientes");</script>';
          }
        }
        else{
          $this->modelD->updatedetalle($pedido,$idexiste,$producto,$cantidad,$color,$talla,true);
        }
      }
      else{
        $sqlid = 'SELECT MAX(pd_id) FROM pedidoscld WHERE pd_pedido = "'.$pedido.'"';
        $resultid = setq($sqlid) or die($sqlid);
        list($id) = mysql_fetch_array($resultid);
        $id++;
        if($existencia < $cantidad){
          if($agotado == "A" AND $descontinuado == 0){
            $this->modelD->insertdetalle($pedido,$id,$producto,$cantidad,$color,$talla);
          }
          elseif($existencia > 0){
            $cantidad = $existencia;
            $this->modelD->insertdetalle($pedido,$id,$producto,$cantidad,$color,$talla);
            echo '<script>alert("Existencias Insuficientes solo se agregaran '.$cantidad.' unidades");</script>';
          }
          else{
            //echo '<script>alert("Existencias Insuficientes");</script>';
            die(alert_back("Existencias Insuficientes",true));
          }
        }
        else{
          $this->modelD->insertdetalle($pedido,$id,$producto,$cantidad,$color,$talla);
        }
      }
      header('Location: ?modulo=pedidosclw&accion=show&id='.$pedido);
    }
    function deletedetalle(){
      $pedido = $_GET['id'];
      $idpd = $_GET['idpd'];
      $this->modelD->deleteDetalle($pedido,$idpd);
      header("Location: ?modulo=pedidosclw&accion=show&id=".$pedido);
    }
    function deletedetalleg(){
      if(!isset($_GET['id'])) $_GET['id'] = NULL;
      $sql= 'SELECT * FROM pedidoscld INNER JOIN articulos ON a_cb = pd_producto WHERE pd_pedido = "'.$_GET['id'].'" ';
      $rs = setq($sql) or die($sql);
      while($row = mysql_fetch_array($rs)){
        if(isset($_POST['check'.$row['pd_id']])){
          $this->modelD->deleteg($_GET['id'],$row['pd_id']);
        }
      }
      header("Location: ?modulo=pedidosclw&accion=show&id=".$_GET['id']);
    }
    function updatearti(){
      if(!isset($_GET['cantidad']))
        $_GET['cantidad'] = NULL;
      if(!isset($_GET['pedido']))
        $_GET['pedido'] = NULL;
      if(!isset($_GET['idpd']))
        $_GET['idpd'] = NULL;
      if(!isset($_GET['producto']))
        $_GET['producto'] = NULL;

      $cantidad = $_GET['cantidad'];
      $pedido = $_GET['pedido'];
      $idpd = $_GET['idpd'];
      $producto = $_GET['producto'];

      $ant = busca($idpd,'pedidoscld','pd_pedido = "'.$pedido.'" AND pd_id','pd_cantidad');
      $talla = busca($idpd,'pedidoscld','pd_pedido = "'.$pedido.'" AND pd_id','pd_talla');
      $color = busca($idpd,'pedidoscld','pd_pedido = "'.$pedido.'" AND pd_id','pd_color');
      $descontinuado = busca($producto,'productos','p_id','p_descontinuado');
      $agotado = busca(busca($producto,'productos','p_id','p_marca'),'marcas','m_id','m_inventagot');

      $existencia = existenciaptc($producto,$talla,$color);

      if($cantidad < $ant){
        $this->modelD->updatedetalle($pedido,$idpd,$producto,$cantidad);
      }
      else{
        if($existencia < ($cantidad+$ant)){
          $confirmadas = busca($pedido,'liga_vrc','l_producto = "'.$producto.'" AND l_talla = "'.$talla.'" AND l_color = '.$color.' AND l_inventario = "1" AND l_pedidoc','COUNT(*)');
          if($agotado == "A" AND $descontinuado == 0){
            $this->modelD->updatedetalle($pedido,$idpd,$producto,$cantidad);
          }
          elseif(($existencia + $confirmados - $ant) > 0){
            $cantidad = ($existencia + $confirmados - $ant);
            $this->modelD->updatedetalle($pedido,$idpd,$producto,$cantidad);
            echo '<script>alert("Existencias Insuficientes solo se agregaran '.$cantidad.' unidades");</script>';
          }
          else{
            echo '<script>alert("Existencias Insuficientes");</script>';
          }
        }
        else{
          $this->modelD->updatedetalle($pedido,$idpd,$producto,$cantidad);
        }
      }
      header("Location: ?modulo=pedidosclw&accion=show&id=".$pedido);
    }
    function cancelar(){
      $id = $_GET['id'];
      $this->model->cancelar($id);
      //$cliente = busca($id,'pedidoscl','p_id','p_cliente');

      //sendmailop("1",NULL,busca($cliente,'clientes','c_id','c_mail'),busca($cliente,'clientes','c_id','c_nmb'),$id);
      header("Location: ?modulo=pedidosclw&accion=show&id=".$id);
    }
    function aplicar(){
      $id = $_GET['id'];
      $this->model->aplicar($id);
      header("Location: ?modulo=pedidosclw&accion=show&id=".$id);
    }
    function deleteped(){
      $cantidad = busca($_GET['id'],'pedidoscld','pd_id = "'.$_GET['idp'].'" AND pd_pedido','pd_cantidad');
      $producto = busca($_GET['id'],'pedidoscld','pd_id = "'.$_GET['idp'].'" AND pd_pedido','pd_producto');
      $talla = busca($_GET['id'],'pedidoscld','pd_id = "'.$_GET['idp'].'" AND pd_pedido','pd_talla');
      $color = busca($_GET['id'],'pedidoscld','pd_id = "'.$_GET['idp'].'" AND pd_pedido','pd_color');

      $this->model->deleteped($_GET['id'],$_GET['idp']);
      header('Location: ?modulo=pedidosclw&accion=show&id='.$_GET['id']);
    }
    function asignarg(){
      $id=$_GET['id'];
      $sql='SELECT SUM(pd_cantidad*p_pesov) FROM pedidoscld INNER JOIN articulos ON p_id=pd_producto
            WHERE pd_pedido="'.$id.'"';
      $res=setq($sql) or die($sql);
      list($pesoped)=mysql_fetch_array($res);
      while($pesoped>0){
            $this->model->aguia($id);
            $pesoped=$pesoped-19;
      }
      $sql='UPDATE pedidoscl SET p_guia="1" WHERE p_id="'.$id.'"';
      setq($sql) or die($sql);
      header('Location: ?modulo=pedidosclw&accion=show&id='.$id);
    }
    function autpago(){
      $sql = 'SELECT * FROM reportepago WHERE r_pedido = "'.$_GET['id'].'"';
      $result = setq($sql) or die($sql);
      $row = mysql_fetch_array($result);
      echo '
      <form id="formpago" name="autorizapago" method="post">

      <form method="post">
      <table class="lista" width="100%">
      <thead><th colspan="2">Datos del Pago</th></thead>
      <tbody>
      <tr><th>Cuenta</th><td>'.busca($row['r_cuenta'],'cuentasfp','c_id','c_udig').'</td></tr>
      <tr><th>Fecha</th><td>'.$row['r_fecha'].'</td></tr>
      <tr><th>Hora</th><td>'.$row['r_hora'].'</td></tr>
      <tr><th>Cantidad</th><td>$ '.number_format($row['r_monto'],4).'</td></tr>
      <tr><th>Comprobante</th><td><a href="../adjpagos/'.busca($row['r_adj'],'adjreportepago','a_id','a_adjunto').'.'.busca($row['r_adj'],'adjreportepago','a_id','a_ext').'" target="_blank">'.busca($row['r_adj'],'adjreportepago','a_id','a_adjunto').'.'.busca($row['r_adj'],'adjreportepago','a_id','a_ext').'</a></td></tr>
      <tr><th>Comentarios</th><td>'.$row['r_observacion'].'</td></tr>
      <tr><td colspan="2"><input type="checkbox" required/> Autorizar</td></tr>
      <tr>';
      ?>
        <td><input type="submit" value="" class="botont" id="cancelar" onclick = "this.form.action = '?modulo=pedidosclw&accion=cancelapago&id=<?php echo $_GET['id'] ?>'" /></td>
        <td><input type="submit" value="" class="botont" id="autorizar" onclick = "this.form.action = '?modulo=pedidosclw&accion=confirmpago&id=<?php echo $_GET['id'] ?>'" /></td>
      <?php
      echo '</tr>
      </tbody></table>
      </form>
      ';
    }
    function setpago(){
      include('dbw.php');
      ?>
      <script language="JavaScript">
        function checkSubmit() {
          document.getElementById("aplicar").value = "JD";
          document.getElementById("aplicar").disabled = true;
          return true;
        }
      </script>
      <?php
      $monto = busca($_GET['id'],'pedidoscld','pd_pedido','SUM(pd_cantidad*pd_precio)');
      $nocajas = busca($_GET['id'],'pedidoscl','p_id','p_nguias');
      $formaenvio = busca($_GET['id'],'pedidoscl','p_id','p_paqueteria');
      //$costoenvio = $nocajas*busca($formaenvio,'paqueterias','p_id','p_costoguia');
      $costoenvio = busca($_GET['id'],'pedidoscl','p_id','p_envio');
      $adicional = busca($_GET['id'],'pedidoscl','p_id','p_adicionalfp');
      $descuento = busca($_GET['id'],'bonificaciones','b_estatus = "A" AND b_pedido','SUM(b_monto)');
      $monto += $costoenvio;
      $monto += $adicional;
      $monto -= $descuento;
      $monto = busca($_GET['id'],'pedidoscl','p_id','p_total');
      echo '<form action="?modulo=pedidosclw&accion=insertpago&id='.$_GET['id'].'" method="post" onsubmit="return checkSubmit();">
        <table class="lista" width="100%">
          <thead><th colspan="2">Ingresar Datos del Pago</th></thead>
          <tbody>
            <tr><th>Cuenta</th><td>'.menu_select_db('cuentasfp','c_id','c_nmb',busca($_GET['id'],'pedidoscl','p_id','p_cuentafp'),'cuenta','c_estatus = "A"', false, false, false, true,true,true).'</td></tr>
            <tr><th>Fecha</th><td><input type="datetime-local" value="'.date('Y-m-d').'T'.date('H:i:s').'" name="fecha" required /></td></tr>
            <tr><th>Cantidad</th><td>$ <input type="number" name="monto" min="'.$monto.'" value="'.number_format($monto,2,'.','').'" step="0.01" required /></td></tr>
            <tr><th>Referencia</th><td><input type="text" name="referencia" value="" required /></td></tr>
            <tr><th>Comprobante</th><td><input type="file" name="comprobante" /></td></tr>
            <tr><th>Comentarios</th><td><textarea cols="35" rows="6" name="observaciones"></textarea></td></tr>
            <tr><td colspan="2"><input type="checkbox" required/> Autorizar</td></tr>
            <tr><td colspan="2"><input type="submit" value="" id="aplicar" class="botont"/></td></tr>
          </tbody>
        </table>
      </form>';
    }
    function confirmpago(){
      if(!isset($_GET['id'])){
        $_GET['id'] = NULL;
      }
      if(isset($_GET['w'])){
        $_GET['w'] = NULL;
      }

      if($_GET['w'] == 1){
        $this->model->confirmpagosh($_GET['id']);
      }
      elseif($_GET['w'] == 2){
        $this->model->confirmpagosu($_GET['id']);
      }
      /*echo '
            <script>
                window.open("?modulo=Facturas&accion=showfactura&factura=1&new=);
            </script>';*/
      header('Location: ?modulo=pedidosclw&accion=show&id='.$_GET['id']);
    }
    function verpago(){
      $idp = $_GET['idped'];
      $idcc = $_GET['idcc'];

      $sql='SELECT cc_id, cc_proyecto FROM cxcobrar WHERE cc_id="'.$idcc.'"';
      $result=setq($sql) or die($sql);
      list($id,$proyecto) = mysql_fetch_array($result);
      if($proyecto) $aux1='&remision='.$proyecto;
      else $aux1='';

      include('dbw.php');
      $sql = 'SELECT * FROM reportepago WHERE r_pedido = "'.$idp.'"';
      $resultrp = setq($sql) or die($sql);

      $sqlw = 'SELECT p_web FROM pedidoscl WHERE p_id = "'.$idp.'"';
      $resultw = setq($sqlw) or die($sqlw);
      list($web) = mysql_fetch_array($resultw);

      echo '<table class="lista" width="100%"><thead>';
      echo '<tr>
            <th>Pedido</th>
            <th>Cliente</th>
            <th>Monto de Pedido</th>
            <th>Cuenta</th>
            <th>Fecha y Hora</th>
            <th>Cantidad</th>
            <th>Referencia</th>
            <th>Comprobante</th>
            <th>Comentarios</th>
            <th>Autorizar</th>
            <th>Cancelar</th>
            </tr></thead>';
      echo '<tbody>';
      while($row = mysql_fetch_array($resultrp)){
        $totalpedido = totalpedido($row['r_pedido'],busca($row['r_pedido'],'pedidoscl','p_id','p_cliente'));
        if($totalpedido == $row['r_monto']) $colb = "#00CC33"; else $colb = "#CC0000";

        echo '<tr>';
        echo '<td>'.$row['r_pedido'].'</td>';
        echo '<td>'.busca(busca($row['r_pedido'],'pedidoscl','p_id','p_cliente'),'clientes','c_id','c_nmb').'</td>';
        echo '<td>$ '.number_format($totalpedido,4).'</td>';
        echo '<td>'.busca($row['r_cuenta'],'cuentasfp','c_id','c_udig').'</td>';
        echo '<td>'.$row['r_fecha'].' - '.$row['r_hora'].'</td>';
        echo '<td style="background:'.$colb.'">$ '.number_format($row['r_monto'],4).'</td>';
        echo '<td>'.$row['r_referencia'].'</td>';
        $aux1.='&ref='.$row['r_referencia'];
        $aux1.='&cta='.busca($row['r_cuenta'],'cuentasfp','c_id','c_cuentav');
        if($web == 1){
          echo '<td><a href="https://jdshop.mx/adjpagos/'.busca($row['r_adj'],'adjreportepago','a_id','a_adjunto').'.'.busca($row['r_adj'],'adjreportepago','a_id','a_ext').'" target="_blank">'.busca($row['r_adj'],'adjreportepago','a_id','a_adjunto').'.'.busca($row['r_adj'],'adjreportepago','a_id','a_ext').'</a></td>';
        }
        elseif($web == 2){
          echo '<td><a href="https://jdsuite.mx/adjpagos/'.busca($row['r_adj'],'adjreportepago','a_id','a_adjunto').'.'.busca($row['r_adj'],'adjreportepago','a_id','a_ext').'" target="_blank">'.busca($row['r_adj'],'adjreportepago','a_id','a_adjunto').'.'.busca($row['r_adj'],'adjreportepago','a_id','a_ext').'</a></td>';
        }
        echo '<td>'.$row['r_observacion'].'</td>';
        if($row['r_estatus'] == "N"){
          echo '<td><a href="?modulo=ingresos&accion=edit&idcxc='.$idcc.$aux1.'"><input type="button" class="botont" id="autorizar" /></a></td>';
          echo '<td><a href="?modulo=cxcobros&accion=condonacion&cuenta='.$idcc.$aux1.'"><input type="button" class="botont" id="cancelar" /></a></td>';
        }
        echo '</tr>';
      }

      echo '</tbody></table>';
    }
    function reportespago(){
      if(!isset($_POST['cuenta']))
        $_POST['cuenta'] = NULL;

      $this->model->resultrpago($_POST['cuenta']);
      $this->view = new viewpedidosclw($this->model);
      $this->view->browserpago();
    }
    function confirmpagom(){
      if(isset($_GET['w'])){ $_GET['w'] = NULL; }
      include('dbw.php');

      $sql = 'SELECT * FROM reportepago WHERE r_estatus = "N"';
      $resultrp = setq($sql) or die($sql);
      while($row = mysql_fetch_array($resultrp)){
        if(isset($_POST['aut'.$row['r_id']])){
          if($_GET['w'] == 1){
            $this->model->confirmpagosh($row['r_pedido']);
          }
          elseif($_GET['w'] == 2){
            $this->model->confirmpagosu($row['r_pedido']);
          }
        }
      }
      header('Location: ?modulo=pedidosclw&accion=reportespago');
    }
    function updatefe(){
      $pedido = $_GET['id'];
      $subtotal = busca($pedido,'pedidoscl','p_id','p_subtotal');
      $adicional = busca($pedido,'pedidoscl','p_id','p_adicionalfp');
      $bonificacion = busca($pedido,'bonificaciones','b_pedido','SUM(b_monto)');
      $esquema = busca(busca($pedido,'pedidoscl','p_id','p_cliente'),'clientes','c_id','c_esquema');
      $fenvio = $_POST['fenvio'];

      $sqlnp = 'SELECT SUM(pd_cantidad) FROM pedidoscld WHERE pd_pedido = "'.$pedido.'"';
      $resultnp = setq($sqlnp) or die($sqlnp);
      list($np) = mysql_fetch_array($resultnp);

      $sqld = 'DELETE FROM productos_empaque WHERE pe_pedido = "'.$pedido.'"';
      setq($sqld) or die($sqld);

      $sqlv = 'SELECT * FROM pedidoscld
              INNER JOIN productos_tallasc ON pd_producto = pt_producto AND pd_talla = pt_talla AND pd_color = pt_color
      WHERE pd_pedido = "'.$pedido.'" ORDER BY pt_pesov DESC';
      $resultv = setq($sqlv) or die($sqlv);
      $pid = 1;
      while($rw = mysql_fetch_array($resultv)){
        for($x=1;$x<=$rw['pd_cantidad'];$x++){
          $sqlin = 'INSERT INTO productos_empaque SET
          pe_id = "'.$pid.'",
          pe_pedido = "'.$pedido.'",
          pe_cantidad = "1",
          pe_producto = "'.$rw['pd_producto'].'",
          pe_talla = "'.$rw['pd_talla'].'",
          pe_color = "'.$rw['pd_color'].'",
          pe_tipop = "0",
          pe_nopaquete = "0",
          pe_pesov = "'.$rw['pt_pesov'].'"
          ';
          setq($sqlin) or die($sqlin);
          $pid++;
        }
      }

      $em = 0;
      $sqlem = 'SELECT * FROM productos_empaque WHERE pe_pedido = "'.$pedido.'" ORDER BY pe_id ASC';
      $caja = 1;
      while($em < $np){
        $resultem = setq($sqlem) or die($sqlem);
        $maxp = busca($pedido,'productos_empaque','pe_tipop = "0" AND pe_pedido','MAX(pe_pesov)');
        $tipop = busca($fenvio,'tipopaquetes','tp_pesovt >= "'.$maxp.'" AND tp_paqueteriad','MIN(tp_id)');
        $pesor = busca($fenvio,'tipopaquetes','tp_id = "'.$tipop.'" AND tp_paqueteriad','tp_pesovt') - busca($pedido,'productos_empaque','pe_nopaquete = "'.$caja.'" AND pe_pedido','SUM(pe_pesov)');
        while($rw = mysql_fetch_array($resultem) AND $pesor >= 0){
          if($rw['pe_nopaquete'] == 0 AND $pesor >= $rw['pe_pesov']){
            $sqlup = 'UPDATE productos_empaque SET
            pe_tipop = "'.$tipop.'",
            pe_nopaquete = "'.$caja.'"
            WHERE pe_id = "'.$rw['pe_id'].'" AND pe_pedido = "'.$pedido.'"';
            setq($sqlup) or die($sqlup);
            $pesor -= $rw['pe_pesov'];
            $em++;
          }
        }
        $caja++;
      }

      $sqlem = 'SELECT pe_tipop,pe_nopaquete FROM productos_empaque WHERE pe_pedido = "'.$pedido.'" GROUP BY pe_nopaquete ORDER BY pe_nopaquete ASC';
      $resultem = setq($sqlem) or die($sqlem);
      $envio = 0;
      while($rw = mysql_fetch_array($resultem)){
        $precioe = busca($esquema,'esquema_paqueteria','ep_paqueteria = "'.$fenvio.'" AND ep_tipop = "'.$rw['pe_tipop'].'" AND ep_esquema','ep_costoguia');
        $envio += $precioe;
      }
      $total = $subtotal + $adicional + $envio - $bonificacion;

      $sqlnp = 'UPDATE pedidoscl SET
      p_nguias = "'.$np.'",
      p_envio = "'.$envio.'",
      p_paqueteria = "'.$fenvio.'",
      p_total = "'.$total.'"
      WHERE p_id = "'.$pedido.'"';
      setq($sqlnp) or die($sqlnp);

        //  $sql = 'UPDATE pedidoscl SET p_paqueteria = "'.$_POST['fenvio'].'" WHERE p_id = "'.$_GET['id'].'"';
        //  setq($sql) or die($sql);

      $linkf = '';
      if(isset($_GET['fp']))
        $linkf = '&fp=1';

      header('Location: ?modulo=pedidosclw&accion=show&id='.$_GET['id'].$linkf.'');
    }
    function updatefp(){
      $pedido = $_GET['id'];
      $subtotal = busca($pedido,'pedidoscl','p_id','p_subtotal');
      $envio = busca($pedido,'pedidoscl','p_id','p_envio');
      $bonificacion = busca($pedido,'bonificaciones','b_pedido','SUM(b_monto)');
      $esquema = busca(busca($pedido,'pedidoscl','p_id','p_cliente'),'clientes','c_id','c_esquema');
      $formap = $_POST['fpago'];

      $adicional = 0;
      $adfp = busca($formap,'formaspago','f_id','f_montoa');
      $porcentajea = busca($formap,'formaspago','f_id','f_porcentajea');
      if($porcentajea == 1)
        $adicional = $subtotal * ($adfp/100);
      else
        $adicional = $adfp;

      $total = $subtotal + $adicional + $envio - $bonificacion;

      $sqlnp = 'UPDATE pedidoscl SET
      p_fpago = "'.$formap.'",
      p_adicionalfp = "'.$adicional.'",
      p_total = "'.$total.'"
      WHERE p_id = "'.$pedido.'"';
      setq($sqlnp) or die($sqlnp);

      /*$fpagoa = busca($_GET['id'],'pedidoscl','p_id','p_fpago');
      $ctafp = busca($_GET['id'],'pedidoscl','p_id','p_cuentafp');
      $factura = busca($_GET['id'],'pedidoscl','p_id','p_factura');
      $total = busca($_GET['id'],'pedidoscl','p_id','p_total');
      $estatus = busca($_GET['id'],'pedidoscl','p_id','p_estatus');
      if($fpagoa == 2 AND $ctafp > 0){
        $sqlupmcta = 'UPDATE cuentasfp SET c_montou = c_montou - '.$total.',c_updated = "0" WHERE c_id = "'.$ctafp.'"';
        setq($sqlupmcta) or die($sqlupmcta);

      }

      if($_POST['fpago'] == 2 AND ($estatus == "P" OR $estatus =="A") ){
        $cta = 0;
        $sqlctas2 = '';

          if($factura == 1)
            $sqlctas2 = 'AND c_factura = "1"';
          $sqlctas = 'SELECT * FROM cuentasfp WHERE c_estatus = "A" AND c_formap = "2" '.$sqlctas2.' ORDER BY c_orden ASC';
          $resultctas = setq($sqlctas) or die($sqlctas);
          while($rowctas = mysql_fetch_array($resultctas) AND $cta == 0){
            if(($rowctas['c_montou']+$total) <= $rowctas['c_limite']){
              $ctaap = $rowctas['c_id'];
              $cta = 1;
              $sqlcta = ', p_cuentafp = "'.$ctaap.'"';
              $sqlupmcta = 'UPDATE cuentasfp SET c_montou = c_montou + '.$total.',c_updated = "0" WHERE c_id = "'.$rowctas['c_id'].'"';
              setq($sqlupmcta) or die($sqlupmcta);
            }
          }
      }
      if($cta == 0 AND $_POST['fpago'] == "2"){
        $_POST['fpago'] = 1;
      }

      $sql = 'UPDATE pedidoscl SET p_fpago = "'.$_POST['fpago'].'" '.$sqlcta.' WHERE p_id = "'.$_GET['id'].'"';
      setq($sql) or die($sql);*/

      $linkf = '';
      if(isset($_GET['fp']))
        $linkf = '&fp=1';

      header('Location: ?modulo=pedidosclw&accion=show&id='.$_GET['id'].$linkf.'');
    }
    function insertpago(){
      $maxa = NULL;
      $this->model->insertpago($_GET['id'],$_POST['cuenta'],$_POST['fecha'],$_POST['importe'],NULL,NULL,NULL);

      redirect('?modulo=pedidosclw&accion=show&id='.$_GET['id']);
    }
    function indexexp(){
      $this->model->resultexp();
      $this->view = new viewpedidosclw($this->model);
      $this->view->browseexp();
    }
    function cancelpexp(){
      $this->model->resultexp();
      while($row = mysql_fetch_array($this->model->result)){
        if(isset($_POST['cancel'.$row['p_id']])){
          $cliente = busca($row['p_id'],'pedidoscl','p_id','p_cliente');
          $this->model->cancelar($row['p_id']);
          //sendmailop("1",NULL,busca($cliente,'clientes','c_id','c_mail'),busca($cliente,'clientes','c_id','c_nmb'),$row['p_id']);
        }
      }
      header('Location: ?modulo=pedidosclw&accion=indexexp');
    }
    function cancelpagom() {
      if(!isset($_GET['w'])){
        $_GET['w'] = NULL;
      }
      $sql = 'SELECT * FROM reportepago
      WHERE r_estatus = "N"
      AND r_pedido IN (SELECT p_id FROM pedidoscl WHERE p_web = "'.$_GET['w'].'" AND r_estatus = "R")';
      $resultrp = setq($sql) or die($sql);

      while($rowrp = mysql_fetch_array($resultrp)){
        if(isset($_POST['aut'.$rowrp['r_id']])){
          $pedido = busca($rowrp['r_id'],'reportepago','r_id','r_pedido');
          $id = $rowrp['r_id'];

          $sqlup = 'UPDATE reportepago SET
          r_estatus = "C",
          r_fcancela = "'.date('Y-m-d').'",
          r_ucancela = "'.$_SESSION['uid'].'"
          WHERE r_id = "'.$id.'"';

          $sqlu = 'UPDATE pedidoscl SET p_estatus = "A" WHERE p_id = "'.$pedido.'"';

          setq($sqlup) or die($sqlup);
          setq($sqlu) or die($sqlu);

          $cliente = busca($pedido,'pedidoscl','p_id','p_cliente');

          sendmailop($_GET['w'],4,$cliente,$pedido,NULL,'cpago',$id);
        }
      }
      header('Location: ?modulo=pedidosclw&accion=reportespago');
    }
    function cancelapago(){
      $pedido = $_GET['id'];
      $id = busca($_GET['id'],'reportepago','r_pedido','r_id');

      $sqlup = 'UPDATE reportepago SET
      r_estatus = "C",
      r_fcancela = "'.date('Y-m-d').'",
      r_ucancela = "'.$_SESSION['uid'].'"
      WHERE r_id = "'.$id.'"';

      $sqlu = 'UPDATE pedidoscl SET p_estatus = "A" WHERE p_id = "'.$_GET['id'].'"';
      setq($sqlup) or die($sqlup);
      setq($sqlu) or die($sqlu);

      $cliente = busca($_GET['id'],'pedidoscl','p_id','p_cliente');

      sendmailop(2,4,$cliente,$pedido,NULL,'cpago',$id);

      header("Location: ?modulo=pedidosclw&accion=show&id=".$_GET['id']);
    }
    function comentario(){
      include('dbw.php');
      echo '<form method="post" action="?modulo=pedidosclw&accion=updatecom&id='.$_GET['id'].'"><table class="lista" width="100%">';
      echo '<tr><th>Comentario</th></tr>';
      echo '<tr><td><textarea cols="50" rows="5" name="comentario">'.busca($_GET['id'],'pedidoscl','p_id','p_comentario').'</textarea></td></tr>';
      echo '<tr><th><input type="submit" class="botont" id="guardar" value="" /></th></tr></form>';
    }
    function updatecom(){
      include('dbw.php');
      $sql = 'UPDATE pedidoscl SET p_comentario = "'.$_POST['comentario'].'" WHERE p_id = "'.$_GET['id'].'"';
      setq($sql) or die($sql);

      header("Location: ?modulo=pedidosclw&accion=show&id=".$_GET['id']);
    }
    function prpago(){
      echo '<form method="post" action="?modulo=pedidosclw&accion=updateprpago&id='.$_GET['id'].'"><table class="lista" width="100%">';
      echo '<tr><th>Autorizar Prorroga para Pago</th></tr>';
      echo '<tr><td>Pedido #'.$_GET['id'].'</td></tr>';
      echo '<tr><td><input type="checkbox" required/></td></tr>';
      echo '<tr><th><input type="submit" class="botont" id="aplicar" value="" /></th></tr></form>';
    }
    function updateprpago(){
      $sql = 'UPDATE pedidoscl SET p_prpago = "1",
      p_fprpago = "'.date('Y-m-d H:i:s').'",
      p_uprpago = "'.$_SESSION['uid'].'"
      WHERE p_id = "'.$_GET['id'].'"';
      setq($sql) or die($sql);

      header("Location: ?modulo=pedidosclw&accion=index");
    }
    function pnegados(){
      if(!isset($_GET['id']))
        $_GET['id'] = NULL;

      $sqln = 'SELECT * FROM pedidoscld_negados WHERE pn_pedidocl = "'.$_GET['id'].'"';
      $resultn = setq($sqln) or die($sqln);

      echo '<table class="lista" width="100%">
      <tr><th colspan="5">Productos Negados</th></tr>
      <tr><th>CB/SKU</th><th>Producto</th><th>Talla</th><th>Color</th><th>Cantidad</th></tr>';
      while($rown = mysql_fetch_array($resultn)){
        if($rown['pn_cantidad'] > 0){
          echo '<tr>
          <td>'.$rown['pn_producto'].' / '.busca($rown['pn_producto'],'productos_tallasc','pt_talla = "'.$rown['pn_talla'].'" AND pt_color = "'.$rown['pn_color'].'" AND pt_producto','pt_sku').'</td>
          <td>'.busca($rown['pn_producto'],'productos','p_id','p_nmb').'</td>
          <td>'.busca($rown['pn_talla'],'tallas','t_id','t_nmb').'</td>
          <td>'.busca($rown['pn_color'],'colores','c_id','c_nmb').'</td>
          <td>'.$rown['pn_cantidad'].'</td>
          </tr>';
        }
      }
      echo '</table>';

    }
    function updatebf(){
      if(!isset($_GET['id']))
        $_GET['id'] = NULL;
      if(!isset($_GET['idb']))
        $_GET['idb'] = NULL;

      if(isset($_POST['b'.$_GET['idb']])){
        $sql = 'UPDATE bonificaciones SET b_pedido = "'.$_GET['id'].'" WHERE b_id = "'.$_GET['idb'].'"';
        setq($sql) or die($sql);
      }
      else{
        $sql = 'UPDATE bonificaciones SET b_pedido = NULL,b_estatus = "N" WHERE b_id = "'.$_GET['idb'].'"';
        setq($sql) or die($sql);
      }

      header('Location: ?modulo=pedidosclw&accion=show&id='.$_GET['id'].'&fp=1');
    }
    function abrir(){
      if(!isset($_GET['id']))
        $_GET['id'] = NULL;
      $id = $_GET['id'];

      $sqle = 'UPDATE pedidoscl SET p_estatus = "N" WHERE p_id = "'.$id.'"';
      include('dbw.php');
      setq($sql) or die($sql);
      include('db.php');

      header('Location: ?modulo=pedidosclw&accion=show&id='.$id);
    }
    function updatefact(){
      $fact = 0;
      if(isset($_POST['fact']))
        $fact = 1;

      $sql = 'UPDATE pedidoscl SET p_factura = "'.$fact.'" WHERE p_id = "'.$_GET['id'].'"';
      include('dbw.php');
      setq($sql) or die($sql);
      include('db.php');

      $linkf = '';
      if(isset($_GET['fp']))
        $linkf = '&fp=1';

      header('Location: ?modulo=pedidosclw&accion=show&id='.$_GET['id'].$linkf.'');
    }
    function updatede(){
      $sql = 'UPDATE pedidoscl SET p_direccion = "'.$_POST['denvio'].'" WHERE p_id = "'.$_GET['id'].'"';
      include('dbw.php');
      setq($sql) or die($sql);
      include('db.php');

      $linkf = '';
      if(isset($_GET['fp']))
        $linkf = '&fp=1';

      header('Location: ?modulo=pedidosclw&accion=show&id='.$_GET['id'].$linkf.'');
    }
    function selectmail(){
      echo ' <form method="post" action="?modulo=pedidosclw&accion=reenviarmail&id='.$_GET['id'].'">
      <table width="100%" class="lista">
      <tr><th colspan="2">Seleccionar Correo</th></tr>
      <tr>
      <td><input type="radio" name="tipo" value="1" checked required/> Pedido Normal</td>
      <td><input type="radio" name="tipo" value="2" required/> Pedido de Preorden</td>
      </tr>
      <tr><td colspan="2"><input type="submit" value="" id="reenviar" class="botont"/></td></tr>
      </table></form>';
    }
    function reenviarmail(){
      if(isset($_POST['tipo'])){
        if($_POST['tipo'] == 1)
          $_GET['correo'] = 3;
        else if($_POST['tipo'] == 2){
          if(busca($_GET['id'],'pedidoscld_negados','pn_pedidocl','COUNT(*)') > 0)
            $_GET['correo'] = 4;
          else
            $_GET['correo'] = 5;
        }
      }
      $cliente = busca($_GET['id'],'pedidoscl','p_id','p_cliente');
      $correodestin = busca($cliente,'clientes','c_id','c_mail');
      $nmbdestin = busca($cliente,'clientes','c_id','CONCAT(c_nmb," ",c_apellidos)');
      //sendmailop(1,$_GET['correo'],$correodestin,$nmbdestin,NULL,$_GET['id']);

      header('Location: ?modulo=pedidosclw&accion=show&id='.$_GET['id']);
    }
    function guias(){
      if(!isset($_GET['id']))
        $_GET['id'] == NULL;
      $this->model->selectgp($_GET['id']);
      $this->view = new viewpedidosclw($this->model);
      $this->view->guias($_GET['id']);
    }
    function updateguias(){
      if(!isset($_GET['id']))
        $_GET['id'] == NULL;
      $this->model->selectgp($_GET['id']);
      while($rw = mysql_fetch_array($this->model->result)){
        $idpaq = busca($rw['p_paqueteria'],'paqueteriasd','pd_id','pd_paqueteria');
        $this->model->updateguia($_GET['id'],$rw['gp_id'],$idpaq,$_POST['guia'.$rw['gp_id']]);
      }
      header('Location: ?modulo=pedidosclw&accion=show&id='.$_GET['id']);
    }
    function borrarguia(){
      $id = $_GET['id'];
      $sql = 'DELETE FROM guias_pedidos WHERE gp_id = "'.$id.'"';
      setq($sql);

      redirect('?modulo=pedidosclw&accion=show&id='.$_GET['pedido']);
    }
    /************PEDIDOS ACADEMY*****************************************************************************/
    function academy(){
      if(!isset($_REQUEST['fini'])) $_REQUEST['fini'] = date("Y-m-01");
      if(!isset($_REQUEST['ffin'])) $_REQUEST['ffin'] = date("Y-m-d");
      if(!isset($_REQUEST['estatus'])) $_REQUEST['estatus'] = "R";

      $this->model->reporteacademy($_REQUEST['estatus'],$_REQUEST['fini'],$_REQUEST['ffin']);
      $this->view = new viewpedidosclw($this->model);
      $this->view->browseacademy($_REQUEST['fini'],$_REQUEST['ffin'], $_REQUEST['estatus']);
    }
    function showacademy(){
      $this->model->selacademy($_GET['id']);
      $this->view = new viewpedidosclw($this->model);
      $this->view->showacademy();
    }
    function finalizaracademy(){
      $this->model->updateacademy($_GET['id'],$_REQUEST['estatus'],clearvmayus($_REQUEST['comentarios']),$_REQUEST['pedido']);
      if($_REQUEST['estatus'] == "A") $mailop = '3';
      else $mailop = '4';
      
      $this->model->sendmailacademy(3,$mailop,$_REQUEST['cliente'],"3",$_REQUEST['pedido'],clearvmayus($_REQUEST['comentarios']));

      redirect('?modulo=pedidosclw&accion=academy');
    }
  } 

  class modelpedidosclw{
    var $id;
    var $estado;
    var $pais;
    function result($pedido=NULL,$cliente=NULL,$apellidos=NULL,$email=NULL,$estatus=NULL,$page,$pageresult,$web,$clienteid){
      $bloque = 50;
      $sql = 'SELECT * FROM pedidoscl ';
      $f = 0;
      if($pedido){
        if($f == 0)
          $sql .= ' WHERE ';
        else
          $sql .= ' AND ';
        $f++;
        $sql .= 'p_id = "'.$pedido.'" ';
      }

      if($cliente){
        $sqlcl = 'SELECT c_id FROM clientes WHERE c_nmb LIKE "%'.$cliente.'%" OR c_apellidos LIKE "%'.$cliente.'%" OR c_mail LIKE "%'.$cliente.'%"';
        if($apellidos)
          $sqlcl .= 'UNION SELECT c_id FROM clientes WHERE c_apellidos LIKE "%'.$apellidos.'%"';
        if($f == 0)
          $sql .= ' WHERE ';
        else
          $sql .= ' AND ';
        $f++;
        $sql .= 'p_cliente IN ('.$sqlcl.')';
      }
      elseif($apellidos){
        $sqlcl = 'SELECT c_id FROM clientes WHERE c_apellidos LIKE "%'.$apellidos.'%"';
        if($f == 0)
          $sql .= ' WHERE ';
        else
          $sql .= ' AND ';
        $f++;
        $sql .= 'p_cliente IN ('.$sqlcl.')';
      }

      if($email){
        if($f == 0)
          $sql .= ' WHERE ';
        else
          $sql .= ' AND ';
        $f++;
        $sql .= 'p_cliente = "'.busca($email,'clientes','c_mail','c_id').'" ';
      }

      if($estatus == "P"){
        if($f == 0)
          $sql .= ' WHERE ';
        else
          $sql .= ' AND ';
        $f++;
        $sql .= ' p_estatus IN ("A","R","F","Q") ';
      }elseif($estatus){
        if($f == 0)
          $sql .= ' WHERE ';
        else
          $sql .= ' AND ';
        $f++;
        $sql.=' p_estatus = "'.$estatus.'" ';
      }
      if($f == 0)
        $sql .= ' WHERE ';
      else
        $sql .= ' AND ';
      $f++;
      $sql .= ' p_web = "'.$web.'" ';
      $sql.=' ORDER BY p_id DESC ';
      if($clienteid) $sql = 'SELECT * FROM pedidoscl WHERE p_cliente = "'.$clienteid.'" ORDER BY p_id DESC '; 
      $result = setq($sql);
      $this->resultt = setq($sql) or die($sql);
      if($result->num_rows > $bloque) $sql.= ' LIMIT '.($bloque*$page).','.$bloque;
      $this->result = setq($sql) or die($sql);
      include('db.php');
    }
    function select($id){
      $sql = 'SELECT * FROM pedidoscl WHERE p_id = "'.$id.'"';
      $result = setq($sql) or die($sql);
      $row = $result->fetch_array();
      $this->id = $row['p_id'];
      $this->web = $row['p_web'];
      $this->cliente = $row['p_cliente'];
      $this->empresa = $row['p_empresa'];
      $this->direccion = $row['p_direccion'];
      $this->ugen = $row['p_ugen'];
      $this->fechagen = $row['p_fechagen'];
      $this->observaciones = $row['p_observaciones'];
      $this->uapli = $row['p_uapli'];
      $this->fechaapli = $row['p_fechaapli'];
      $this->ucan = $row['p_ucan'];
      $this->fechacan = $row['p_fechacan'];
      $this->estatus = $row['p_estatus'];
      $this->fpago = $row['p_fpago'];
      $this->paqueteria = $row['p_paqueteria'];
      $this->cuentafp = $row['p_cuentafp'];
      $this->nguias = $row['p_nguias'];
      $this->subtotal = $row['p_subtotal'];
      $this->adicionalfp = $row['p_adicionalfp'];
      $this->envio = $row['p_envio'];
      $this->total = $row['p_total'];
      $this->factura = $row['p_factura'];
    }
    function insertguiapedido($pedido,$guia,$paqueteria,$nopaq){
      $sql = 'INSERT INTO guias_pedidos SET
              gp_pedido = "'.$pedido.'",
              gp_paqueteria = "'.clearvmayus($paqueteria).'",
              gp_guia = "'.clearvmayus($guia).'",
              gp_paquete = "'.clearvmayus($nopaq).'"';
      setq($sql);
    }
    function deleteguiapedido($id){
      $sql = 'DELETE FROM guias_pedidos WHERE gp_id = "'.$id.'"';
      setq($sql);
    }
    function enviarpedido($pedido){
      $cliente = busca($pedido,'pedidoscl','p_id','p_cliente');
      //corregir existencias
      /*
        $sqlp = 'SELECT * FROM pedidoscld WHERE pd_pedido = "'.$pedido.'" ORDER BY pd_id ASC';
        $resultp = setq($sqlp) or die($sqlp);
        while($rwp = $resultp->fetch_array()){
          $cantidad = $rwp['pd_cantidad'];
          $sqle = 'SELECT * FROM existencias WHERE e_articulo = "'.$rwp['pd_producto'].'"
                  AND e_talla = "'.$rwp['pd_talla'].'" AND e_color = "'.$rwp['pd_color'].'"
                  ORDER BY e_secuencia ASC';
          $resulte = setq($sqle) or die($sqle);
          while($rwe = $resulte->fetch_array() or $cantidad > 0){
            if($rwe['e_cantidad'] <= $cantidad ){
              $sqlupe = 'UPDATE existencias SET e_cantidad = "0"
                          WHERE e_articulo = "'.$rwp['pd_producto'].'" AND e_talla = "'.$rwp['pd_talla'].'"
                          AND e_color = "'.$rwp['pd_color'].'" AND e_secuencia = "'.$rwe['e_secuencia'].'"';
              setq($sqlupe) or die($sqlupe);
              $cantidad -= $rwe['e_cantidad'];
            }
            else{
              $sqlupe = 'UPDATE existencias SET e_cantidad = e_cantidad - '.$cantidad.'
                          WHERE e_articulo = "'.$rwp['pd_producto'].'" AND e_talla = "'.$rwp['pd_talla'].'"
                          AND e_color = "'.$rwp['pd_color'].'" AND e_secuencia = "'.$rwe['e_secuencia'].'"';
              setq($sqlupe) or die($sqlupe);
              $cantidad = 0;
            }
          }
        }
      */
      $sqlup = 'UPDATE pedidoscl SET
                p_estatus = "Q",
                p_fautoriza = "'.date('Y-m-d H:i:s').'",
                p_uautoriza = "'.$_SESSION['uid'].'"
                '.$sqlcta.'
                WHERE p_id = "'.$pedido.'"';
      setq($sqlup) or die($sqlup);

      $sqle = 'UPDATE guias_pedidos SET gp_fenvio = "'.date('Y-m-d H:i:s').'", gp_uenvio = "'.$_SESSION['uid'].'",
                gp_estatus = "E" WHERE gp_pedido = "'.$pedido.'"';
      setq($sqle);

      $correodestin = busca($cliente,'clientes','c_id','c_mail');
      $nmbdestin = busca($cliente,'clientes','c_id','CONCAT(c_nmb," ",c_apellidos)');
      sendmailop(1,6,$cliente,$pedido);
    }
    function recepcionpedido($pedido){
      $sqlup = 'UPDATE pedidoscl SET
                p_estatus = "X",
                p_fautoriza = "'.date('Y-m-d H:i:s').'",
                p_uautoriza = "'.$_SESSION['uid'].'"
                '.$sqlcta.'
      WHERE p_id = "'.$pedido.'"';
      setq($sqlup) or die($sqlup);

      $sqle = 'UPDATE guias_pedidos SET gp_frecepcion = "'.date('Y-m-d H:i:s').'",
              gp_estatus = "F" WHERE gp_pedido = "'.$pedido.'"';
      setq($sqle);

      $sqlut = 'UPDATE crm_tableros SET ct_estatus = "F" WHERE ct_pedidoweb= "'.$pedido.'"';
      setq($sqlut);

      $cliente = busca($pedido,'pedidoscl','p_id','p_cliente');
      sendmailop(1,7,$cliente,$pedido);
    }
    function setData($id, $cliente, $observaciones,$empresa){
      mb_internal_encoding("UTF-8");
      $simbol = array('"',"'","#","$","%", "&","/","(",")","=","?","¡","*","+","~","^","[","°","|");
      $this->id = $id;
      $this->cliente = $cliente;
      $this->observaciones = str_replace($simbol,"", mb_strtoupper(trim($observaciones)));
      $this->empresa = "1";
      if($empresa)
        $this->empresa = $empresa;
    }
    function insert(){
      $sqlins = 'INSERT INTO pedidoscl SET
      p_web = "'.$this->web.'",
      p_id = "'.$this->id.'",
      p_cliente = "'.$this->cliente.'",
      p_observaciones = "'.$this->observaciones.'",
      p_ugen = "'.$_SESSION['uid'].'",
      p_fechagen = "'.date('Y-m-d H:i:s').'",
      p_empresa = "'.$this->empresa.'",
      p_fpago = "1",
      p_paqueteria = "1",
      p_cuentafp = "1",
      p_nguias = "1",
      p_direccion = "'.busca($this->cliente,'direnvio','d_predeterminado = "1" AND d_cliente','d_id').'",
      p_estatus = "N"';
      setq($sqlins);
    }
    function insertdetalle($pedido,$id,$producto,$cantidad,$color,$talla,$precio){
      $cliente = busca($pedido,'pedidoscl','p_id','p_cliente');
      $esquema = busca($cliente,'clientes','c_id','c_esquema');
      /*
        $precio = precioesq($esquema,$producto,$talla);
        $newprecio = preciocam($producto,$precio);
        if($newprecio != $precio){
          $precio = $newprecio;
        }

      */  
      $sql = 'INSERT INTO pedidoscld SET
              pd_pedido = "'.$pedido.'",
              pd_id = "'.$id.'",
              pd_producto = "'.$producto.'",
              pd_color = "'.$color.'",
              pd_talla = "'.$talla.'",
              pd_precio = "'.$precio.'",
              pd_cantidad = "'.$cantidad.'"';
      setq($sql);

      $sqlv = 'SELECT * FROM pedidoscld WHERE pd_pedido = "'.$pedido.'" ORDER BY pd_id ASC';
      $resultv = setq($sqlv) or die($sqlv);
      $pvol = 0;
      $pvolc = 0;
      while($rowv = $resultv->fetch_array()){
        if(busca($rowv['pd_producto'],'articulosw','aw_id','aw_envio') != "2")
          $pvolc += ($rowv['pd_cantidad'] * busca($rowv['pd_producto'],'productos_tallasc','pt_talla = "'.$rowv['pd_talla'].'" AND pt_color = "'.$rowv['pd_color'].'" AND pt_producto','pt_pesov'));
          $pvol += ($rowv['pd_cantidad'] * busca($rowv['pd_producto'],'productos_tallasc','pt_talla = "'.$rowv['pd_talla'].'" AND pt_color = "'.$rowv['pd_color'].'" AND pt_producto','pt_pesov'));
        }

      $np = ceil($pvol / 100);
      $npc = ceil($pvolc / 100);
      $sqlnp = 'UPDATE pedidoscl SET p_nguias = "'.$np.'",p_nguiascobrar = "'.$npc.'" WHERE p_id = "'.$pedido.'"';
      setq($sqlnp) or die($sqlnp);
    }
    function update(){
      include('dbw.php');
      $sqlins = 'UPDATE pedidoscl SET
      p_cliente = "'.$this->cliente.'",
      p_empresa = "'.$this->empresa.'",
      p_observaciones = "'.$this->observaciones.'"
      WHERE p_id = "'.$this->id.'"';
      setq($sqlins) or die($sqlins);
      include('db.php');
    }
    function cancelar($id){
      include('dbw.php');
      $sql = 'SELECT * FROM pedidoscld WHERE pd_pedido = "'.$id.'"';
      $result = setq($sql) or die($sql);
      while($rowc = mysql_fetch_array($result)){

          $sqlex = 'SELECT e_secuencia FROM existencias WHERE
          e_producto = "'.$rowc['pd_producto'].'" AND e_talla = "'.$rowc['pd_talla'].'"
          AND e_color = "'.$rowc['pd_color'].'"';
          $resultex = setq($sqlex) or die($sqlex);
          list($existe) = mysql_fetch_array($resultex);
          if($existe){
            $sqlup = 'UPDATE existencias SET e_cantidad = e_cantidad + '.$rowc['pd_cantidad'].' WHERE
            e_producto = "'.$rowc['pd_producto'].'" AND e_talla = "'.$rowc['pd_talla'].'"
            AND e_color = "'.$rowc['pd_color'].'"
            AND e_secuencia = "'.$existe.'"';
            setq($sqlup) or die($sqlup);
          }
          else{
            $sqlmax = 'SELECT MAX(e_secuencia) FROM existencias WHERE
            e_producto = "'.$rowc['pd_producto'].'" AND e_talla = "'.$rowc['pd_talla'].'"
            AND e_color = "'.$rowc['pd_color'].'"';
            $resultmax = setq($sqlmax) or die($sqlmax);
            list($nsec) = mysql_fetch_array($resultmax);
            $nsec++;

            $sqlin = 'INSERT INTO existencias SET
            e_producto = "'.$rowc['pd_producto'].'",
            e_color = "'.$rowc['pd_color'].'",
            e_talla = "'.$rowc['pd_talla'].'",
            e_cantidad = "'.$rowc['pd_cantidad'].'",
            e_almacen = "1",
            e_secuencia = "'.$nsec.'",
            e_empresa = "1"
            ';
            setq($sqlin) or die($sqlin);
          }

      }

      $sql = 'UPDATE pedidoscl SET p_estatus = "C",
      p_ucan = "'.$_SESSION['uid'].'",
      p_fechacan = "'.date('Y-m-d H:i:s').'"
      WHERE p_id = "'.$id.'"';
      setq($sql) or die($sql);

      $sqlb = 'DELETE FROM bonificaciones WHERE b_pedido = "'.$id.'"';
      setq($sqlb) or die($sqlb);
      include('db.php');
    }
    function aplicar($id){
      $pedido = $id;
      $cliente = busca($pedido,'pedidoscl','p_id','p_cliente');
      $fpago = busca($pedido,'pedidoscl','p_id','p_fpago');
      $ctafp = busca($pedido,'pedidoscl','p_id','p_cuentafp');
      $total = busca($pedido,'pedidoscl','p_id','p_total');
      $subfp = busca($fpago,'formaspago','f_id','f_subforma');

      if($subfp == 1 AND $ctafp > 0){
        $sqlupmcta = 'UPDATE cuentasfp SET c_montou = c_montou - '.$total.' WHERE c_id = "'.$ctafp.'"';
        setq($sqlupmcta) or die($sqlupmcta);
      }

      //corregir existencias
      $sqlp = 'SELECT * FROM pedidoscld WHERE pd_pedido = "'.$pedido.'" ORDER BY pd_id ASC';
      $resultp = setq($sqlp) or die($sqlp);
      while($rwp = mysql_fetch_array($resultp)){
        $cantidad = $rwp['pd_cantidad'];
        $sqle = 'SELECT * FROM existencias WHERE e_producto = "'.$rwp['pd_producto'].'"
        AND e_talla = "'.$rwp['pd_talla'].'" AND e_color = "'.$rwp['pd_color'].'"
        ORDER BY e_secuencia ASC';
        $resulte = setq($sqle) or die($sqle);
        while($rwe = mysql_fetch_array($resulte) or $cantidad > 0){
          if($rwe['e_cantidad'] <= $cantidad ){
            $sqlupe = 'UPDATE existencias SET e_cantidad = "0"
            WHERE e_producto = "'.$rwp['pd_producto'].'" AND e_talla = "'.$rwp['pd_talla'].'"
            AND e_color = "'.$rwp['pd_color'].'" AND e_secuencia = "'.$rwe['e_secuencia'].'"
            ';
            setq($sqlupe) or die($sqlupe);
            $cantidad -= $rwe['e_cantidad'];
          }
          else{
            $sqlupe = 'UPDATE existencias SET e_cantidad = e_cantidad - '.$cantidad.'
            WHERE e_producto = "'.$rwp['pd_producto'].'" AND e_talla = "'.$rwp['pd_talla'].'"
            AND e_color = "'.$rwp['pd_color'].'" AND e_secuencia = "'.$rwe['e_secuencia'].'"
            ';
            setq($sqlupe) or die($sqlupe);
            $cantidad = 0;
          }
        }
      }

      $sqlcta = '';
      if($subfp == 1){
        $cta = 0;
        $sqlctas2 = '';
        if($factura == 1)
          $sqlctas2 = 'AND c_factura = "1"';
        $sqlctas = 'SELECT * FROM cuentasfp WHERE c_estatus = "A" AND c_formap = "'.$fpago.'" '.$sqlctas2.' ORDER BY c_orden ASC';
        $resultctas = setq($sqlctas) or die($sqlctas);
        while($rowctas = mysql_fetch_array($resultctas) AND $cta == 0){
          if(($rowctas['c_montou'] + $total) <= $rowctas['c_limite'] OR $rowctas['c_limite'] == 0){
            $ctaap = $rowctas['c_id'];
            $cta = 1;
            $sqlcta = ', p_cuentafp = "'.$ctaap.'"';
            $sqlupmcta = 'UPDATE cuentasfp SET c_montou = c_montou + '.$total.' WHERE c_id = "'.$rowctas['c_id'].'"';
            setq($sqlupmcta) or die($sqlupmcta);
          }
        }
      }
      if($cta == 0 AND $subfp == 1){
        $fpago = 0;
        $sqlcta = 'p_fpago = "'.$fpago.'", p_cuentafp = "0"';
      }

      $sqlup = 'UPDATE pedidoscl SET
                p_estatus = "Q",
                p_fautoriza = "'.date('Y-m-d H:i:s').'",
                p_uautoriza = "'.$_SESSION['uid'].'"
                '.$sqlcta.'
      WHERE p_id = "'.$pedido.'"';
      setq($sqlup) or die($sqlup);

      $correodestin = busca($cliente,'clientes','c_id','c_mail');
      $nmbdestin = busca($cliente,'clientes','c_id','CONCAT(c_nmb," ",c_apellidos)');
      //sendmailop(1,3,$correodestin,$nmbdestin,NULL,$id);
    }
    function liga($pedido,$producto,$talla,$color,$cantidad){
      include('dbw.php');
      $empresa = busca($pedido,'pedidoscl','p_id','p_empresa');
      $sqlsec = 'SELECT MAX(l_secuencia) FROM liga_vrc WHERE
      l_producto = "'.$producto.'" AND l_talla = "'.$talla.'" AND l_color = "'.$color.'"';
      $resultsec = setq($sqlsec) or die($sqlsec);
      list($secuencia) = mysql_fetch_array($resultsec);
      for($x=1;$x<=$cantidad;$x++){
        $secuencia++;
        $sqlinr = 'INSERT INTO liga_vrc SET
        l_producto = "'.$producto.'",
        l_color = "'.$color.'",
        l_talla = "'.$talla.'",
        l_secuencia = "'.$secuencia.'",
        l_empresa = "'.$empresa.'",
        l_almacen = "1",
        l_pedidoc = "'.$pedido.'",
        l_cpedidoc = "1"
        ';
        setq($sqlinr) or die($sqlinr);
      }
    }
    function insertr($pedido){
      include('dbw.php');
      $empresa = busca($pedido,'pedidoscl','p_id','p_empresa');
      $sqlid = 'SELECT MAX(r_id) FROM requisiciones';
      $resultid = setq($sqlid) or die($sqlid);
      list($this->rid) = mysql_fetch_array($resultid);
      $this->rid++;
      $sqlins = 'INSERT INTO requisiciones SET
      r_id = "'.$this->rid.'",
      r_tipo = "P",
      r_observaciones = "",
      r_ugen = "'.$_SESSION['uid'].'",
      r_fechagen = "'.date('Y-m-d H:i:s').'",
      r_sucursal = "0",
      r_empresa = "'.$empresa.'",
      r_estatus = "N"';
      setq($sqlins) or die($sqlins);
    }
    function insertrd($requisicion,$producto,$talla,$color,$cantidad){
      include('dbw.php');
      $sqld = 'SELECT MAX(rd_id) FROM requisicionesd WHERE rd_requisicion = "'.$requisicion.'"';
      $resultd = setq($sqld) or die($sqld);
      list($idrd) = mysql_fetch_array($resultd);
      $idrd++;
      $sqlrd = 'INSERT INTO requisicionesd SET
      rd_requisicion = "'.$requisicion.'",
      rd_id = "'.$idrd.'",
      rd_producto = "'.$producto.'",
      rd_color = "'.$color.'",
      rd_talla = "'.$talla.'",
      rd_cantidad = "'.$cantidad.'"';
      setq($sqlrd) or die($sqlrd);

      $sqlupl = '';
    }
    function corrigeinv($producto,$talla,$color,$almacen){
      include('dbw.php');
      $secuencia = busca($almacen,'existencias','e_producto = "'.$producto.'" AND e_talla = "'.$talla.'" AND e_color = "'.$color.'" AND e_almacen','e_secuencia');
      if($secuencia){
        $sqlex = 'UPDATE existencias SET e_cantidad = e_cantidad+1 WHERE e_producto = "'.$producto.'"
        AND e_talla = "'.$talla.'" AND e_color = "'.$color.'" AND e_almacen = "'.$almacen.'" AND e_secuencia = "'.$secuencia.'" ';
      }
      else{
        $sqlsec = 'SELECT MAX(e_secuencia) FROM existencias WHERE e_producto = "'.$producto.'" AND e_talla = "'.$talla.'"
        AND e_color = "'.$color.'" AND e_almacen = "'.$almacen.'"';
        $resultsec = setq($sqlsec) or die($sqlsec);
        list($secuencia) = mysql_fetch_array($resultsec);
        $secuencia++;

        $sqlex = 'INSERT INTO existencias SET
        e_producto = "'.$producto.'",
        e_talla = "'.$talla.'",
        e_color = "'.$color.'",
        e_cantidad = "1",
        e_almacen = "'.$almacen.'",
        e_secuencia = "'.$secuencia.'",
        e_empresa = "1"';
      }
      setq($sqlex) or die($sqlex);
    }
    function deleteliga($producto,$talla,$color,$secuencia,$pedido){
      include('dbw.php');
      $sqldel = 'DELETE FROM liga_vrc WHERE l_producto = "'.$producto.'" AND l_talla = "'.$talla.'"
      AND l_color = "'.$color.'" AND l_secuencia = "'.$secuencia.'" AND l_pedidoc = "'.$pedido.'"';
      setq($sqldel) or die($sqldel);
    }
    function deleteped($pedido,$idp){
      include('dbw.php');
      $sqldel = 'DELETE FROM pedidoscld WHERE pd_pedido = "'.$pedido.'" AND pd_id = "'.$idp.'"';
      setq($sqldel) or die($sqldel);
    }
    function deleteorden($ordenc,$producto,$talla,$color){
      include('dbw.php');
      $sqlor = 'UPDATE pedidosd SET pd_cantidad = pd_cantidad-1 WHERE pd_producto = "'.$producto.'"
      AND pd_talla = "'.$talla.'" AND pd_color = "'.$color.'" AND pd_pedido = "'.$ordenc.'"';
      setq($sqlor) or die($sqlor);

      $sqldel = 'DELETE FROM pedidosd WHERE pd_cantidad <= 0';
      setq($sqldel) or die($sqldel);
    }
    function deletereq($requisicion,$producto,$talla,$color){
      include('dbw.php');
      $sqlor = 'UPDATE requisicionesd SET rd_cantidad = rd_cantidad-1 WHERE rd_producto = "'.$producto.'"
      AND rd_talla = "'.$talla.'" AND rd_color = "'.$color.'" AND rd_requisicion = "'.$requisicion.'"';
      setq($sqlor) or die($sqlor);

      $sqldel = 'DELETE FROM requisicionesd WHERE rd_cantidad <= 0';
      setq($sqldel) or die($sqldel);
    }
    function aguia($pedido){
      include('dbw.php');
      $sqlg='SELECT * FROM guias WHERE g_estatus="N" order by g_falta DESC limit 1';
      $rsg=setq($sqlg) or die($sqlg);
      $rwg=mysql_fetch_array($rsg);

      $sql='UPDATE guias SET g_estatus="A" WHERE g_id="'.$rwg['g_id'].'"';
      setq($sql) or die($sql);

      $sql='SELECT MAX(pg_id) FROM pedido_guia';
      $respg=setq($sql) or die($sql);
      list($id)=mysql_fetch_array($respg);
      $id++;
      $sql='INSERT INTO pedido_guia SET
      pg_id="'.$id.'",
      pg_guia="'.$rwg['g_id'].'",
      pg_costo="'.$rwg['g_costo'].'",
      pg_pedido="'.$pedido.'",
      pg_estatus="A"';
      setq($sql) or die($sql);
    }
    function confirmpagosh($pedido){
      include('dbw.php');
      $cliente = busca($pedido,'pedidoscl','p_id','p_cliente');
      $esquema = busca($cliente,'clientes','c_id','c_esquema');

      /*
        $sqld = 'SELECT DISTINCT(p_linea) FROM pedidoscld
                  INNER JOIN articulos ON pd_producto = s_id
                  WHERE pd_pedido = "'.$pedido.'"';
        $resultd = setq($sqld) or die($sqld);
        $alinean = array();
        while($rwd = mysql_fetch_array($resultd)){
          $alinean[] = $rwd['p_linea'];
        }
      */

      /*
        if(busca($pedido,'pedidoscl','p_id','p_factura') == 1){
          include('facturas.php');
          $facts = new ModelFacturas();
          $idfact = $facts->insertfacturablanco($cliente);

          $sqld = 'SELECT * FROM pedidoscld WHERE pd_pedido = "'.$pedido.'"';
          $result = setq($sqld) or die($sqld);
          while($row = mysql_fetch_array($result)){
          $facts->insertcon($idfact,$row['pd_cantidad'],$row['pd_precio'],busca(busca($row['pd_producto'],'productos','p_id','p_unidad'),'unidades','u_id','u_nmb'),descripcion($row['pd_producto']),0,1,1);
          }
          $costoenvio = busca($pedido,'pedidoscl','p_id','p_envio');

          $facts->insertcon($idfact,'1',$costoenvio,"SERVICIO","ENVIO",0,1,1);

          echo '
          <script>
              window.open("?modulo=facturas&accion=gettimbre&id='.$idfact.'","_blank");
          </script>
          ';
        }
        else{
      */
      $idfact = NULL;
      //  }

      $sqlrp = 'UPDATE reportepago SET
              r_estatus = "A",
              r_fautoriza = "'.date('Y-m-d H:i:s').'",
              r_uautoriza = "'.$_SESSION['uid'].'"
              WHERE r_pedido = "'.$pedido.'" AND r_estatus != "C"
              ';
              setq($sqlrp) or die($sqlrp);

      $sqlp = 'UPDATE pedidoscl SET
              p_estatus = "F",
              p_fautoriza = "'.date('Y-m-d H:i:s').'",
              p_uautoriza = "'.$_SESSION['uid'].'",
              p_idfactura = "'.$idfact.'"
              WHERE p_id = "'.$pedido.'"';
      setq($sqlp) or die($sqlp);

      sendmailop(1,5,$cliente,$pedido,NULL,NULL,NULL);
    }
    function confirmpagosu($pedido){
      include('dbw.php');
      $cliente = busca($pedido,'pedidoscl','p_id','p_cliente');
      $esquema = busca($cliente,'clientes','c_id','c_esquema');

      $sqld = 'SELECT DISTINCT(p_linea) FROM pedidoscld
              INNER JOIN productos ON pd_producto = p_id
              WHERE pd_pedido = "'.$pedido.'"';
      $resultd = setq($sqld) or die($sqld);
      $alinean = array();
      while($rwd = mysql_fetch_array($resultd)){
        $alinean[] = $rwd['p_linea'];
      }

      //Validación de correos electrónicos de acuerdo con los productos comprados
      $ncorreo = 15;
      if(in_array(6,$alinean)){
        $ncorreo = 5;
        if(in_array(10,$alinean)){
          $ncorreo = 6;
        }
      }
      elseif(in_array(12,$alinean)){
        $ncorreo = 7;
        if(in_array(10,$alinean)){
          $ncorreo = 8;
        }
      }
      elseif(in_array(5,$alinean)){
        $ncorreo = 9;
        if(in_array(10,$alinean)){
          $ncorreo = 10;
        }
      }
      elseif(in_array(13,$alinean)){
        $ncorreo = 11;
        if(in_array(10,$alinean)){
          $ncorreo = 12;
        }
      }

      /*
        if(busca($pedido,'pedidoscl','p_id','p_factura') == 1){
          include('facturas.php');
          $facts = new ModelFacturas();
          $idfact = $facts->insertfacturablanco($cliente);

          $sqld = 'SELECT * FROM pedidoscld WHERE pd_pedido = "'.$pedido.'"';
          $result = setq($sqld) or die($sqld);
          while($row = mysql_fetch_array($result)){
          $facts->insertcon($idfact,$row['pd_cantidad'],$row['pd_precio'],busca(busca($row['pd_producto'],'productos','p_id','p_unidad'),'unidades','u_id','u_nmb'),descripcion($row['pd_producto']),0,1,1);
          }
          $costoenvio = busca($pedido,'pedidoscl','p_id','p_envio');

          $facts->insertcon($idfact,'1',$costoenvio,"SERVICIO","ENVIO",0,1,1);

          echo '
          <script>
              window.open("?modulo=facturas&accion=gettimbre&id='.$idfact.'","_blank");
          </script>
          ';
        }
        else{
      */
        $idfact = NULL;
      //  }

      $sqlrp = 'UPDATE reportepago SET
                r_estatus = "A",
                r_fautoriza = "'.date('Y-m-d H:i:s').'",
                r_uautoriza = "'.$_SESSION['uid'].'"
                WHERE r_pedido = "'.$pedido.'" AND r_estatus != "C"';
      setq($sqlrp) or die($sqlrp);

      $sqlp = 'UPDATE pedidoscl SET
              p_estatus = "F",
              p_fautoriza = "'.date('Y-m-d H:i:s').'",
              p_uautoriza = "'.$_SESSION['uid'].'",
              p_idfactura = "'.$idfact.'"
              WHERE p_id = "'.$pedido.'"';
      setq($sqlp) or die($sqlp);

      sendmailop(2,$ncorreo,$cliente,$pedido,NULL,NULL,NULL);
    }
    function resultrpago($cuenta){
      include('dbw.php');
      $sql2 = '';
      if($cuenta){
        $sql2 = 'AND r_cuenta = "'.$cuenta.'"';
      }

      $sql = 'SELECT * FROM reportepago
      WHERE r_estatus = "N" AND r_pedido IN (SELECT p_id FROM pedidoscl WHERE p_web = "2")
      '.$sql2.' ';
      $this->resultrp = setq($sql) or die($sql);
      include('db.php');
    }
    function insertpago($id,$cuenta,$fecha,$monto,$observaciones,$idadj = NULL,$referencia){
      mb_internal_encoding("UTF-8");
      $simbol = array('"',"'","#","$","%", "&","/","(",")","=","?","¡","*","+","~","^","[","°","|");

      $fecha = str_replace("T"," ",$fecha);
      $fechaf = explode(" ",$fecha);

      $pedido = $id;
      $formap = busca($pedido,'pedidoscl','p_id','p_fpago');
      $factura = busca($pedido,'pedidoscl','p_id','p_factura');
      $cliente = busca($pedido,'pedidoscl','p_id','p_cliente');
      $cuenta = $cuenta;
      $fecha = $fechaf[0];
      $hora = $fechaf[1];
      $monto = $monto;
      $referencia = str_replace($simbol,"", mb_strtoupper(trim($referencia)));
      $observaciones = str_replace($simbol,"", mb_strtoupper(trim($observaciones)));

      $web =  busca($id,'pedidoscl','p_id','p_web');

      $sqlm = 'SELECT MAX(r_id) FROM reportepago';
      $resultm = setq($sqlm);
      list($max) = $resultm->fetch_row();
      $max++;

      $sql = 'INSERT INTO reportepago SET
      r_id = "'.$max.'",
      r_pedido = "'.$id.'",
      r_cuenta = "'.busca($cuenta,'cuentasfp','c_id','c_nmb').' ('.busca($cuenta,'cuentasfp','c_id','c_udig').')",
      r_fecha = "'.$fechaf[0].'",
      r_hora = "'.$fechaf[1].'",
      r_monto = "'.number_format($monto,2,'.','').'",
      r_referencia = "'.$referencia.'",
      r_observacion = "'.$observaciones.'",
      r_adj = "'.$maxa.'",
      r_estatus = "N",
      r_falta = "'.date('Y-m-d').'",
      r_ualta = "'.$_SESSION['uid'].'"';
      setq($sql) or die($sql);

      $sqlp = 'UPDATE pedidoscl SET p_estatus = "R",
              p_cuentafp = "'.$cuenta.'"
              WHERE p_id = "'.$id.'"';
      setq($sqlp) or die($sqlp);

      /*
        $clientejd = $cliente;
        $mysqli = mysqli_connect("localhost",'aqlzfcar_root','Wptyeall.0','aqlzfcar_ceo');
        $sqlcl = 'SELECT c_id FROM clientes WHERE c_idsuite = "'.$clientejd.'"';
        $result = $mysqli->query($sqlcl);
        list($clienteceo) = $result->fetch_array();
        if(!$clienteceo){
          $sqlc = 'SELECT c_id idsuite,c_nmb nombre,"A" estatus, c_telefono telefono,c_mail mail,c_rfc rfc,
          c_razon razon,c_calle calle,c_nume exterior,c_numi interior,c_colonia colonia,c_cp cp,
          c_municipio municipio,c_estado estado,c_pais pais
          FROM clientes WHERE c_id = "'.$clientejd.'"';
          $result = setq($sqlc) or die($sqlc);
          $rowc = mysql_fetch_array($result);

          $aliasor = $rowc['nombre'];
          $alias = $rowc['nombre'];

          $concat = 0;
          do{
            $concat++;
            $sqlal = 'SELECT COUNT(*) FROM clientes WHERE c_alias = "'.$alias.'"';
            $resultal = $mysqli->query($sqlal) or die($sqlal);
            list($coincide) = $resultal->fetch_array();
            if($coincide > 0) $alias=$aliasor.'_'.$concat;
          }while($coincide != 0);

          $sqlcc='SELECT MAX(c_id) FROM clientes';
          $resultcc= $mysqli->query($sqlcc) or die($sqlcc);
          list($idcl) = $resultcc->fetch_array();
          $idcl++;

          $sqlic ='
          INSERT INTO clientes SET
          c_id="'.$idcl.'",
          c_estatus="A",
          c_rfc="'.$rowc['rfc'].'",
          c_nmb="'.utf8_decode($rowc['nombre']).'",
          c_razon="'.utf8_decode($rowc['razon']).'",
          c_contacto="'.utf8_decode($rowc['nombre']).'",
          c_alias="'.utf8_decode($alias).'",
          c_calle="'.utf8_decode($rowc['calle']).'",
          c_exterior="'.utf8_decode($rowc['exterior']).'",
          c_interior="'.$rowc['interior'].'",
          c_colonia="'.utf8_decode($rowc['colonia']).'",
          c_municipio="'.utf8_decode($rowc['municipio']).'",
          c_estado="'.$rowc['estado'].'",
          c_cp="'.$rowc['cp'].'",
          c_telef="'.$rowc['telefono'].'",
          c_email="'.$rowc['mail'].'",
          c_antiguedad="'.date('Y-m-d').'",
          c_tipoc="C",
          c_esquemap="1",
          c_origenc="7",
          c_idsuite = "'.$clientejd.'",
          c_pais="'.$rowc['pais'].'"';
          $mysqli->query($sqlic) or die($sqlic);
        }
        else{
          $idcl = $clienteceo;
        }
      */

      //Folio de la remisión
      $empresa = 1;
      $clienteceo = busca($cliente,'clientes','c_id','c_idc');
      $acronimo = busca($empresa,'empresas','e_id','e_siglas');
      $acrof = busca($empresa,'empresas','e_id','e_siglas').'-RM';
      $clientejd = busca($pedido,'pedidoscl','p_id','p_cliente');
      $subtotal = busca($pedido,'pedidoscl','p_id','p_subtotal');
      $idcl = $clienteceo;

      $folio = getmax('ct_folio','crm_tableros');
      //  if($folio == $acronimo.'1') $folio = $acronimo.str_pad(1,6,"0",STR_PAD_LEFT);
      $fini = explode(" ",date('Y-m-d H:i:s'));

      $_POST['nmbac'] = "PEDIDO ".$pedido." JD SHOP";
      $sql = 'INSERT INTO crm_tableros SET
              ct_empresa = "'.$empresa.'",
              ct_nmb = "'.$_POST['nmbac'].'",
              ct_folio = "'.$folio.'",
              ct_cliente = "'.$idcl.'",
              ct_agente = "JD SHOP",
              ct_fini = "'.$fini[0].'",
              ct_hini = "'.$fini[1].'",
              ct_pedidoweb = "'.$pedido.'",
              ct_visibility = "1",
              ct_fcierre = "'.$fini[0].'"';
      setq($sql);
      $idtablero = getmax('ct_id','crm_tableros','ct_empresa = "'.$empresa.'"',false);

      //inserto remision
      $total = $subtotal;
      $folioint = getmax('r_folio','remisiones','r_empresa = "'.$empresa.'"');
      if($folioint == "1"){
        $folioint = $acrof.str_pad($folioint,6,"0",STR_PAD_LEFT);
      }

      $mail = busca($clientejd,'clientes','c_id','c_mail');
      $sql = 'INSERT INTO remisiones SET
              r_empresa = "'.$empresa.'",
              r_tablero = "'.$idtablero.'",
              r_cliente = "'.$idcl.'",
              r_folio = "'.$folioint.'",
              r_almacen = "1",
              r_descuento = "0",
              r_nmb = "'.$_POST['nmbac'].'",
              r_encargado = "JD SHOP",
              r_email = "'.$mail.'",
              r_fliquidacion = "'.$fini[0].'",
              r_falta = "'.date('Y-m-d H:i:s').'",
              r_ualta = "JD SHOP",
              r_subtotal = "'.$subtotal.'",
              r_total = "'.$total.'",
              r_pedidoweb = "'.$pedido.'",
              r_estatus	 = "N"';
      setq($sql);
      $idrem = getmax('r_id','remisiones','r_empresa = "'.$empresa.'"',false);

      $nmbal = "JDSHOP";
      $nmbrem = 'PEDIDO '.$pedido.' '.$nmbal;

      $sqlrd = 'SELECT * FROM pedidoscld WHERE pd_pedido = "'.$pedido.'"';
      $resultrd = setq($sqlrd) or die($sqlrd);
      while($rowrd = $resultrd->fetch_array()){
        $nmbpr = busca($rowrd['pd_producto'],'articulos','a_cb','a_nmb');
        $linean = busca($rowrd['pd_producto'],'articulos','a_cb','a_lineaneg');

        $idrec = getmax('rd_id','remisionesd','rd_remision');
        $idprod = busca($rowrd['pd_producto'],'articulos','a_cb','a_id');
        $costopr = busca($idprod,'articulo_proveedor','ap_estatus = "1" AND ap_articulo','ap_costo');

        $sql = 'INSERT INTO remisionesd SET
                rd_remision = "'.$idrem.'",
                rd_id = "'.$idrec.'",
                rd_articulo = "'.$idprod.'",
                rd_cantidad = "'.$rowrd['pd_cantidad'].'",
                rd_precio = "'.$rowrd['pd_precio'].'",
                rd_costo = "'.$costopr.'",
                rd_linean = "'.$linean.'",
                rd_nmbarticulo = "'.$nmbpr.'",
                rd_iva = "0"';
        setq($sql);
      }

      $envio = busca($pedido,'pedidoscl','p_id','p_envio');
      if($envio > 0){
        $idrec = getmax('rd_id','remisionesd','rd_remision');

        $sql = 'INSERT INTO remisionesd SET
                rd_remision = "'.$idrem.'",
                rd_id = "'.$idrec.'",
                rd_cantidad = "1",
                rd_precio = "'.$envio.'",
                rd_linean = "15",
                rd_nmbarticulo = "ENVÍO",
                rd_iva = "0"';
        setq($sql);

      $subenvio = $subtotal+$envio;
      $total = $subenvio;
      $sql = 'UPDATE remisiones SET
              r_subtotal = "'.$subenvio.'",
              r_total = "'.$total.'"
              WHERE r_id = "'.$idrem.'"';
      setq($sql);

      $sql3 = 'UPDATE crm_tableros SET
              ct_estatus = "A"
              WHERE ct_id="' . $idtablero. '"';
      setq($sql3);
      }
      //Aplico remision y creo CXC
      $sql = 'INSERT INTO cxcobrar SET
              cx_empresa = "'.$empresa.'",
              cx_cliente = "'.$clienteceo.'",
              cx_referencia = "'.$idrem.'",
              cx_tipo = "R",
              cx_importe = "'.$monto.'",
              cx_observaciones = "PEDIDO JD SHOP '.$pedido.'",
              cx_abonado = "0",
              cx_estatus = "N",
              cx_fini = "'.date('Y-m-d H:i:s').'"';
      setq($sql);

      $sql3 = 'UPDATE remisiones SET
              r_estatus = "A",
              r_faplica = "'.date('Y-m-d H:i:s').'",
              r_uaplica = "JD SHOP"
              WHERE r_id="' . $idrem. '"';
      setq($sql3);


    }
    function resultexp($estatus=NULL){
      include('dbw.php');
      $fecha = date('Y-m-d');
      $flimite = strtotime ( '-2 day' , strtotime ($fecha));
      $flimite = date ( 'Y-m-d' , $flimite);
      $sql = 'SELECT * FROM pedidoscl WHERE p_estatus = "A" AND DATE(p_fechaapli) < "'.$flimite.'" AND p_prpago = "0" ORDER BY p_fechaapli DESC';
      $this->result = setq($sql) or die($sql);
    }
    function selectgp($pedido){
      $sql = 'SELECT * FROM guias_pedidos INNER JOIN pedidoscl ON gp_pedido = p_id WHERE gp_pedido = "'.$pedido.'"';
      $this->result = setq($sql) or die($sql);
    }
    function updateguia($pedido,$idgp,$idpaq,$guia){
      include('dbw.php');
      if(busca($guia,'guias_pedidos','gp_id != "'.$idgp.'" AND gp_pedido != "'.$pedido.'" AND gp_paqueteria = "'.$idpaq.'" AND gp_guia','COUNT(*)') > 0){
        die(alert_back('La Guia '.$guia.' Ya se encuantra asignada a otro paquete',true));
      }
      $sqlupg = 'UPDATE guias_pedidos SET
      gp_guia = "'.$guia.'",
      gp_paqueteria = "'.$idpaq.'"
      WHERE gp_id = "'.$idgp.'" AND gp_pedido = "'.$pedido.'"
      ';
      setq($sqlupg) or die($sqlupg);
    }
    /************PEDIDOS ACADEMY*****************************************************************************/
    function reporteacademy($estatus,$fini,$ffin){
      $sql = 'SELECT * FROM usuarios_pedidos INNER JOIN usuarios ON up_usuario = u_id 
              INNER JOIN cursos ON up_curso = c_id WHERE 1';
      if($estatus) $sql .= ' AND up_estatus = "'.$estatus.'"';
      if($fini && $ffin) $sql .= ' AND DATE(up_fecha) BETWEEN "'.$fini.'" AND "'.$ffin.'"';
      $sql .= ' ORDER BY up_fecha DESC';
    
      $this->resultrc = setqAcademy($sql);
      $this->resultrcc = setqAcademy($sql);
    }
    function selacademy($reporte){
      $sql = 'SELECT * FROM usuarios_pedidos INNER JOIN usuarios ON up_usuario = u_id 
              INNER JOIN cursos ON up_curso = c_id
              INNER JOIN formaspago ON f_id = up_fpago
              WHERE up_id = "'.$reporte.'"';
      $result = setqAcademy($sql);
      $row = $result->fetch_array();

      $this->cliente = $row['u_id'];
      $this->nmb = $row['u_nmb'];
      $this->apellidos = $row['u_apellidos'];
      $this->correo = $row['u_correo'];
      $this->regimen = $row['u_regimenfis'];
      $this->rfc = $row['u_rfc'];
      $this->razonsocial = $row['u_razon'];
      $this->calle = $row['u_calle'];
      $this->nume = $row['u_nume'];
      $this->colonia = $row['u_colonia'];
      $this->numi = $row['u_numi'];
      $this->cp = $row['u_cp'];
      $this->municipio = $row['u_municipio'];
      $this->estado = $row['u_estado'];
      $this->pais = $row['u_pais'];;
      $this->estatus = $row['up_estatus'];
      $this->formapago = $row['f_nmb'];
      $this->fechapedido = $row['up_fecha'];
      $this->curso = $row['c_nmb'];
      $this->factura = $row['up_facturacion'];
    }
    function updateacademy($id,$estatus,$comentarios,$pedido){
      $sql = 'UPDATE reportepago SET
          r_estatus = "'.$estatus.'",
          r_uautoriza = "8",
          r_fautoriza = "'.date('Y-m-d H:i:s').'",
          r_comentario = "'.$comentarios.'"
          WHERE r_id = "'.$id.'"';
      setqAcademy($sql);

      $sqlpedido = 'UPDATE usuarios_pedidos SET
                up_estatus = "'.$estatus.'"
                WHERE up_id = "'.$pedido.'"';
      setqAcademy($sqlpedido);
    }
    function sendmailacademy($web,$correop,$usuario,$accion = NULL,$pedido = NULL,$comentarios = NULL){
      require_once('lib/mailer/class.phpmailer.php');
      require_once("lib/mailer/class.smtp.php");

      $sql = 'SELECT * FROM correosop WHERE c_id = "'.$correop.'" AND c_web = "'.$web.'"';
      $result = setq($sql);
      $rwco = $result->fetch_array();

      $codescripcion = nl2br($rwco['c_descripcion']);
      $coemail = $rwco['c_mail'];
      $cotitulo = $rwco['c_titulo'];
      $coccp1 = $rwco['c_ccp1'];
      $coccp2 = $rwco['c_ccp2'];
      $coccp3 = $rwco['c_ccp3'];

      $sqlUsu = 'SELECT u_nmb,u_apellidos,u_correo FROM usuarios WHERE u_id = "'.$usuario.'"';
      $resultUsu = setqAcademy($sqlUsu);
      $rowus = $resultUsu->fetch_array();

      $nmbc = $rowus['u_nmb'].' '.$rowus['u_apellidos'];
      $mailc = $rowus['u_correo'];
      //$mailc = 'jorge.guerrero0913@gmail.com';

      $sqlPed = 'SELECT * FROM  usuarios_pedidos INNER JOIN cursos ON c_id = up_curso WHERE up_id = "'.$pedido.'"';
      $resultPed = setqAcademy($sqlPed);
      $rowPed = $resultPed->fetch_array();
      
      $title = $cotitulo;
      //$title = str_replace('--%nombrecliente%--',$pedido,$title);

      $message = $codescripcion;
      $message = str_replace('--%nombrecliente%--',trim($nmbc),$message);

      if($accion == 3) $rem = 'Tu escuela virtual de JDSHOPMX';
      
      $message = str_replace('--%nombrecurso%--',trim($rowPed['c_nmb']),$message);
      $message = str_replace('--%comentarios%--',trim($comentarios),$message);

      $mail = new PHPMailer();
      $body = (utf8_decode($message));

      $mail->setFrom($coemail,$rem);
      $mail->AddAddress($mailc, $nmbc);

      if(!empty($coccp1))
        $mail->AddBCC($coccp1);
      if(!empty($coccp2))
        $mail->AddBCC($coccp2);
      if(!empty($coccp3))
        $mail->AddBCC($coccp3);

      $mail->Subject = utf8_decode($title);
      $mail->AltBody = "";
      $mail->MsgHTML($body);

      if($adjunto)
        $mail->AddAttachment($adjunto);

      $mail->SMTPSecure = "ssl";
      $mail->Host='jdshop.mx';
      $mail->Port = "465";
      $mail->IsSMTP();
      $mail->SMTPAuth = true;
      $mail->Username = $coemail;
      $mail->Password = "WpJDTye2020#";
      //$mail->SMTPDebug = 2;

      if(!$mail->send()) {
      }else{
      }
    }
  }

  class viewpedidosclw{
    var $model;
    function __construct($model) {
      $this->model = $model;
      $this->estp = array("N"=>"En Carrito de Compra","A"=>"Proceso de pago","R"=>"Pago Reportado","F"=>"Proceso de Surtido","S"=>"Proceso de Surtido","Q"=>"En ruta de paqueteria","X"=>"Finalizado","C"=>"Cancelado");
      $this->estc = array("N"=>"#FEF900","P"=>"#FDCD00","A"=>"#FD7B01","R"=>"#FD2401","F"=>"#7824B7","S"=>"#0127B2","Q"=>"#006699","X"=>"#01B700","C"=>"#CC0000");

      $this->model->estpac = array("N"=>"En Carrito de Compra","R"=>"Pago Reportado","A"=>"Aceptado","C"=>"Cancelado");
      $this->model->estcac = array("N"=>"#FEF900","R"=>"#FD2401","F"=>"#7824B7","A"=>"#01B700","C"=>"#CC0000");

      $this->model->txtestatus = array("A" => "Finalizada","P" => "En espera de autorización","N" => "En Captura","C" => "Cancelada");
      $this->model->backestatus = array("A" => "btn-success","P" => "bg-amber bg-darken-2","N"=>"bg-yellow bg-darken-3","C" => "btn-danger");

      $this->model->txtestatusc = array("N"=>"NUEVA","F"=>"FINALIZADA","C"=>"CANCELADA","A"=>"ABONADA");
      $this->model->backestatusc = array("N"=>"background-color:#FF5C5C;color:white;","F"=>"background-color:#47B6FF","C"=>"background-color:#000000; color:white;","A"=>"background-color:#FFE447");
      
      $this->calificacion = array("P" => "Positivo", "E" => "Neutral", "N" => "Negativo");
    }
    function browse($pedido,$cliente,$apellidos,$email,$estatus,$page,$pageresult,$web){
      $empresas = 1;
      $aest = array("N"=>"Pedido Nuevo","P" => "Confirmación de Inventario","A"=>"Proceso de Pago","F"=>"Pedido Finalizado","C"=>"Pedido Cancelado","R"=>"Pago Reportado <br> (Requiere Verificacion)");
      $acolor = array("N" => "#FFFF33","P" => "#FFCC33","F" => "#0066CC","A" => "#0066CC","C" => "#F50000") ;
      if($estatus == "N") $seln = 'selected';
      elseif($estatus == "A") $sela = 'selected';
      elseif($estatus == "R") $selr = 'selected';
      elseif($estatus == "C") $selc = 'selected';
      elseif($estatus == "F") $self = 'selected';
      elseif($estatus == "Q") $selq = 'selected';
      elseif($estatus == "X") $selx = 'selected';
      elseif($estatus == "P") $selpr = 'selected';
      else $selt = 'selected';
      //Sección: E1 Encabezado - Botones de acción
      /* echo '<div class="row page-title-actions">
        <div class="form-inline mb-1">
          <div class="btn-group" role="group">
            <a type="button" class="btn btn-success text-white" href="?modulo=pedidosclw&accion=index&w=1">
              <i class="icon-world"></i> JD SHOP
            </a>
            <a type="button" class="btn btn-info btn-darken-2 text-white" href="?modulo=pedidosclw&accion=academy">
              <i class="icon-graduation-cap"></i> Academy JD SHOP
            </a>
          </div>
          <button accesskey="L" id="filtrar" type="button" class="btn btn-info"  data-toggle="collapse" data-target="#filter-panel">
            <span class="glyphicon glyphicon-cog"></span><i class="fa fa-search5"></i> Filtrar
          </button>
        </div>';

        echo'<form autocomplete="off" action="?modulo=pedidosclw&accion=academy" method="post" onsubmit="return checkSubmitenviar();" class="row form-inline mb-1" id="filtro">
          <div id="filter-panel" class="collapse filter-panel col-md-12">
            <div class="mb-5">
              <label class="mr-sm-2">Desde:</label>
              <input type="date" id="fini" name="fini" value="'.$fini.'" class="form-control">
            </div>
            <div class="mb-5">
              <label class="mr-sm-2">Hasta:</label>
              <input type="date" id="ffin" name="ffin" value="'.$ffin.'" class="form-control">
            </div>';
            echo'&nbsp;<div class="mb-5">
              <label class="mr-sm-2">Estatus:</label>';
              echo '<select name="estatus" class="form-control">
                <option value="" '.$selt.'>Todos</option>
                <option value="R" '.$selr.'>Reportados</option>
                <option value="A" '.$sela.'>Aprobados</option>
                <option value="C" '.$selc.'>Cancelados</option>';
              echo '</select>';
            echo '</div>';
            echo'<div class="mb-5 ml-1">
              <label style="color:transparent">Filtrar:</label><br>
              <button type="submit" class="btn btn-info"><i class="icon-filter2"></i> Filtrar</button>
            </div>
            <div class="mb-5">
              <label style="color:transparent">Limpiar:</label><br>
              <a href="?modulo=pedidosclw&accion=academy" class="btn btn-warning"><i class="fa fa-times"></i> Limpiar</a>
            </div>
          </div>
        </form>
      </div>'; */
      echo '<div class="row page-title-actions">
        <div class="form-inline mb-1">
          <div class="btn-group" role="group">
            <a type="button" class="btn btn-success active text-white" href="?modulo=pedidosclw&accion=index&w=1">
              <i class="icon-world"></i> JD SHOP
              <span style="height:2.7px;display:block"></span>
            </a>
            <a type="button" class="btn btn-info btn-darken-1 text-white" href="?modulo=pedidosclw&accion=academy">
              <i class="icon-graduation-cap"></i> Academy JD SHOP
              <span class="tag tag-pill tag-danger" bis_skin_checked="1">'.buscaAcademy("R",'reportepago','r_estatus','COUNT(*)').'</span>
            </a>
          </div>
          <button accesskey="L" id="filtrar" type="button" class="btn btn-info"  data-toggle="collapse" data-target="#filter-panel">
            <span class="glyphicon glyphicon-cog"></span><i class="fa fa-search5"></i> Filtrar
            <span style="height:2.7px;display:block"></span>
          </button>
        </div>';
        //Sección: E2 Encabezado - Filtros
        ?>
        <div id="filter-panel" class="collapse col-md-12 filter-panel mb-2">
          <div class="panel panel-default">
            <div class="panel-body">
              <form class="form-inline" role="form" method="post" id="formbr">
                <input name="page" id="page" value="<?php echo $page; ?>" hidden> 
                <div class="mb-5 mr-1">
                  <label for="tipom">Pedido:</label>
                  <input type="text" class="form-control" name="pedido" placeholder="ID Pedido" onfocus="this.select();" value="<?php echo $pedido ?>" />
                </div>
                <div class="mb-5 mr-1">
                  <label for="tipom">Cliente:</label>
                  <input type="text" class="form-control" name="cliente" placeholder="Nombre, alias o correo " onfocus="this.select();" value="<?php echo $cliente ?>" />
                </div>
                <div class="mb-5 mr-1">
                  <label for="tipom">Estatus:</label>
                  <select class="form-control" name="estatus" id="estatus" >
                    <option value="" <?php echo $selt; ?> >Todos</option>
                    <option value="P" <?php echo $selpr; ?> >En proceso</option>
                    <option value="N" <?php echo $seln; ?> >En Carrito de Compra</option>
                    <option value="A" <?php echo $sela; ?> >Proceso de pago</option>
                    <option value="R" <?php echo $selr; ?> >Pago Reportado</option>
                    <option value="F" <?php echo $self; ?> >Proceso de Surtido</option>
                    <option value="Q" <?php echo $selq; ?> >En ruta de paqueteria</option>
                    <option value="X" <?php echo $selx; ?> >Finalizado</option>
                    <option value="C" <?php echo $selc; ?> >Cancelado</option>
                  </select>
                </div><!-- form group [search] -->
                <div class="mb-5">
                  <label for="">Acciones:</label> <br>
                  <button type="submit" class="btn btn-info">
                    <span class="glyphicon glyphicon-record"></span> <i class="fa fa-redo"></i> Actualizar
                  </button>
                  <a href="?modulo=pedidosclw&accion=index"><button type="button" class="btn btn-warning">
                    <span class="glyphicon glyphicon-record"></span> <i class="icon-android-cancel"></i> Limpiar
                  </button></a>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div></div>
      <div class="main-card mb-3 card">
        <div class="card-body row">
          <?php
          //echo '<a href="?modulo=pedidosclw&accion=editpedido" title="Agregar pedido" acceskey="N" onclick="this.onclick = function(){return false;}"><input type="button" value=" " id="nuevo" class="botonb"></a>';
          $onkeyup = 'onkeyup ="var key = window.event.keyCode;if(key==13){ var time=setTimeout(submit(), 1000); }"';
          echo '<div class="table table-responsive">
            <center>
              <table  class="table table-hover table-striped" width="100%">
                <thead class="thead bg-blue bg-darken-3 text-white pt-1 pb-1">';
                  echo '<tr>
                    <th>Id</th>';
                    echo '<th>Cliente</th>
                    <th>Fecha</th>';
                    if($estatus == "X")
                      echo '<th>Calificación</th>';
                    echo'<th>Total</th>
                    <th>Estatus</th>
                    <th></th>
                  </tr>
                </thead>';
                echo '<tbody>';
                  $link = NULL;
                  while($row = $this->model->result->fetch_array()){
                    $totalprod = busca($row['p_id'],'pedidoscl INNER JOIN pedidoscld','pd_pedido','COUNT(pd_id)');
                    
                    if($totalprod > 0 ){
                    $colorb = "#FF0000";
                    $link = '';
                    echo '<tr>';
                      echo '<th>'.$row['p_id'].'</th>';
                      echo '<td>'.busca($row['p_cliente'],'clientes','c_id','c_nmb').' '.busca($row['p_cliente'],'clientes','c_id','c_apellidos').'</td>';
                      echo '<td>'.$row['p_fechagen'].'</td>';
                      if($estatus == "X") echo '<td>'.$this->calificacion[$row['p_calificacion']].'</td>';

                      
                      if(number_format($row['p_total']) == 0 && busca($row['p_id'],'pedidoscld','pd_pedido','COUNT(*)') > 0){
                        
                        if(busca($row['p_id'],'pedidoscld','pd_pedido','SUM(pd_precio*pd_cantidad)') < 5000 ) $envio = 130; else $envio = 0;
                        $adfp = busca($row['p_fpago'],'formaspago','f_id','f_montoa');
                        $porcentajea = busca($row['p_fpago'],'formaspago','f_id','f_porcentajea');
                        if($porcentajea == 1)
                          $adfp = $subtotal * ($adfp/100);
                        
                        $descuento = busca($row['p_id'],'bonificaciones','b_pedido','SUM(b_monto)');
                        if(!$descuento)
                          $descuento = 0;
                        $totalx = busca($row['p_id'],'pedidoscld','pd_pedido','SUM(pd_precio*pd_cantidad)')+$envio+$adfp-$descuento;
                        //echo "<td>SIMON $descuento $adfp $envio $totalx</td>";
                        //echo $row['p_total']
                      }else{
                        $totalx = $row['p_total'];
                      }

                      echo '<td class="number-align">$ '.number_format($totalx,2).'</td>';
                      echo '<td style="background:'.$this->estc[$row['p_estatus']].'">'.$this->estp[$row['p_estatus']].'</td>';
                      echo '<td>
                        <a href="?modulo=pedidosclw&accion=show&id='.$row['p_id'].'" onclick="this.onclick = function(){return false;}">
                          <button class="btn btn-primary"><i class="icon-list-alt"></i>Ver pedido</button>
                        </a>
                      </td>';
                    echo '</tr>';
                    }
                  }
                echo '</tbody>';
              echo '</table>
            </center>
          </div>';

          if($_REQUEST['page'] == 0){
            $hidden = 'style="pointer-events: none;
            background: #70707026;
            color: black;"';
          }else $hidden = "";

          if($pedido || $cliente){
          }else{
            echo '<div class="col-md-12 text-xs-center">
              <div class="mb-3">
                <nav aria-label="Page navigation">
                  <ul class="pagination">
                    <li class="page-item">
                      <a class="page-link" onclick="mandar('.($_REQUEST['page']-1).')" aria-label="Previous" '.$hidden.'>
                        <span aria-hidden="true">&laquo; Ant</span>
                        <span class="sr-only">Anterior</span>
                      </a>
                    </li>';
                    echo '<script>
                      function mandar(id){
                        document.getElementById("page").value = id;
                        document.getElementById("formbr").submit();
                      }
                    </script>';
                    $numres = 50;
                    $bloque = 50;
                    $nr = $this->model->resultt->num_rows;
                    $pageres = ceil($nr/$numres);
                    if($pageres>=10){
                      if($_REQUEST['page'] == 0) { $min = 1; $nombre = "Inicio";}
                      else {$min = $_REQUEST['page']-1; $nombre = "Inicio...";}
                      if($min <= 1) $min = 1;

                      if($_REQUEST['page'] == ($pageres-1)) $max = ($pageres-1);
                      else $max = $_REQUEST['page']+3;
                      if($max >= ($pageres-1)) $max = ($pageres-1);

                      if($_REQUEST['page'] == 0) $active = "active";
                      else $active = "";
                      echo '<li class="page-item '.$active.'"><a class="page-link" onclick="mandar(0);">'.$nombre.'</a></li>';
                    }elseif($pageres<=9){
                      $max=$pageres;
                      $min=0;
                    }
                    for($i=$min;$i<$max;$i++){
                      if($_REQUEST['page'] == $i) $active = "active";
                      else $active = "";
                      if($i == 0) $nombre = "Inicio";
                      else $nombre = $i;
                      echo '<li class="page-item '.$active.'"><a class="page-link" onclick="mandar('.$i.');">'.$nombre.'</a></li>';
                    }
                    if($pageres<=9){
                    }else{
                      if($_REQUEST['page'] == ($pageres-5)) $nombre = ($pageres-2);
                      else $nombre = '... '.($pageres-2);
                      if($_REQUEST['page'] == $i) $active = "active";
                      else $active = "";
                      if($_REQUEST['page'] >= ($pageres-4)) echo '';
                      else echo '<li class="page-item '.$active.'">
                        <a class="page-link" onclick="mandar('.($pageres-2).')">'.$nombre.'</a>
                      </li>';
                      echo '<li class="page-item '.$active.'">
                        <a class="page-link" onclick="mandar('.($pageres-1).')">'.($pageres-1).'</a>
                      </li>';
                    }
                    if($_REQUEST['page'] == ($pageres-1) || $pageres <= 1) 
                      $hidden2 = 'style="pointer-events: none;
                                  background: #70707026;
                                  color: black;"';
                    else $hidden2 = '';
                    echo '<li class="page-item">
                      <a class="page-link" onclick="mandar('.($_REQUEST['page']+1).')" aria-label="Next" '.$hidden2.'>
                        <span aria-hidden="true">Sig &raquo;</span>
                        <span class="sr-only">Siguiente</span>
                      </a>
                    </li>
                  </ul>
                </nav>
              </div>
            </div>'; 
          }
    }
    function edit($empresa){
      include_once('config.php');
      $config = new modelconfig();
      $config->selectesp();
      $empresas = $config->verunaempresa;
      $colores = $config->vercolores;
      $tallas = $config->vertallas;
      $stlemp = 'style="display:none"';
      if($empresas == "1")
        $stlemp = '';
      echo '<div class="div2" id="bboton01">
        <a href="?modulo=pedidosclw&accion=index"><input type="button" value="" id="cancelar" class="botonb" /></a>
      </div></div>';
      if($this->model->id) $accion = "update";
      else $accion = "insert";
      ?><script language="JavaScript">
        function checkSubmitedit() {
          document.getElementById("guardar").value = "JD";
          document.getElementById("guardar").disabled = true;
          return true;
        }
      </script><?php
      echo '<form autocomplete="off" name="savepedido" method="post" action="?modulo=pedidosclw&accion='.$accion.'" onsubmit="return checkSubmitedit();">';
        echo '<input type="hidden" name="id" value="'.$this->model->id.'">';
        echo '<center>
          <table class="lista" width="75%">';
            echo '<thead>
              <tr>
                <th>Número de Pedido</th>';
                if($empresas == "1")
                  echo '<th>Empresa</th>';
                echo '<th>Cliente</th><th>Observaciones</th><th>Creación</th>';
              echo '</tr>
            </thead>';
            echo '<tr>
              <td>'.$this->model->id.'</td>';
              $sqlp = 'SELECT * FROM configuracion';
              if($this->model->empresa){
                $empresa = $this->model->empresa;
                $sqlp.=' WHERE c_consecutivo = "'.$empresa.'"';
              }
              else
                $this->model->empresa = $empresa;
              $resultp = setq($sqlp) or die($sqlp);
              if($empresas == "1"){
                echo '<td >
                  <select name="empresa" id="empsel" required onchange="send();">
                    <option></option>';
                    while($row = mysql_fetch_array($resultp)){
                      if($row['c_consecutivo'] == $this->model->empresa)
                        $chem = "selected";
                      else
                        $chem = "";
                      echo '<option value="'.$row['c_consecutivo'].'" '.$chem.'>'.$row['c_id'].'</option>';
                    }
                  echo '</select>
                </td>';
              }
              if($this->model->id)
                $sqlcl = 'SELECT * FROM clientes WHERE c_estatus = "A" ORDER BY c_nmb';
              else
                $sqlcl = 'SELECT * FROM clientes WHERE c_estatus = "A" AND c_id NOT IN 
                          (SELECT p_cliente FROM pedidoscl WHERE p_estatus = "N") ORDER BY c_nmb';
              $resultcl = setq($sqlcl) or die($sqlcl);
              echo '<td>
                <select name="cliente" required">
                  <option></option>';
                  while($rowcl = mysql_fetch_array($resultcl)){
                    if($this->model->cliente == $rowcl['c_id'])
                      echo ' <option value="'.$rowcl['c_id'].'" selected>'.$rowcl['c_nmb'].' '.$rowcl['c_apellidos'].'</option> ';
                    else
                      echo ' <option value="'.$rowcl['c_id'].'">'.$rowcl['c_nmb'].' '.$rowcl['c_apellidos'].'</option> ';
                  }
                echo '</select>
              </td>
              <td><input type="text" name="observaciones" value="'.$this->model->observaciones.'"></td>';
              echo '<td>'.$_SESSION['uid'].'</td>';
              //echo '<td><input type="checkbox" name="stock"></td>';
            echo '</tr>';
            echo '<tr><td colspan="5"><input type="submit" value=" "id="guardar" class="botont"></td></tr>';
          echo '</table>
        </center>
      </form>';
    }
    function show($producto,$cantidad,$showdetail){
      ?><script>
        function checksubmitguardar() {
          document.getElementById("guardar").value = "JD";
          document.getElementById("guardar").disabled = true;
          return true;
        }
        function confirmcancel(pedido){
          var cancel = confirm("Desea cancelar el pedido "+pedido);
          if(cancel){
            document.location.href="?modulo=pedidosclw&accion=cancelar&id="+pedido;
          }
        }
        function sendpedido(pedido){
          var conf = confirm("Al enviar este pedido, se notificará al cliente la guía y pasara a enviado\n¿Deseas continuar?");
          if(conf == true){
            document.location.href="?modulo=pedidosclw&accion=enviarpedido&id="+pedido;
          }
        }
        function recibirpedido(pedido){
          var conf = confirm("Este botón indica que el cliente ha recibido su paquete\n¿Deseas continuar?");
          if(conf == true){
            document.location.href="?modulo=pedidosclw&accion=recepcionpedido&id="+pedido;
          }
        }
      </script><?php
      $tallas = 0;
      $colores = 0;
      $empresas = 0;
      $aest = array("N"=>"Pedido Nuevo","P" => "Confirmación de Inventario","A"=>"Proceso de Pago","F"=>"Pedido Finalizado","C"=>"Pedido Cancelado","R"=>"Pago Reportado <br> (Requiere Verificacion)");
      $acolor = array("N" => "#FFFF33","P" => "#FFCC33","F" => "#0066CC","A" => "#0066CC","C" => "#F50000") ;
      echo '<div class="row page-title-actions">';
        echo '<a href="?modulo=pedidosclw&accion=index&w='.$this->model->web.'"onclick="this.onclick = function(){return false;}">
          <button type="button" class="btn btn-warning mr-2 ml-1"><i class="fa fa-arrow-left"></i> Atras </button>
        </a>';
        if($this->model->estatus == "N"){
          if(isset($_GET['fp'])){
            echo '<a href="?modulo=pedidosclw&accion=show&id='.$this->model->id.'" title="regresar" onclick="this.onclick = function(){return false;}"><input type="button" value=" " id="atras" class="botonb"></a>';
            //echo '<a href="?modulo=pedidosclw&accion=aplicar&id='.$this->model->id.'" title="Aplicar Pedido" acceskey="A" onclick="this.onclick = function(){return false;}"><input type="button" value=" " id="finalizar" class="botonb"></a>';
          }
          else{
            //echo '<a href="?modulo=pedidosclw&accion=aplicar&id='.$this->model->id.'" title="Aplicar Pedido" acceskey="A" onclick="this.onclick = function(){return false;}"><input type="button" value=" " id="aplicar" class="botonb"></a>';
          }
        }
        elseif($this->model->estatus == "P" OR $this->model->estatus == "A"){
          //    echo '<a href="?modulo=pedidosclw&accion=abrir&id='.$this->model->id.'" title="Editar Pedido" acceskey="E" onclick="this.onclick = function(){return false;}"><input type="button" value=" " id="editar" class="botonb"></a>';
          if($this->model->estatus == "A")
            echo '<a data-fancybox data-type="ajax" data-src="popup/setpagow.php?id='.$this->model->id.'&rand='.rand(0,100).'" href="javascript:;">
              <button class="btn btn-success mr-1" ><i class="icon-cc-visa"></i> Registrar pago</button>
            </a>';
        }
        if($this->model->estatus != "X" && $this->model->estatus != "C"){
        }
        if($this->model->estatus == "F"){
          echo '<button type="button" class="btn btn-primary mr-2 ml-1" style="display:none;" id="enviarpaq" onclick="sendpedido('.$this->model->id.');"><i class="fa fa-paper-plane"></i> Confirmar envío </button>';
        }
        if($this->model->estatus == "Q"){
          echo '<button type="button" class="btn btn-success mr-2 ml-1" style="display:none;" id="enviarpaq" onclick="recibirpedido('.$this->model->id.');"><i class="icon-ship"></i> Confirmar recepción </button>';
        }
        if($this->model->estatus == "P" OR $this->model->estatus == "A" OR $this->model->estatus == "R"){
          echo '<a href="#" title="Cancelar Pedido" acceskey="C" onclick="confirmcancel('.$this->model->id.')">
            <button class="btn btn-danger"><i class="icon-times-circle-o"></i>Cancelar</button>
          </a>';
        }
        if(busca($this->model->cliente,'pedidoscl','p_cliente','COUNT(p_id)') > 1 ){
          $sqlpedidos = 'SELECT * FROM pedidoscl WHERE p_cliente = "'.$this->model->cliente.'" ';
          $resultpedido = setq($sqlpedidos);
          //while($rowpedido = $resultpedido->fetch_array()){}
          echo '
          <!-- Button trigger modal -->
          <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-target="#exampleModal">
            Historial
          </button>

          <!-- Modal -->
          <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <div class="table table-responsive">
                    <center>
                      <table  class="table table-hover table-striped" width="100%">
                        <thead class="thead bg-blue bg-darken-3 text-white pt-1 pb-1">';
                          echo '<tr>
                            <th>Id</th>';
                            echo '<th>Cliente</th>
                            <th>Fecha</th>';
                            if($estatus == "X")
                              echo '<th>Calificación</th>';
                            echo'<th>Total</th>
                            <th>Estatus</th>
                            <th></th>
                          </tr>
                        </thead>';
                        echo '<tbody>';
                          $link = NULL;
                          while($rowpedido = $resultpedido->fetch_array()){
                            $totalprod = busca($rowpedido['p_id'],'pedidoscl INNER JOIN pedidoscld','pd_pedido','COUNT(pd_id)');
                            
                            if($totalprod > 0 ){
                            $colorb = "#FF0000";
                            $link = '';
                            echo '<tr>';
                              echo '<th>'.$rowpedido['p_id'].'</th>';
                              echo '<td>'.busca($rowpedido['p_cliente'],'clientes','c_id','c_nmb').' '.busca($rowpedido['p_cliente'],'clientes','c_id','c_apellidos').'</td>';
                              echo '<td>'.$rowpedido['p_fechagen'].'</td>';
                              if($estatus == "X") echo '<td>'.$this->calificacion[$rowpedido['p_calificacion']].'</td>';
        
                              
                              if(number_format($rowpedido['p_total']) == 0 && busca($rowpedido['p_id'],'pedidoscld','pd_pedido','COUNT(*)') > 0){
                                
                                if(busca($rowpedido['p_id'],'pedidoscld','pd_pedido','SUM(pd_precio*pd_cantidad)') < 5000 ) $envio = 130; else $envio = 0;
                                $adfp = busca($rowpedido['p_fpago'],'formaspago','f_id','f_montoa');
                                $porcentajea = busca($rowpedido['p_fpago'],'formaspago','f_id','f_porcentajea');
                                if($porcentajea == 1)
                                  $adfp = $subtotal * ($adfp/100);
                                
                                $descuento = busca($rowpedido['p_id'],'bonificaciones','b_pedido','SUM(b_monto)');
                                if(!$descuento)
                                  $descuento = 0;
                                $totalx = busca($rowpedido['p_id'],'pedidoscld','pd_pedido','SUM(pd_precio*pd_cantidad)')+$envio+$adfp-$descuento;
                                //echo "<td>SIMON $descuento $adfp $envio $totalx</td>";
                                //echo $rowpedido['p_total']
                              }else{
                                $totalx = $rowpedido['p_total'];
                              }
        
                              echo '<td class="number-align">$ '.number_format($totalx,2).'</td>';
                              echo '<td style="background:'.$this->estc[$rowpedido['p_estatus']].'">'.$this->estp[$rowpedido['p_estatus']].'</td>';
                              echo '<td>
                                <a href="?modulo=pedidosclw&accion=show&id='.$rowpedido['p_id'].'" onclick="this.onclick = function(){return false;}">
                                  <button class="btn btn-primary btn-sm"><i class="icon-list-alt"></i>Ver pedido</button>
                                </a>
                              </td>';
                            echo '</tr>';
                            }
                          }
                        echo '</tbody>';
                      echo '</table>
                    </center>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                  <button type="button" class="btn btn-primary">Save changes</button>
                </div>
              </div>
            </div>
          </div>
          ';
        }
      echo '</div></div>';
      $con = 'SELECT * FROM articulos ORDER BY a_nmb';
      $query = setq($con) or die($con);
      while ($row = $query->fetch_array()) {
        $elementos[] = '"' . $row['a_nmb'] . '"';
      }
      $arreglo = implode(", ", $elementos);
      ?><script>
        $(function() {
          var availableTags = new Array(<?php echo $arreglo; ?>);//imprime el arreglo dentro de un array de javascript
          $("#tags").autocomplete({
            source: availableTags
          });
        });
      </script><?php
      echo '<div class="table">';
        echo '<div class="table-responsive">
          <table class="table table-hover table-striped">
            <thead class="thead bg-blue bg-darken-3 text-white pt-1 pb-1">';
              echo '<tr>';
                echo '<th colspan="8">Pedido de "'.busca($this->model->cliente,'clientes','c_id','CONCAT(c_nmb," ",c_apellidos)').'" '.busca($this->model->cliente,'clientes','c_id','c_mail').'</th>';
              echo '</tr>';
              echo '<tr>
                <th>Número de Pedido</th>
                <th>Fecha de Creación</th>
                <th>Fecha Ultima Modificación</th>
                <th>Generó</th>
                <th>Guias asignadas</th>
                <th>Estatus</th><b></b>
              </tr>
            </thead>
            <tr>
              <td><b>'.$this->model->id.'</b></td>
              <td><b>'.$this->model->fechagen.'</b></td>
              <td><b>'.$this->model->fechaapli.'</b></td>
              <td><b>'.$this->model->ugen.'</b></td>
              <td class="text-medium">';
                if($this->model->estatus == "F")
                  echo '<div class="col-md-12 mb-1">
                    <a data-fancybox data-type="ajax" data-src="popup/setguiaweb.php?pedido='.$this->model->id.'" href="javascript:;">
                      <button class="btn bg-green bg-darken-3 text-white"><i class="icon-square-plus"></i> Agregar</button>
                    </a>
                  </div>';
                $sqlrp = 'SELECT * FROM guias_pedidos WHERE gp_pedido = "'.$this->model->id.'"';
                $rspg = setq($sqlrp);
                $noguias = 0;
                while($rwpg = $rspg->fetch_array()){
                  $noguias++;
                  if($this->model->estatus == "F"){
                    $guias = '
                    <div class="col-md-2">
                      <button type="button" onclick="eliminarguia('.$rwpg['gp_id'].','.$this->model->id.');" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></button> 
                    </div>
                    <div class="col-md-10 mb-5 border-left-blue-grey">'.$rwpg['gp_guia'].'-'.$rwpg['gp_paqueteria'].'</div>
                    <br>
                    ';
                  }else{
                    $guias = '
                      <div class="col-md-10 mb-5 border-left-blue-grey">'.$rwpg['gp_guia'].'-'.$rwpg['gp_paqueteria'].'</div>
                    ';
                  }
                  echo  $guias;
                }
                if($noguias > 0){
                  echo '<script>
                    document.getElementById("enviarpaq").style.display = "";
                  </script>';
                }
                echo '<script>
                  function eliminarguia(id,pedido){
                    var c = confirm("¿Deseas eliminar esta guía?");
                    if(c){
                      window.location.href="?modulo=pedidosclw&accion=borrarguia&id="+id+"&pedido="+pedido;  
                    }
                  }
                </script>';
              echo '</td>
              <td class="pl-1" style="background:'.$this->estc[$this->model->estatus].'"><b>'.$this->estp[$this->model->estatus].'</b></td>
            </tr>
            <tr><th>Observaciones</th><td colspan="5">'.$this->model->observaciones.'</td></tr>';
            $sqld = 'SELECT * FROM direnvio WHERE d_id = "'.$this->model->direccion.'"';
            $resultd = setq($sqld) or die($sqld);
            $rowd = $resultd->fetch_array();
            echo'<tr>
              <th colspan="2" width="25%">Forma de Pago</th>
              <th>Cuenta de Pago</th>
              <th>Forma de Envio</th>
              <th>No. Cajas</th>
              <th width="25%">Direccion de envio</th>
            </tr>
            <tr>';
              $linkf = '';
              if(isset($_GET['fp']))
                $linkf = '&fp=1';
              if($this->model->estatus == "N" OR $this->model->estatus == "A" OR $this->model->estatus == "P"){
                $sqlfp = 'SELECT * FROM formaspago WHERE f_estatus = "A"';
                $resultfp = setq($sqlfp) or die($sqlfp);
                echo '<td colspan="2">';
                  echo '<form action="?modulo=pedidosclw&accion=updatefp&id='.$this->model->id.$linkf.'" method="post">
                    <select name="fpago" id="fpago" class="form-control" style="width:95%;" onchange="this.form.submit();">';
                      while($rowfp = $resultfp->fetch_array()){
                        if($rowfp['f_id'] == $this->model->fpago)
                          echo '<option value="'.$rowfp['f_id'].'" selected>'.$rowfp['f_nmb'].'</option> ';
                        else
                          echo '<option value="'.$rowfp['f_id'].'">'.$rowfp['f_nmb'].'</option> ';
                      }
                    echo '</select>
                  </form>';
                echo '</td>';
              }
              else{
                echo '<td colspan="2">'.busca($this->model->fpago,'formaspago','f_id','f_nmb').'</td>';
              }
              echo '<td>'.busca($this->model->cuentafp,'cuentasfp','c_id','c_nmb').'</td>';
              if($this->model->estatus == "N" OR $this->model->estatus == "A" OR $this->model->estatus == "P"){
                $sqlpq = 'SELECT * FROM paqueterias INNER JOIN paqueteriasd ON p_id = pd_paqueteria WHERE p_estatus = "A" AND pd_estatus = "A"';
                $resultpq = setq($sqlpq) or die($sqlpq);
                echo '<td>';
                  echo '<form action="?modulo=pedidosclw&accion=updatefe&id='.$this->model->id.$linkf.'" method="post">
                    <select name="fenvio" id="fenvio" style="width:95%;" onchange="this.form.submit();">';
                      while($rowpq = $resultpq->fetch_array()){
                        if($rowpq['pd_id'] == $this->model->paqueteria)
                          echo '<option value="'.$rowpq['pd_id'].'" selected>'.$rowpq['p_nmb'].' '.$rowpq['pd_nmb'].'</option> ';
                        else
                          echo '<option value="'.$rowpq['pd_id'].'">'.$rowpq['p_nmb'].' '.$rowpq['pd_nmb'].'</option> ';
                      }
                    echo '</select>
                  </form>';
                echo '</td>';
              }
              else{
                $paq = busca($this->model->paqueteria,'paqueteriasd','pd_id','pd_paqueteria');
                echo '<td>'.busca($paq,'paqueterias','p_id','p_nmb').' '.busca($this->model->paqueteria,'paqueteriasd','pd_id','pd_nmb').'</td>';
              }
              echo '<td>'.busca($this->model->id,'productos_empaque','pe_pedido','MAX(pe_nopaquete)').'</td>';
              if($this->model->estatus == "N" OR $this->model->estatus == "A" OR $this->model->estatus == "P"){
                $sqlde = 'SELECT * FROM direnvio WHERE d_cliente = "'.$this->model->cliente.'"';
                $resultde = setq($sqlde) or die($sqlde);
                echo '<td>';
                  echo '<form action="?modulo=pedidosclw&accion=updatede&id='.$this->model->id.$linkf.'" method="post">
                    <select name="denvio" id="denvio" style="width:95%;" onchange="this.form.submit();">';
                      while($rowde = $resultde->fetch_array()){
                        if($rowde['d_id'] == $this->model->direccion)
                          echo '<option value="'.$rowde['d_id'].'" selected>'.$rowde['d_calle'].' '.$rowde['d_numi'].' '.$rowde['d_nume'].' '.$rowde['d_colonia'].' '.$rowde['d_cp'].' '.busca($rowde['d_estado'],'estado','e_id','e_nmb').' '.$rowde['d_pais'].'</option> ';
                        else
                          echo '<option value="'.$rowde['d_id'].'">'.$rowde['d_calle'].' '.$rowde['d_numi'].' '.$rowde['d_nume'].' '.$rowde['d_colonia'].' '.$rowde['d_cp'].' '.busca($rowde['d_estado'],'estado','e_id','e_nmb').' '.$rowde['d_pais'].'</option> ';
                      }
                    echo '</select>
                  </form>';
                  //echo '<a id="popup" href="?modulo=clientes&accion=adddir&p='.$this->model->id.'&c='.$this->model->cliente.'"><input type="button" id="agregar" class="botont"/></a>';
                echo '</td>';
              }
              else{
                $estado = busca($rowd['d_estado'],'estado','e_id','e_nmb');
                  if(!$estado) $estado = $rowd['d_estado'];
                  echo '<td>'.$rowd['d_calle'].' '.$rowd['d_numi'].' '.$rowd['d_nume'].' '.$rowd['d_colonia'].'<br>
                  '.$rowd['d_municipio'].' '.$estado.' '.$rowd['d_cp'].' '.$rowd['d_pais'].'</td>
                </tr>';
              }
            echo '<tr>';
              echo '<th colspan="3">Facturacion</th>';
              /*
                if(busca($this->model->id,'asignacion_pedidoscl','ap_pedido','ap_guia')){
                  echo '<th>Paqueteria</th>';
                  if($this->model->estatus = "X")
                    echo '<th>Fecha Salida</th>';
                }
              */
            echo '</tr>
            <tr>';
              if($this->model->factura == 1){
                if($this->model->estatus == "N" OR $this->model->estatus == "A" OR $this->model->estatus == "P"){
                  echo '<td colspan="3">
                    <form action="?modulo=pedidosclw&accion=updatefact&id='.$this->model->id.'" method="post">
                      <input type="checkbox" name="fact" onclick="this.form.submit();" checked/><br>
                    </form>';
                    if(busca($this->model->cliente,'clientes','c_id','c_razon'))
                      echo busca($this->model->cliente,'clientes','c_id','c_razon').'<br>';
                    else
                      echo 'No hay razon social definida<br>';
                    if(busca($this->model->cliente,'clientes','c_id','c_rfc'))
                      echo busca($this->model->cliente,'clientes','c_id','c_rfc').'<br>';
                    else
                      echo 'No hay RFC definido<br>';
                    if(busca($this->model->cliente,'clientes','c_id','c_calle'))
                      echo busca($this->model->cliente,'clientes','c_id','c_calle').'<br>';
                    else
                      echo 'No hay calle definida<br>';
                    if(busca($this->model->cliente,'clientes','c_id','c_nume'))
                      echo busca($this->model->cliente,'clientes','c_id','c_nume').'<br>';
                    else
                      echo 'No hay numero exterior definido<br>';
                    if(busca($this->model->cliente,'clientes','c_id','c_numi'))
                      echo busca($this->model->cliente,'clientes','c_id','c_numi').'<br>';
                    else
                      echo 'No hay numero interior definido<br>';
                    if(busca($this->model->cliente,'clientes','c_id','c_colonia'))
                      echo busca($this->model->cliente,'clientes','c_id','c_colonia').'<br>';
                    else
                      echo 'No hay colonia definida<br>';
                    if(busca($this->model->cliente,'clientes','c_id','c_municipio'))
                      echo busca($this->model->cliente,'clientes','c_id','c_municipio').'<br>';
                    else
                      echo 'No hay municipio definido<br>';
                    if(busca($this->model->cliente,'clientes','c_id','c_cp'))
                      echo busca($this->model->cliente,'clientes','c_id','c_cp').'<br>';
                    else
                      echo 'No hay Codigo postal definido<br>';
                    if(busca(busca($this->model->cliente,'clientes','c_id','c_estado'),'estado','e_id','e_nmb'))
                      echo busca(busca($this->model->cliente,'clientes','c_id','c_estado'),'estado','e_id','e_nmb').'<br>';
                    else
                      echo 'No hay estado definido<br>';
                    //echo '<br><a id="popup" href="?modulo=clientes&accion=updatedf&id='.$this->model->id.'&c='.$this->model->cliente.'"><input type="button" id="actualizar" class="botont"/></a>';
                    $cfdi = busca($this->model->id,'pedidoscl','p_id','p_cfdi');
                    $cfdinmb = busca($cfdi,'cfdi_uso','cu_id','cu_nmb');
                    echo 'Uso de CFDI: '.$cfdinmb;
                  echo '</td>';
                }
                else{
                  echo '<td colspan="3">';
                    if(busca($this->model->cliente,'clientes','c_id','c_razon'))
                      echo busca($this->model->cliente,'clientes','c_id','c_razon').'<br>';
                    else
                      echo 'No hay razon social definida<br>';
                    if(busca($this->model->cliente,'clientes','c_id','c_rfc'))
                      echo busca($this->model->cliente,'clientes','c_id','c_rfc').'<br>';
                    else
                      echo 'No hay RFC definido<br>';
                    if(busca($this->model->cliente,'clientes','c_id','c_calle'))
                      echo busca($this->model->cliente,'clientes','c_id','c_calle').'<br>';
                    else
                      echo 'No hay calle definida<br>';
                    if(busca($this->model->cliente,'clientes','c_id','c_nume'))
                      echo busca($this->model->cliente,'clientes','c_id','c_nume').'<br>';
                    else
                      echo 'No hay numero exterior definido<br>';
                    if(busca($this->model->cliente,'clientes','c_id','c_numi'))
                      echo busca($this->model->cliente,'clientes','c_id','c_numi').'<br>';
                    else
                      echo 'No hay numero interior definido<br>';
                    if(busca($this->model->cliente,'clientes','c_id','c_colonia'))
                      echo busca($this->model->cliente,'clientes','c_id','c_colonia').'<br>';
                    else
                      echo 'No hay colonia definida<br>';
                    if(busca($this->model->cliente,'clientes','c_id','c_municipio'))
                      echo busca($this->model->cliente,'clientes','c_id','c_municipio').'<br>';
                    else
                      echo 'No hay municipio definido<br>';
                    if(busca($this->model->cliente,'clientes','c_id','c_cp'))
                      echo busca($this->model->cliente,'clientes','c_id','c_cp').'<br>';
                    else
                      echo 'No hay Codigo postal definido<br>';
                    if(busca($this->model->cliente,'clientes','c_id','c_cp'))
                      echo busca($this->model->cliente,'clientes','c_id','c_cp').'<br>';
                    else
                      echo 'No hay Codigo postal definido<br>';
                    if(busca($this->model->cliente,'clientes','c_id','c_regimenfis')){
                      $regimen = busca($this->model->cliente,'clientes','c_id','c_regimenfis');
                      echo $regimen.' - '.busca($regimen,'cfdi_regimenfiscal','cr_id','cr_nmb').'<br>';
                    }
                    else
                      echo 'No hay Codigo postal definido<br>';
                    if(busca(busca($this->model->cliente,'clientes','c_id','c_estado'),'estado','e_id','e_nmb'))
                      echo busca(busca($this->model->cliente,'clientes','c_id','c_estado'),'estado','e_id','e_nmb').'<br>';
                    else
                      echo 'No hay estado definido<br>';
                  echo '</td>';
                }
              }
              else{
                if($this->model->estatus == "N" OR $this->model->estatus == "A" OR $this->model->estatus == "P"){
                  echo '<td colspan="3">
                    <form action="?modulo=pedidosclw&accion=updatefact&id='.$this->model->id.'" method="post">
                      <input type="checkbox" name="fact" onclick="this.form.submit();"/>
                    </form>
                  </td>';
                }
                else{
                  echo '<td colspan="3">NO</td>';
                }
              }
              /*
                if(busca($this->model->id,'asignacion_pedidoscl','ap_pedido','ap_guia')){
                  echo '<td>'.busca(busca(busca($this->model->id,'asignacion_pedidoscl','ap_pedido','ap_guia'),'guias','g_id','g_paqueteria'),'sociosenvio','se_id','se_nmb').'</td>';
                  if($this->model->estatus = "X")
                  echo '<td>'.busca(busca($this->model->id,'entrega_paqd','e_pedido','e_entrega'),'entrega_paq','ep_id','ep_fecha').'</td>';
                }
              */
            echo'</tr></div>';
            if($this->model->estatus == "R"){
              $sql = 'SELECT * FROM reportepago WHERE r_pedido = "'.$this->model->id.'"';
              $resultrp = setq($sql) or die($sql);

              $sqlw = 'SELECT p_web,p_total FROM pedidoscl WHERE p_id = "'.$this->model->id.'"';
              $resultw = setq($sqlw) or die($sqlw);
              list($web,$totalpedido) = $resultw->fetch_array();
              echo '<div class="table-responsive">
                <table class="table table-hover table-striped">
                  <thead class="thead bg-blue bg-darken-3 text-white pt-1 pb-1">';
                    echo '<tr>
                      <th>Pedido</th>
                      <th>Cliente</th>
                      <th>Monto de Pedido</th>
                      <th>Cuenta</th>
                      <th>Fecha y Hora</th>
                      <th>Cantidad</th>
                      <th>Referencia</th>
                      <th>Comprobante</th>
                      <th>Comentarios</th>
                    </tr>
                  </thead>';
                  echo '<tbody>';
                    while($row = $resultrp->fetch_array()){
                      //$totalpedido = totalpedido($row['r_pedido'],busca($row['r_pedido'],'pedidoscl','p_id','p_cliente'));
                      if($totalpedido == $row['r_monto']) $colb = "#00CC33"; else $colb = "#CC0000";
                      echo '<tr>';
                        echo '<td>'.$row['r_pedido'].'</td>';
                        echo '<td>'.busca(busca($row['r_pedido'],'pedidoscl','p_id','p_cliente'),'clientes','c_id','c_nmb').'</td>';
                        echo '<td>$ '.number_format($totalpedido,4).'</td>';
                        echo '<td>'.busca($row['r_cuenta'],'cuentasfp','c_id','c_udig').'</td>';
                        echo '<td>'.$row['r_fecha'].' - '.$row['r_hora'].'</td>';
                        echo '<td style="background:'.$colb.'">$ '.number_format($row['r_monto'],4).'</td>';
                        echo '<td>'.$row['r_referencia'].'</td>';
                        $aux1.='&ref='.$row['r_referencia'];
                        $aux1.='&cta='.busca($row['r_cuenta'],'cuentasfp','c_id','c_cuentav');
                        if($web == 1){
                          echo '<td><a href="https://jdshop.mx/adjpagos/'.busca($row['r_adj'],'adjreportepago','a_id','a_adjunto').'.'.busca($row['r_adj'],'adjreportepago','a_id','a_ext').'" target="_blank">'.busca($row['r_adj'],'adjreportepago','a_id','a_adjunto').'.'.busca($row['r_adj'],'adjreportepago','a_id','a_ext').'</a></td>';
                        }
                        echo '<td>'.$row['r_observacion'].'</td>';
                      echo '</tr>';
                    }
                  echo '</tbody>
                </table>
              </div>';
            }
            echo '</tbody>';
          echo '</table>
        </div>';
        if($this->model->estatus == "V" AND !isset($_GET['fp'])){
          ?><script language="JavaScript">
            function checkSubmitshow() {
              document.getElementById("aplicar").value = "JD";
              document.getElementById("aplicar").disabled = true;
              return true;
            }
            function updatecant(ped,n){
              var cant = document.getElementById("cant"+n).value;
              var cb = document.getElementById("cb"+n).value;
              document.location.href="?modulo=pedidosclw&accion=updatearti&cantidad="+cant+"&pedido="+ped+"&idpd="+n+"&producto="+cb;
            }
            function seleccionar_todo(){
              for (i=0;i<document.pedidod.elements.length;i++)
                if(document.pedidod.elements[i].type == "checkbox"){
                  document.pedidod.elements[i].checked=1;
                }
            }
            function deseleccionar_todo(){
              for (i=0;i<document.pedidod.elements.length;i++)
                if(document.pedidod.elements[i].type == "checkbox")
                  document.pedidod.elements[i].checked=0
            }
          </script><?php
          echo '<table>
            <tr>
              <td width="65%" valign="top">
                <form name="pedidod" action="?modulo=pedidosclw&accion=deletedetalleg&id='.$this->model->id.'" method="post">';
                  $this->modelD = new modelpedidosclwDetalle();
                  $this->ViewD = new viewpedidosclwDetalle($this->modelD);
                  
                  $this->modelD->resultprod($this->model->id);
                  $this->ViewD->browseprod($this->model->id);
                echo '</form>
              </td>';
              echo '<td width="30%" valign="top">';
                $autof1 = "autofocus";
                $autof2 = "";
                $cb = "";
                echo '<table>
                  <thead>
                    <tr><td><a href="modulos/popup/conjunto.php?id='.$this->model->id.'" accesskey="s" onclick="window.open(this.href,\'window\',\'width=870, height=650\');return false"><input type="button" value="" id="asigconjunto" class="botonb"></a></td></tr>
                  </thead>
                </table>';
                echo '<table width="100%" class="lista">
                  <thead>
                    <tr>
                      <th colspan="2">Agregar</th>
                    </tr>
                  </thead>';
                  $subp = '';
                  $subc = '';
                  if(($colores == "1" OR $tallas == "1")){
                    if($showdetail == "0")
                      $subp = 'onfocus="forms.fprod.submit();"';
                    else
                      $subc = 'onblur="submit();"';
                    echo '<form name="fprod" method="post">
                      <input type="hidden" name="showd" value="1" />
                      <tr>
                        <td><b>Producto</b></td>
                        <td>
                          <input id="tags" '.$autof1.' type="text" size="50" value="'.$producto.'" name="producto" '.$subc.' required>
                        </td>
                      </tr>
                    </form>';
                    echo '<form autocomplete="off" name="agrarticulo" action="?modulo=pedidosclw&accion=insertarti&pedido='.$this->model->id.'" method="post" onsubmit="return checksubmitguardar();">';
                      echo '<input type="hidden" name="producto" value="'.$cb.'">';
                  }
                  else{
                    echo '<form autocomplete="off" name="agrarticulo" action="?modulo=pedidosclw&accion=insertarti&pedido='.$this->model->id.'" method="post" onsubmit="return checksubmitguardar();">';
                      echo '<tr>
                        <td><b>Producto</b></td>
                        <td>
                          <label for="tags">
                          <input id="tags" type="text" size="50" value="" name="producto" required autofocus></label>
                        </td>
                      </tr>';
                  }
                  echo '<tr>
                    <td><b>Cantidad</b></td>
                    <td><center><input type="number" id="cantidad" name="cantidad"  min="0" max="9999999" step="any" size="15" value="'.$cantidad.'" required title="Se necesita un valor"  placeholder="Cantidad a pedir" '.$subp.' '.$autof2.'></center></td>
                  </tr>';
                  echo '<tr><td colspan="2"><center><input type="submit" value=" " id="guardar" class="botont"/></center></td></tr>
                </table></form>';
              echo '</td>
            </tr>
          </table>';
          echo '</td></tr></table> ';
        }
        elseif($this->model->estatus == "N" AND isset($_GET['id'])){
          ?><script language="JavaScript">
            function checkSubmitshow() {
              document.getElementById("aplicar").value = "JD";
              document.getElementById("aplicar").disabled = true;
              return true;
            }
          </script><?php
          echo '<div class="table-responsive">
            <table>';
              echo '<tr>
                <td width="65%" valign="top">';
                  $this->modelD = new modelpedidosclwDetalle();
                  $this->ViewD = new viewpedidosclwDetalle($this->modelD);

                  $this->modelD->resultDetalle($this->model->id);
                  $this->ViewD->listDetalle($this->model->id);
                echo '</td>';
              echo '</tr>
            </table>
          </div> ';
        }
        else{
          $this->modelD = new modelpedidosclwDetalle();
          $this->ViewD = new viewpedidosclwDetalle($this->modelD);

          $this->modelD->resultDetalle($this->model->id);
          $this->ViewD->listDetalle($this->model->id);
        }
        /*
          echo '</td><td width="20%" valign="top">';
          $sqldp = 'SELECT aw_departamento departamento,aw_ctye nmb,SUM(pd_cantidad) total FROM pedidoscl
                    INNER JOIN pedidoscld ON pedidoscl.p_id = pd_pedido
                    INNER JOIN articulosw ON pd_producto = aw_cb
                    INNER JOIN departamentosw ON p_departamento = d_id
                    WHERE pedidoscl.p_cliente = "'.$this->model->cliente.'"
                    GROUP BY departamentosw ORDER BY total DESC LIMIT 0,1';
          $resultdp = setq($sqldp) or die($sqldp);
          $rwd = mysql_fetch_array($resultdp);
          $dep = $rwd['nmb'];

          $sqlct = 'SELECT p_categoria categoria,c_nmb nmb,SUM(pd_cantidad) total FROM pedidoscl
          INNER JOIN pedidoscld ON pedidoscl.p_id = pd_pedido
          INNER JOIN productos ON pd_producto = productos.p_id
          INNER JOIN categorias ON p_categoria = c_id
          WHERE pedidoscl.p_cliente = "'.$this->model->cliente.'"
          GROUP BY categoria ORDER BY total DESC LIMIT 0,1';
          $resultct = setq($sqlct) or die($sqlct);
          $rwc = mysql_fetch_array($resultct);
          $cat = $rwc['nmb'];

          $sqlp = 'SELECT * FROM pedidoscl WHERE p_cliente = "'.$this->model->cliente.'" AND p_estatus IN ("A","R","F","X")
          ORDER BY p_fechaapli DESC LIMIT 0,5';
          $resultp = setq($sqlp) or die($sqlp);

          $sqlpv = 'SELECT p_nmb nmb,SUM(pd_cantidad) cantidad FROM pedidoscl
          INNER JOIN pedidoscld ON p_id = pd_pedido
          INNER JOIN productos ON productos.p_id = pd_producto
          WHERE p_cliente = "'.$this->model->cliente.'" AND pedidoscl.p_estatus IN ("A","R","F","X")
          GROUP BY pd_producto,pd_color,pd_talla
          ORDER BY cantidad DESC LIMIT 0,3';
          $resultpv = setq($sqlpv) or die($sqlpv);

          $sqlw = 'SELECT * FROM deseos
          INNER JOIN productos ON p_id = d_producto
          WHERE d_cliente = "'.$this->model->cliente.'" LIMIT 0,5';
          $resultw = setq($sqlw) or die($sqlw);

          echo '<table class="lista" width="100%">
          <tr><th colspan="2">Departamento mas Comprado</th></tr>
          <tr><td colspan="2">'.$dep.'</td></tr>
          <tr><th colspan="2">Categoria mas Comprado</th></tr>
          <tr><td colspan="2">'.$cat.'</td></tr>
          <tr><th colspan="2">Fecha de Registro</th></tr>
          <tr><td colspan="2">'.busca($this->model->cliente,'clientes','c_id','DATE(c_falta)').'</td></tr>
          <tr><th colspan="2">Cumpleaños</th></tr>
          <tr><td colspan="2">'.busca($this->model->cliente,'clientes','c_id','c_fnacimiento').'</td></tr>
          <tr><th colspan="2">Ultimas Compras</th></tr>
          <tr><th>Fecha</th><th>Total</th></tr>';
          while($rwp = mysql_fetch_array($resultp)){
            echo '<tr><td>'.$rwp['p_fechaapli'].'</td><td>$ '.number_format($rwp['p_total'],2).'</td></tr>';
          }
          echo '<tr><th colspan="2">Productos mas Comprados</th></tr>
          <tr><th>Producto</th><th>Cantidad</th></tr>';
          while($rwpv = mysql_fetch_array($resultpv)){
            echo '<tr><td>'.$rwpv['nmb'].'</td><td>'.number_format($rwpv['cantidad'],2).'</td></tr>';
          }
          echo '<tr><th colspan="2">Lista de Deseos</th></tr>
          <tr><th>Producto</th><th>Pesonas Interesadas</th></tr>';
          while($rww = mysql_fetch_array($resultw)){
            echo '<tr><td>'.$rww['p_nmb'].'</td><td>'.busca($rww['p_id'],'deseos','d_producto','COUNT(*)').'</td></tr>';
          }
          echo '</td></tr></table>';
          include('db.php');
        */
    }
    function browserpago(){
      ?><script>
        function seleccionar_todo(){
          for (i=0;i<document.cpago.elements.length;i++)
            if(document.cpago.elements[i].type == "checkbox")
              document.cpago.elements[i].checked = 1;
        }
        function deseleccionar_todo(){
          for (i=0;i<document.cpago.elements.length;i++)
            if(document.cpago.elements[i].type == "checkbox")
              document.cpago.elements[i].checked = 0;
        }
        function checkautoriza(accion){
          if(accion == 1){
            var conf = confirm("¿Deseas autorizar los pagos seleccionados?");
            if(conf == true){
              document.forms.cpago.action= "?modulo=pedidosclw&accion=confirmpagom";
              $("#subpagos").click();
            }
          }
          else if(accion == 2){
            var conf = confirm("¿Deseas denegar los pagos seleccionados?");
            if(conf == true){
              document.forms.cpago.action= "?modulo=pedidosclw&accion=cancelpagom";
              $("#subpagos").click();
            }
          }
        }
      </script><?php
      echo '<div class="div2" id="bboton01">
      </div></div>';
      $sqlc = 'SELECT DISTINCT r_cuenta FROM reportepago WHERE r_estatus = "N"';
      $resultc = setq($sqlc) or die($sqlc);
      echo '<center>
        <form action="" method="post">';
          /*
            echo '<input type=button value=" " onClick="javascript:seleccionar_todo();" id="marcartodos" class="botont">
            <input type=button value=" " onClick="javascript:deseleccionar_todo();" id="desmarcartodos" class="botont">';
          */
          echo '&nbsp;&nbsp;&nbsp;Cuenta:
          <select name="cuenta" onchange="this.form.submit();">
            <option value="">Todas</option>';
            while($rw = mysql_fetch_array($resultc)){
              $selc = '';
              if($_POST['cuenta'] == $rw['r_cuenta'])
                $selc = 'selected';
              echo '<option value="'.$rw['r_cuenta'].'" '.$selc.'>'.busca($rw['r_cuenta'],'cuentasfp','c_id','c_udig').'</option> ';
            }
          echo '</select>
        </form>';
        echo'<form name="cpago" action="#" method="post">
          <table class="lista" width="95%">
            <thead>';
              echo '<tr>
                <th></th>
                <th>Pedido</th>
                <th>Cliente</th>
                <th>Monto de Pedido</th>
                <th>Cuenta</th>
                <th>Fecha y Hora</th>
                <th>Cantidad</th>
                <th>Referencia</th>
                <th>Comprobante</th>
                <th>Comentarios</th>
              </tr>
            </thead>';
            echo '<tbody>';
              while($row = mysql_fetch_array($this->model->resultrp)){
                if(isset($_GET['idped']) && $_GET['idped'] == $row['r_pedido']) $colbck = "#003366"; else $colbck = "";
                echo '<tr style="background:'.$colbck.';">';
                  echo '<td><input type="checkbox" name="aut'.$row['r_id'].'"/></td>';
                  echo '<td>'.$row['r_pedido'].'</td>';
                  echo '<td>'.busca(busca($row['r_pedido'],'pedidoscl','p_id','p_cliente'),'clientes','c_id','c_nmb').'</td>';
                  echo '<td>$ '.number_format(totalpedido($row['r_pedido'],busca($row['r_pedido'],'pedidoscl','p_id','p_cliente')),4).'</td>';
                  echo '<td>'.busca($row['r_cuenta'],'cuentasfp','c_id','c_udig').'</td>';
                  echo '<td>'.$row['r_fecha'].' - '.$row['r_hora'].'</td>';
                  echo '<td>$ '.number_format($row['r_monto'],4).'</td>';
                  echo '<td>'.$row['r_referencia'].'</td>';
                  echo '<td><a href="https://jdsuite.mx/adjpagos/'.busca($row['r_adj'],'adjreportepago','a_id','a_adjunto').'.'.busca($row['r_adj'],'adjreportepago','a_id','a_ext').'" target="_blank">'.busca($row['r_adj'],'adjreportepago','a_id','a_adjunto').'.'.busca($row['r_adj'],'adjreportepago','a_id','a_ext').'</a></td>';
                  echo '<td>'.$row['r_observacion'].'</td>';
                echo '</tr>';
              }
              /*
                echo '
                <tr><td colspan="10">
                <input type="button" value="" id="autorizar" class="botont" onclick="checkautoriza(1)"/>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <input type="button" value="" id="cancelar" class="botont" onclick="checkautoriza(2)"/>
                <input type="submit" id="subpagos" value="" style="display: none"/>
                </td></tr>';
              */
            echo '</tbody>
          </table>
        </form>';
    }
    function browseexp(){
      ?><script language="JavaScript">
        function checkSubmitcancelar() {
          document.getElementById("cancelar").value = "JD";
          document.getElementById("cancelar").disabled = true;
          return true;
        }
        function seleccionar_todo(){
          for (i=0;i<document.cancel.elements.length;i++)
            if(document.cancel.elements[i].type == "checkbox")
              document.cancel.elements[i].checked = 1;
        }
        function deseleccionar_todo(){
          for (i=0;i<document.cancel.elements.length;i++)
            if(document.cancel.elements[i].type == "checkbox")
              document.cancel.elements[i].checked = 0;
        }
        function confirmcancel(){
          var conf = confirm("Desea Cancelar los pedidos seleccionados y liberar inventario?");
          if(conf == true){
            document.getElementById("cancelar").value = "JD";
            document.getElementById("cancelar").disabled = true;
            document.cancel.submit();
          }
        }
      </script><?php
      $empresas = busca('1','configuracionesp','c_id','c_verunaempresa');
      $aest = array("N"=>"Pedido Nuevo","P" => "Confirmación de Inventario","A"=>"Proceso de Pago","F"=>"Pedido Finalizado","C"=>"Pedido Cancelado","R"=>"Pago Reportado <br> (Requiere Verificacion)");
      $acolor = array("N" => "#FFFF33","P" => "#FFCC33","F" => "#0066CC","A" => "#0066CC","C" => "#F50000") ;
      echo '<div class="div2" id="bboton01">
        <input type="button" value=" " id="cancelar" class="botonb" onclick="confirmcancel();">
      </div></div>';
      echo '<center>
        <form autocomplete="off" name="cancel" method="post" action="?modulo=pedidosclw&accion=cancelpexp" onsubmit="return checkSubmitcancelar();">
          <input type="button" value="" id="marcartodos" class="botont" onclick="seleccionar_todo();"/>
          &nbsp;<input type="button" value="" id="desmarcartodos" class="botont" onclick="deseleccionar_todo();"/>
          <table class="lista" width="95%">
            <thead>';
              echo '<tr>
                <th style="width:1%;">Cancelar</th>
                <th>Id</th>';
                if($empresas == "1" )
                  echo '<th>Empresa</th>';
                echo '<th>Cliente</th>
                <th>Fecha</th>
                <th>Observaciones</th>
                <th>Estatus</th>
              </tr>
            </thead>';
            echo '<tbody>';
              $link = NULL;
              while($row = mysql_fetch_array($this->model->result)){
                $colorb = "#FF0000";
                $link = '';
                echo '<tr>';
                  echo '<th><input type="checkbox" name="cancel'.$row['p_id'].'"/></th>';
                  echo '<th><a href="?modulo=pedidosclw&accion=show&id='.$row['p_id'].'" onclick="this.onclick = function(){return false;}">'.$row['p_id'].'</a></th>';
                  if($empresas == "1")
                    echo '<td>'.busca($row['p_empresa'],'configuracion','c_consecutivo','c_id').'</td>';
                  echo '<td>'.busca($row['p_cliente'],'clientes','c_id','c_nmb').' '.busca($row['p_cliente'],'clientes','c_id','c_apellidos').'</td>';
                  echo '<td>'.$row['p_fechaapli'].'</td>';
                  echo '<td>'.$row['p_observaciones'].'</td>';
                  echo '<td style="background:'.$this->estc[$row['p_estatus']].'">'.$this->estp[$row['p_estatus']].'</td>';
                echo '</tr>';
              }
            echo '</tbody>';
          echo '</table>
        </form>
      </center>';
    }
    function guias($pedido){
      ?><script language="JavaScript">
        function checksubmit() {
          document.getElementById("guardar").value = "JD";
          document.getElementById("guardar").disabled = true;
          return true;
        }
      </script><?php
      echo '<div class="row page-title-actions">
        <div class="col-12 col-md-6">';
          echo '<a href="?modulo=pedidosclw&accion=show&id='.$pedido.'" accesskey="">
            <button type="button" class="btn btn-warning mr-2 ml-1"><i class="fa fa-arrow-left"></i> Atras </button>
          </a>
        </div>
      </div>';
      echo '<form autocomplete="off" name="cancel" method="post" action="?modulo=pedidosclw&accion=updateguias&id='.$pedido.'" onsubmit="return checksubmit();">
        <div class="table table-responsive">
          <table class="table table-hover table-striped">
            <thead class="thead bg-blue bg-darken-3 text-white pt-1 pb-1">';
              echo '<tr>
                <th>No</th>';
                echo '<th>Tipo</th>
                <th>Productos</th>
                <th>Peso</th>
                <th>Guia</th>
              </tr>
            </thead>';
            echo '<tbody>';
              $paq = busca($pedido,'pedidoscl','p_id','p_paqueteria');
              while($row = $this->model->result->fetch_array()){
                echo '<tr>';
                  echo '<th>'.$row['gp_paquete'].'</th>';
                  echo '<td>'.busca(busca($row['gp_paquete'],'productos_empaque','pe_nopaquete','pe_tipop'),'tipopaquetes','tp_paqueteriad = "'.$paq.'" AND tp_id','tp_nmb').' '.busca($row['p_cliente'],'clientes','c_id','c_apellidos').'</td>';
                  echo '<td>'.$row['gp_nproductos'].'</td>';
                  echo '<td>'.$row['gp_pesov'].'</td>';
                  echo '<td><input type="text" name="guia'.$row['gp_id'].'" value="'.$row['gp_guia'].'" required/></td>';
                echo '</tr>';
              }
              echo '<tr><td colspan="5"><input type="submit" value="" id="guardar" class="botont"/></td></tr>';
            echo '</tbody>';
          echo '</table>
        </div>
      </form>';
    }
    /************PEDIDOS ACADEMY*****************************************************************************/
    function browseacademy($fini,$ffin,$estatus){ 
      if($estatus == "R") $selr = 'selected';
      elseif($estatus == "A") $sela = 'selected';
      elseif($estatus == "C") $selc = 'selected';
      elseif($estatus == "N") $seln = 'selected';
      else $selt = 'selected';
      echo '<div class="row page-title-actions">
        <div class="form-inline mb-1">
          <div class="btn-group" role="group">
            <a type="button" class="btn btn-success text-white" href="?modulo=pedidosclw&accion=index&w=1">
              <i class="icon-world"></i> JD SHOP
              <span style="height:2.7px;display:block"></span>
            </a>
            <a type="button" class="btn btn-info btn-darken-2 active text-white" href="?modulo=pedidosclw&accion=academy">
              <i class="icon-graduation-cap"></i> Academy JD SHOP
              <span style="height:2.7px;display:block"></span>
            </a>
          </div>
          <button accesskey="L" id="filtrar" type="button" class="btn btn-info"  data-toggle="collapse" data-target="#filter-panel">
            <span class="glyphicon glyphicon-cog"></span><i class="fa fa-search5"></i> Filtrar
            <span class="tag tag-pill tag-danger" bis_skin_checked="1">'.$this->model->resultrcc->num_rows.'</span>
          </button>
        </div>';

        echo'<form autocomplete="off" action="?modulo=pedidosclw&accion=academy" method="post" onsubmit="return checkSubmitenviar();" class="row form-inline mb-1" id="filtro">
          <div id="filter-panel" class="collapse filter-panel col-md-12">
            <div class="mb-5">
              <label class="mr-sm-2">Desde:</label>
              <input type="date" id="fini" name="fini" value="'.$fini.'" class="form-control">
            </div>
            <div class="mb-5">
              <label class="mr-sm-2">Hasta:</label>
              <input type="date" id="ffin" name="ffin" value="'.$ffin.'" class="form-control">
            </div>';
            echo'&nbsp;<div class="mb-5">
              <label class="mr-sm-2">Estatus:</label>';
              echo '<select name="estatus" class="form-control">
                <option value="" '.$selt.'>Todos</option>
                <option value="R" '.$selr.'>Reportados</option>
                <option value="N" '.$seln.'>En Carrito de Compra</option>
                <option value="A" '.$sela.'>Aprobados</option>
                <option value="C" '.$selc.'>Cancelados</option>';
              echo '</select>';
            echo '</div>';
            echo'<div class="mb-5 ml-1">
              <label style="color:transparent">Filtrar:</label><br>
              <button type="submit" class="btn btn-info"><i class="icon-filter2"></i> Filtrar</button>
            </div>
            <div class="mb-5">
              <label style="color:transparent">Limpiar:</label><br>
              <a href="?modulo=pedidosclw&accion=academy" class="btn btn-warning"><i class="fa fa-times"></i> Limpiar</a>
            </div>
          </div>
        </form>
      </div>';

      echo '<div class="main-card mb-3 card">';
        echo'<div class="card-body row">
          <div class="table table-responsive">
            <table class="table table-striped table-hover ">
              <thead class="bg-darken-2 bg-light-blue text-white">
                <tr>
                  <th>Id</th>
                  <th>Cliente</th>
                  <th>Fecha de pedido</th>
                  <th>Fecha de reporte</th>
                  <th>Total</th>
                  <th>Estatus</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>';
                while($rowrp = $this->model->resultrc->fetch_array()){
                  echo'<tr>
                    <th>'.$rowrp['up_id'].'</th>
                    <td nowrap>'.$rowrp['u_nmb'].' '.$rowrp['u_apellidos'].'</td>
                    <td nowrap>'.$rowrp['up_fecha'].'</td>
                    <td nowrap>'.buscaAcademy($rowrp['up_id'],'reportepago','r_pedido','r_fecha').' '.buscaAcademy($rowrp['up_id'],'reportepago','r_pedido','r_hora').'</td>
                    <td nowrap class="number-align">$'.$rowrp['c_costo'].'</td>
                    <td nowrap style="background-color:'.$this->model->estcac[$rowrp['up_estatus']].'">'.$this->model->estpac[$rowrp['up_estatus']].'</td>
                    <td nowrap>';
                      $txtbtn = 'Ver pedido'; 
                      $stbtn = 'btn-primary';
                      echo'<a href="?modulo=pedidosclw&accion=showacademy&id='.$rowrp['up_id'].'">
                        <button class="btn '.$stbtn.'" type="button">
                          <i class="icon-list-alt"></i> '.$txtbtn.'
                        </button>
                      </a>';
                    echo'</td>
                  </tr>';
                }
              echo'</tbody>
            </table>
          </div>
        </div>';
        echo '';
      echo'</div>';
    }
    function showacademy(){
      ?><script>
        function dictamen(estatus){
          document.getElementById("estatus").value = estatus;

          document.getElementById("formdictamen").submit();
        }
      </script><?php
      $sqlreporte = 'SELECT r_remision,r_pedido,r_observacion,r_adj,r_fecha,r_hora,r_id
                      FROM reportepago WHERE r_pedido = "'.$_GET['id'].'"';
      $resultped = setqAcademy($sqlreporte);
      $rowpedido = $resultped->fetch_array();

      $this->model->idreporte = $rowpedido['r_id'];
      $this->model->remision = $rowpedido['r_remision'];
      $this->model->pedido = $rowpedido['r_pedido'];
      $this->model->observacion = $rowpedido['r_observacion'];
      $this->model->adjunto = $rowpedido['r_adj'];
      $this->model->fechareporte = $rowpedido['r_fecha'];
      $this->model->horareporte = $rowpedido['r_hora'];

      echo '<div class="row page-title-actions">';
        //Sección: E2 Encabezado - Filtros
        //$rowinf = $this->model->result->fetch_array();
        echo '<a class="btn btn-warning" href="?modulo=pedidosclw&accion=academy"><i class="fa fa-arrow-left"></i> Atras</a>';
        if($this->model->estatus == "R"){
          echo'&nbsp;<button type="button" class="btn btn-success" data-bs-toggle="modal" data-target="#dictamen">
            <i class="fa fa-check"></i> Dictamen
          </button>';
        }
      echo'</div>';
      //Modal - APROBAR O DENEGAR PAGO RECIBIDO
      echo '<div class="modal fade text-xs-left" id="dictamen" tabindex="-1" role="dialog" aria-labelledby="myModalLabel33" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
              <label class="modal-title text-text-bold-600" id="myModalLabel33">Reporte final de pago</label>
            </div>
            <form method="post" action="?modulo=pedidosclw&accion=finalizaracademy&id='.$this->model->idreporte.'" autocomplete="off" id="formdictamen">
              <input type="hidden" name="estatus" id="estatus">
              <input type="hidden" name="cliente" value="'.$this->model->cliente.'">
              <input type="hidden" name="pedido" value="'.$this->model->pedido.'">
              <div class="modal-body row">
                <div class="col-md-12">
                  <div class="mb-5">  
                    <label>Observaciones:*</label>
                    <textarea id="comentarios" name="comentarios" placeholder="Comentarios a enviar del dictamen por correo electronico" rows="4" class="form-control"></textarea>
                  </div>
                  <div id="suggestions-block"></div>
                </div>
              </div>
              <div class="modal-footer p-2">
                <button type="button" onclick="dictamen('."'A'".')" class="btn btn-success"><i class="fa fa-check"></i> Aprobar</button>
                <button type="button" onclick="dictamen('."'C'".')" class="btn btn-danger"><i class="fa fa-times"></i> Denegar</button>
                <button type="button" class="btn btn-warning" data-dismiss="modal" aria-label="Close"><i class="icon-alert-circled"></i> Cancelar</button>
              </div>
            </form>
          </div>
        </div>
      </div>';
      echo'<div class="row mt-2">
        <div class="table">
          <div class="table-responsive">
            <center>
              <table class="table table-hover table-striped">
                <thead class="thead bg-blue bg-darken-3 text-white">
                  <tr>
                    <th colspan="6">Pedido de  "'.$this->model->nmb.' '.$this->model->apellidos.'" '.$this->model->correo.'</th>
                  </tr>
                  <tr>
                    <th colspan="3">Curso</th>
                    <th>Fecha de pedido</th>
                    <th colspan="2">Estatus</th>
                  </tr>
                </thead>
                <tbody>';
                  echo '<tr>
                    <td colspan="3">'.$this->model->curso.'</td>
                    <td nowrap>'.$this->model->fechapedido.'</td>
                    <td nowrap colspan="2" style="background:'.$this->model->estcac[$this->model->estatus].'">'.$this->model->estpac[$this->model->estatus].'</td>
                  </tr>
                  <tr style="height: 20px;"></tr>
                  <tr class="thead bg-blue bg-darken-3 text-white">
                    <th colspan="6">Información adicional</th>
                  </tr>';
                  echo'<tr>
                    <th nowrap><b>FORMA DE PAGO</b></th>
                    <th nowrap><b>FECHA DE REPORTE</b></th>
                    <th><b>ADJUNTO DE REPORTE</b></th>
                    <th colspan="2"><b>OBSERVACIONES</b></th>
                  </tr>';
                  echo'<tr>
                    <td nowrap>'.$this->model->formapago.'</td>
                    <td nowrap>'.$this->model->fechareporte.' '.$this->model->horareporte.'</td>
                    <td>';
                      if($this->model->adjunto)
                        echo'<a target="_BLANK" href="../../jdshop.mx/escuelavirtual/'.$this->model->adjunto.'">
                          <button type="button" class="btn btn-secondary"><i class="icon-attachment"></i> '.$this->model->adjunto.'</button>
                        </a>';
                      else echo'<b>Sin adjunto</b>';
                    echo'</td>
                    <td colspan="2">'.$this->model->observacion.'</td>
                  </tr>';
                  $rowspan = '';
                  $colspan = '';
                  if($this->model->factura == "1") {$rowspan = 'rowspan="2"'; $colspan = 'colspan="4"';}
                  echo'<tr>
                    <th '.$colspan.' nowrap><b>FACTURACIÓN</b></th>';
                    if($this->model->remision)
                      echo'<th '.$rowspan.' nowrap>
                        <b>Remisión</b> &nbsp;&nbsp;
                        <a href="?modulo=remisiones&accion=show&id='.$this->model->remision.'">
                          <button type="button" class="btn btn-sm btn-primary">
                            <i class="fa fa-eye"></i>
                          </button>
                        </a>
                      </th>';
                  echo'</tr>';
                  if($this->model->remision){
                    $estrem = busca($this->model->remision,'remisiones','r_id','r_estatus');
                    if($estrem == "A"){
                      $remision = '<td nowrap style="text-align: center;'.$this->model->backestatusc[busca($this->model->remision,'cxcobrar','cx_tipo = "R" AND cx_referencia','cx_estatus')].'">
                        '.$this->model->txtestatusc[busca($this->model->remision,'cxcobrar','cx_tipo = "R" AND cx_referencia','cx_estatus')].'
                      </td>';
                    }else{
                      $remision = '<td nowrap style="text-align: center;" class="'.$this->model->backestatus[$estrem].'">'.$this->model->txtestatus[$estrem].'</td>';
                    }
                  }else $remision = '';
                  echo'<tr>';
                    if($this->model->factura == "1"){
                      if($this->model->numi || $this->model->numi != 0) $ni = ', #'.$this->model->numi;
                      $dir = $this->model->calle.', #'.$this->model->nume.$ni.', '.$this->model->colonia.', '.$this->model->cp.', '.$this->model->municipio.', '.buscaAcademy($this->model->estado,'estado','e_id','e_nmb').', '.$this->model->pais;
                      echo'
                        <th style="text-align: center;">Razón social</th>
                        <th style="text-align: center;">RFC</th>
                        <th style="text-align: center;">Regimen fiscal</th>
                        <th style="text-align: center;">Dirección</th>
                      </tr>
                      <tr>
                        <td nowrap style="text-align: center;">'.$this->model->razonsocial.'</td>
                        <td nowrap style="text-align: center;">'.$this->model->rfc.'</td>
                        <td style="text-align: center;">'.$this->model->regimen.' - '.buscaAcademy($this->model->regimen,'cfdi_regimenfiscal','cr_regimen','cr_descripcion').'</td>
                        <td style="text-align: center;">'.$dir.'</td>
                        '.$remision.'
                      </tr>';
                    }else{
                      echo'<td '.$colspan.'>No</td>
                      '.$remision.'';
                    }
                  echo'</tr>';
                echo'</tbody>
              </table>
            </center>
          </div>
        </div>
      </div>';
      if($this->model->remision){
        echo '<div class="row mt-2">
          <div class="table">
            <div class="table-responsive">
              <center>
                <table class="table table-hover table-striped">
                  <thead class="thead bg-blue bg-darken-3 text-white">
                    <tr>
                      <th colspan="6">Detalle del pedido</th>
                    </tr>
                    <tr>
                      <th>Modelo</th>
                      <th colspan="2">Articulo</th>
                      <th>Precio</th>
                      <th>Cantidad</th>
                      <th>Importe</th>
                    </tr>
                  </thead>
                  <tbody>';
                    $sqlrem = 'SELECT * FROM remisionesd WHERE rd_remision = "'.$this->model->remision.'"';
                    $resultrem = setq($sqlrem);
                    $sub = 0;
                    $total = 0;
                    $descuento = 0;
                    while($rowrem = $resultrem->fetch_array()){
                      if(!busca($rowrem['rd_articulo'],'articulos','a_id','a_modelo')) $modelo = '-';
                      else $modelo = busca($rowrem['rd_articulo'],'articulos','a_id','a_modelo');
                      $importe = ($rowrem['rd_precio'] * $rowrem['rd_cantidad']);
                      echo'<tr>
                        <td>'.$modelo.'</td>
                        <td colspan="2">'.$rowrem['rd_nmbarticulo'].'</td>
                        <td>$'.number_format($rowrem['rd_precio'],2).'</td>
                        <td>'.number_format($rowrem['rd_cantidad'],2).'</td>
                        <td>$'.number_format($importe,2).'</td>
                      </tr>';
                    }
                  echo'</tbody>
                  <tfoot>
                    <tr>
                      <th colspan="4" style="text-align:right;">Subtotal</th>
                      <th colspan="2" style="text-align:right;">$ '.busca($this->model->remision,'remisiones','r_id','r_subtotal').'</th>
                    </tr>
                    <tr>
                      <th colspan="4" style="text-align:right;">Descuento</th>
                      <th colspan="2" style="text-align:right;">$ '.busca($this->model->remision,'remisiones','r_id','r_mdescuento').'</th>
                    </tr>
                    <tr>
                      <th colspan="4" style="text-align:right;">Total</th>
                      <th colspan="2" style="text-align:right;">$ '.busca($this->model->remision,'remisiones','r_id','r_total').'</th>
                    </tr>
                  </tfoot>
                </table>
              </center>
            </div>
          </div>
        </div>';
      }
    }
  }

  class modelpedidosclwDetalle{
    function resultprod($pedido){
      $sqlart = 'SELECT * FROM pedidoscld INNER JOIN articulos ON pd_producto = a_id WHERE pd_pedido = "'.$pedido.'" ';
      $this->resultart = setq($sqlart) or die($sqlart);
    }
    function insertdetalle($pedido,$id,$producto,$cantidad,$color,$talla,$precio){
      $cliente = busca($pedido,'pedidoscl','p_id','p_cliente');
      $esquema = busca($cliente,'clientes','c_id','c_esquema');
      /*
        $precio = precioesq($esquema,$producto,$talla);
        $newprecio = preciocam($producto,$precio);
        if($newprecio != $precio){
          $precio = $newprecio;
        }
      */  
      $sql = 'INSERT INTO pedidoscld SET
              pd_pedido = "'.$pedido.'",
              pd_id = "'.$id.'",
              pd_producto = "'.$producto.'",
              pd_color = "'.$color.'",
              pd_talla = "'.$talla.'",
              pd_precio = "'.$precio.'",
              pd_cantidad = "'.$cantidad.'"';
      setq($sql) or die($sql);

      $sqlv = 'SELECT * FROM pedidoscld WHERE pd_pedido = "'.$pedido.'" ORDER BY pd_id ASC';
      $resultv = setq($sqlv) or die($sqlv);
      $pvol = 0;
      $pvolc = 0;
      while($rowv = mysql_fetch_array($resultv)){
        if(busca($rowv['pd_producto'],'productos','p_id','p_envio') != "2")
          $pvolc += ($rowv['pd_cantidad'] * busca($rowv['pd_producto'],'productos_tallasc','pt_talla = "'.$rowv['pd_talla'].'" AND pt_color = "'.$rowv['pd_color'].'" AND pt_producto','pt_pesov'));
        $pvol += ($rowv['pd_cantidad'] * busca($rowv['pd_producto'],'productos_tallasc','pt_talla = "'.$rowv['pd_talla'].'" AND pt_color = "'.$rowv['pd_color'].'" AND pt_producto','pt_pesov'));
      }
      $np = ceil($pvol / 100);
      $npc = ceil($pvolc / 100);
      $sqlnp = 'UPDATE pedidoscl SET p_nguias = "'.$np.'",p_nguiascobrar = "'.$npc.'" WHERE p_id = "'.$pedido.'"';
      setq($sqlnp) or die($sqlnp);
    }
    function updatedetalle($pedido,$id,$producto,$cantidad,$color,$talla,$origen = NULL){
      include('dbw.php');
      if($origen)
        $sql2 = 'pd_cantidad = pd_cantidad + '.$cantidad.'';
      else
        $sql2 = 'pd_cantidad = "'.$cantidad.'"  ';
      $sql = 'UPDATE pedidoscld SET
              '.$sql2.'
              WHERE pd_pedido = "'.$pedido.'"
              AND pd_id = "'.$id.'"
              AND pd_producto = "'.$producto.'"';
      setq($sql) or die($sql);

      $sqlv = 'SELECT * FROM pedidoscld WHERE pd_pedido = "'.$pedido.'" ORDER BY pd_id ASC';
      $resultv = setq($sqlv) or die($sqlv);
      $pvol = 0;
      $pvolc = 0;
      while($rowv = mysql_fetch_array($resultv)){
        if(busca($rowv['pd_producto'],'productos','p_id','p_envio') != "2")
          $pvolc += ($rowv['pd_cantidad'] * busca($rowv['pd_producto'],'productos_tallasc','pt_talla = "'.$rowv['pd_talla'].'" AND pt_color = "'.$rowv['pd_color'].'" AND pt_producto','pt_pesov'));
        $pvol += ($rowv['pd_cantidad'] * busca($rowv['pd_producto'],'productos_tallasc','pt_talla = "'.$rowv['pd_talla'].'" AND pt_color = "'.$rowv['pd_color'].'" AND pt_producto','pt_pesov'));
      }

      $np = ceil($pvol / 100);
      $npc = ceil($pvolc / 100);
      $sqlnp = 'UPDATE pedidoscl SET p_nguias = "'.$np.'",p_nguiascobrar = "'.$npc.'" WHERE p_id = "'.$pedido.'"';
      setq($sqlnp) or die($sqlnp);
    }
    function deleteDetalle($pedido,$idpd){
      include('dbw.php');
      $sqldel = 'DELETE FROM pedidoscld WHERE pd_pedido = "'.$pedido.'" AND pd_id = "'.$idpd.'"';
      setq($sqldel) or die($sqldel);
    }
    function deleteg($pedido,$pd_id){
      include('dbw.php');
      $sqldel = 'DELETE FROM pedidoscld WHERE pd_id = "'.$pd_id.'" AND pd_pedido = "'.$pedido.'"';
      setq($sqldel) or die($sqldel);
    }
    function resultDetalle($pedido){
      $sqlart = 'SELECT * FROM pedidoscld INNER JOIN articulos ON a_cb = pd_producto WHERE pd_pedido = "'.$pedido.'" ';
      $this->detalle = setq($sqlart) or die($sqlart);
      $this->sad = setq($sqlart) or die($sqlart);
    }
  }

  class viewpedidosclwDetalle{
    var $model;
    function __construct($model) {
      $this->modelD = $model;
    }
    function browseprod($id){
      $this->model->id = $id;
      $this->model->estatus = busca($this->model->id,'pedidoscl','p_id','p_estatus');
      echo '<table  width="100%">
        <tr>
          <td>';
            if($this->model->estatus!="C") echo '<input type="button" value="" id="marcartodos" class="botont"  onclick="seleccionar_todo();"/>';
            if($this->model->estatus!="C") echo '<input type="button" value="" id="desmarcartodos" class="botont" onclick="deseleccionar_todo()"/>';
          echo '</td>
        </tr>
      </table>';
      echo '<table  width="100%" class="lista">
        <thead>';
          echo '<tr><th colspan="7">Productos</th></tr>';
          echo '<tr>
            <th width="1%"><input type="submit" value="" id="borrar" class="botont"/></th>
            <th width="40%">Producto</th>';
            echo '<th>Marca</th>';
            if(busca('1','configuracionesp','c_id','c_vercolores') == 1)
              echo '<th>Color</th>';
            if(busca('1','configuracionesp','c_id','c_vertallas') == 1)
              echo '<th width="20%">Talla</th>';
            echo '<th width="10%">Cantidad</th>';
            echo '<th width="5%">Eliminar</th>
          </tr>
        </thead>';
        while($rowd = $this->modelD->resultart->fetch_array()){
          $cantidad = $rowd['pd_cantidad'];
          if($cantidad == NULL) $cantidad = 0;
          echo '<tr>
            <td>'; if($this->model->estatus != "C") echo '<input type="checkbox" name="check'.$rowd['pd_id'].'" id="check'.$rowd['pd_id'].'" />'; echo'</td>';
            echo '<td>'.strtoupper(descripcion($rowd['pd_producto'])).'</td>';
            echo '<td>'.strtoupper(busca(busca($rowd['pd_producto'],'articulos','a_id','a_marca'),'marcas','m_id','m_nmb')).'</td>';
            echo '<td>
              <input type="number" name="cantidad" style="text-align:right;width:100%;" min="1" step="any" max="9999999" id="cant'.$rowd['pd_id'].'" value="'.$cantidad.'" onfocus="this.select();" onChange="updatecant('.$this->model->id.','.$rowd['pd_id'].');">
              <input type="hidden" id="cb'.$rowd['pd_id'].'" value="'.$rowd['pd_producto'].'"/></td>
            <td><a href="?modulo=pedidosclw&accion=deletedetalle&id='.$this->model->id.'&idpd='.$rowd['pd_id'].'&producto='.$rowd['pd_producto'].'" onclick="this.onclick = function(){return false;}"><center><input type="button" value=" " id="borrar" class="botont"></center></a></td>';
          echo '</tr>';
        }
      echo '</table>';
    }
    function listDetalle($id){
      ?><script>
        function checkb(idb,monto,descuento,total){
          if(document.getElementById("b"+idb).checked == true){
            if((descuento+monto)<=total)
              document.getElementById("fb"+idb).submit();
            else{
              alert("Las bonificaciones seleccionadas superan el total del pedido");
              document.getElementById("b"+idb).checked = false;
            }
          }
          else{
            document.getElementById("fb"+idb).submit();
          }
        }
      </script><?php
      $this->modelD->id = $id;
      $descuento = busca($id,'bonificaciones','b_pedido','SUM(b_monto)');
      if(!$descuento)
        $descuento = 0;
      $subtotal = 0;
      $envio = 0;
      $estatus = busca($this->modelD->id,'pedidoscl','p_id','p_estatus');
      echo '<input type="hidden" name="pedido" value="'.$this->modelD->id.'">';
      echo '<div class="table table-responsive table-hover">
        <table class="table table-hover table-striped">
          <thead class="thead bg-blue bg-darken-3 text-white pt-1 pb-1">';
            echo '<tr><th colspan="6">Productos Solicitados</th></tr>';
            echo '<tr>
              <th>CB / SKU</th>
              <th width="30%">Producto</th>';
              echo '<th>Marca</th>';
              echo '<th>Precio</th>';
              echo '<th>Cantidad</th>';
              echo '<th>Importe</th>';
            echo '</tr>
          </thead>';
          $monto = 0;
          while($rowd = $this->modelD->detalle->fetch_array()){
            //$montoxd += $rowd['pd_precio'] * $rowd['pd_cantidad'];
            $subtotal += $rowd['pd_precio'] * $rowd['pd_cantidad'];
            $sel = '';
            $cantidad = $rowd['pd_cantidad'];
            echo '<tr>';
              echo '<td>'.busca($rowd['pd_producto'],'articulos','a_cb','a_modelo').'</td>';
              echo '<td><p style="text-align: right">'.strtoupper(busca($rowd['pd_producto'],'articulos','a_cb','a_nmb')).'</p></td>';
              echo '<td>'.strtoupper(busca(busca($rowd['pd_producto'],'articulos','a_cb','a_marca'),'marcas','m_id','m_nmb')).'</td>';
              echo '<td>$ '.number_format($rowd['pd_precio'],2).'</td>';
              echo '<td>'.number_format($cantidad,2).'</td>';
              echo '<td>$ '.number_format(($cantidad*$rowd['pd_precio']),2).'</td>';
              //if($estatus == "P")
              //  echo '<td><a href="?modulo=pedidosclw&accion=deleteped&id='.$id.'&idp='.$rowd['pd_id'].'"><input type="button" value="" id="borrar" class="botont"/></a></td>';
            echo '</tr>';
          }
          if(isset($_GET['fp'])){
            $paqueteria = busca($id,'pedidoscl','p_id','p_paqueteria');
            $pago = busca($id,'pedidoscl','p_id','p_fpago');
            $nguias = busca($id,'pedidoscl','p_id','p_nguias');
            $nguiascobrar = busca($id,'pedidoscl','p_id','p_nguiascobrar');
            $cliente = busca($id,'pedidoscl','p_id','p_cliente');
            $esquema = busca($cliente,'clientes','c_id','c_esquema');
            $preciog = busca($paqueteria,'esquema_paqueteria','ep_esquema = "'.$esquema.'" AND ep_paqueteria','ep_costoguia');
            $enviog = busca('1','configuracionesp','c_id','c_verenviogratis');
            $enviog = busca($esquema,'esquemas','e_id','e_enviog');
            $cajas = $nguias;
            $adfp = busca($pago,'formaspago','f_id','f_montoa');
            $porcentajea = busca($pago,'formaspago','f_id','f_porcentajea');
            if($porcentajea == 1)
              $adfp = $subtotal * ($adfp/100);
            if($enviog <= $subtotal AND $enviog > 0)
              $nguiascobrar--;
            $envio = $nguiascobrar * $preciog;
            $total = $subtotal + $envio + $adfp - $descuento;
            echo '<tr>
              <td colspan="6">';
                $sqlb = 'SELECT * FROM bonificaciones WHERE b_cliente = '.$cliente.' AND 
                          (b_estatus = "N" OR (b_estatus = "A" AND b_pedido = "'.$id.'")) ';
                $resultb = setq($sqlb) or die($sqlb);
                if(mysql_num_rows($resultb) > 0){
                  echo '<table width="100%" border="0" cellspacing="0" class="lista">';
                    echo '<tr><th colspan="3">Bonificaciones</th></tr>';
                    echo '<tr><th width="1%;"></th><th>Descripcion</th><th>Monto</th></tr>';
                    while($rowb = $resultb->fetch_array()){
                      $check = '';
                      if($rowb['b_pedido'] == $id)
                        $check = 'checked';
                      echo '<tr>';
                        echo '<td>
                          <form name="fb'.$rowb['b_id'].'" id="fb'.$rowb['b_id'].'" action="?modulo=pedidosclw&accion=updatebf&id='.$id.'&idb='.$rowb['b_id'].'" method="post">
                            <input type="checkbox" '.$check.' name="b'.$rowb['b_id'].'" id="b'.$rowb['b_id'].'" onclick="checkb('.$rowb['b_id'].','.$rowb['b_monto'].','.$descuento.','.$total.');"/>
                          </form>
                        </td>
                        <td width="70%" style="padding:5pt;">'.$rowb['b_motivo'].'</td>
                        <td width="12%" class="cnum"><span>$ '.number_format($rowb['b_monto'],2).'</span></td>
                        <input type="hidden" name="m'.$rowb['b_id'].'" id="m'.$rowb['b_id'].'" value="'.$rowb['b_monto'].'"/>
                      </tr>';
                    }
                  echo '</table>';
                }
              echo '</td>
            </tr>';
            echo '<tfoot>
              <tr><th colspan="4" style="text-align:right;">Subtotal</th><td colspan="2" style="text-align: right;">$ '.number_format($subtotal,2).'</td></tr>
              <tr><th colspan="4" style="text-align:right;">Envio</th><td colspan="2" style="text-align: right;">$ '.number_format($envio,2).'</td></tr>
              <tr><th colspan="4" style="text-align:right;">Adicional</th><td colspan="2" style="text-align: right;">$ '.number_format($adfp,2).'</td></tr>
              <tr><th colspan="4" style="text-align:right;">Descuento</th><td colspan="2" style="text-align: right;">$ '.number_format($descuento,2).'</td></tr>
              <tr><th colspan="4" style="text-align:right;">Total</th><td colspan="2" style="text-align: right;">$ '.number_format($total,2).'</td></tr>
            </tfoot>
            </table>';
          }
          else{
            if(number_format(busca($id,'pedidoscl','p_id','p_subtotal')) == 0 && $this->modelD->detalle->num_rows > 0){
              if($subtotal < 5000 ) $envio = 130; else $envio = 0;
                $total = $subtotal+$envio+$adfp-$descuento;
              echo '<tfoot>
                <tr><th colspan="4" style="text-align:right;">Subtotal</th><td colspan="2" style="text-align: right;">$ '.number_format($subtotal,2).'</td></tr>
                <tr><th colspan="4" style="text-align:right;">Envio</th><td colspan="2" style="text-align: right;">$ '.number_format($envio,2).'</td></tr>
                <tr><th colspan="4" style="text-align:right;">Adicional</th><td colspan="2" style="text-align: right;">$ '.number_format($adfp,2).'</td></tr>
                <tr><th colspan="4" style="text-align:right;">Descuento</th><td colspan="2" style="text-align: right;">$ '.number_format($descuento,2).'</td></tr>
                <tr><th colspan="4" style="text-align:right;">Total</th><td colspan="2" style="text-align: right;">$ '.number_format($total,2).'</td></tr>
              </tfoot>
              </table>';
            }else{
              echo '<tfoot>
                <tr><th colspan="4" style="text-align:right;">Subtotal</th><td colspan="2" style="text-align: right;">$ '.number_format(busca($id,'pedidoscl','p_id','p_subtotal'),2).'</td></tr>
                <tr><th colspan="4" style="text-align:right;">Envio</th><td colspan="2" style="text-align: right;">$ '.number_format(busca($id,'pedidoscl','p_id','p_envio'),2).'</td></tr>
                <tr><th colspan="4" style="text-align:right;">Adicional</th><td colspan="2" style="text-align: right;">$ '.number_format(busca($id,'pedidoscl','p_id','p_adicionalfp'),2).'</td></tr>
                <tr><th colspan="4" style="text-align:right;">Descuento</th><td colspan="2" style="text-align: right;">$ '.number_format($descuento,2).'</td></tr>
                <tr><th colspan="4" style="text-align:right;">Total</th><td colspan="2" style="text-align: right;">$ '.number_format(busca($id,'pedidoscl','p_id','p_total'),2).'</td></tr>
              </tfoot></table>';
            }
          }
    }
  }
?>