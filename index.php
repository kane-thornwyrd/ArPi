<!DOCTYPE HTML>
<?php

require_once 'backend.php';
$errors = '';
try
{
  $formulaire = new Formulaire_ArPi('conf.ini');
} catch (Exception $e)
{
  $errors = $e;
}

?><html lang="fr-FR">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ArPi — Interface Web Domotique</title>
    <link rel="stylesheet" href="css/jquery.mobile-1.1.0.min.css" />
    <link rel="stylesheet" href="css/jquery.mobile.structure-1.1.0.min.css" />
    <link rel="stylesheet" href="css/luluArPi.min.css" />
    <link rel="stylesheet" href="css/custom.css" />
    <script src="js/jquery-1.7.2.min.js"></script>
    <script src="js/jquery.mobile-1.1.0.min.js"></script>
  </head>
  <body>
    <section data-role="page" id="page-une" data-theme="a">
      <header data-role="header">
        <h1>ArPi</h1>
      </header>
      <div data-role="content">

        <h2>Première page</h2>

        <form action="/backend.php" method="POST">
          <div data-role="fieldcontain" class="ui-hide-label">
            <label for="flip-a">Select slider:</label>
            <select name="slider" id="flip-a" data-role="slider">
              <option value="off">Off</option>
              <option value="on">On</option>
            </select>
          </div>

        </form>

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
      <footer data-role="footer">

      </footer>
    </section>
  </body>
</html>
