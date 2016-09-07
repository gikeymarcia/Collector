<?php
    header('Content-Type: text/html; charset=utf-8');
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Page title -->
  <?php $title = isset($title) ? $title : 'Collector'; ?>
  <title><?= $title ?></title>

  <!-- Icons -->
  <link rel="icon" href="<?= $_FILES->get_path('Icon') ?>" type="image/png">
  <link rel="shortcut icon" href="<?= $_FILES->get_path('Icon') ?>" type="image/png">

  <!-- Custom fonts: Roboto (headers), Open Sans (body), Inconsolata (monospace) -->
  <link href='http://fonts.googleapis.com/css?family=Roboto:400,700' rel='stylesheet' type='text/css'>
  <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,700' rel='stylesheet' type='text/css'>
  <link href='http://fonts.googleapis.com/css?family=Inconsolata' rel='stylesheet' type='text/css'>

  <!-- Base styles -->
  <link rel="stylesheet" href="<?= $_FILES->get_path('Global CSS') ?>">
  <link rel="stylesheet" href="<?= $_FILES->get_path('Jquery UI Custom CSS') ?>">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
  <!-- Base scripts -->
  <script>
    if (typeof jQuery === "undefined") {
      document.write("<script src='<?= $_FILES->get_path('Jquery') ?>'><\/script>");
    }
  </script>

  <!-- JS Tools -->
  <script src="<?= $_FILES->get_path('Jquery UI Custom') ?>"></script>
<?php
    if (isset($added_scripts) && is_array($added_scripts)) {
        foreach ($added_scripts as $script_src) {
          echo "<script src='{$script_src}'></script>";
        }
    }

?>
</head>

<body id="flexBody">
  <!-- redirect if Javascript is disabled -->
  <noscript>
    <meta http-equiv="refresh" content="0;url=<?= $_FILES->get_path('No JS') ?>" />
  </noscript>
