<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <link rel="stylesheet" href="<?php echo $_codeF . 'css/global.css' ?>">
  <link rel="stylesheet" href="<?php echo $_codeF . 'css/jquery-ui-1.10.4.custom.min.css' ?>">
  <script src="<?php echo $_codeF . 'javascript/jquery-1.10.2.min.js' ?>"></script>
  <script src="<?php echo $_codeF . 'javascript/jquery-ui-1.10.4.custom.min.js' ?>"></script>
  <script src="<?php echo $_codeF . 'javascript/collector_1.0.0.js' ?>"></script>
  <title><?php echo isset($title) ? $title : pathinfo( $_SERVER['PHP_SELF'], PATHINFO_FILENAME ) ?></title>
</head>
<body data-controller="<?= isset($_dataController) ? $_dataController : '' ?>" data-action="<?= isset($_dataAction) ? $_dataAction : '' ?>">
  <!-- redirect if Javascript is disabled -->
  <noscript>
    <meta http-equiv="refresh" content="0;url=<?= $_codeF ?>nojs.php" />
  </noscript>
  
  <div class="flexParent">