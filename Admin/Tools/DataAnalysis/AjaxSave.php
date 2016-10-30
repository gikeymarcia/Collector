<?php

require '../../initiateTool.php';
ob_end_clean(); // no need to transmit useless data
if (!isset($_POST['filename'], $_POST['filetext'])) exit;

header('Content-type: text/plain; charset=utf-8');

$filename = $_POST['filename'];
$filetext = $_POST['filetext'];

if ($filename !== '' AND preg_match('/[^a-zA-Z0-9._ -]/', $filename) === 0) {
  if (!is_dir('Analyses')) mkdir('Analyses', 0777, true);
  file_put_contents("Analyses/$filename.txt",$filetext);
}
