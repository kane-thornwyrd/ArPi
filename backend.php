<?php

function exception_error_handler($errno, $errstr, $errfile, $errline ) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}
set_error_handler("exception_error_handler");

function shutdown() {
  $a = error_get_last();
  if ($a != null) {
    print_r($a);
  }

}
register_shutdown_function('shutdown');

class ArduinoConnector {

  public $device, $pin;

  function __construct( $device ) {
    $this->device = $device;
    $this->pin = array();

  }

  /**
   * Le principe c'est de sauvegarder dans un tableau les valeurs à envoyer
   * selon la pin interrogée… Enfin, après je sais absolument pas comment ça
   * marche XD LOL !
   * Au pire, tu pourra modifier ça pour stocker des callbacks à executer à
   * l'appel de telle ou telle pin.
   *
   * @param type $num
   * @param type $value
   */
  public function register_pin( $num, $value ) {
    $this->pin[$num] = $value;

  }

  /**
   * Implémentation "en dur" du code de la vidéo avec cette technique.
   */
  public function send_13() {
    $pin_num = 13;
    return self::send_value($this, $pin_num);

  }

  /**
   * Implémentation d'un envoi sur une autre pin qui nécessite une attente
   * configurable.
   * L'idée c'est de t'en donner sur comment utiliser et tuner cette classe.
   */
  public function send_24( $temps = 0 ) {
    $pin_num = 24;
    $arduino = self::send_value($this, $pin_num);
    sleep($temps);
    return $arduino;

  }

  /**
   * découplage du code pour envoyer l'ordre, si tu change de manière d'
   * interroger l'arduino.
   * @param type $pin
   * @param type $value
   * @param type $device
   */
  protected static function send_value( ArduinoConnector $arduino, $pin ) {
    $value = $arduino->pin[$pin];
    $device = $arduino->device;
    exec('echo ' . $value . ' > ' . $device);
    return $arduino;

  }

  /**
   * Fonction magique qui intercepte tout appel à méthode qui n'existe pas.
   * Et forward vers la pin si elle existe.
   * @param type $name
   * @param type $arguments
   */
  public function __call( $name, $arguments ) {
    /**
     * C'est la fonction d'appel de pin générique, on sait pas
     * quoi faire avec ces arguments à la noix…
     */
    unset($arguments);
    // RegEx pour check qu'on appel bien un envoi vers une pin.
    if (preg_match('#^send_(?<pin_num>\d+)$#', $name, $match)) {
      if (isset($this->pin[$match['pin_num']])) {
        return self::send_value($this, $match['pin_num']);
      }
    }

  }
}

//Instanciation de la classe de communication avec l'arduino.
$arduino = new ArduinoConnector('/dev/ttyAMA0');

/**
 * Fonction random pour te donner des idées d'utilisation ^^.
 * @param ArduinoConnector $arduino
 */
//function fait_le_con( ArduinoConnector $arduino ) {
//  $arduino->send_13()->send_24(10)->send_2();
//
//}
//voilà l'invocation de la fonction déclarée au-dessus.
//fait_le_con($arduino);


class Formulaire_ArPi {

  protected $conf;

  function __construct( $fichier_de_conf ) {
    if (!file_exists($fichier_de_conf)) {
      touch($fichier_de_conf);
    }
    if ($content = parse_ini_file($fichier_de_conf, TRUE)) {
      $this->conf = $content;
    }

  }
}