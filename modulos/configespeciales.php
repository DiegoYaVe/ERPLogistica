<?php
//ini_set('display_errors', 1);
class configespeciales
{
  var $model;
  var $view;
  function __construct()
  {
    $this->model = new modelconfigespeciales(isset($obj));
  }
  function index()
  {
    $this->model->select();
    $this->view = new viewconfigespeciales($this->model);
    $this->view->datos();
    $this->view->edit();
  }
  function update()
  {
    
    if(!isset($_POST['estatusvale'])){
      $estatusval = 0;
    } else{
      $estatusval = 1;
    }
    /* foreachdie(); */
    $_GET['id'] = 1;
    $this->model->setdata($_GET['id'], $_POST['iva'], $_POST['cupo'], $_POST['ihorario'], $_POST['fhorario'], $_POST['porcentarjet'], $_POST['retiromax'], $_POST['intervalotime'], $_POST['retiroforzoso'], $_POST['limitehorario'], '00:'.$_POST['vidaticket'].':00', $_POST['proximo'], $_POST['msjregion'], $_POST['msjpred1'], $_POST['msjpred2'], $_POST['tiempovale'], $_POST['montovale'], $_POST['cupores'], $estatusval);
    $this->model->update();
    redirect("?modulo=configespeciales&accion=index");
  }
  function insertdescuento()
  {
    $check = busca($_POST['descuento'], 'descuentos', 'd_categoria = "'.$_POST['categoria'].'" AND d_descuento', 'COUNT(*)');
    if($check){
      echo '<script>
        alert("La cantidad ya existe, intente con otra.")
      </script>';
    } else {
      $this->model->insertdescuento($_POST['descuento'], $_POST['articulos'], $_POST['categoria']);
    }
    redirect("?modulo=configespeciales&accion=index&descuento=1");
  }

  function insertimporte()
  {
    $this->model->insertimporte($_POST['importe']);
    redirect("?modulo=configespeciales&accion=index&importe=1");
  }

  function insertdgarantia() {
    $this->model->insertgarantia($_POST['dias']);
    redirect("?modulo=configespeciales&accion=index&dgarantia=1");
  }

  function updatedescuento()
  { 
    //foreachdie();
    $id=$_GET['id'];
    $check = busca($_POST['descuento'], 'descuentos', 'd_id != "'.$id.'" AND d_categoria = "'.$_POST['categoria'].'" AND d_descuento', 'COUNT(*)');
    if($check){
      echo '<script>
        alert("La cantidad ya existe, intente con otra.")
      </script>';
    } else {
      if(isset($_POST['estatus'])) $estatus = "1";
      else $estatus = "0";
      $this->model->updatedescuento($id, $_POST['descuento'], $_POST['articulos'], $estatus, $_POST['categoria']);
    }
    redirect("?modulo=configespeciales&accion=index&descuento=1");
  }
  function deletedescuento()
  {
    $this->model->deletedescuento($_GET['id']);
    redirect("?modulo=configespeciales&accion=index&descuento=1");
  }
  function insertcondicion(){
    //foreachdie();
    if(isset($_POST['obliga'])) $obliga = "1";
    else $obliga = "0";
    if(isset($_POST['estatus'])) $estatus = "A";
    else $estatus = "I";
    $this->model->insertcondicion( $_POST['nmb'], $estatus, $obliga);
    redirect("?modulo=configespeciales&accion=index&condiciones=1");
  }
  function updatecondicion(){
    //foreachdie();
    if(isset($_POST['obliga'])) $obliga = "1";
    else $obliga = "0";
    if(isset($_POST['estatus'])) $estatus = "A";
    else $estatus = "I";
    $this->model->updatecondicion($_REQUEST['id'], $_POST['nmb'], $estatus, $obliga);
    redirect("?modulo=configespeciales&accion=index&condiciones=1");
  }
  function deletecondicion(){
    $this->model->deletecondicion($_REQUEST['id']);
    redirect("?modulo=configespeciales&accion=index&condiciones=1");
  }
  function insertcategoria(){
    //foreachdie();
    if(isset($_POST['estatus'])) $estatus = "A";
    else $estatus = "I";
    $this->model->insertcategoria($_POST['nmb']);
    redirect("?modulo=configespeciales&accion=index&categoria=1");
  }
  function updatecategoria(){
    //foreachdie();
    if(isset($_POST['obliga'])) $obliga = "1";
    else $obliga = "0";
    if(isset($_POST['estatus'])) $estatus = "A";
    else $estatus = "I";
    $this->model->updatecategoria($_REQUEST['id'], $_POST['nmb'], $estatus);
    redirect("?modulo=configespeciales&accion=index&categoria=1");
  }
  function deletecategoria(){
    $this->model->deletecategoria($_REQUEST['id']);
    redirect("?modulo=configespeciales&accion=index&categoria=1");
  }

  function inserttarifa(){
    //foreachdie();
    if(isset($_POST['estatus'])) $estatus = "A";
    else $estatus = "I";
    if(isset($_POST['internacional'])) $int = "1";
    else $int = "0";
    $this->model->inserttarifa($_POST['nmb'], $_POST['costo'], $_POST['paqueteria'], $int, $estatus);
    redirect("?modulo=configespeciales&accion=index&tarifas=1");
  }
  function updatetarifa(){
    //foreachdie();
    if(isset($_POST['internacional'])) $int = "1";
    else $int = "0";
    if(isset($_POST['estatus'])) $estatus = "A";
    else $estatus = "I";
    $this->model->updatetarifa($_REQUEST['id'], $_POST['nmb'], $_POST['costo'], $_POST['paqueteria'], $int, $estatus);
    redirect("?modulo=configespeciales&accion=index&tarifas=1");
  }
  function deletetarifa(){
    $this->model->deletetarifa($_REQUEST['id']);
    redirect("?modulo=configespeciales&accion=index&tarifas=1");
  }
  function updatemeta(){
    //foreachdie();
    $this->model->updatemeta($_REQUEST['id'], $_POST['cantidad']);
    redirect("?modulo=configespeciales&accion=index&meta=1");
  }

  function setmetagrupo(){

    $this->model->setmetagrupo($_REQUEST['id'], $_POST['metagrupo'], $_POST['mes'], $_POST['anio']);
    redirect("?modulo=configespeciales&accion=index&metagrupo=1");

  }

  function insertcomision(){
    $this->model->insertcomision($_POST['exhibiciones'], $_POST['porcentaje'], $_POST['descripcion']);
    redirect("?modulo=configespeciales&accion=index&comision=1");
  }

  function updatecomision(){
    if(isset($_POST['estatus'])){
      $estatus = "A";
    } else{
      $estatus = "I";
    }
    $this->model->updatecomision($_GET['id'], $_POST['exhibiciones'], $_POST['porcentaje'], $estatus, $_POST['descripcion']);
    redirect("?modulo=configespeciales&accion=index&comision=1");
  }

  function deletecomision(){
    $this->model->deletecomision($_GET['id']);
    redirect("?modulo=configespeciales&accion=index&comision=1");
  }

  function settextoguia(){
    $this->model->settextoguia($_POST['textoguias'], $_POST['textoguias2']);
    redirect("?modulo=configespeciales&accion=index&textoguia=1");
  }

  function settextoticket(){
    $this->model->settextoticket($_POST['textotickets']);
    redirect("?modulo=configespeciales&accion=index&textoticket=1");
  }

  function dobledesc(){
    if(isset($_POST['habilitar'])){
      $desc = "1";
    } else {
      $desc = "0";
    }
    $sqlupd = 'UPDATE articulos SET a_enviogratis = "'.$desc.'"';
    setq($sqlupd);
    $sql = 'UPDATE configuracionesp SET c_dobledesc = "'.$desc.'"';
    setq($sql);
    redirect("?modulo=configespeciales&accion=index&descuento=1");
  }

  function insertcomisionventa(){
    if(isset($_POST['estatus'])){
      $estatus = "A"; 
    } else{
      $estatus = "I";
    }
    $this->model->insertcomisionventa($_POST['nmb'], $_POST['repartir'], $_POST['meta'], $estatus);
    redirect("?modulo=configespeciales&accion=index&comisionventa=1");
  }

  function updatecomisionventa(){
    if(isset($_POST['estatus'])){
      $estatus = "A"; 
    } else{
      $estatus = "I";
    }
    $this->model->updatecomisionventa($_GET['id'], $_POST['nmb'], $_POST['repartir'], $_POST['meta'], $estatus);
    redirect("?modulo=configespeciales&accion=index&comisionventa=1");
  }

  function deletecomisionventa(){
    $this->model->deletecomisionventa($_GET['id']);
    redirect("?modulo=configespeciales&accion=index&comisionventa=1");
  }

  function updateminimoventa(){
    $sql = 'UPDATE configuracionesp SET c_articulosminimos = "'.$_POST['ventaminima'].'", c_comision = "'.$_POST['comision'].'" WHERE c_id = "1"';
    setq($sql);
    redirect("?modulo=configespeciales&accion=index&comisionventa=1");
  }
}
class modelconfigespeciales
{
  function select()
  {
    $sql = 'SELECT * FROM configuracionesp WHERE c_id="1"';
    $result = setq($sql);
    $row = $result->fetch_array();
    $this->id = $row['c_id'];
    $this->iva = $row['c_iva'];
    $this->cupoarea = $row['c_cupoarea'];
    $this->ihorario = $row['c_ihorario'];
    $this->fhorario = $row['c_fhorario'];
    $this->porcentarjet = $row['c_porcentarjet'];
    $this->retiromax = $row['c_retiromax'];
    $this->intervalotime = $row['c_tiempo'];
    $this->retiroforzoso = $row['c_retiroforzoso'];
    $this->limitehorario = $row['c_limitehorario'];
    $this->vidaticket = $row['c_vidaticket'];
    $this->proximo = $row['c_proximo'];
    $this->msjregion = $row['c_msjregion'];
    $this->msjpred1 = $row['c_msjpred1'];
    $this->msjpred2 = $row['c_msjpred2'];
    $this->tiempovale = $row['c_tiempovale'];
    $this->montovale = $row['c_montovale'];
    $this->cuporeservacion = $row['c_cuporeservacion'];
    $this->estatusvale = $row['c_estatusvale'];  
    $this->articulosminimos = $row['c_articulosminimos'];  
    $this->comision = $row['c_comision'];  
    
  }
  function setdata($id, $iva, $cupoarea, $ihorario, $fhorario, $porcentarjet, $retiromax, $intervalotime, $rforzoso, $limhorario, $vidaticket, $proximo, $msjregion, $msjpred1, $msjpred2, $tiempovale, $montovale, $cuporeservacion, $estatusvale)
  {
    mb_internal_encoding("UTF-8");
    $this->id = clearvmayus($id);
    $this->iva = clearvmayus($iva);
    $this->cupoarea = clearvmayus($cupoarea);
    $this->ihorario = clearvmayus($ihorario);
    $this->fhorario = clearvminus($fhorario);
    $this->porcentarjet = clearvminus($porcentarjet);
    $this->retiromax = clearvminus($retiromax);
    $this->tiempo = clearvminus($intervalotime);
    $this->retiroforzoso = clearvminus($rforzoso);
    $this->limitehorario = clearvminus($limhorario);
    $this->vidaticket = clearvminus($vidaticket);
    $this->proximo = clearvminus($proximo);
    $this->msjregion = $msjregion;
    $this->msjpred1 = $msjpred1;
    $this->msjpred2 = $msjpred2;
    $this->tiempovale = $tiempovale;
    $this->montovale = $montovale;
    $this->cuporeservacion = $cuporeservacion;
    $this->estatusvale = $estatusvale;
    
    
  }
  function update()
  {
    $sqlemp = 'UPDATE configuracionesp SET
                c_iva = "' . $this->iva . '",
                c_cupoarea  = "' . $this->cupoarea . '",
                c_ihorario  = "' . $this->ihorario . '",
                c_fhorario  = "' . $this->fhorario . '",
                c_porcentarjet = "' . $this->porcentarjet . '",
                c_retiromax = "' . $this->retiromax . '",
                c_tiempo = "' . $this->tiempo . '",
                c_retiroforzoso = "' . $this->retiroforzoso . '",
                c_limitehorario = "' . $this->limitehorario . '",
                c_vidaticket = "' . $this->vidaticket . '",
                c_proximo = "' . $this->proximo . '",
                c_msjregion = "' . $this->msjregion . '",
                c_msjpred1 = "' . $this->msjpred1 . '",
                c_msjpred2 = "' . $this->msjpred2 . '",
                c_tiempovale = "' . $this->tiempovale . '",
                c_montovale = "' . $this->montovale . '",
                c_cuporeservacion = "' . $this->cuporeservacion . '",
                c_estatusvale = "' . $this->estatusvale . '"
                WHERE c_id = "' . $this->id . '"';
    setq($sqlemp);
  }
  function insertdescuento($cantidad, $narticulos, $categoria)
  {
    $sql = 'INSERT INTO descuentos SET
            d_descuento = "' . $cantidad . '",
            d_numeroart = "' . $narticulos . '",
            d_categoria = "' . $categoria . '",
            d_estatus = "1"';
    setq($sql);
  }
  
  function updatedescuento($id,$cantidad, $narticulos, $estatus, $categoria)
  {
    $sql = 'UPDATE descuentos SET
            d_descuento = "' . $cantidad . '",
            d_numeroart = "' . $narticulos . '",
            d_categoria = "' . $categoria . '",
            d_estatus = "' . $estatus . '"
            WHERE d_id = "'.$id.'"';
    setq($sql);
  }
  function deletedescuento($id)
  {
    $sql = 'DELETE FROM descuentos WHERE
            d_id = "' . $id . '"';
    setq($sql);
  }

  function insertimporte($cantidad)
  {
    $sql = 'UPDATE configuracionesp SET
            c_maximoenvio = "' . $cantidad . '"
            WHERE c_id = "1"';
    setq($sql);
  }

  function insertgarantia($dias)
  {
    $sql = 'UPDATE configuracionesp SET
            c_dgarantia = "' . $dias . '"
            WHERE c_id = "1"';
    setq($sql);
  }

  function insertregion($nmb)
  {
    $sql = 'INSERT INTO regiones SET
            r_nmb = "' . $nmb . '"
            ';
    setq($sql);
  }
  function updateregion($id,$nmb)
  {
    $sql = 'UPDATE regiones SET
            r_nmb = "' . $nmb . '"
            WHERE r_id = "'.$id.'"';
    setq($sql);
  }
  function deleteregion($id)
  {
    $sql = 'DELETE FROM regiones WHERE
            r_id = "' . $id . '"';
    setq($sql);
  }

  function updatehorarios($dia, $entrada, $salida, $scomida, $ecomida){
    $sql='UPDATE horarios SET h_inicio = "'.$entrada.'", h_fin="'.$salida.'", h_scomida = "'.$scomida.'", h_ecomida="'.$ecomida.'" WHERE h_dia = "'.$dia.'"';
    setq($sql);
  }

  function inserthorarios($dia, $entrada, $salida, $scomida, $ecomida){
    $sql='INSERT INTO horarios SET h_inicio = "'.$entrada.'", h_fin="'.$salida.'", h_scomida = "'.$scomida.'", h_ecomida="'.$ecomida.'", h_dia = "'.$dia.'"';
    setq($sql);
  }

  function updatepresupuesto($mes, $importe) {
    $sql = 'UPDATE pv_presupuesto SET pp_importe = "'.$importe.'" WHERE pp_year = "'.date('Y').'" AND pp_mes = "'.$mes.'"';
    setq($sql);
  }
  function insertpresupuesto($mes, $importe) {
    $sql = 'INSERT INTO pv_presupuesto SET pp_importe = "'.$importe.'", pp_year = "'.date('Y').'", pp_mes = "'.$mes.'"';
    setq($sql);
  }
  function insertcondicion($nmb, $estatus, $obligatorio){
    $sql = 'INSERT INTO condicionesc SET c_nmb = "'.$nmb.'",
                                        c_estatus = "'.$estatus.'",
                                        c_obligatorio = "'.$obligatorio.'"';
    setq($sql);                                    
  }

  function updatecondicion($id, $nmb, $estatus, $obligatorio){
    $sql = 'UPDATE condicionesc SET c_nmb = "'.$nmb.'",
                c_estatus = "'.$estatus.'",
                c_obligatorio = "'.$obligatorio.'" WHERE c_id = "'.$id.'"';
    setq($sql);     
  } 
  function deletecondicion($id){
    $sql = 'DELETE FROM condicionesc WHERE c_id = "'.$id.'"';
    setq($sql);  
  }
  function insertcategoria($nmb){
    $sql = 'INSERT INTO catalogo_perdidos SET cp_nmb = "'.$nmb.'",
                                        cp_estatus = "A"';
    setq($sql);                                    
  }

  function updatecategoria($id, $nmb, $estatus){
    $sql = 'UPDATE catalogo_perdidos SET cp_nmb = "'.$nmb.'",
                cp_estatus = "'.$estatus.'" WHERE cp_id = "'.$id.'"';
    setq($sql);     
  } 
  function deletecategoria($id){
    $sql = 'DELETE FROM catalogo_perdidos WHERE cp_id = "'.$id.'"';
    setq($sql);  
  }
  function inserttarifa($nmb, $costo, $paqueteria, $inter, $estatus){
    $sql = 'INSERT INTO tarifas_envios SET te_nmb = "'.$nmb.'",
                                          te_costo = "'.$costo.'",
                                          te_paqueteria = "'.$paqueteria.'",
                                          te_internacional = "'.$inter.'",
                                        te_estatus = "'.$estatus.'"';
    setq($sql);                                    
  }

  function updatetarifa($id, $nmb, $costo,$paqueteria, $inter, $estatus){
    $sql = 'UPDATE tarifas_envios SET te_nmb = "'.$nmb.'",
                                      te_costo = "'.$costo.'",
                                      te_paqueteria = "'.$paqueteria.'",
                                      te_internacional = "'.$inter.'",
                                      te_estatus = "'.$estatus.'" 
                                      WHERE te_id = "'.$id.'"';
    setq($sql);     
  } 
  function deletetarifa($id){
    $sql = 'DELETE FROM tarifas_envios WHERE te_id = "'.$id.'"';
    setq($sql);  
  }
  function updatemeta($id, $cantidad){
    $sql = 'UPDATE usuarios_orden SET uo_metamensual = "'.$cantidad.'"
                                      WHERE uo_uid = "'.$id.'"';
    setq($sql);
  }

  function setmetagrupo($id, $metagrupo,$mes,$anio){
    $sql = 'INSERT INTO config_metagrupal SET
            cm_anio = "'.$anio.'",
            cm_mes = "'.$mes.'",
            cm_meta = "'.$metagrupo.'"
            ON DUPLICATE KEY UPDATE
            cm_meta = "'.$metagrupo.'"';
    setq($sql);

  }
  function insertcomision($exhibiciones, $porcentaje, $descripcion)
  {
    $sql = 'INSERT INTO comisiones_tarjeta SET
            cta_exhibiciones = "' . $exhibiciones . '",
            cta_porcentaje = "'.$porcentaje.'",
            cta_descripcion = "'.$descripcion.'",
            cta_estatus = "A"';
    setq($sql);
  }
  
  function updatecomision($id,$exhibiciones,$porcentaje,$estatus,$descripcion)
  {
    $sql = 'UPDATE comisiones_tarjeta SET
            cta_exhibiciones = "' . $exhibiciones . '",
            cta_porcentaje = "'.$porcentaje.'",
            cta_estatus = "'.$estatus.'",
            cta_descripcion = "'.$descripcion.'"
            WHERE cta_id = "'.$id.'"';
    setq($sql);
  }
  function deletecomision($id)
  {
    $sql = 'DELETE FROM comisiones_tarjeta WHERE
            cta_id = "' . $id . '"';
    setq($sql);
  }

  function settextoguia($textguia, $textguia2){
    $sql = 'UPDATE configuracionesp SET c_textoguias = "'.formmayus($textguia,false).'", c_textoguias2 = "'.formmayus($textguia2,false).'" WHERE c_id = "1"' ;
    setq($sql);
  }

  function settextoticket($textoticket){
    $sql = 'UPDATE configuracionesp SET c_textoticket = "'.formmayus($textoticket,false).'" WHERE c_id = "1"' ;
    setq($sql);

  }
  function insertcomisionventa($nmb, $repartir, $meta, $estatus)
  {
    $sql = 'INSERT INTO config_comisiones SET
            cc_nmb = "' . $nmb . '",
            cc_montorepartido = "'.$repartir.'",
            cc_metaventa = "'.$meta.'",
            cc_estatus = "'.$estatus.'"';
    setq($sql);
  }
  
  function updatecomisionventa($id,$nmb, $repartir, $meta, $estatus)
  {
    $sql = 'UPDATE config_comisiones SET
            cc_nmb = "' . $nmb . '",
            cc_montorepartido = "'.$repartir.'",
            cc_metaventa = "'.$meta.'",
            cc_estatus = "'.$estatus.'" WHERE cc_id = "'.$id.'"';
    setq($sql);
  }
  function deletecomisionventa($id)
  {
    $sql = 'DELETE FROM config_comisiones WHERE
            cc_id = "' . $id . '"';
    setq($sql);
  }
}

class viewconfigespeciales
{
  var $model;
  function __construct($model)
  {
?>
    <script>
      function checkguardar() {
        document.getElementById("guardar").innerHTML = "GUARDAR";
        document.getElementById("guardar").disabled = true;
        return true;
      }
    </script>
  <?php
    $this->model = $model;
  }
  function datos()
  {
    toolbar("CONFIGURACIONES ESPECIALES");
    /* echo '<div class="form-group col-md-12 form-inlinee">
    <button type="button" onclick="sincronizarconf()" id="btnsincroart" class="btn btn-success"><i class="icon-cloud-download2"></i> Sincronizar configuración</button> 
    <!--</div>-->'; */

    echo '
    <script>
    function sincronizarconf() {
      Swal.fire({
        title: "Sincronización en curso. ¡No cierre la ventana!",
        showConfirmButton: false,
        timerProgressBar: true,
        didOpen: () => {
          Swal.showLoading();
        },
        allowOutsideClick: false, // Evita que se cierre al hacer clic fuera de la alerta
      });
  
      $("#btnsincroart").prop("disabled", true);
      $.ajax({
        method: "POST",
        url: "query/sincronizarconfiguracion.php",
      }).success(function (data) {
        if (data == 1) {
          window.location.reload();
        }
      });
    }
  </script>
    ';

    
    echo '
    <div class="col-12 mt-5">
    <div class="table-responsive row">
        ';
    $descuentos = busca('1', 'descuentos','d_estatus', 'COUNT(*)');
    echo '
      <div class="col-12 col-md-3" >
          <div class="card">
              <div class="card-body col-xl-13" id="descuentosdiv">
                  <div class="card-block">
                    <div class="media">
                        <div class="d-flex align-items-center mr-2">
                        <div class="symbol symbol-45 symbol-light-danger me-4 flex-shrink-0 bg-white">
                          <div class="symbol-label">
                            <svg style="fill:#009688" height="60px" viewBox="0 0 640 512"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path d="M96 464v32c0 8.84 7.16 16 16 16h224c8.84 0 16-7.16 16-16V153.25c4.56-2 8.92-4.35 12.99-7.12l142.05 47.63c8.38 2.81 17.45-1.71 20.26-10.08l10.17-30.34c2.81-8.38-1.71-17.45-10.08-20.26l-128.4-43.05c.42-3.32 1.01-6.6 1.01-10.03 0-44.18-35.82-80-80-80-29.69 0-55.3 16.36-69.11 40.37L132.96.83c-8.38-2.81-17.45 1.71-20.26 10.08l-10.17 30.34c-2.81 8.38 1.71 17.45 10.08 20.26l132 44.26c7.28 21.25 22.96 38.54 43.38 47.47V448H112c-8.84 0-16 7.16-16 16zM0 304c0 44.18 57.31 80 128 80s128-35.82 128-80h-.02c0-15.67 2.08-7.25-85.05-181.51-17.68-35.36-68.22-35.29-85.87 0C-1.32 295.27.02 287.82.02 304H0zm56-16l72-144 72 144H56zm328.02 144H384c0 44.18 57.31 80 128 80s128-35.82 128-80h-.02c0-15.67 2.08-7.25-85.05-181.51-17.68-35.36-68.22-35.29-85.87 0-86.38 172.78-85.04 165.33-85.04 181.51zM440 416l72-144 72 144H440z"/></svg>
                          </div>
                        </div>
                        <div ms-5>
                          <h3 style="color: #009688">'.$descuentos.' descuentos</h3>
                          <div class="font-size-sm text-muted font-weight-bold mt-1">Cantidad de descuentos activos</div>
                        </div>                  
                      </div>
                    </div>
                  </div>
              </div>
          </div>
      </div>';
      $condiciones = busca('A', 'condicionesc','c_estatus', 'COUNT(*)');
      echo '
      <div class="col-12 col-md-3" >
          <div class="card">
              <div class="card-body col-xl-13" id="condicionesdiv">
                  <div class="card-block">
                      <div class="media">
                      <div class="d-flex align-items-center mr-2">
                      <div class="symbol symbol-45 symbol-light-danger me-4 flex-shrink-0 bg-white">
                        <div class="symbol-label">
                          <svg style="fill:#00BCD4" height="50px" viewBox="0 0 448 512"><path d="M448 360V24c0-13.3-10.7-24-24-24H96C43 0 0 43 0 96v320c0 53 43 96 96 96h328c13.3 0 24-10.7 24-24v-16c0-7.5-3.5-14.3-8.9-18.7-4.2-15.4-4.2-59.3 0-74.7 5.4-4.3 8.9-11.1 8.9-18.6zM128 134c0-3.3 2.7-6 6-6h212c3.3 0 6 2.7 6 6v20c0 3.3-2.7 6-6 6H134c-3.3 0-6-2.7-6-6v-20zm0 64c0-3.3 2.7-6 6-6h212c3.3 0 6 2.7 6 6v20c0 3.3-2.7 6-6 6H134c-3.3 0-6-2.7-6-6v-20zm253.4 250H96c-17.7 0-32-14.3-32-32 0-17.6 14.4-32 32-32h285.4c-1.9 17.1-1.9 46.9 0 64z"/></svg>  
                        </div>
                      </div>
                      <div ms-5>
                        <h3 style="color: #00BCD4">'.$condiciones.' condiciones</h3>
                        <div class="font-size-sm text-muted font-weight-bold mt-1">Cantidad de condiciones comerciales activas</div>
                      </div>                  
                    </div>
                      </div>
                  </div>
              </div>
          </div>
      </div>';
      $categorias = busca('A', 'catalogo_perdidos','cp_estatus', 'COUNT(*)');
      echo '
      <div class="col-12 col-md-3" >
          <div class="card">
              <div class="card-body col-xl-13" id="categoriasdiv">
                  <div class="card-block">
                      <div class="media">
                      <div class="d-flex align-items-center mr-2">
                      <div class="symbol symbol-45 symbol-light-danger me-4 flex-shrink-0 bg-white">
                        <div class="symbol-label">
                          <svg style="fill:#FF5722" xmlns="http://www.w3.org/2000/svg" height="60px" viewBox="0 0 640 512"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path d="M64 240c0 49.6 21.4 95 57 130.7-12.6 50.3-54.3 95.2-54.8 95.8-2.2 2.3-2.8 5.7-1.5 8.7 1.3 2.9 4.1 4.8 7.3 4.8 66.3 0 116-31.8 140.6-51.4 32.7 12.3 69 19.4 107.4 19.4 27.4 0 53.7-3.6 78.4-10L72.9 186.4c-5.6 17.1-8.9 35-8.9 53.6zm569.8 218.1l-114.4-88.4C554.6 334.1 576 289.2 576 240c0-114.9-114.6-208-256-208-65.1 0-124.2 20.1-169.4 52.7L45.5 3.4C38.5-2 28.5-.8 23 6.2L3.4 31.4c-5.4 7-4.2 17 2.8 22.4l588.4 454.7c7 5.4 17 4.2 22.5-2.8l19.6-25.3c5.4-6.8 4.1-16.9-2.9-22.3z"/></svg>
                        </div>
                      </div>
                      <div ms-5>
                        <h3 style="color: #FF5722">'.$categorias.' categorías</h3>
                        <div class="font-size-sm text-muted font-weight-bold mt-1">Cantidad de categorías al perder un lead activas</div>
                      </div>                  
                    </div>
                      </div>
                  </div>
              </div>
          </div>
      </div>';
    
      //INICIA PANEL DE TARIFAS
      $tarifas = busca('A', 'tarifas_envios','te_estatus', 'COUNT(*)');
      echo '
      <div class="col-12 col-md-3" >
          <div class="card">
              <div class="card-body col-xl-13" id="tarifasdiv">
                  <div class="card-block">
                      <div class="media">
                      <div class="d-flex align-items-center mr-2">
                      <div class="symbol symbol-45 symbol-light-danger me-4 flex-shrink-0 bg-white">
                        <div class="symbol-label">
                          <svg style="fill:#4CAF50" height="50px" viewBox="0 0 700 512"><path d="M608 32H32C14.33 32 0 46.33 0 64v384c0 17.67 14.33 32 32 32h576c17.67 0 32-14.33 32-32V64c0-17.67-14.33-32-32-32zM176 327.88V344c0 4.42-3.58 8-8 8h-16c-4.42 0-8-3.58-8-8v-16.29c-11.29-.58-22.27-4.52-31.37-11.35-3.9-2.93-4.1-8.77-.57-12.14l11.75-11.21c2.77-2.64 6.89-2.76 10.13-.73 3.87 2.42 8.26 3.72 12.82 3.72h28.11c6.5 0 11.8-5.92 11.8-13.19 0-5.95-3.61-11.19-8.77-12.73l-45-13.5c-18.59-5.58-31.58-23.42-31.58-43.39 0-24.52 19.05-44.44 42.67-45.07V152c0-4.42 3.58-8 8-8h16c4.42 0 8 3.58 8 8v16.29c11.29.58 22.27 4.51 31.37 11.35 3.9 2.93 4.1 8.77.57 12.14l-11.75 11.21c-2.77 2.64-6.89 2.76-10.13.73-3.87-2.43-8.26-3.72-12.82-3.72h-28.11c-6.5 0-11.8 5.92-11.8 13.19 0 5.95 3.61 11.19 8.77 12.73l45 13.5c18.59 5.58 31.58 23.42 31.58 43.39 0 24.53-19.05 44.44-42.67 45.07zM416 312c0 4.42-3.58 8-8 8H296c-4.42 0-8-3.58-8-8v-16c0-4.42 3.58-8 8-8h112c4.42 0 8 3.58 8 8v16zm160 0c0 4.42-3.58 8-8 8h-80c-4.42 0-8-3.58-8-8v-16c0-4.42 3.58-8 8-8h80c4.42 0 8 3.58 8 8v16zm0-96c0 4.42-3.58 8-8 8H296c-4.42 0-8-3.58-8-8v-16c0-4.42 3.58-8 8-8h272c4.42 0 8 3.58 8 8v16z"/></svg>
                        </div>
                      </div>
                      <div ms-5>
                        <h3 style="color: #4CAF50">'.$tarifas.' tarifas</h3>
                        <div class="font-size-sm text-muted font-weight-bold mt-1">Tarifas de envío para artículos</div>
                      </div>                  
                    </div>
                      </div>
                  </div>
              </div>
          </div>
      </div>';
      //FINALIZA PANEL DE TARIFAS

      //INICIA PANEL DE IMPORTE MAXIMO DE ENVIO
      $maximoenvio = busca('1', 'configuracionesp','c_id', 'c_maximoenvio');
      echo '
      <div class="col-12 col-md-3 mt-4" >
          <div class="card">
              <div class="card-body col-xl-13" id="importediv">
                  <div class="card-block">
                      <div class="media">
                      <div class="d-flex align-items-center mr-2">
                      <div class="symbol symbol-45 symbol-light-danger me-4 flex-shrink-0 bg-white">
                        <div class="symbol-label">
                          <svg style="fill:#E91E63 " height="50px" viewBox="0 0 448 512"><path d="M209.2 233.4l-108-31.6C88.7 198.2 80 186.5 80 173.5c0-16.3 13.2-29.5 29.5-29.5h66.3c12.2 0 24.2 3.7 34.2 10.5 6.1 4.1 14.3 3.1 19.5-2l34.8-34c7.1-6.9 6.1-18.4-1.8-24.5C238 74.8 207.4 64.1 176 64V16c0-8.8-7.2-16-16-16h-32c-8.8 0-16 7.2-16 16v48h-2.5C45.8 64-5.4 118.7.5 183.6c4.2 46.1 39.4 83.6 83.8 96.6l102.5 30c12.5 3.7 21.2 15.3 21.2 28.3 0 16.3-13.2 29.5-29.5 29.5h-66.3C100 368 88 364.3 78 357.5c-6.1-4.1-14.3-3.1-19.5 2l-34.8 34c-7.1 6.9-6.1 18.4 1.8 24.5 24.5 19.2 55.1 29.9 86.5 30v48c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16v-48.2c46.6-.9 90.3-28.6 105.7-72.7 21.5-61.6-14.6-124.8-72.5-141.7z"/></svg>
                        </div>
                      </div>
                      <div ms-5>
                        <h3 style="color: #E91E63 ">'.$maximoenvio.' importe</h3>
                        <div class="font-size-sm text-muted font-weight-bold mt-1">Tarifas de envío para artículos</div>
                      </div>                  
                    </div>
                      </div>
                  </div>
              </div>
          </div>
      </div>';
      //FINALIZA PANEL DE IMPORTE MAXIMO DE ENVIO

      //INICIA PANEL DE META MENSUAL DE VENTAS
      $metamensual = busca('1', 'usuarios_orden','1', 'SUM(uo_metamensual)');
      echo '
      <div class="col-12 col-md-3 mt-4" >
          <div class="card">
              <div class="card-body col-xl-13" id="metadiv">
                  <div class="card-block">
                      <div class="media">
                      <div class="d-flex align-items-center mr-2">
                      <div class="symbol symbol-45 symbol-light-danger me-4 flex-shrink-0 bg-white">
                        <div class="symbol-label">
                          <svg style="fill:#9C27B0" height="40px" viewBox="0 0 650 512"><path d="M96 224c35.3 0 64-28.7 64-64s-28.7-64-64-64-64 28.7-64 64 28.7 64 64 64zm448 0c35.3 0 64-28.7 64-64s-28.7-64-64-64-64 28.7-64 64 28.7 64 64 64zm32 32h-64c-17.6 0-33.5 7.1-45.1 18.6 40.3 22.1 68.9 62 75.1 109.4h66c17.7 0 32-14.3 32-32v-32c0-35.3-28.7-64-64-64zm-256 0c61.9 0 112-50.1 112-112S381.9 32 320 32 208 82.1 208 144s50.1 112 112 112zm76.8 32h-8.3c-20.8 10-43.9 16-68.5 16s-47.6-6-68.5-16h-8.3C179.6 288 128 339.6 128 403.2V432c0 26.5 21.5 48 48 48h288c26.5 0 48-21.5 48-48v-28.8c0-63.6-51.6-115.2-115.2-115.2zm-223.7-13.4C161.5 263.1 145.6 256 128 256H64c-35.3 0-64 28.7-64 64v32c0 17.7 14.3 32 32 32h65.9c6.3-47.4 34.9-87.3 75.2-109.4z"/></svg>
                        </div>
                      </div>
                      <div ms-5>
                        <h3 style="color: #9C27B0">$ '.number_format($metamensual).'</h3>
                        <div class="font-size-sm text-muted font-weight-bold mt-1">Meta mensual de ventas</div>
                      </div>                  
                    </div>
                      </div>
                  </div>
              </div>
          </div>
      </div>';
      //INICIA PANEL DE META MENSUAL DE VENTAS
    
      //INICIA PANEL DE META GRUPAL
      $meses = array("","Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
      $metames = busca(date('m'), 'config_metagrupal','cm_anio = "'.date('Y').'" AND cm_mes', 'cm_meta');
      echo '
      <div class="col-12 col-md-3 mt-4" >
        <div class="card">
          <div class="card-body col-xl-13" id="metagrupodiv">
            <div class="card-block">
              <div class="media">
                <div class="d-flex align-items-center mr-2">
                  <div class="symbol symbol-45 symbol-light-danger me-4 flex-shrink-0 bg-white">
                    <div class="symbol-label">
                      <svg style="fill:#E0E000 " height="50px" viewBox="0 0 448 512"><path d="M243.2 189.9V258c26.1 5.9 49.3 15.6 73.6 22.3v-68.2c-26-5.8-49.4-15.5-73.6-22.2zm223.3-123c-34.3 15.9-76.5 31.9-117 31.9C296 98.8 251.7 64 184.3 64c-25 0-47.3 4.4-68 12 2.8-7.3 4.1-15.2 3.6-23.6C118.1 24 94.8 1.2 66.3 0 34.3-1.3 8 24.3 8 56c0 19 9.5 35.8 24 45.9V488c0 13.3 10.7 24 24 24h16c13.3 0 24-10.7 24-24v-94.4c28.3-12.1 63.6-22.1 114.4-22.1 53.6 0 97.8 34.8 165.2 34.8 48.2 0 86.7-16.3 122.5-40.9 8.7-6 13.8-15.8 13.8-26.4V95.9c.1-23.3-24.2-38.8-45.4-29zM169.6 325.5c-25.8 2.7-50 8.2-73.6 16.6v-70.5c26.2-9.3 47.5-15 73.6-17.4zM464 191c-23.6 9.8-46.3 19.5-73.6 23.9V286c24.8-3.4 51.4-11.8 73.6-26v70.5c-25.1 16.1-48.5 24.7-73.6 27.1V286c-27 3.7-47.9 1.5-73.6-5.6v67.4c-23.9-7.4-47.3-16.7-73.6-21.3V258c-19.7-4.4-40.8-6.8-73.6-3.8v-70c-22.4 3.1-44.6 10.2-73.6 20.9v-70.5c33.2-12.2 50.1-19.8 73.6-22v71.6c27-3.7 48.4-1.3 73.6 5.7v-67.4c23.7 7.4 47.2 16.7 73.6 21.3v68.4c23.7 5.3 47.6 6.9 73.6 2.7V143c27-4.8 52.3-13.6 73.6-22.5z"/></svg>
                    </div>
                  </div>
                  <div ms-5>
                    <h3 style="color: #E0E000 ">'.number_format($metames,2).' </h3>
                    <div class="font-size-sm text-muted font-weight-bold mt-1">Meta grupal del mes '.$meses[date('n')].'</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>';
      //FINALIZA PANEL DE IMPORTE META GRUPAL

      //INICIA PANEL DE COMISIONES DE PAGO CON TARJETA
      $sqltar =  'SELECT COUNT(*) FROM comisiones_tarjeta';
      $resultar = setq($sqltar);
      list($comisionesreg) = $resultar->fetch_array();
      echo '
      <div class="col-12 col-md-3 mt-4" >
          <div class="card">
              <div class="card-body col-xl-13" id="comisiondiv">
                  <div class="card-block">
                      <div class="media">
                      <div class="d-flex align-items-center mr-2">
                      <div class="symbol symbol-45 symbol-light-danger me-4 flex-shrink-0 bg-white">
                        <div class="symbol-label">
                          <svg style="fill:#00BCD4" height="50px" viewBox="0 0 448 512"><path d="M527.9 32H48.1C21.5 32 0 53.5 0 80v352c0 26.5 21.5 48 48.1 48h479.8c26.6 0 48.1-21.5 48.1-48V80c0-26.5-21.5-48-48.1-48zM54.1 80h467.8c3.3 0 6 2.7 6 6v42H48.1V86c0-3.3 2.7-6 6-6zm467.8 352H54.1c-3.3 0-6-2.7-6-6V256h479.8v170c0 3.3-2.7 6-6 6zM192 332v40c0 6.6-5.4 12-12 12h-72c-6.6 0-12-5.4-12-12v-40c0-6.6 5.4-12 12-12h72c6.6 0 12 5.4 12 12zm192 0v40c0 6.6-5.4 12-12 12H236c-6.6 0-12-5.4-12-12v-40c0-6.6 5.4-12 12-12h136c6.6 0 12 5.4 12 12z"/></svg>
                        </div>
                      </div>
                      <div ms-5>
                        <h3 style="color: #00BCD4">'.$comisionesreg.' comisiones</h3>
                        <div class="font-size-sm text-muted font-weight-bold mt-1">Comisiones de pago con tarjeta de crédito</div>
                      </div>                  
                    </div>
                      </div>
                  </div>
              </div>
          </div>
      </div>';
      //FINALIZA PANEL DE COMISIONES DE PAGO CON TARJETA

      //INICIA PANEL DE TEXTO EN GUIAS
      $sqltg =  'SELECT c_textoguias FROM configuracionesp WHERE c_id = "1"';
      $resultatg = setq($sqltg);
      list($textog) = $resultatg->fetch_array();
      echo '
      <div class="col-12 col-md-3 mt-4" >
          <div class="card">
              <div class="card-body col-xl-13" id="textoguiadiv">
                  <div class="card-block">
                      <div class="media">
                      <div class="d-flex align-items-center mr-2">
                      <div class="symbol symbol-45 symbol-light-danger me-4 flex-shrink-0 bg-white">
                        <div class="symbol-label">
                          <svg style="fill:#006633" height="50px" viewBox="0 0 640 512"><path d="M624 352h-16V243.9c0-12.7-5.1-24.9-14.1-33.9L494 110.1c-9-9-21.2-14.1-33.9-14.1H416V48c0-26.5-21.5-48-48-48H48C21.5 0 0 21.5 0 48v320c0 26.5 21.5 48 48 48h16c0 53 43 96 96 96s96-43 96-96h128c0 53 43 96 96 96s96-43 96-96h48c8.8 0 16-7.2 16-16v-32c0-8.8-7.2-16-16-16zM160 464c-26.5 0-48-21.5-48-48s21.5-48 48-48 48 21.5 48 48-21.5 48-48 48zm320 0c-26.5 0-48-21.5-48-48s21.5-48 48-48 48 21.5 48 48-21.5 48-48 48zm80-208H416V144h44.1l99.9 99.9V256z"/></svg>
                        </div>
                      </div>
                      <div ms-5>
                        <h3 style="color: #006633">Texto en envio de guías</h3>
                      </div>
                    </div>
                      </div>
                  </div>
              </div>
          </div>
      </div>';
      //FINALIZA PANEL DE TEXTO EN GUIAS

      //INICIA PANEL DE TEXTO EN TICKETS
      $sqltg =  'SELECT c_textoticket FROM configuracionesp WHERE c_id = "1"';
      $resultatg = setq($sqltg);
      list($textog) = $resultatg->fetch_array();
      echo '
      <div class="col-12 col-md-3 mt-4" >
          <div class="card">
              <div class="card-body col-xl-13" id="textoticketdiv">
                  <div class="card-block">
                      <div class="media">
                      <div class="d-flex align-items-center mr-2">
                      <div class="symbol symbol-45 symbol-light-danger me-4 flex-shrink-0 bg-white">
                        <div class="symbol-label">
                          <svg style="fill:#333333" height="50px" viewBox="0 0 640 512"><path d="M358.4 3.2L320 48 265.6 3.2a15.9 15.9 0 0 0-19.2 0L192 48 137.6 3.2a15.9 15.9 0 0 0-19.2 0L64 48 25.6 3.2C15-4.7 0 2.8 0 16v480c0 13.2 15 20.7 25.6 12.8L64 464l54.4 44.8a15.9 15.9 0 0 0 19.2 0L192 464l54.4 44.8a15.9 15.9 0 0 0 19.2 0L320 464l38.4 44.8c10.5 7.9 25.6.4 25.6-12.8V16c0-13.2-15-20.7-25.6-12.8zM320 360c0 4.4-3.6 8-8 8H72c-4.4 0-8-3.6-8-8v-16c0-4.4 3.6-8 8-8h240c4.4 0 8 3.6 8 8v16zm0-96c0 4.4-3.6 8-8 8H72c-4.4 0-8-3.6-8-8v-16c0-4.4 3.6-8 8-8h240c4.4 0 8 3.6 8 8v16zm0-96c0 4.4-3.6 8-8 8H72c-4.4 0-8-3.6-8-8v-16c0-4.4 3.6-8 8-8h240c4.4 0 8 3.6 8 8v16z"/></svg>
                        </div>
                      </div>
                      <div ms-5>
                        <h3 style="color: #333333">Texto en envio de guías</h3>
                      </div>
                    </div>
                      </div>
                  </div>
              </div>
          </div>
      </div>';
      //FINALIZA PANEL DE TEXTO EN TICKETS

      //INICIA PANEL DE GARANTÍA
      $sqltg =  'SELECT c_dgarantia FROM configuracionesp WHERE c_id = "1"';
      $resultatg = setq($sqltg);
      list($textog) = $resultatg->fetch_array();
      echo '
      <div class="col-12 col-md-3 mt-4" >
          <div class="card">
              <div class="card-body col-xl-13" id="diasgarantiadiv">
                  <div class="card-block">
                      <div class="media">
                      <div class="d-flex align-items-center mr-2">
                      <div class="symbol symbol-45 symbol-light-danger me-4 flex-shrink-0 bg-white">
                        <div class="symbol-label">
                          <svg style="fill:#ffa87c" height="50px" viewBox="0 0 640 512"><path d="M0 464c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48V192H0v272zm64-192c0-8.8 7.2-16 16-16h96c8.8 0 16 7.2 16 16v96c0 8.8-7.2 16-16 16H80c-8.8 0-16-7.2-16-16v-96zM400 64h-48V16c0-8.8-7.2-16-16-16h-32c-8.8 0-16 7.2-16 16v48H160V16c0-8.8-7.2-16-16-16h-32c-8.8 0-16 7.2-16 16v48H48C21.5 64 0 85.5 0 112v48h448v-48c0-26.5-21.5-48-48-48z"/></svg>
                        </div>
                      </div>
                      <div ms-5>
                        <h3 style="color: #ffa87c">Días de garantía</h3>
                      </div>
                    </div>
                      </div>
                  </div>
              </div>
          </div>
      </div>';
      //FINALIZA PANEL DE GARANTÍA

      //INICIA PANEL DE COMISIONES
      $sqltg =  'SELECT COUNT(*) FROM config_comisiones';
      $resultatg = setq($sqltg);
      list($comisionv) = $resultatg->fetch_array();
      echo '
      <div class="col-12 col-md-3 mt-4" >
          <div class="card">
              <div class="card-body col-xl-13" id="comisionesventasdiv">
                  <div class="card-block">
                      <div class="media">
                      <div class="d-flex align-items-center mr-2">
                      <div class="symbol symbol-45 symbol-light-danger me-4 flex-shrink-0 bg-white">
                        <div class="symbol-label">
                          <svg style="fill:#58D68D" height="50px" viewBox="0 0 640 512"><path d="M416 192c0-88.37-93.12-160-208-160S0 103.63 0 192c0 34.27 14.13 65.95 37.97 91.98C24.61 314.22 2.52 338.16 2.2 338.5A7.995 7.995 0 0 0 8 352c36.58 0 66.93-12.25 88.73-24.98C128.93 342.76 167.02 352 208 352c114.88 0 208-71.63 208-160zm-224 96v-16.29c-11.29-.58-22.27-4.52-31.37-11.35-3.9-2.93-4.1-8.77-.57-12.14l11.75-11.21c2.77-2.64 6.89-2.76 10.13-.73 3.87 2.42 8.26 3.72 12.82 3.72h28.11c6.5 0 11.8-5.92 11.8-13.19 0-5.95-3.61-11.19-8.77-12.73l-45-13.5c-18.59-5.58-31.58-23.42-31.58-43.39 0-24.52 19.05-44.44 42.67-45.07V96c0-4.42 3.58-8 8-8h16c4.42 0 8 3.58 8 8v16.29c11.29.58 22.27 4.51 31.37 11.35 3.9 2.93 4.1 8.77.57 12.14l-11.75 11.21c-2.77 2.64-6.89 2.76-10.13.73-3.87-2.43-8.26-3.72-12.82-3.72h-28.11c-6.5 0-11.8 5.92-11.8 13.19 0 5.95 3.61 11.19 8.77 12.73l45 13.5c18.59 5.58 31.58 23.42 31.58 43.39 0 24.53-19.05 44.44-42.67 45.07V288c0 4.42-3.58 8-8 8h-16c-4.42 0-8-3.58-8-8zm346.01 123.99C561.87 385.96 576 354.27 576 320c0-66.94-53.49-124.2-129.33-148.07.86 6.6 1.33 13.29 1.33 20.07 0 105.87-107.66 192-240 192-10.78 0-21.32-.77-31.73-1.88C207.8 439.63 281.77 480 368 480c40.98 0 79.07-9.24 111.27-24.98C501.07 467.75 531.42 480 568 480c3.2 0 6.09-1.91 7.34-4.84 1.27-2.94.66-6.34-1.55-8.67-.31-.33-22.42-24.24-35.78-54.5z"/></svg>
                        </div>
                      </div>
                      <div ms-5>
                        <h3 style="color: #58D68D">'.$comisionv.' comisiones</h3>
                        <div class="font-size-sm text-muted font-weight-bold mt-1">Comisiones por meta de venta</div>
                      </div>
                    </div>
                      </div>
                  </div>
              </div>
          </div>
      </div>';
      //FINALIZA PANEL DE COMISIONES

    echo ' </div>
      </div>';
    ?>
    <script src="assets/js/configuracionesp.js">
    </script>
    <?php
  }
  function edit()
  {
  ?>
    <?php
    $url = 'index';
    
    ?>
    <style>
      .titulo {
        margin: 0; /* Elimina el margen predeterminado del h3 */
        white-space: nowrap; /* Evita que el título se envuelva a la siguiente línea */
        overflow: hidden; /* Oculta cualquier parte del título que se haya cortado */
      }
      #descuentosdiv:hover {
        cursor: pointer;
      }
      #condicionesdiv:hover {
        cursor: pointer;
      }
      #categoriasdiv:hover {
        cursor: pointer;
      }
      #tarifasdiv:hover {
        cursor: pointer;
      }
      #importediv:hover {
        cursor: pointer;
      }
      #comisionesdiv:hover{
        cursor: pointer;
      }
      #comisiondiv:hover{
        cursor: pointer;
      }
      #textoguiadiv:hover{
        cursor: pointer;
      }
      #textoticketdiv:hover{
        cursor: pointer;
      }
      #diasgarantiadiv:hover{
        cursor: pointer;
      }
      #comisionesventasdiv:hover{
        cursor: pointer;
      }
    </style>
    <?php
    if (isset($_GET['descuentos'])) {
      $showdescuentos = "";
      $showcondiciones = "hidden";
      $showcategorias = "hidden";
      $showformulario = "hidden";
      $showtarifas = "hidden";
      $showimporte = "hidden";
      $showmeta = "hidden";
      $showmetagrupo = "hidden";
      $showcomisiones = "hidden";
      $showtextoguia = "hidden";
      $showtextoticket = "hidden";
      $showgarantia = "hidden";
      $showcomisionesventa = "hidden";
    } else if(isset($_GET['condiciones'])){
      $showdescuentos = "hidden";
      $showcondiciones = "";
      $showcategorias = "hidden";
      $showformulario = "hidden";
      $showtarifas = "hidden";
      $showimporte = "hidden";
      $showmeta = "hidden";
      $showmetagrupo = "hidden";
      $showcomisiones = "hidden";
      $showtextoguia = "hidden";
      $showtextoticket = "hidden";
      $showgarantia = "hidden";
      $showcomisionesventa = "hidden";
    } else if(isset($_GET['categoria'])){
      $showdescuentos = "hidden";
      $showcondiciones = "hidden";
      $showcategorias = "";
      $showformulario = "hidden";
      $showtarifas = "hidden";
      $showimporte = "hidden";
      $showmeta = "hidden";
      $showmetagrupo = "hidden";
      $showcomisiones = "hidden";
      $showtextoguia = "hidden";
      $showtextoticket = "hidden";
      $showgarantia = "hidden";
      $showcomisionesventa = "hidden";
    } else if(isset($_GET['tarifas'])){
      $showdescuentos = "hidden";
      $showcondiciones = "hidden";
      $showcategorias = "hidden";
      $showformulario = "hidden";
      $showtarifas = "";
      $showimporte = "hidden";
      $showmeta = "hidden";
      $showmetagrupo = "hidden";
      $showcomisiones = "hidden";
      $showtextoguia = "hidden";
      $showtextoticket = "hidden";
      $showgarantia = "hidden";
      $showcomisionesventa = "hidden";
    } else if(isset($_GET['importe'])){
      $showdescuentos = "hidden";
      $showcondiciones = "hidden";
      $showcategorias = "hidden";
      $showformulario = "hidden";
      $showtarifas = "hidden";
      $showimporte = "";
      $showmeta = "hidden";
      $showmetagrupo = "hidden";
      $showcomisiones = "hidden";
      $showtextoguia = "hidden";
      $showtextoticket = "hidden";
      $showgarantia = "hidden";
      $showcomisionesventa = "hidden";
    } else if(isset($_GET['meta'])){
      $showdescuentos = "hidden";
      $showcondiciones = "hidden";
      $showcategorias = "hidden";
      $showformulario = "hidden";
      $showtarifas = "hidden";
      $showimporte = "hidden";
      $showmeta = "";
      $showmetagrupo = "hidden";
      $showcomisiones = "hidden";
      $showtextoguia = "hidden";
      $showtextoticket = "hidden";
      $showgarantia = "hidden";
      $showcomisionesventa = "hidden";
    } else if(isset($_GET['metagrupo'])){
      $showdescuentos = "hidden";
      $showcondiciones = "hidden";
      $showcategorias = "hidden";
      $showformulario = "hidden";
      $showtarifas = "hidden";
      $showimporte = "hidden";
      $showmeta = "hidden";
      $showmetagrupo = "hidden";
      $showcomisiones = "hidden";
      $showtextoguia = "hidden";
      $showtextoticket = "hidden";
      $showgarantia = "hidden";
      $showcomisionesventa = "hidden";
    } else if(isset($_GET['comision'])){
      $showdescuentos = "hidden";
      $showcondiciones = "hidden";
      $showcategorias = "hidden";
      $showformulario = "hidden";
      $showtarifas = "hidden";
      $showimporte = "hidden";
      $showmetagrupo = "hidden";
      $showmeta = "hidden";
      $showcomisiones = "";
      $showtextoguia = "hidden";
      $showtextoticket = "hidden";
      $showgarantia = "hidden";
      $showcomisionesventa = "hidden";
    }
    else if(isset($_GET['textoguia'])){
      $showdescuentos = "hidden";
      $showcondiciones = "hidden";
      $showcategorias = "hidden";
      $showformulario = "hidden";
      $showtarifas = "hidden";
      $showimporte = "hidden";
      $showmetagrupo = "hidden";
      $showmeta = "hidden";
      $showcomisiones = "hidden";
      $showtextoguia = "";
      $showtextoticket = "hidden";
      $showgarantia = "hidden";
      $showcomisionesventa = "hidden";
    }
    else if(isset($_GET['textoticket'])){
      $showdescuentos = "hidden";
      $showcondiciones = "hidden";
      $showcategorias = "hidden";
      $showformulario = "hidden";
      $showtarifas = "hidden";
      $showimporte = "hidden";
      $showmetagrupo = "hidden";
      $showmeta = "hidden";
      $showcomisiones = "hidden";
      $showtextoguia = "hidden";
      $showtextoticket = "";
      $showgarantia = "hidden";
      $showcomisionesventa = "hidden";
    } else if(isset($_GET['dgarantia'])){
      $showdescuentos = "hidden";
      $showcondiciones = "hidden";
      $showcategorias = "hidden";
      $showformulario = "hidden";
      $showtarifas = "hidden";
      $showimporte = "hidden";
      $showmetagrupo = "hidden";
      $showmeta = "hidden";
      $showcomisiones = "hidden";
      $showtextoguia = "hidden";
      $showtextoticket = "hidden";
      $showgarantia = "";
      $showcomisionesventa = "hidden";
    }else if(isset($_GET['comisionventa'])){
      $showdescuentos = "hidden";
      $showcondiciones = "hidden";
      $showcategorias = "hidden";
      $showformulario = "hidden";
      $showtarifas = "hidden";
      $showimporte = "hidden";
      $showmetagrupo = "hidden";
      $showmeta = "hidden";
      $showcomisiones = "hidden";
      $showtextoguia = "hidden";
      $showtextoticket = "hidden";
      $showgarantia = "hidden";
      $showcomisionesventa = "";
    } else {
      $showdescuentos = "";
      $showcondiciones = "hidden";
      $showcategorias = "hidden";
      $showformulario = "hidden";
      $showtarifas = "hidden";
      $showimporte = "hidden";
      $showmeta = "hidden";
      $showmetagrupo = "hidden";
      $showcomisiones = "hidden";
      $showtextoguia = "hidden";
      $showtextoticket = "hidden";
      $showgarantia = "hidden";
      $showcomisionesventa = "hidden";
    }

    echo '<div id="divupd" class="main-card mb-3 mt-5 card" ' . $showformulario . '>
          <form autocomplete="off" name="datosconf" id="datosconf" action="?modulo=configespeciales&accion=update&id=' . $this->model->id . '" method="post" onsubmit="checkguardar();">
            <div class="card-body row"><div class="col-xl-12 col-md-12 center">
              <div>
                <h5 class="card-title p-1">Actualizar Datos</h5>
                </div>
                <div class="alinear-campos row">
                <div class="col-md-3">
                  <div class="position-relative form-group">
                    <b><label for="cupo" class="">Cupo del área</label></b>
                    <input type="number" name="cupo" id="cupo" value="' . $this->model->cupoarea . '" placeholder="Cupo del área" class="form-control" >
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="position-relative form-group">
                    <b><label for="iva" class="">% IVA</label></b>
                    <input type="number" step="0.01" name="iva" id="iva" value="' . number_format($this->model->iva, 2) . '" placeholder="IVA" class="form-control">
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="position-relative form-group">
                    <b><label for="porcentarjet" class="">% Porcentaje adicional de cobro con tarjeta</label></b>
                    <input type="number" min="0" name="porcentarjet" id="porcentarjet" step="0.01" value="' . $this->model->porcentarjet . '" placeholder="Porcentaje adicional" class="form-control">
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="position-relative form-group">
                    <b><label for="porcentarjet" class="">Tiempo de actualización de la tabla de proximos a salir</label></b>
                    <input type="number" min="0" name="intervalotime" id="intervalotime" step="1" value="' . $this->model->intervalotime . '" placeholder="Intervalo de tiempo" class="form-control">
                  </div>
                </div>
                </div>
                
              <div class="form-row alinear-campos row">
                <div class="col-md-3">
                  <div class="form-group">
                    <b><label for="ihorario" class="">Hora de apertura</label></b>
                    <input type="time" name="ihorario" id="ihorario" value="' . $this->model->ihorario . '" placeholder="Hora de apertura" class="form-control">
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <b><label for="fhorario" class="">Hora de cierre</label></b>
                    <input type="time" name="fhorario" id="fhorario" value="' . $this->model->fhorario . '" placeholder="Hora de cierre" class="form-control">
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <b><label for="retiromax" class="">Retiro máximo de la caja</label></b>
                    <input type="number" min="0" step="0.01" name="retiromax" id="retiromax" value="' . $this->model->retiromax . '" placeholder="Retiro máximo en caja" class="form-control">
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <b><label for="retiroforzoso" class="">Retiro forzoso en caja</label></b>
                    <input type="number" min="0" step="0.01" name="retiroforzoso" id="retiroforzoso" value="' . $this->model->retiroforzoso . '" placeholder="Retiro máximo en caja" class="form-control">
                  </div>
                </div>
              </div>
              <div class="form-row alinear-campos row">
                <div class="col-md-3">
                  <div class="form-group">
                    <b><label for="limitehorario" class="">Límite de tiempo para cambiar el horario</label></b>
                    <input type="number" min="0" name="limitehorario" id="limitehorario" value="' . $this->model->limitehorario . '" placeholder="Hora de apertura" class="form-control">
                  </div>
                </div>';
                $partes = explode(":", $this->model->vidaticket);
                $minutos = $partes[1];
                echo '<div class="col-md-3">
                  <div class="form-group">
                    <b><label for="limitehorario" class="">Tiempo de vida de un ticket</label></b>
                    <input type="number" min="0" max="59" name="vidaticket" id="vidaticket" value="' . $minutos . '" placeholder="Tiempo de vida de un ticket" class="form-control">
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <b><label for="limitehorario" class="">Tiempo restante para visualización al próximo a salir</label></b>
                    <input type="number" min="0" name="proximo" id="proximo" value="' . $this->model->proximo . '" placeholder="Próximo a salir" class="form-control">
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <b><label for="msjregion" class="">Mensaje al seleccionar la región en el punto de venta</label></b>
                    <input type="text" name="msjregion" id="msjregion" value="' . $this->model->msjregion . '" placeholder="Mensaje de región" class="form-control">
                  </div>
                </div>
              </div>
              <div class="form-row alinear-campos row">
                <div class="col-md-3">
                  <div class="form-group">
                    <b><label for="msjpred1" class="">Mensaje predeterminado 1</label></b>
                    <input type="text" name="msjpred1" id="msjpred1" value="' . $this->model->msjpred1 . '" placeholder="Mensaje predeterminado 1" class="form-control">
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <b><label for="msjpred2" class="">Mensaje predeterminado 2</label></b>
                    <input type="text" name="msjpred2" id="msjpred2" value="' . $this->model->msjpred2 . '" placeholder="Mensaje predeterminado 2" class="form-control">
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <b><label for="tiempovale" class="">Días de caducidad de un vale de registro</label></b>
                    <input type="number" name="tiempovale" id="tiempovale" value="' . $this->model->tiempovale . '" placeholder="Tiempo en días de un vale" class="form-control">
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <b><label for="montovale" class="">Monto del vale de registro</label></b>
                    <input type="number" name="montovale" id="montovale" value="' . $this->model->montovale . '" placeholder="Monto del vale de registro" class="form-control">
                  </div>
                </div>
                <style>
                .switch_box{
                  display: -webkit-box;
                  display: -ms-flexbox;
                  display: flex;
                  max-width: 100%;
                  min-width: 45px;
                  height: 30px;
                  -webkit-box-pack: center;
                      -ms-flex-pack: center;
                          justify-content: center;
                  -webkit-box-align: center;
                      -ms-flex-align: center;
                          align-items: center;
                  -webkit-box-flex: 1;
                      -ms-flex: 1;
                          flex: 1;
                }
                .box_1{
                  background: #ffffff;
                }
                input[type="checkbox"].switch_1{
                  font-size: 15px;
                  -webkit-appearance: none;
                     -moz-appearance: none;
                          appearance: none;
                  width: 3.5em;
                  height: 1.5em;
                  background: #ddd;
                  border-radius: 3em;
                  position: relative;
                  cursor: pointer;
                  outline: none;
                  -webkit-transition: all .2s ease-in-out;
                  transition: all .2s ease-in-out;
                  }
                  
                  input[type="checkbox"].switch_1:checked{
                  background: #0ebeff;
                  }
                  
                  input[type="checkbox"].switch_1:after{
                  position: absolute;
                  content: "";
                  width: 1.5em;
                  height: 1.5em;
                  border-radius: 50%;
                  background: #fff;
                  -webkit-box-shadow: 0 0 .25em rgba(0,0,0,.3);
                          box-shadow: 0 0 .25em rgba(0,0,0,.3);
                  -webkit-transform: scale(.7);
                          transform: scale(.7);
                  left: 0;
                  -webkit-transition: all .2s ease-in-out;
                  transition: all .2s ease-in-out;
                  }
                  
                  input[type="checkbox"].switch_1:checked:after{
                  left: calc(100% - 1.5em);
                  }
                </style>

                <script>
                  function cambsucursal(from){
                    var suc = document.getElementById("sucursal").value;
                    var suc2 = document.getElementById("sucursal2").value
                    var suc3 = document.getElementById("sucursal3").value;
                    
                    if(from == "U"){
                      window.location.href="?modulo=configespeciales&accion=index&sucursal="+suc;
                    } else if (from == "P"){
                      window.location.href="?modulo=configespeciales&accion=index&presupuesto=1&sucursal="+suc2;
                    } else if (from == "H"){
                      window.location.href="?modulo=configespeciales&accion=index&horarios=1&sucursal="+suc3;
                    } 
                    
                  }

                </script>
                <div class="col-md-2">
                <div class="form-group"><b>
                <label for="cupores" class="">Estatus del vale de registro</label>
                <div class="switch_box box_1">';
                if ($this->model->estatusvale == "1")
                $checkedval = "checked";
              else
                $checkedval = "";
                echo '<input type="checkbox" id="estatusvale" name="estatusvale" class="switch_1" '.$checkedval.'>
                </div>
                </div>
                </div>

              

                
              </div>
              <div class="form-row alinear-campos row">
                <div class="col-md-3">
                  <div class="form-group">
                    <label for="cupores" class="">Cupo para reservaciones</label>
                    <input type="text" name="cupores" id="cupores" value="' . $this->model->cuporeservacion . '" placeholder="Cupo para reservaciones" class="form-control">
                  </div>
                </div>
              </div>
                <div class="col-md-12 text-xs-center">
                  <button id="guardar" class="mt-1 mb-1 btn btn-success"><i class="far fa-save"></i> Guardar</button>
                  </div>
                </form>
                

              </div>
              </div>
              </div>

              <script>
                function subform(){
                  document.getElementById("form2").submit();
                }
              </script>
              ';

        echo '
        <div id="divdescuentos" class="main-card mb-3 card mt-5"  ' . $showdescuentos . '>   
          <form autocomplete="off" name="dobledesc" id="dobledesc" action="?modulo=configespeciales&accion=dobledesc" method="post" onsubmit="checkguardar();">
            <div class="col-12 col-md-3 p-4">
              <label>Habilitar descuento y envío gratis</label>';
              $dobledesc = busca('1', 'configuracionesp', 'c_id', 'c_dobledesc');
              if($dobledesc == "1") $check = 'checked';
              else $check = '';
              echo '<input class="flipswitch" type="checkbox" name="habilitar" id="habilitar" onclick="aplicardesc();" '.$check.'>  
            </div>
          </form>
            <center>
              <form autocomplete="off" name="insdesc" id="insdesc" action="?modulo=configespeciales&accion=insertdescuento" method="post" onsubmit="checkguardar();">
                <div class="card-body center-form">
                  <div class="col-12 col-md-6">
                    <div>
                      <h5 class="card-title p-1">Configuración de descuentos</h5>
                    </div> 
                    <div class="row d-flex">
                      <div class="col-md-4 col-6 mt-3">
                        <div class="position-relative form-group">
                          <b><label for="nombre" class="">Cantidad del descuento (0-100)</label></b>
                          <input type="number" step="0.01" min="0.01" max="100" name="descuento" id="descuento" value="0" placeholder="Cantidad del descuento entre 0 y 100" class="form-control" required>
                        </div>
                      </div>
                      <div class="col-md-2 col-4 mt-3">
                        <div class="position-relative form-group">
                          <b><label  class="">Cantidad de artículos</label></b>
                          <input type="number" step="1" min="0" name="articulos" id="articulos" value="0" placeholder="Cantidad de artículos necesarios para poder aplicar el descuento" class="form-control" required>
                        </div>
                      </div>
                      <div class="col-md-3 col-4 mt-3">
                        <div class="position-relative form-group">
                          <b><label  class="">Categoria del artículo</label></b>
                          <select id="categoria" name="categoria" class="form-control">';
                            $sql = 'SELECT * FROM categorias WHERE cat_estatus = "A" AND cat_inflable = "1"';
                            $result  = setq($sql);
                            while($row = $result -> fetch_array()){
                              echo '<option value="'.$row['cat_id'].'">'.$row['cat_nmb'].'</option>';
                            }
                          echo '<option value="T">TODOS LOS INFLABLES</option>';  
                          echo '</select>
                        </div>
                      </div>
                      <div class="col-md-2 col-2 mt-9">
                        <div class="position-relative form-group alinear-campos row">
                          <button id="añadir" class="btn btn-primary"><i class="fas fa-plus"></i> Añadir</button>
                        </div>
                      </div>
                    </div>
                  </div> 
                </div> 
              </form>';
              $sql = 'SELECT * FROM descuentos ORDER BY d_descuento ASC';
              $result = setq($sql);
          
              if ($result->num_rows > 0) {
                echo '
                <div class="col-md-10 center">
                <table width="100%" class="table table-stripped table-bordered table-hover mt-5">
                  <thead class="bg-primary text-white">
                    <tr>
                      <td style="width:20%;">Descuento</td>
                      <td style="width:20%;">Cantidad</td>
                      <td style="width:20%;">Categoría</td>
                      <td style="width:20%;">Estatus</td>
                      <td style="width:20%;">Actualizar/Borrar</td>
                    </tr>
                  </thead>
                  <tbody>';
                  while ($row = $result->fetch_array()) {
                  if($row['d_estatus'] == "1") $estatus = "checked";
                  else $estatus = "";
                  if($row['d_categoria'] == "T") $selt = 'selected';
                  else $selt = '';
                  echo '
                    <form autocomplete="off" name="updregion" id="updregion" action="?modulo=configespeciales&accion=updatedescuento&id='.$row['d_id'].'" method="post">
                      <tr>
                        <td style="width:20%;">
                          <input type="text" name="descuento" id="descuento" value="'.$row['d_descuento'].'" placeholder="Cantidad de descuento" class="form-control" required>
                        </td>
                        <td style="width:20%;">
                          <input type="text" name="articulos" id="articulos" value="'.$row['d_numeroart'].'" placeholder="Cantidad de artículos necesarios para aplicar el descuento" class="form-control" required>
                        </td>
                        <td style="width:20%;">';
                          echo '<select name="categoria" id="categoria" class="form-control">';
                          $sqlcat = 'SELECT * FROM categorias WHERE cat_estatus = "A" AND cat_inflable = "1"';
                          $resultcat  = setq($sqlcat);
                          while($rowcat = $resultcat -> fetch_array()){
                            if($row['d_categoria'] == $rowcat['cat_id']) $sel = 'selected';
                            else $sel = '';
                            echo '<option value="'.$rowcat['cat_id'].'" '.$sel.'>'.$rowcat['cat_nmb'].'</option>';
                          }
                        echo '<option value="T" '.$selt.'>TODOS LOS INFLABLES</option>';  
                        echo '</select>
                        </td>
                        <td style="width:20%;">
                          <input type="checkbox" name="estatus" id="estatus" placeholder="Estatus del descuento" class="form-control flipswitch2" '.$estatus.'>
                        </td>
                        <td style="width:20%;">
                          <button type="submit" class="btn-sm btn-primary">
                            <i class="fas fa-redo" style="color:#ffffff"></i>
                          </button>
                          <button type="button" class="btn-sm btn-danger" onclick="deldescuento(' . $row['d_id'] . ');">
                            <i class="fas fa-times-circle" style="color:#ffffff"></i>
                          </button>
                        </td>   
                      </tr> 
                    </form>
              </center>
                  ';
              }
            } 
      echo '    </tbody>
              </table>  
              <script>
              function deldescuento(id){  
                var conf = confirm("¿Estas seguro que deseas eliminar el descuento seleccionado?");
                if(conf == true){
                  window.location.href="?modulo=configespeciales&accion=deletedescuento&id="+id+"";
                }
              }
              function aplicardesc(){
                 var form = document.getElementById("dobledesc");
                 form.submit();   
              } 
              </script>
              
              <br><br>
                </div>
              </div>
          ';
          

      echo '<center>
        <div id="divcondiciones" class="main-card mb-3 card mt-5"  ' . $showcondiciones . '>
              <form autocomplete="off" name="insregion" id="insregion" action="?modulo=configespeciales&accion=insertcondicion" method="post" onsubmit="checkguardar();">
                <div class="card-body center-form">
                <div class="col-12 col-md-8">
                  <div>
                    <h5 class="card-title p-1">Configuración de condiciones comerciales</h5>
                  </div> 
                  <div class="row">
                    <div class="mb-5 col-md-4">
                    <label>Nombre de la condición:</label>
                      <input type="text" id="nmb" class="form-control focus mayus" placeholder="Nombre de la condición" name="nmb" value="" required="required" >
                    </div>
                    <div class="mb-5 col-6 col-md-3">  
                      <label>Estatus</label>';
                      echo '
                      <input class= "flipswitch form-control" type="checkbox" name="estatus" id="estatus">
                    </div>
                    <div class="mb-5 col-6 col-md-3">  
                      <label>Obligatorio</label>';
                      echo '
                      <input class= "flipswitch2 form-control" type="checkbox" name="obliga" id="obliga">
                    </div>
                    <div class="mb-5 col-md-2 mt-5">
                    
                      <button type="submit" class="btn btn-primary" ><i class="fas fa-plus"></i> Añadir
                    </div>
                  </div>
                </form>';
        $sql = 'SELECT * FROM condicionesc';
        $result = setq($sql);
    
        if ($result->num_rows > 0) {
          echo '
          <div class="col-md-12 center">
          <table width="100%" class="table table-stripped table-bordered table-hover mt-5">
          <thead class="bg-primary text-white">
            <tr>
              <td style="width:40%;">Descripción</td>
              <td style="width:15%;">Estatus</td>
              <td style="width:15%;">Obligatorio</td>
              <td style="width:30%;">Actualizar/Borrar</td>
            </tr>
          </thead>
          <tbody>';
            while ($row = $result->fetch_array()) {
              if($row['c_estatus'] == "A") $checked = "checked";
              else $checked = "";
              if($row['c_obligatorio'] == "1") $checked2 = "checked";
              else $checked2 = "";
              echo '
                <form autocomplete="off" name="updregion" id="updregion" action="?modulo=configespeciales&accion=updatecondicion&id='.$row['c_id'].'" method="post">
                <tr>
                <td style="width:40%;">
                  <input type="text" name="nmb" id="nmb" value="'.$row['c_nmb'].'" placeholder="Descripción de la condición" class="form-control" required>
                </td>
                <td style="width:15%;">
                  <input type="checkbox" name="estatus" id="estatus" class="form-control flipswitch2" '.$checked.'>
                </td>
                <td style="width:15%;">
                  <input type="checkbox" name="obliga" id="obliga" class="form-control flipswitch2" '.$checked2.'>
                </td> 
                <td style="width:30%;">
                  <button type="submit" class="btn-sm btn-primary">
                    <i class="fas fa-redo" style="color:#ffffff"></i>
                  </button>
                  <button type="button" class="btn-sm btn-danger" onclick="delcondicion(' . $row['c_id'] . ');">
                    <i class="fas fa-times-circle" style="color:#ffffff"></i>
                  </button>
                </td>   
              </tr> 
              </form>
              ';
          }
        }
    echo '</tbody>
              </table>  
              <script>
              function delcondicion(id){  
                var conf = confirm("¿Estas seguro que deseas eliminar la condición seleccionada?");
                if(conf == true){
                  window.location.href="?modulo=configespeciales&accion=deletecondicion&id="+id+"";
                }
              }
              </script>
              
              <br><br>
              </div>
            </div>
            </div>
          </center>';


              echo '<center>
        <div id="divcategorias" class="main-card mb-3 card mt-5"  ' . $showcategorias . '>
              <form autocomplete="off" name="insregion" id="insregion" action="?modulo=configespeciales&accion=insertcategoria" method="post" onsubmit="checkguardar();">
                <div class="card-body center-form">
                <div class="col-12 col-md-8">
                  <div>
                    <h5 class="card-title p-1">Configuración de categorías al perder un lead</h5>
                  </div> 
                  <div class="row">
                    <div class="mb-5 col-8 col-md-6">
                    <label>Nombre de la condición:</label>
                      <input type="text" id="nmb" class="form-control focus mayus" placeholder="Nombre de la condición" name="nmb" value="" required="required" >
                    </div>
                    <div class="mb-5 col-2 col-md-2 mt-6">
                      <button type="submit" class="btn btn-primary" ><i class="fas fa-plus"></i> Añadir
                    </div>
                  </div>
                </form>';
        $sql = 'SELECT * FROM catalogo_perdidos';
        $result = setq($sql);
    
        if ($result->num_rows > 0) {
          echo '
          <div class="col-md-12 center">
          <table width="100%" class="table table-stripped table-bordered table-hover mt-5">
          <thead class="bg-primary text-white">
            <tr>
              <td style="width:40%;">Descripción</td>
              <td style="width:30%;">Estatus</td>
              <td style="width:30%;">Actualizar/Borrar</td>
            </tr>
          </thead>
          <tbody>';
            while ($row = $result->fetch_array()) {
              if($row['cp_estatus'] == "A") $checked = "checked";
              else $checked = "";
              echo '
                <form autocomplete="off" name="updregion" id="updregion" action="?modulo=configespeciales&accion=updatecategoria&id='.$row['cp_id'].'" method="post">
                <tr>
                <td style="width:40%;">
                  <input type="text" name="nmb" id="nmb" value="'.$row['cp_nmb'].'" placeholder="Descripción de la condición" class="form-control" required>
                </td>
                <td style="width:30%;">
                  <input type="checkbox" name="estatus" id="estatus" class="form-control flipswitch2" '.$checked.'>
                </td>
                <td style="width:30%;">
                  <button type="submit" class="btn-sm btn-primary">
                    <i class="fas fa-redo" style="color:#ffffff"></i>
                  </button>
                  <button type="button" class="btn-sm btn-danger" onclick="delcategoria(' . $row['cp_id'] . ');">
                    <i class="fas fa-times-circle" style="color:#ffffff"></i>
                  </button>
                </td>   
              </tr> 
              </form>
              ';
          }
        }
    echo '</tbody>
              </table>  
              <script>
              function delcategoria(id){  
                var conf = confirm("¿Estas seguro que deseas eliminar la categoría seleccionada?");
                if(conf == true){
                  window.location.href="?modulo=configespeciales&accion=deletecategoria&id="+id+"";
                }
              }
              </script>
              
              <br><br>
              </div>
            </div>
            </div>
          </center>';

          //INICIA PANEL DE TARIFAS
          echo '<center>
          <div id="divtarifas" class="main-card mb-3 card mt-5" ' . $showtarifas . '>
                <form autocomplete="off" name="insregion" id="insregion" action="?modulo=configespeciales&accion=inserttarifa" method="post" onsubmit="checkguardar();">
                  <div class="card-body center-form">
                  <div class="col-12 col-md-12">
                    <div>
                      <h5 class="card-title p-1">Configuración de tarifas de envío</h5>
                    </div> 
                    <div class="row">
                      <div class="mb-5 col-8 col-md-3">
                      <label>Nombre de la tarifa:</label>
                        <input type="text" id="nmb" class="form-control focus mayus" placeholder="Nombre de la tarifa" name="nmb" value="" required="required" >
                      </div>
                      <div class="mb-5 col-8 col-md-2">
                        <label>Costo</label>
                        <input type="number" name="costo" id="costo" min="1" value="1" placeholder="costo de la tarifa" class="form-control" required>
                      </div>
                      <div class="mb-5 col-8 col-md-2">
                      <label>Paqueteria</label>
                      <select class="form-control" name="paqueteria" id="paqueteria">';
                        $sqlpaq = 'SELECT p_id, p_nmb FROM paqueterias';
                        $result = setq($sqlpaq);
                        while($row = $result->fetch_array()){
                          echo '<option value="'.$row['p_id'].'">'.$row['p_nmb'].'</option>';
                        }
                      echo '</select>
                      </div>
                      <div class="mb-5 col-6 col-md-2">
                        <label>Tarifa internacinal</label>
                        <input type="checkbox" name="internacional" id="internacional" class="form-control flipswitch2">
                      </div>
                      <div class="mb-5 col-6 col-md-2">
                        <label>Estatus</label>
                        <input type="checkbox" name="estatus" id="estatus" class="form-control flipswitch2" checked>
                      </div>
                      <div class="mb-5 col-6 col-md-1">
                        <button type="submit" class="btn btn-primary" ><i class="fas fa-plus"></i> Añadir
                      </div>
                    </div>
                  </form>';
          $sql = 'SELECT * FROM tarifas_envios';
          $result = setq($sql);
      
          if ($result->num_rows > 0) {
            echo '
            <div class="col-md-12 center">
            <table width="100%" class="table table-stripped table-bordered table-hover mt-5">
            <thead class="bg-primary text-white">
              <tr>
                <td style="width:20%;">Nombre</td>
                <td style="width:16%;">Costo</td>
                <td style="width:16%;"></td>
                <td style="width:16%;">Internacional</td>
                <td style="width:16%;">Estatus</td>
                <td style="width:16%;">Actualizar/Borrar</td>
              </tr> 
            </thead>
            <tbody>';
              while ($row = $result->fetch_array()) {
                if($row['te_estatus'] == "A") $checked = "checked";
                else $checked = "";
                if($row['te_internacional'] == "1") $checked2 = "checked";
                else $checked2 = "";
                $count= busca($row[' te_id'], 'articulos', 'a_tarifa', 'COUNT(*)');
                echo '
                  <form autocomplete="off" name="updtarifa" id="updtarifa" action="?modulo=configespeciales&accion=updatetarifa&id='.$row['te_id'].'" method="post">
                  <tr>
                  <td>
                    <input type="text" name="nmb" id="nmb" value="'.$row['te_nmb'].'" placeholder="Nombre de referencia a la tarifa" class="form-control" required>
                  </td>
                  <td>
                    <input type="number" name="costo" id="costo" value="'.$row['te_costo'].'" placeholder="costo de la tarifa" class="form-control" required>
                  </td>
                  <td>
                    <select class="form-control" name="paqueteria" id="paqueteria">';
                    $sqlpaq = 'SELECT p_id, p_nmb FROM paqueterias';
                    $resultpaq = setq($sqlpaq);
                    while($rowpaq = $resultpaq->fetch_array()){
                      if($rowpaq['p_id'] == $row['te_paqueteria']) $sel = 'selected';
                      else $sel = '';
                      echo '<option value="'.$rowpaq['p_id'].'" '.$sel.'>'.$rowpaq['p_nmb'].'</option>';
                    }
                  echo '</select>
                  </td>
                  <td>
                    <input type="checkbox" name="internacional" id="internacional" class="form-control flipswitch2" '.$checked2.'>
                  </td>
                  <td>
                    <input type="checkbox" name="estatus" id="estatus" class="form-control flipswitch2" '.$checked.'>
                  </td>
                  
                  <td>
                    <button type="submit" class="btn-sm btn-primary">
                      <i class="fas fa-redo" style="color:#ffffff"></i>
                    </button>';
                    if($count >0){
                      echo '<button type="button" class="btn-sm btn-secondary" disabled>
                        <i class="fas fa-times-circle" style="color:#ffffff"></i>
                      </button>';
                    }else {
                      echo '<button type="button" class="btn-sm btn-danger" onclick="deltarifa(' . $row['te_id'] . ');">
                        <i class="fas fa-times-circle" style="color:#ffffff"></i>
                      </button>';
                    }
                  echo '</td>   
                </tr> 
                </form>
                ';
            }
          }
      echo '</tbody>
                </table>  
                <script>
                function deltarifa(id){  
                  var conf = confirm("¿Estas seguro que deseas eliminar la tarifa seleccionada?");
                  if(conf == true){
                    window.location.href="?modulo=configespeciales&accion=deletetarifa&id="+id+"";
                  }
                }
                </script>
                
                <br><br>
                </div>
              </div>
              </div>
            </center>';
          //FINALIZA PANEL DE TARIFAS


          // INICIA PANEL DEL IMPORTE MÁXIMO PARA ENVIOS GRATIS
          $importe = busca("1", "configuracionesp", "c_id", "c_maximoenvio");
          echo '<center>
          <div id="divimporte" class="main-card mb-3 card mt-5"  ' . $showimporte . '>
                <form autocomplete="off" name="insimporte" id="insimporte" action="?modulo=configespeciales&accion=insertimporte" method="post" onsubmit="checkguardar();">
                  <div class="card-body center-form">
                  <div class="col-12 col-md-6">
                    <div>
                      <h5 class="card-title p-1">Configuración del importe máximo para envíos</h5>
                    </div> 
                    <div class="row d-flex">
                      <div class="col-md-6 col-8 mt-3">
                        <div class="position-relative form-group">
                          <b><label for="nombre" class="">Cantidad en pesos: </label></b>
                          <input type="number" step="0.01" min="0.01" name="importe" id="importe" value="'.$importe.'" placeholder="Cantidad del importe" class="form-control" required>
                        </div>
                      </div>
                      <div class="col-md-2 col-2 mt-9">
                        <div class="position-relative form-group alinear-campos row">
                          <button id="añadir" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> Añadir</button>
                        </div>
                      </div>
                    </div>
                  </form>
              </div>
            </center>';
          //FINALIZA PANEL DEL IMPORTE MÁXIMO PARA ENVIOS GRATIS


        // INICIA PANEL DE META MENSUAL POR VENDEDOR
          echo '
                  <div id="divmeta" class="main-card mb-3 card mt-5"  ' . $showmeta . '>
                  <center>
                  <div class="mt-5">
                    <h5 class="card-title p-1">Configuración de la meta mensual</h5>
                  </div>';

                  $sql = 'SELECT * FROM usuarios_orden INNER JOIN usuarios ON u_id = uo_uid';
                  $result = setq($sql);
                  if ($result->num_rows > 0) {
                    echo '
                    <div class="col-8 center">
                      <table width="100%" class="table table-stripped table-bordered table-hover mt-5">
                        <thead class="bg-primary text-white">
                          <tr>
                            <td style="width:40%;">Vendedor</td>
                            <td style="width:30%;">Meta</td>
                            <td style="width:30%;">Actualizar</td>
                          </tr>
                        </thead>
                        <tbody>';
                          while ($row = $result->fetch_array()) {
                            echo '
                              <form autocomplete="off" name="updregion" id="updregion" action="?modulo=configespeciales&accion=updatemeta&id='.$row['u_id'].'" method="post">
                                  <tr>
                                  <td>'.$row['u_nmb'].' '.$row['u_apellidos'].'</td>
                                  <td style="width:40%;">
                                    <input type="text" name="cantidad" id="cantidad" value="'.$row['uo_metamensual'].'" placeholder="Cantidad de descuento" class="form-control" required>
                                  </td>
                                  <td style="width:30%;">
                                    <button type="submit" class="btn-sm btn-primary">
                                      <i class="fas fa-redo" style="color:#ffffff"></i>
                                    </button>
                                  </td>   
                                </tr> 
                              </form>
                            ';
                          }
                  } 
                  echo '</tbody>
                      </table>  
                      <br><br>
                    </div>
                  </div>
                </div></center>
              </div>';

        // FINALIZA PANEL DE META MENSUAL POR VENDEDOR

          // INICIA PANEL DE META GRUPAL
        $meses = array("01"=>"Enero", "02"=>"Febrero", "03"=>"Marzo", "04"=>"Abril", "05"=>"Mayo", "06"=>"Junio", "07"=>"Julio", "08"=>"Agosto", "09"=>"Septiembre", "10"=>"Octubre", "11"=>"Noviembre", "12"=>"Diciembre");
        $metames = busca(date('m'), 'config_metagrupal','cm_anio = "'.date('Y').'" AND cm_mes', 'cm_meta');

        echo '<div id="divmetagrupo" class="main-card mb-3 card mt-5"  ' . $showmetagrupo . '>
                <center>
                <form autocomplete="off" name="metagrup" id="metagrup" action="?modulo=configespeciales&accion=setmetagrupo" method="post" onsubmit="checkguardar();">
                  <div class="card-body center-form">
                  <div class="col-12 col-md-6">
                    <div>
                      <h5 class="card-title p-1">Configuración de la meta grupal</h5>
                    </div>
                    <div class="row d-flex">
                      <div class="col-md-3 col-4 mt-3">
                        <div class="position-relative form-group">
                          <b><label for="nombre" class="">Mes</label></b>
                          '.menu_select_array($meses,date('m'),'mes').'
                        </div>
                      </div>
                      <div class="col-md-3 col-4 mt-3">
                        <div class="position-relative form-group">
                          <b><label for="nombre" class="">Año</label></b>
                          <select name="anio" id="anio" class="form-control" required>';
                          $ite = 0;
                          for($i=date('y');$i<=date('y',strtotime('+ 2 years'));$i++){
                            $anii = date('Y',strtotime('+ '.$ite.' years'));
                            echo '<option value="'.$i.'">'.$anii.'</option>';
                            $ite++;
                          }
                          echo '</select>
                        </div>
                      </div>

                      <div class="col-md-6 col-8 mt-3">
                        <div class="position-relative form-group">
                          <b><label for="nombre" class="">Cantidad en pesos: </label></b>
                          <input type="number" step="0.01" min="0.01" name="metagrupo" id="metagrupo" value="'.$metames.'" placeholder="Cantidad del importe" class="form-control" required>
                        </div>
                      </div>
                      <div class="col-md-2 col-2 mt-9">
                        <div class="position-relative form-group alinear-campos row">
                          <button id="Definir meta" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> Añadir</button>
                        </div>
                      </div>
                    </div>
                  </form>
            </center>
        </div>';
          //FINALIZA PANEL DE META GRUPAL

         //INICIA PANEL DE COMISONES A TARJETAS DE CRÉDITO
          echo '<center>
        <div id="divcomision" class="main-card mb-3 card mt-5"  ' . $showcomisiones . '>
              <form autocomplete="off" name="inscomision" id="inscomision" action="?modulo=configespeciales&accion=insertcomision" method="post" onsubmit="checkguardar();">
                <div class="card-body center-form">
                <div class="col-12 col-md-8">
                  <div>
                    <h5 class="card-title p-1">Configuración de las comisiones al pagar con tarjeta de crédito</h5>
                  </div> 
                  <div class="row">
                    <div class="mb-5 col-3 col-md-3">
                    <label>Exhibiciones:</label>
                      <input type="text" id="exhibiciones" class="form-control focus" placeholder="Numero de exhibiciones de pago" name="exhibiciones" value="" required="required" >
                    </div>
                    <div class="mb-5 col-3 col-md-3">
                    <label>Porcentaje:</label>
                      <input type="text" id="porcentaje" class="form-control" placeholder="Porcentaje de comisión" name="porcentaje" value="" required="required" >
                    </div>
                    <div class="mb-5 col-4 col-md-4">
                    <label>Descripción:</label>
                      <input type="text" id="descripcion" class="form-control" placeholder="Descripción de la comisión" name="descripcion" value="" required="required" >
                    </div>
                    <div class="mb-5 col-2 col-md-2 mt-2">
                      <button type="submit" class="btn btn-primary" ><i class="fas fa-plus"></i> Añadir
                    </div>
                  </div>
                </form>';
        $sql = 'SELECT * FROM comisiones_tarjeta';
        $result = setq($sql);
    
        if ($result->num_rows > 0) {
          echo '
          <div class="col-md-12 center">
          <table width="100%" class="table table-stripped table-bordered table-hover mt-5">
          <thead class="bg-primary text-white">
            <tr>
              <td style="width:40%;">Exhibiciones</td>
              <td style="width:40%;">Porcentaje</td>
              <td style="width:30%;">Descripción</td>
              <td style="width:30%;">Estatus</td>
              <td style="width:30%;">Actualizar/Borrar</td>
            </tr>
          </thead>
          <tbody>';
            while ($row = $result->fetch_array()) {
              if($row['cta_estatus'] == "A") $checked = "checked";
              else $checked = "";
              echo '
                <form autocomplete="off" name="updcomision" id="updcomision" action="?modulo=configespeciales&accion=updatecomision&id='.$row['cta_id'].'" method="post">
                <tr>
                <td style="width:40%;">
                  <input type="text" name="exhibiciones" id="exhibiciones" value="'.$row['cta_exhibiciones'].'" placeholder="Número de exhibiciones de la comisión" class="form-control" required>
                </td>
                <td style="width:40%;">
                  <input type="text" name="porcentaje" id="porcentaje" value="'.$row['cta_porcentaje'].'" placeholder="Porcentaje de la comisión" class="form-control" required>
                </td>
                <td style="width:30%;">
                  <textarea name="descripcion" id="descripcion" class="form-control flipswitch2">'.$row['cta_descripcion'].'</textarea>
                </td>
                <td style="width:30%;">
                  <input type="checkbox" name="estatus" id="estatus" class="form-control flipswitch2" '.$checked.'>
                </td>
                <td style="width:30%;">
                  <button type="submit" class="btn-sm btn-primary">
                    <i class="fas fa-redo" style="color:#ffffff"></i>
                  </button>
                  <button type="button" class="btn-sm btn-danger" onclick="delcomision(' . $row['cta_id'] . ');">
                    <i class="fas fa-times-circle" style="color:#ffffff"></i>
                  </button>
                </td>   
              </tr> 
              </form>
              ';
          }
        }
      echo '</tbody>
              </table>  
              <script>

              function delcomision(id){
                Swal.fire({
                  title: "Atención",
                  text: "¿Estas seguro que deseas eliminar la comisión seleccionada?",
                  icon: "question",
                  showCancelButton: true,
                  confirmButtonColor: "#3085d6",
                  cancelButtonColor: "#d33",
                  confirmButtonText: "Estoy seguro",
                  cancelButtonText: "Cancelar"
                }).then((result) => {
                  if (result.isConfirmed) {
                    window.location.href="?modulo=configespeciales&accion=deletecomision&id="+id+"";
                  }
                })
              }
              </script>
              
              <br><br>
              </div>
            </div>
            </div>
          </center>';
        //FINALIZA PANEL DE COMISONES A TARJETAS DE CRÉDITO

        // INICIA PANEL DE TEXTO EN ENVIO DE GUIAS
        echo '<div id="divtextoguia" class="main-card mb-3 card mt-5"  ' . $showtextoguia . '>
                <center>
                <form autocomplete="off" name="textoguia" id="textoguia" action="?modulo=configespeciales&accion=settextoguia" method="post" onsubmit="checkguardar();">
                  <div class="card-body center-form">
                  <div class="col-12 col-md-6">
                    <div>
                      <h5 class="card-title p-1">Texto para envío de guias</h5>
                    </div>
                    <div class="row d-flex">
                      <div class="col-md-12 col-12 mt-3">
                        <label>Texto superior: </label
                        <div class="position-relative form-group">
                          <textarea name="textoguias" rows="10" id="textoguias" placeholder="Texto superior para envío de guias" class="form-control">'.busca('1','configuracionesp','c_id','c_textoguias').'</textarea>
                        </div>
                      </div>
                      <div class="col-md-12 col-12 mt-3">
                        <label>Texto inferior: </label
                        <div class="position-relative form-group">
                          <textarea name="textoguias2" rows="10" id="textoguias2" placeholder="Texto inferior para envío de guias" class="form-control">'.busca('1','configuracionesp','c_id','c_textoguias2').'</textarea>
                        </div>
                      </div>
                      <div class="col-md-6 col-6 mt-9">
                        <div class="position-relative form-group alinear-campos row">
                          <button id="Definir meta" class="btn btn-sm btn-primary"><i class="fas fa-sync"></i> Actualizar</button>
                        </div>
                      </div>
                    </div>
                  </form>
            </center>
        </div>';
          //FINALIZA PANEL TEXTO EN GUIAS

        // INICIA PANEL DE TEXTO EN TICKETS
        echo '<div id="divtextoticket" class="main-card mb-3 card mt-5"  ' . $showtextoticket. '>
                <center>
                <form autocomplete="off" name="textoticket" id="textoticket" action="?modulo=configespeciales&accion=settextoticket" method="post" onsubmit="checkguardar();">
                  <div class="card-body center-form">
                    <div class="col-12 col-md-6">
                      <div>
                        <h5 class="card-title p-1">Texto al pie de la impresión de ticket</h5>
                      </div>
                      <div class="row d-flex">
                        <div class="col-md-12 col-12 mt-3">
                          <div class="position-relative form-group">
                            <textarea name="textotickets" rows="2" id="textotickets" placeholder="Texto para envío de ticket" class="form-control">'.busca('1','configuracionesp','c_id','c_textoticket').'</textarea>
                          </div>
                        </div>

                        <div class="col-md-12 col-12 mt-9">
                          <div class="position-relative form-group alinear-campos row">
                            <button id="Definir meta" class="btn btn-sm btn-primary"><i class="fas fa-sync"></i> Actualizar</button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </form>
            </center>
        </div>';
          //FINALIZA PANEL TICKETS

        // INICIA PANEL DE DIAS DE GARANTÍA
        $dgarantia = busca("1", "configuracionesp", "c_id", "c_dgarantia");
        echo '<center>
        <div id="divdiasgarantia" class="main-card mb-3 card mt-5"  ' . $showgarantia . '>
              <form autocomplete="off" name="insgarantia" id="insgarantia" action="?modulo=configespeciales&accion=insertdgarantia" method="post" onsubmit="checkguardar();">
                <div class="card-body center-form">
                <div class="col-12 col-md-6">
                  <div>
                    <h5 class="card-title p-1">Configuración del número de días de garantía de artículos</h5>
                  </div> 
                  <div class="row d-flex">
                    <div class="col-md-6 col-8 mt-3">
                      <div class="position-relative form-group">
                        <b><label for="dias" class="">Días: </label></b>
                        <input type="number" step="1" min="1" name="dias" id="dias" value="'.$dgarantia.'" placeholder="Tiempo de garantía en días" class="form-control" required>
                      </div>
                    </div>
                    <div class="col-md-2 col-2 mt-9">
                      <div class="position-relative form-group alinear-campos row">
                        <button id="añadird" class="btn btn-sm btn-primary"><i class="fas fa-save"></i> Guardar</button>
                      </div>
                    </div>
                  </div>
                </form>
            </div>
          </center>';
        //FINALIZA PANEL DE DIAS DE GARANTÍA
      

        // INICIO DE LA CONFIGURACIÓN DE COMISIONES
        echo '<center>
        <div id="divcomisionesventas" class="main-card mb-3 card mt-5"  ' . $showcomisionesventa . '>
            <form autocomplete="off" name="comisionventa" id="comisionventa" action="?modulo=configespeciales&accion=insertcomisionventa" method="post" onsubmit="checkguardar();">
              <div class="card-body center-form">
              <div class="col-12 col-md-10">
                <div>
                  <h5 class="card-title p-1">Configuración de comisiones por venta</h5>
                </div> 
                <div class="row">
                  <div class="mb-5 col-md-2">
                    <label>Nombre de la comision:</label>
                    <input type="text" id="nmb" class="form-control focus mayus" placeholder="Nombre de la comisión" name="nmb" value="" required="required" >
                  </div>
                  <div class="mb-5 col-md-3">
                    <label>Cantidad a repartir:</label>
                    <input type="number" id="repartir" class="form-control" placeholder="Cantidad a repartir" name="repartir" value="0" min="1" required="required" >
                  </div>
                  <div class="mb-5 col-md-3">
                    <label>Meta por alcanzar:</label>
                    <input type="number" id="meta" class="form-control" placeholder="Meta por alcanzar" name="meta" value="0" min="1" required="required" >
                  </div>
                  <div class="mb-5 col-6 col-md-2">  
                    <label>Estatus</label>
                    <input class= "flipswitch form-control" type="checkbox" name="estatus" id="estatus" checked>
                  </div>
                  <div class="mb-5 col-md-2 mt-5">
                    <button type="submit" class="btn btn-primary" ><i class="fas fa-plus"></i> Añadir
                  </div>
                </div>
              </form>';
        $sql = 'SELECT * FROM config_comisiones';
        $result = setq($sql);
    
        if ($result->num_rows > 0) {
          echo '
          <div class="col-md-12 center">
          <table width="100%" class="table table-stripped table-bordered table-hover mt-5">
          <thead class="bg-primary text-white">
            <tr>
              <td style="width:30%;">Nombre</td>
              <td style="width:20%;">Monto a repartir</td>
              <td style="width:20%;">Meta</td>
              <td style="width:10%;">Estatus</td>
              <td style="width:20%;">Actualizar/Borrar</td>
            </tr>
          </thead>
          <tbody>';
            while ($row = $result->fetch_array()) {
              if($row['cc_estatus'] == "A") $checked = "checked";
              else $checked = "";
              echo '
                <form autocomplete="off" name="updcomision" id="updregion" action="?modulo=configespeciales&accion=updatecomisionventa&id='.$row['cc_id'].'" method="post">
                <tr>
                <td style="width:30%;">
                  <input type="text" name="nmb" id="nmb" value="'.$row['cc_nmb'].'" placeholder="Descripción de la condición" class="form-control" required>
                </td>
                <td style="width:20%;">
                  <input type="number" id="repartir" class="form-control" placeholder="Cantidad a repartir" name="repartir" value="'.$row['cc_montorepartido'].'" min="1" required="required" >
                </td>
                <td style="width:20%;">
                  <input type="number" id="meta" class="form-control" placeholder="Meta por alcanzar" name="meta" value="'.$row['cc_metaventa'].'" min="1" required="required" >
                </td>
                <td style="width:10%;">
                  <input type="checkbox" name="estatus" id="estatus" class="form-control flipswitch" '.$checked.'>
                </td>
                <td style="width:20%;">
                  <button type="submit" class="btn-sm btn-primary">
                    <i class="fas fa-redo" style="color:#ffffff"></i>
                  </button>
                  <button type="button" class="btn-sm btn-danger" onclick="delcondicion(' . $row['cc_id'] . ');">
                    <i class="fas fa-times-circle" style="color:#ffffff"></i>
                  </button>
                </td>   
              </tr> 
              </form>
              ';
          }
        } else {
          echo '<div class="alert alert-danger">
            No se han registrado datos
          </div>';
        }
    echo '</tbody>
              </table>  
              <script>
              function delcondicion(id){  
                var conf = confirm("¿Estas seguro que deseas eliminar la comisión seleccionada?");
                if(conf == true){
                  window.location.href="?modulo=configespeciales&accion=deletecomisionventa&id="+id+"";
                }
              }
              </script>
              
              <br><br>
              </div>
              <form autocomplete="off" name="updminimoventa" id="updregion" action="?modulo=configespeciales&accion=updateminimoventa" method="post">
                <div class="row">
                <div class="col-6 col-md-4"><label>Cantidad mínima de artículos: </label><input type="number" name="ventaminima" id="ventaminima" value="'.$this->model->articulosminimos.'" class="form-control" min="1"></div>
                <div class="col-6 col-md-4"><label>Comisión por inflable: </label><input type="number" name="comision" id="comision" value="'.$this->model->comision.'" class="form-control" min="1"></div>
                <div class="col-3 col-md-2"><button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save"></i>Guardar</button></div>
                </div>
              </form>
            </div>
            </div>
          </center>';
    // FIN DE LA CONFIGURACIÓN DE COMISIONES
  }
}
?>