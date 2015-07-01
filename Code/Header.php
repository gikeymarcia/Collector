<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <link rel="icon" href="<?= $_rootF . $codeF ?>icon.png" type="image/png">
  <link rel="shortcut icon" href="<?= $_rootF . $codeF ?>icon.png" type="image/png">
  <link rel="stylesheet" href="<?php echo $_codeF . 'css/global.css' ?>">
  <link rel="stylesheet" href="<?php echo $_codeF . 'css/jquery-ui-1.10.4.custom.min.css' ?>">
  <script src="<?php echo $_codeF . 'javascript/jquery-1.11.3.min.js' ?>"></script>
  <script src="<?php echo $_codeF . 'javascript/jquery-ui-1.10.4.custom.min.js' ?>"></script>
  <script src="<?php echo $_codeF . 'javascript/collector_1.0.0.js' ?>"></script>
  <link href='http://fonts.googleapis.com/css?family=Roboto:400,700' rel='stylesheet' type='text/css'>          <!-- Load Roboto font (used for headers) -->
  <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,700' rel='stylesheet' type='text/css'>       <!-- Load Open Sans font (used for headers) -->
  <link href='http://fonts.googleapis.com/css?family=Inconsolata' rel='stylesheet' type='text/css'>             <!-- Load Inconsolata font (used for monospace) -->
  <title><?php echo isset($title) ? $title : pathinfo( $_SERVER['PHP_SELF'], PATHINFO_FILENAME ) ?></title>
  <?php
    if (isset($addedStyles)) {
        foreach ($addedStyles as $additionalStyle) {
            echo '<link rel="stylesheet" href="' . $additionalStyle . '">';
        }
    }
    if (isset($addedScripts)) {
        foreach ($addedScripts as $additionalScript) {
            echo '<script src="' . $additionalScript . '"></script>';
        }
    }
  ?>
</head>
<?php
    if (!isset($_dataController)) {             // if $_dataController is not set
        $_dataController = '';                      // set it to an empty string
    }
    if (!isset($_dataAction)) {                 // if $_dataAction is not set
        $_dataAction = '';                          // set it to an empty string
    }
?>
<body id="flexBody" data-controller="<?= $_dataController ?>" data-action="<?= $_dataAction ?>">
  <!-- redirect if Javascript is disabled -->
  <noscript>
    <meta http-equiv="refresh" content="0;url=<?= $_codeF ?>nojs.php" />
  </noscript>