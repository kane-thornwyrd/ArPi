<!DOCTYPE HTML>
<?php

require_once 'lib.php';
$errors = '';

try
{
  $arduino = new ArduinoConnector('/dev/ttyAMA0');
  $form = new Formulaire_ArPi('conf.ini');
} catch (Exception $e)
{
  $errors = $e;
}

?>
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="fr"> <!--<![endif]-->
  <head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ArPi — Interface Web Domotique</title>
    <link rel="shortcut icon" href="/favicon.ico">
    <link rel="stylesheet" href="css/jquery.mobile-1.1.0.min.css" />
    <link rel="stylesheet" href="css/jquery.mobile.structure-1.1.0.min.css" />
    <link rel="stylesheet" href="css/luluArPi.min.css" />
    <link rel="stylesheet" href="css/custom.css" />
  </head>
  <body>
    <section data-role="page" id="page-une" data-transition="slide">
      <form method="post" action="" id="main-form" data-ajax="false">
        <div data-role="header" class="ui-bar">
          <?php if ($errors): ?>
            <a href="#errors_dialog" data-role="button" data-icon="alert" data-theme="b" data-iconpos="notext" data-rel="dialog" data-transition="pop" class="ui-btn-left">il y a des erreurs !</a>
          <?php endif; ?>
          <h1>ArPi</h1>
        </div>



        <div data-role="content">
          <?php $form->display_pin_board($arduino->get_pin_list()) ?>
        </div>


        <div data-role="footer" class="ui-bar" data-position="fixed">
          <div data-role="controlgroup" data-type="horizontal">
            <button type="reset" data-theme="a" data-icon="refresh" id="reload" title="Recharger">Recharger</button>
            <button type="submit" data-theme="c" data-icon="check" name="validate" id="validate" title="Valider">Valider</button>
        </div>
        </div>
      </form>
    </section>

    <section data-role="page" id="errors_dialog" data-transition="slidedown">

      <div data-role="header" data-theme="b">
        <h2>Erreurs !</h2>
      </div><!-- /header -->

      <div data-role="content" data-theme="d">


        <?php

        if ($errors) {
          if (isset($errors->xdebug_message)) {
            echo '<table data-theme="b">';
            echo $errors->xdebug_message;
            echo '</table>';
          }
          else {
            echo '<pre>';
            print_r($errors);
            echo '</pre>';
          }
        }

        ?>
      </div>
    </section>



  </body>
  <script src="js/jquery-1.7.2.min.js"></script>
  <script src="js/jquery.mobile-1.1.0.min.js"></script>
  <script>
    (function(log){
      var
      form = $('#main-form'),
      process_form_submission = function(event){
        event.preventDefault();
        $.post("/backend.php", form.serialize(), function(data){
          if(data.action === 'reset'){
            document.location = '';
          }
          log(data);
        });
      };



      form.live('submit', process_form_submission);

    })(console.log);
  </script>
</html>
<script>
  $('html').removeClass('no-js');
</script>
