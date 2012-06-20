<?php

function exception_error_handler( $errno, $errstr, $errfile, $errline ) {
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

  protected $screen_running, $device, $pin;

  function __construct( $device ) {
    $this->device = $device;
    $this->pin = array(
      'pin-02' => 'a',
      'pin-03' => 'b',
      'pin-04' => 'c',
      'pin-05' => 'd',
      'pin-06' => 'e',
      'pin-07' => 'f',
      'pin-08' => 'g',
      'pin-09' => 'h',
      'pin-10' => 'i',
      'pin-11' => 'j',
      'pin-12' => 'k',
      'pin-13' => 'l',
    );

  }

  public function get_pin_list() {
    return array_keys($this->pin);

  }

  public function screen_exist() {
    return (strpos(shell_exec("screen -ls"), 'No Sockets') === FALSE);

  }

  public function screen_init( $sleep_time = 1 ) {
    exec('screen -dmS arduino ' . $this->device . ' 115200');
    sleep($sleep_time);
    exec('screen -p 0 -S arduino -X eval \'stuff \"2\"\'');

    if (!$this->screen_exist()) {
      throw new Exception('Impossible d\'ouvrir le screen vers l\'arduino.');
    }

    return $this;

  }

  public function toggle_pin( $pin_name ) {
    if (!$this->screen_exist()) {
      $this->screen_init();
    }
    if ($this->pin_exist($pin_name)) {
      return exec('screen -p 0 -S arduino -X eval \'stuff \"' . $this->pin[$pin_name] . '\"\'');
    }
    else {
      throw new Exception('La pin demandée n\'existe pas !');
    }

  }

  protected function pin_exist( $pin_name ) {
    return isset($this->pin[$pin_name]);

  }

  protected static function intToPin( int $int ) {
    return 'pin ' . sprintf('%02d', $match['pin_num']);

  }

  protected static function explode_log_line( $line ) {
    return explode(";", $line);

  }

  public function get_arduino_log() {
    exec('screen -p 0 -S arduino -X eval \'stuff \"1\"\'');
    exec('screen -p 0 -S arduino -X hardcopy /var/www/status.txt');
    $log = file('/var/www/status.txt');
    $log = array_map('ArduinoConnector::explode_log_line', $log);
    return $log;

  }

  public function get_arduino_status() {
    return array_pad($this->get_arduino_log(), -1);

  }

  public function __call( $name, $arguments ) {
    /**
     * C'est la fonction d'appel de pin générique, on sait pas
     * quoi faire avec ces arguments à la noix…
     */
    unset($arguments);
    // RegEx pour check qu'on appel bien un envoi vers une pin.
    if (preg_match('#^toggle_(?<pin_num>\d+)$#', $name, $match)) {
      $pin_name = self::intToPin($match['pin_num']);
      if ($this->pin_exist($pin_name)) {
        $this->toggle_pin($pin_name);
      }
    }

  }
}

class Formulaire_ArPi {

  protected $conf, $fichier_de_conf, $arduino;

  function __construct( $fichier_de_conf, ArduinoConnector $arduino ) {
    $this->fichier_de_conf = $fichier_de_conf;
    $this->arduino = $arduino;
    if (!file_exists($this->fichier_de_conf)) {
      touch($this->fichier_de_conf);
    }
    $content = file_get_contents($this->fichier_de_conf);
    if ($content !== FALSE) {
      $this->conf = unserialize($content);
      foreach ($this->conf['pin'] as $pin_name => $state) {
        $this->arduino->toggle_pin($pin_name);
      }
    }

    if (!isset($this->conf)) {
      $this->conf = array('pin' => array());
    }

  }

  function get_conf() {
    return $this->conf;

  }

  function save() {
    file_put_contents($this->fichier_de_conf, serialize($this->conf));

  }

  function set_pin( $pin_name, $state ) {
    if (isset($this->conf['pin'][$pin_name])) {
      $this->conf['pin'][$pin_name] = $state;
    }

  }

  function set_pin_off( $pin_name ) {
    $this->set_pin(FALSE);

  }

  function set_pin_on( $pin_name ) {
    $this->set_pin(TRUE);
    $this->arduino->toggle_pin($pin_name);

  }

  function display_pin_board( array $pin_list ) {

    $pin_control_lines = '';

    foreach ($pin_list as $key => $pin) {
      $pin_control_lines .= $this->construct_pin_checkbox($pin, $key % 2);
    }

    $markup = <<<HTML
    <fieldset data-role="controlgroup" data-theme="a">
        {$pin_control_lines}
    </fieldset>
HTML;

    echo $markup;

  }

  function construct_pin_checkbox( $pin_name, $zebra = FALSE ) {

    $checked = '';
    if (isset($this->conf['pin'][$pin_name]) && $this->conf['pin'][$pin_name]) {
      $checked = ' checked="checked"';
    }

    $classes = implode(' ',
      array(
      'pin-line',
      $zebra
        ? 'even'
        : 'odd',
      ));

    $markup = <<<HTML
    <div class="{$classes}" data-type="horizontal">
        <input type="checkbox" name="{$pin_name}" id="{$pin_name}" data-theme="a"{$checked}/>
        <label for="{$pin_name}">{$pin_name}</label>
    </div>
HTML;

    return $markup;

  }
}
