<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Page title -->
  <?php $php_self = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_STRING); ?>
  <?php $title = isset($title) ? $title : pathinfo($php_self, PATHINFO_FILENAME); ?>
  <title><?= $title ?></title>

  <!-- Icons -->
  <link rel="icon" href="<?= $_PATH->get('Icon', 'url') ?>" type="image/png">
  <link rel="shortcut icon" href="<?= $_PATH->get('Icon', 'url') ?>" type="image/png">

  <!-- Custom fonts: Roboto (headers), Open Sans (body), Inconsolata (monospace) -->
  <link href='http://fonts.googleapis.com/css?family=Roboto:400,700' rel='stylesheet' type='text/css'>
  <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,700' rel='stylesheet' type='text/css'>
  <link href='http://fonts.googleapis.com/css?family=Inconsolata' rel='stylesheet' type='text/css'>

  <!-- Base styles -->
  <link rel="stylesheet" href="<?= $_PATH->get('Global CSS', 'url') ?>">
  <link rel="stylesheet" href="<?= $_PATH->get('Jquery UI Custom CSS', 'url') ?>">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>

  <!-- JS Tools -->
  <script src="<?= $_PATH->get('Jquery UI Custom', 'url') ?>"></script>
  <script src="<?= $_PATH->get('Collector JS', 'url') ?>"></script>

  <!-- Additional styles -->
  <?php if (isset($addedStyles)): foreach ($addedStyles as $additionalStyle): ?>
  <link rel='stylesheet' href='<?= $additionalStyle ?>'>
  <?php endforeach; endif; ?>
</head>

<?php
 // if $_dataController/$_dataAction is not set set it to an empty string
$_dataController = isset($_dataController) ? $_dataController : '';
$_dataAction = isset($_dataAction) ? $_dataAction : '';
?>
<body id="flexBody" data-controller="<?= $_dataController ?>" data-action="<?= $_dataAction ?>">
  <!-- redirect if Javascript is disabled -->
  <noscript>
    <meta http-equiv="refresh" content="0;url=<?= $_PATH->get('No JS', 'url') ?>" />
  </noscript>
