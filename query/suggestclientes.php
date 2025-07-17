<?php
  session_start();
  ini_set('display_errors', 0);
  include('../funciones.php');

  $html = '';
  $key = strtoupper($_POST['cliente']);

  $grupo = busca($_SESSION['uid'], 'usuarios', 'u_id', 'u_grupo');
  if($grupo != "ADMIN" && $grupo != "GERENCIA") $sqladd = 'AND c_uregistro = "'.$_SESSION['uid'].'"';
  else $sqladd = "";
  $sql = 'SELECT c_id,c_alias,c_nmb,c_apellidos FROM crm_clientes
          WHERE c_estatus = "A" '.$sqladd.' AND (c_alias LIKE "%'.strip_tags($key).'%"
          OR c_nmb LIKE "%'.strip_tags($key).'%" OR c_apellidos LIKE "%'.strip_tags($key).'%")';
  $result = setq($sql);
  
  if(!empty($key) && strlen($key) >= 3)
  while ($row = $result->fetch_assoc()) {
    $id[] = $row['c_id'];
    $alias[] = $row['c_alias'];
    if($row['c_apellidos'] != NULL) $nombre1 = $row['c_nmb'].' '.$row['c_apellidos'];
    else $nombre1 = $row['c_nmb'];
    $nombre[] = $nombre1;
  }

  $aliasunique = array_unique($alias);
  $finalias = array_filter($aliasunique);
  $nmbunique = array_unique($nombre);
  $finnmb = array_filter($nmbunique);
  $idunique = array_unique($id);
  $finid = array_filter($idunique);

  for($i=0; $i<sizeof($finid); $i++){
    $result = array_filter($finalias, function ($item) use ($key) {
      if (stripos($item, strip_tags($key)) !== false) return $item;
      return false;
    });
    if($result) $name = $result[$i];
    else{
      $result = array_filter($finnmb, function ($item) use ($key) {
        if (stripos($item, strip_tags($key)) !== false) return $item;
        return false;
      });
      if($result) $name = $result[$i];
    }

    $name = $finnmb[$i];
    $html .= '<div><a class="suggest-element" data="'.$nombre[$i].'" id="client'.$finid[$i].'">'.$name.'</a></div>';
  }
  echo $html;
?>