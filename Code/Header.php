<!DOCTYPE html>
<html>
<head>
    <meta  http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link  href="<?= $_codeF ?>css/global.css"                      rel="stylesheet" type="text/css" />
	<link  href="<?= $_codeF ?>css/jquery-ui-1.10.4.custom.min.css" rel="stylesheet" type="text/css" />
    <script src="<?= $_codeF ?>javascript/jquery-1.10.2.min.js"                      type="text/javascript"></script>
    <script src="<?= $_codeF ?>javascript/jquery-ui-1.10.4.custom.min.js"            type="text/javascript"></script>
    <script src="<?= $_codeF ?>javascript/collector_1.0.0.js"                        type="text/javascript"></script>
    <title><?= isset($title) ? $title : pathinfo( $_SERVER['PHP_SELF'], PATHINFO_FILENAME ) ?></title>
</head>
<body data-controller="<?= isset($_dataController) ? $_dataController : '' ?>" data-action="<?= isset($_dataAction) ? $_dataAction : '' ?>">
	<!-- redirect if Javascript is disabled -->
    <noscript>
        <meta http-equiv="refresh" content="0;url=<?= $_codeF ?>nojs.php" />
    </noscript>

    <div class="cframe-outer">
        <div class="cframe-inner">