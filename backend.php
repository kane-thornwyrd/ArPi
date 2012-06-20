<?php
require_once 'lib.php';

header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');
  $arduino = new ArduinoConnector('/dev/ttyAMA0');
  $form = new Formulaire_ArPi('conf.ini', $arduino);

$_REQUEST = array_merge($_GET, $_POST);

function reponse($action, $data = array()){
  echo json_encode(array(
    'action' => $action,
    'data' => $data,
  ));
  exit();
}

if (isset($_REQUEST['reset'])) {
  $pin_list = $arduino->get_pin_list();
  foreach ($pin_list as $pin_name) {
      $form->set_pin_off($pin_name);
  }
  reponse('reset');
}

try{
  $pin_list = $arduino->get_pin_list();

  $data = array('activated' => array());

  foreach ($pin_list as $pin_name) {
    if(isset($_REQUEST[$pin_name]) && $_REQUEST[$pin_name] == 'on'){
      $form->set_pin_on($pin_name);
      $data['activated'][] = $pin_name;
    }else{
      $form->set_pin_off($pin_name);
    }
    unset($_REQUEST[$pin_name]);
  }

  $form->save();


}  catch (Exception $e){

}

  reponse('validate', $form->get_conf());