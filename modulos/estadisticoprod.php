<?php
ini_set('display_errors',1);
class estadisticoprod{
  var $model;
  var $view;

  function __construct(){
    $this->model = new modelestadisticoprod(isset($obj));
  }

  function index(){
    $this->model->result();
    $this->view = new viewestadisticoprod($this->model);
    $this->view->browse();
  }

  function insertarprod(){
    
    $this->model->setdata($_POST['modelo'],$_POST['asin'],$_POST['descripcion'],$_POST['idprod']);
    $this->model->insertarprod();

    redirect('?modulo=estadisticoprod&accion=index');
  }

  function actualizaprod(){

    $this->model->result();
    $this->model->actualizaprod();
    redirect('?modulo=estadisticoprod&accion=index');

  }

  function eliminarprod(){
    $idprod = $_GET['id'];
    $this->model->eliminarprod($idprod);
    redirect('?modulo=estadisticoprod&accion=index');

  }

  function edit(){
    $idprod = $_GET['id'];
    $this->model->select($idprod);
    $this->view = new viewestadisticoprod($this->model);
    $this->view->edit();
  }

  function update(){
    $this->model->update($_GET['id'],$_POST['asin']);
    redirect('?modulo=estadisticoprod&accion=index');
  }

}

class modelestadisticoprod{


function setdata($modelo,$asin,$descripcion,$idprod){

  $this->modelo = clearvmayus($modelo);
  $this->asin = clearvmayus($asin);
  $this->descripcion = clearvmayus($descripcion);
  $this->idprod = clearvmayus($idprod);


}

function update($id,$asin){
  $sql = 'UPDATE estadisticoprod SET
          e_asin = "'.$asin.'"
          WHERE e_id = "'.$id.'"';

  setq($sql);

}

function insertarprod(){

  if($_POST['idprod']){
    $idprodbus = $_POST['idprod'];
    $dolartye = busca(1,'empresas','e_id','e_valorusd');
    $urltoken = "https://developers.syscom.mx/oauth/token";
    //$token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjkxODllZjE1ZDk0YjA5MDdlYTUwYTdkMTMxMDJjNzgxY2Q1NTE0ODEyZDBlY2YwYjRlOTQ5ZmNiYzU3MWY1NjQ5ZjRkM2RmMjFhZWU4ZmNmIn0.eyJhdWQiOiJPMmRwN0dvTTBCMndVTHVwOXNDeUhmS2VJWDN6WWZkbCIsImp0aSI6IjkxODllZjE1ZDk0YjA5MDdlYTUwYTdkMTMxMDJjNzgxY2Q1NTE0ODEyZDBlY2YwYjRlOTQ5ZmNiYzU3MWY1NjQ5ZjRkM2RmMjFhZWU4ZmNmIiwiaWF0IjoxNjMxMjk5MDUzLCJuYmYiOjE2MzEyOTkwNTMsImV4cCI6MTY2MjgzNTA1Mywic3ViIjoiIiwic2NvcGVzIjpbXX0.SKeZK_Vmg9QbZZb-nIt8-yXFKnwXndKQ3VBHxqfX4-ynJIhxbKLLWDAYptsgHLx98byVSWTLK-xfaj5RkUhvt4FRuF3bl4lfdDdUPdgSJTfP1c6ojPBxsThwB0sCUqaeYd0ZhdY0YAIWoobJrpvLeQJbX_3PbdMYITFpiur1CuAAFdf79bJpoquDMgIyQssKQ8LUX8iDqAKgMsa7O_Y3SdMsYkABGgHpA7CX9Sia5YHGk-JrkZc5E26rLY9zsASlAKSe7Bh2tv04HCIWdH9Y6YVvL2TBJwHxvE-Rp-l9JNqZUoqZpwq2G2J6LUpSjEuouZ4mlPhVwbvdRVgHu1LRGKCZxKEc87nCf9MGDrfzu9NaDtrFAXk92xJFR0a5DEM1zJmHXzLFEgJAOTw41rsDGUr-p8dzq5OsbxrvsCBvEDDpGFecR55hCgdsnhDVuZNKWteCcJ01w3-PV2hnwIy1nzAMs4T5h7f9N5CDPSOpFuiEcyxpSmoiNEE-i_zmHfm0R-LbuV5AM8cMM1eXkrkXv_EyKSexaGCruyburhWhu43Uyttc2Z2B3rs9JRvufyh8TtUCc88HEgQZKghxIhQKjK8q_TQmqOMHSN4lbsHKfAUfQsPGFCgUAe-ajbdkhSToyf_NRAgSZOV9zucEOhFHoJoJCGHZWSl-Dg_692DJNEU';
    $token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImNjNDNhZTRmNzdlNDdiYzgwNDBkYzliNDVjNDU3ZTMzMWU4YjI2YWM1MGQ1ZjIyOTU2MmQ4YWI5MzcwOGY3NDYyNjVjOWUyNzBkZjY0MDMyIn0.eyJhdWQiOiJPMmRwN0dvTTBCMndVTHVwOXNDeUhmS2VJWDN6WWZkbCIsImp0aSI6ImNjNDNhZTRmNzdlNDdiYzgwNDBkYzliNDVjNDU3ZTMzMWU4YjI2YWM1MGQ1ZjIyOTU2MmQ4YWI5MzcwOGY3NDYyNjVjOWUyNzBkZjY0MDMyIiwiaWF0IjoxNjYzMDA0MzQwLCJuYmYiOjE2NjMwMDQzNDAsImV4cCI6MTY5NDU0MDM0MCwic3ViIjoiIiwic2NvcGVzIjpbXX0.Bntz-zDZaQF7HkZH7NnPzG2_U87NOL-UYE8ZuwJQwXJGrO7LRiWYapFJE7_cQsiQtP9JL-TB_gY9TXgYysxYLvSn8OsesAFbw2lb9wsgpTIVrQ5lfllY8STYPxsLB2I-eu6x1uyGPi-IofZKgOG8-LgyY0aVDK8MZn8chfcnZXSPsEYIFwDrtY5oEy0iYbBxbaYVKVRfX2EZZFH-kxhsF7P7yzdBnGwyoss4XNPH9VMmH7wBJEfjdJskmNjEymb0pxJbITBslNzBVsRTicNPR0v4y_k9d1cEvyVpqXerobQVW-R7dGP16uIU-v8u8Qm5qi-dbn7Ud3FGvY7CG5wdEvYpbe-1t7OklaPvw25rBL1psW0GzL8QACibhVbWOHdRQpP0Og8lVrpy2kB4lIay8M37KTqifyR7iTys2ILSEE5ge1EhyjhTBNVm8nHhFHJw43Hd6lcmc8cDIoW_Uq8bbFOAaK8MTq0aamwY9nP-Ui_RD9Ucwsb5PJPK4Y3pExqD5ZwzJYMMrmC7JpbnFI5_RCwbWo6IpaikCGTlS3ayrE18D6JbCK3IctqNN622diUp6jEasaUe7v8phexZQOpf63cS1K73Bd4TNC6uX4KJ3TCYwnXvIObxYgiNH6ZW8QUTYuD3DFudPvM8bVAu0ShuwRywJTu2wwBtN_TW0MQtRpY';
    $url = "https://developers.syscom.mx/api/v1/productos/".$idprodbus."?inventarios=1";
    
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  
    $headers = array(
      "Accept: application/json",
      "Authorization: Bearer {$token}",
    );
  
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    //for debug only!
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
  
    $resp = curl_exec($curl);
    curl_close($curl);
    $respuesta = json_decode($resp, true);
    if($respuesta == NULL) {
      echo '<script> alert("ERROR: Verifica el modelo del producto o busca el producto por ID"); </script>';
    }else{
      $sql = 'SELECT * FROM estadisticoprod WHERE e_modelo = "'.$respuesta['modelo'].'"';
      $result = setq($sql);
      if($result->num_rows > 0) { echo '<script> alert("El producto ingresado ya existe en la lista"); </script>'; }
      else {
        $estatus = "N";
        $preciotye = ($respuesta['precios']['precio_descuento']*0.96);
        //die(var_dump($respuesta).' - '.$preciotye);
    
        if($preciotye != 0){
    
          $usd = 1;
          $costoi = $preciotye*$dolartye;
          $costousd = $preciotye;
          $costosiva = $costoi;
          $costo = $costoi*1.16;
          if(($costo+200) < 2000) $envio = 200; else $envio = 0;
    
        }
        
        $sql = 'INSERT INTO estadisticoprod SET
        e_modelo = "'.$respuesta['modelo'].'",
        e_asin = "'.$this->asin.'",
        e_descripcion = "'.str_replace('"','\"',$respuesta['titulo']).'",
        e_precio1 = "'.$preciotye.'",
        e_fecha1 = "'.date('Y-m-d H:i:s').'",
        e_img = "'.$respuesta['img_portada'].'",
        e_estatus = "'.$estatus.'",
        e_idprod = "'.$respuesta['producto_id'].'"';
        setq($sql);
    
        if($respuesta['total_existencia'] <= 100){
            echo '
            <script>
            alert("Pocas existencias del modelo ingresados");
            </script>';
        }      
      }
    }
  }else{
    $sql = 'SELECT * FROM `articulos` INNER JOIN articulo_proveedor ON ap_articulo = a_id WHERE `a_modelo` = "'.$this->modelo.'" ORDER BY `a_modelo` DESC';
    $result = setq($sql);
    $rowid = $result->fetch_array();
    $idprodbus = $rowid['ap_idproducto'];
    $dolartye = busca(1,'empresas','e_id','e_valorusd');
    $urltoken = "https://developers.syscom.mx/oauth/token";
    //$token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjkxODllZjE1ZDk0YjA5MDdlYTUwYTdkMTMxMDJjNzgxY2Q1NTE0ODEyZDBlY2YwYjRlOTQ5ZmNiYzU3MWY1NjQ5ZjRkM2RmMjFhZWU4ZmNmIn0.eyJhdWQiOiJPMmRwN0dvTTBCMndVTHVwOXNDeUhmS2VJWDN6WWZkbCIsImp0aSI6IjkxODllZjE1ZDk0YjA5MDdlYTUwYTdkMTMxMDJjNzgxY2Q1NTE0ODEyZDBlY2YwYjRlOTQ5ZmNiYzU3MWY1NjQ5ZjRkM2RmMjFhZWU4ZmNmIiwiaWF0IjoxNjMxMjk5MDUzLCJuYmYiOjE2MzEyOTkwNTMsImV4cCI6MTY2MjgzNTA1Mywic3ViIjoiIiwic2NvcGVzIjpbXX0.SKeZK_Vmg9QbZZb-nIt8-yXFKnwXndKQ3VBHxqfX4-ynJIhxbKLLWDAYptsgHLx98byVSWTLK-xfaj5RkUhvt4FRuF3bl4lfdDdUPdgSJTfP1c6ojPBxsThwB0sCUqaeYd0ZhdY0YAIWoobJrpvLeQJbX_3PbdMYITFpiur1CuAAFdf79bJpoquDMgIyQssKQ8LUX8iDqAKgMsa7O_Y3SdMsYkABGgHpA7CX9Sia5YHGk-JrkZc5E26rLY9zsASlAKSe7Bh2tv04HCIWdH9Y6YVvL2TBJwHxvE-Rp-l9JNqZUoqZpwq2G2J6LUpSjEuouZ4mlPhVwbvdRVgHu1LRGKCZxKEc87nCf9MGDrfzu9NaDtrFAXk92xJFR0a5DEM1zJmHXzLFEgJAOTw41rsDGUr-p8dzq5OsbxrvsCBvEDDpGFecR55hCgdsnhDVuZNKWteCcJ01w3-PV2hnwIy1nzAMs4T5h7f9N5CDPSOpFuiEcyxpSmoiNEE-i_zmHfm0R-LbuV5AM8cMM1eXkrkXv_EyKSexaGCruyburhWhu43Uyttc2Z2B3rs9JRvufyh8TtUCc88HEgQZKghxIhQKjK8q_TQmqOMHSN4lbsHKfAUfQsPGFCgUAe-ajbdkhSToyf_NRAgSZOV9zucEOhFHoJoJCGHZWSl-Dg_692DJNEU';
    $token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImNjNDNhZTRmNzdlNDdiYzgwNDBkYzliNDVjNDU3ZTMzMWU4YjI2YWM1MGQ1ZjIyOTU2MmQ4YWI5MzcwOGY3NDYyNjVjOWUyNzBkZjY0MDMyIn0.eyJhdWQiOiJPMmRwN0dvTTBCMndVTHVwOXNDeUhmS2VJWDN6WWZkbCIsImp0aSI6ImNjNDNhZTRmNzdlNDdiYzgwNDBkYzliNDVjNDU3ZTMzMWU4YjI2YWM1MGQ1ZjIyOTU2MmQ4YWI5MzcwOGY3NDYyNjVjOWUyNzBkZjY0MDMyIiwiaWF0IjoxNjYzMDA0MzQwLCJuYmYiOjE2NjMwMDQzNDAsImV4cCI6MTY5NDU0MDM0MCwic3ViIjoiIiwic2NvcGVzIjpbXX0.Bntz-zDZaQF7HkZH7NnPzG2_U87NOL-UYE8ZuwJQwXJGrO7LRiWYapFJE7_cQsiQtP9JL-TB_gY9TXgYysxYLvSn8OsesAFbw2lb9wsgpTIVrQ5lfllY8STYPxsLB2I-eu6x1uyGPi-IofZKgOG8-LgyY0aVDK8MZn8chfcnZXSPsEYIFwDrtY5oEy0iYbBxbaYVKVRfX2EZZFH-kxhsF7P7yzdBnGwyoss4XNPH9VMmH7wBJEfjdJskmNjEymb0pxJbITBslNzBVsRTicNPR0v4y_k9d1cEvyVpqXerobQVW-R7dGP16uIU-v8u8Qm5qi-dbn7Ud3FGvY7CG5wdEvYpbe-1t7OklaPvw25rBL1psW0GzL8QACibhVbWOHdRQpP0Og8lVrpy2kB4lIay8M37KTqifyR7iTys2ILSEE5ge1EhyjhTBNVm8nHhFHJw43Hd6lcmc8cDIoW_Uq8bbFOAaK8MTq0aamwY9nP-Ui_RD9Ucwsb5PJPK4Y3pExqD5ZwzJYMMrmC7JpbnFI5_RCwbWo6IpaikCGTlS3ayrE18D6JbCK3IctqNN622diUp6jEasaUe7v8phexZQOpf63cS1K73Bd4TNC6uX4KJ3TCYwnXvIObxYgiNH6ZW8QUTYuD3DFudPvM8bVAu0ShuwRywJTu2wwBtN_TW0MQtRpY';
    $url = "https://developers.syscom.mx/api/v1/productos/".$idprodbus."?inventarios=1";
    
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  
    $headers = array(
      "Accept: application/json",
      "Authorization: Bearer {$token}",
    );
  
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    //for debug only!
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
  
    $resp = curl_exec($curl);
    curl_close($curl);
    $respuesta = json_decode($resp, true);
    if($respuesta == NULL) {
      echo '<script> alert("ERROR: Verifica el modelo del producto o busca el producto por ID"); </script>';
    }else{
      $sql = 'SELECT * FROM estadisticoprod WHERE e_modelo = "'.$this->modelo.'"';
      $result = setq($sql);
      if($result->num_rows > 0) { echo '<script> alert("El producto ingresado ya existe en la lista"); </script>'; }
      else {
        $estatus = "N";
        $preciotye = ($respuesta['precios']['precio_descuento']*0.96);
        //die(var_dump($respuesta).' - '.$preciotye);
    
        if($preciotye != 0){
    
          $usd = 1;
          $costoi = $preciotye*$dolartye;
          $costousd = $preciotye;
          $costosiva = $costoi;
          $costo = $costoi*1.16;
          if(($costo+200) < 2000) $envio = 200; else $envio = 0;
    
        }
        
        $sql = 'INSERT INTO estadisticoprod SET
        e_modelo = "'.$respuesta['modelo'].'",
        e_asin = "'.$this->asin.'",
        e_descripcion = "'.str_replace('"','\"',$respuesta['titulo']).'",
        e_precio1 = "'.$preciotye.'",
        e_fecha1 = "'.date('Y-m-d H:i:s').'",
        e_img = "'.$respuesta['img_portada'].'",
        e_estatus = "'.$estatus.'",
        e_idprod = "'.$respuesta['producto_id'].'"';
        setq($sql);
    
        if($respuesta['total_existencia'] <= 100){
            echo '
            <script>
            alert("Pocas existencias del modelo ingresados");
            </script>';
        }
      }
    }

}
}


function actualizaprod(){
  
  //die(var_dump($this->result));
  $dolartye = busca(1,'empresas','e_id','e_valorusd');
  $urltoken = "https://developers.syscom.mx/oauth/token";
  //$token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjkxODllZjE1ZDk0YjA5MDdlYTUwYTdkMTMxMDJjNzgxY2Q1NTE0ODEyZDBlY2YwYjRlOTQ5ZmNiYzU3MWY1NjQ5ZjRkM2RmMjFhZWU4ZmNmIn0.eyJhdWQiOiJPMmRwN0dvTTBCMndVTHVwOXNDeUhmS2VJWDN6WWZkbCIsImp0aSI6IjkxODllZjE1ZDk0YjA5MDdlYTUwYTdkMTMxMDJjNzgxY2Q1NTE0ODEyZDBlY2YwYjRlOTQ5ZmNiYzU3MWY1NjQ5ZjRkM2RmMjFhZWU4ZmNmIiwiaWF0IjoxNjMxMjk5MDUzLCJuYmYiOjE2MzEyOTkwNTMsImV4cCI6MTY2MjgzNTA1Mywic3ViIjoiIiwic2NvcGVzIjpbXX0.SKeZK_Vmg9QbZZb-nIt8-yXFKnwXndKQ3VBHxqfX4-ynJIhxbKLLWDAYptsgHLx98byVSWTLK-xfaj5RkUhvt4FRuF3bl4lfdDdUPdgSJTfP1c6ojPBxsThwB0sCUqaeYd0ZhdY0YAIWoobJrpvLeQJbX_3PbdMYITFpiur1CuAAFdf79bJpoquDMgIyQssKQ8LUX8iDqAKgMsa7O_Y3SdMsYkABGgHpA7CX9Sia5YHGk-JrkZc5E26rLY9zsASlAKSe7Bh2tv04HCIWdH9Y6YVvL2TBJwHxvE-Rp-l9JNqZUoqZpwq2G2J6LUpSjEuouZ4mlPhVwbvdRVgHu1LRGKCZxKEc87nCf9MGDrfzu9NaDtrFAXk92xJFR0a5DEM1zJmHXzLFEgJAOTw41rsDGUr-p8dzq5OsbxrvsCBvEDDpGFecR55hCgdsnhDVuZNKWteCcJ01w3-PV2hnwIy1nzAMs4T5h7f9N5CDPSOpFuiEcyxpSmoiNEE-i_zmHfm0R-LbuV5AM8cMM1eXkrkXv_EyKSexaGCruyburhWhu43Uyttc2Z2B3rs9JRvufyh8TtUCc88HEgQZKghxIhQKjK8q_TQmqOMHSN4lbsHKfAUfQsPGFCgUAe-ajbdkhSToyf_NRAgSZOV9zucEOhFHoJoJCGHZWSl-Dg_692DJNEU';
  $token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImNjNDNhZTRmNzdlNDdiYzgwNDBkYzliNDVjNDU3ZTMzMWU4YjI2YWM1MGQ1ZjIyOTU2MmQ4YWI5MzcwOGY3NDYyNjVjOWUyNzBkZjY0MDMyIn0.eyJhdWQiOiJPMmRwN0dvTTBCMndVTHVwOXNDeUhmS2VJWDN6WWZkbCIsImp0aSI6ImNjNDNhZTRmNzdlNDdiYzgwNDBkYzliNDVjNDU3ZTMzMWU4YjI2YWM1MGQ1ZjIyOTU2MmQ4YWI5MzcwOGY3NDYyNjVjOWUyNzBkZjY0MDMyIiwiaWF0IjoxNjYzMDA0MzQwLCJuYmYiOjE2NjMwMDQzNDAsImV4cCI6MTY5NDU0MDM0MCwic3ViIjoiIiwic2NvcGVzIjpbXX0.Bntz-zDZaQF7HkZH7NnPzG2_U87NOL-UYE8ZuwJQwXJGrO7LRiWYapFJE7_cQsiQtP9JL-TB_gY9TXgYysxYLvSn8OsesAFbw2lb9wsgpTIVrQ5lfllY8STYPxsLB2I-eu6x1uyGPi-IofZKgOG8-LgyY0aVDK8MZn8chfcnZXSPsEYIFwDrtY5oEy0iYbBxbaYVKVRfX2EZZFH-kxhsF7P7yzdBnGwyoss4XNPH9VMmH7wBJEfjdJskmNjEymb0pxJbITBslNzBVsRTicNPR0v4y_k9d1cEvyVpqXerobQVW-R7dGP16uIU-v8u8Qm5qi-dbn7Ud3FGvY7CG5wdEvYpbe-1t7OklaPvw25rBL1psW0GzL8QACibhVbWOHdRQpP0Og8lVrpy2kB4lIay8M37KTqifyR7iTys2ILSEE5ge1EhyjhTBNVm8nHhFHJw43Hd6lcmc8cDIoW_Uq8bbFOAaK8MTq0aamwY9nP-Ui_RD9Ucwsb5PJPK4Y3pExqD5ZwzJYMMrmC7JpbnFI5_RCwbWo6IpaikCGTlS3ayrE18D6JbCK3IctqNN622diUp6jEasaUe7v8phexZQOpf63cS1K73Bd4TNC6uX4KJ3TCYwnXvIObxYgiNH6ZW8QUTYuD3DFudPvM8bVAu0ShuwRywJTu2wwBtN_TW0MQtRpY';
  while($row = $this->result->fetch_array()){
    //$sql = 'SELECT * FROM `articulos` INNER JOIN articulo_proveedor ON ap_articulo = a_id WHERE `a_modelo` = "'.$row['e_modelo'].'" ORDER BY `a_modelo` DESC';
    //$result = setq($sql);
    //$rowid = $result->fetch_array();
    $url = "https://developers.syscom.mx/api/v1/productos/".$row['e_idprod']."?inventarios=1";
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  
    $headers = array(
      "Accept: application/json",
      "Authorization: Bearer {$token}",
    );
  
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    //for debug only!
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
  
    $resp = curl_exec($curl);
    curl_close($curl);
    $respuesta = json_decode($resp, true);
    $preciotye = ($respuesta['precios']['precio_descuento']*0.96);

    if($preciotye != 0){
      $usd = 1;
      $costoi = $preciotye*$dolartye;
      $costousd = $preciotye;
      $costosiva = $costoi;
      $costo = $costoi*1.16;
      if(($costo+200) < 2000) $envio = 200; else $envio = 0;
    }
    
    if($row['e_precio1'] == round($preciotye,2)) $estatus = "A";
    else $estatus = "I";

    $sql = 'UPDATE estadisticoprod SET
    e_precio1 = "'.$preciotye.'",
    e_fecha1 = "'.date('Y-m-d H:i:s').'",
    e_precio2 = "'.$row['e_precio1'].'",
    e_fecha2 = "'.$row['e_fecha1'].'",
    e_precio3 = "'.$row['e_precio2'].'",
    e_fecha3 = "'.$row['e_fecha2'].'",
    e_precio4 = "'.$row['e_precio3'].'",
    e_fecha4 = "'.$row['e_fecha3'].'",
    e_estatus = "'.$estatus.'" 
    WHERE e_modelo = "'.$row['e_modelo'].'"';

    setq($sql);

    
    if($respuesta['total_existencia'] <= 100){
        $estatus = "I";
        echo '
        <script>
         alert("Pocas existencias del modelo '.$row['e_modelo'].'");
        </script>';

    }

  }



}

function result(){  
  
  $sql = 'SELECT * FROM estadisticoprod';  
  $result = setq($sql);
  $this->result = setq($sql); 

}

function select($id){
  $sql = 'SELECT * FROM estadisticoprod WHERE e_id = "'.$id.'"';
  $result = setq($sql);
  $row = $result->fetch_array();
  $this->id = $row['e_id'];
  $this->modelo = $row['e_modelo'];
  $this->asin = $row['e_asin'];
  $this->descripcion = $row['e_descripcion'];
  $this->precio1 = $row['e_precio1'];
  $this->precio2 = $row['e_precio2'];
  $this->precio3 = $row['e_precio2'];
  $this->precio4 = $row['e_precio4'];
  $this->fecha1 = $row['e_fecha1'];
  $this->fecha2 = $row['e_fecha2'];
  $this->fecha3 = $row['e_fecha3'];
  $this->fecha4 = $row['e_fecha4'];
  $this->img = $row['e_img'];
  $this->estatus = $row['e_estatus'];
  $this->usd = $row['e_usd'];
  $this->iva = $row['e_iva'];

}

function eliminarprod($id){

  $sql = 'DELETE FROM estadisticoprod WHERE e_id = "'.$id.'"';
  setq($sql);

}


}



class viewestadisticoprod{
  function __construct($model){
    $this->model = $model;
  }

  function browse(){


    $sql = 'SELECT COUNT(*) FROM estadisticoprod';
    $result = setq($sql);
    $row2 = $result->fetch_array();
    if($row2['COUNT(*)'] > 90){
      $display = "block";
      $disabled = "disabled";
    }else{
      $display = "none";
      $disabled = "";
    }

    echo '
    
    <div clas="page-title-actions row">
    </div>
    <div class="card p-2">
      <form action="?modulo=estadisticoprod&accion=insertarprod" method="POST">
      <div class="row">
      <div class="alert alert-danger text-xs-center col-md-12" style="display: '.$display.';">Más de 90 productos en la lista, elimina alguno para poder ingresar más</div>
        <h6 class="mb-1">Agregar Producto:</h6>  
        <div class="col-md-12">
          <div class="mb-5 col-lg-3 col-md-6">
            <label for="">Modelo: </label> <br>
            <input type="text" name="modelo" value="" class="form-control" id="modelo" required '.$disabled.' autofocus>
          </div>
          <div class="mb-5 col-lg-3 col-md-6">
            <label>ID del Producto:</label>
            <input type="number" class="form-control" name="idprod" id="idprod" disabled '.$disabled.'>
          </div>
          <div class="mb-5 col-md-1">
            <label>Buscar ID:</label>
            <input type="checkbox" onchange="buscarid();" name="cambiar" id="cambiar">
          </div>
          <div class="mb-5  col-lg-3 col-md-6">
            <label for="">ASIN: </label>
            <input type="text" name="asin" value="" class="form-control" id="asin" '.$disabled.'>
          </div>
          <!-- <div class="mb-5 col-lg-4 col-md-6">
            <label for="">Descripción: </label>
            <textarea class="form-control" name="descripcion" id="descripcion" cols="5" rows="2">HOLAA</textarea>
          </div> -->
          <div class="mb-5 col-lg-2 col-md-6">
            <label for="">Guardar: </label><br>
            <button class="btn btn-success" '.$disabled.'><i class="fa fa-save"></i> Guardar</button>
          </div>
        </div>
        </form>
        <h6 class="">Detalle de Productos:</h6> 
        <div class="col-md-12 mb-1 text-xs-right" style="">
          <!-- <a href="" class="btn btn-sm btn-orange text-white">Refrescar</a> -->
          <button type="button" onclick="confirma();" class="btn btn-sm btn-info text-white"><i class="fa fa-redo"></i> Actualizar lista</button>
        </div>
        <div class="col-md-12" style="font-size: x-small;">
          <div class="col-md-12">
            <div class="table-responsive" style="max-height: 350px">
              <table class="table table-striped table-hover">
                <thead>
                  <tr>
                    <th>Modelo</th>
                    <th>Descripcion</th>
                    <th>ASIN</th>
                    <th class="bg-orange">Precio Más Reciente</th>
                    <th class="bg-orange">Fecha </th>
                    <th class="bg-blue">Precio Anterior 1</th>
                    <th class="bg-blue">Fecha </th>
                    <th class="bg-yellow">Precio Anterior 2</th>
                    <th class="bg-yellow">Fecha </th>
                    <th class="bg-grey">Precio Anterior 3</th>
                    <th class="bg-grey">Fecha </th>
                    <th>IMG</th>
                    <th>Estatus</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <tbody>';
                //var_dump($this->model->result);
                //echo $this->model->result->num_rows.'  -------------- A';
                while($row = $this->model->result->fetch_array()){
                echo '
                  <tr>
                    <td>'.$row['e_modelo'].'</td>
                    <td style="font-size: xx-small;">'.$row['e_descripcion'].'</td>
                    <td class="number-align">'.$row['e_asin'].'</td>
                    <td class="number-align bg-orange">$'.number_format($row['e_precio1'],2).' USD</td>
                    <td class="bg-orange">'.fecha_formato($row['e_fecha1'],true,false).'</td>
                    <td class="number-align bg-blue">$'.number_format($row['e_precio2'],2).' USD</td>
                    <td class="bg-blue">'.fecha_formato($row['e_fecha2'],true,false).'</td>
                    <td class="number-align bg-yellow">$'.number_format($row['e_precio3'],2).' USD</td>
                    <td class="bg-yellow">'.fecha_formato($row['e_fecha3'],true,false).'</td>
                    <td class="number-align bg-grey">$'.number_format($row['e_precio4'],2).' USD</td>
                    <td class="bg-grey">'.fecha_formato($row['e_fecha4'],true,false).'</td>
                    <td><a onclick="copiarAlPortapapeles('.$row['e_id'].');" data-toggle="tooltip" data-placement="top" title="Copiar a portapapeles" ><img id="'.$row['e_id'].'" src="'.$row['e_img'].'" alt="" style="max-width: 50px;"></a></td>';
                    if($row['e_estatus']  == "A") { $estatus = "tag-success"; $text = "Mismo Precio"; }
                    elseif($row['e_estatus'] == "I") { $estatus = "tag-danger"; $text = "Cambio de Precio"; }
                    elseif($row['e_estatus'] == "N") { $estatus = "tag-warning"; $text = "Nuevo"; }
                    echo '
                    <td><span class="tag '.$estatus.'">'.$text.'</span></td>
                    <td>
                      <button type="button" onclick="borrar('.$row['e_id'].');" class="btn btn-sm btn-secondary"><i class="fa fa-trash"></i></button>
                      <a href="?modulo=estadisticoprod&accion=edit&id='.$row['e_id'].'" class="btn btn-sm btn-secondary"><i class="icon-edit"></i></a>
                    </td>
                  </tr> ';
                }
                echo '
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div> ';


  }

  function edit(){


    if($this->model->modelo){
      $readonly = "readonly";
    }else{
      $readonly = "";
    }

    echo '
    <div clas="page-title-actions row">
    <a href="?modulo=estadisticoprod&accion=index" class="btn btn-warning"><i class="icon-left"></i>Atras</a>
    </div>
    <div class="card p-2">
      <form action="?modulo=estadisticoprod&accion=update&id='.$_GET['id'].'" method="POST">
      <div class="row">
        <h6 class="mb-1">Modificar ASIN del producto: </h6>  
        <div class="col-md-12">
          <div class="mb-5 col-lg-3 col-md-6">
            <label for="">Modelo: </label> <br>
            <input type="text" name="modelo" value="'.$this->model->modelo.'" class="form-control" id="modelo" required '.$disabled.' '.$readonly.'>
          </div>
          <div class="mb-5  col-lg-3 col-md-6">
            <label for="">ASIN: </label>
            <input type="text" name="asin" value="'.$this->model->asin.'" class="form-control" id="asin" '.$disabled.' autofocus>
          </div>
          <!-- <div class="mb-5 col-lg-4 col-md-6">
            <label for="">Descripción: </label>
            <textarea class="form-control" name="descripcion" id="descripcion" cols="5" rows="2">HOLAA</textarea>
          </div> -->
          <div class="mb-5 col-lg-2 col-md-6">
            <label for="">Guardar: </label><br>
            <button class="btn btn-success" '.$disabled.'><i class="fa fa-save"></i> Guardar</button>
          </div>
        </div>
        </form>
      </div>
    </div>
    </div>

    ';


  }


}


?>


<script>

function confirma(){
  var a = confirm('¿Estás seguro de actualizar la lista completa de productos?');
  if(a){
    window.location.href  = "?modulo=estadisticoprod&accion=actualizaprod"; 
  }
}

function borrar(id){
  var a = confirm('¿Estás seguro de eliminar este producto de la lista?');
  if(a){
    window.location.href  = "?modulo=estadisticoprod&accion=eliminarprod&id="+id;
  }

}

function copiarAlPortapapeles(id) {

var img = document.getElementById(id).src;
// Crea un campo de texto "oculto"
var aux = document.createElement("input");

// Asigna el contenido del elemento especificado al valor del campo
aux.setAttribute("value", img);

// Añade el campo a la página
document.body.appendChild(aux);

// Selecciona el contenido del campo
aux.select();

// Copia el texto seleccionado
document.execCommand("copy");

// Elimina el campo de la página
document.body.removeChild(aux);

}


function buscarid(){
  var check = document.getElementById('cambiar');
  if(check.checked){
    var modelo = document.getElementById('modelo');
    var idprod = document.getElementById('idprod');
    $("#modelo").attr('disabled', true);
    $("#idprod").prop('disabled', false);
  }else{
    $("#modelo").attr('disabled', false);
    $("#idprod").prop('disabled', true);
  }
}

</script>



