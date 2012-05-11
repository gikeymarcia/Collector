<?php
session_start();

$num1 = $_POST['Num1'];
$num2 = $_POST['Num2'];
$num3 = $_POST['Num3'];
$num4 = $_POST['Num4'];

$op1 = $_POST['op1'];
$op2 = $_POST['op2'];
$op3 = $_POST['op3'];

$num1 = isPI($num1);
$num2 = isPI($num2);
$num3 = isPI($num3);
$num4 = isPI($num4);


function MathFunc($num1, $op, $num2) {
	if($op == 'add') {
		$result = $num1 + $num2;
	}
	elseif ($op == 'subtract') {
		$result = $num1 - $num2;
	}
	elseif ($op == 'multiply') {
		$result = $num1 * $num2;
	}
	elseif ($op == 'divide') {
		$result = $num1 / $num2;
	}
	else {
		$result = $num1;
	}
	return $result;
}


function isPI($num) {
	$num = trim(strtolower($num));
	if(($num == 'pi') OR ($num == 'pie')) {
		$num = 3.14;
	}
	return $num;
}

$evaled = MathFunc($num1, $op1, $num2);
$evaled = MathFunc($evaled, $op2, $num3);
$evaled = MathFunc($evaled, $op3, $num4);

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link href="css/global.css" rel="stylesheet" type="text/css" />
	<link href='http://fonts.googleapis.com/css?family=Kreon' rel='stylesheet' type='text/css' />
	<title>Evaluating Function</title>
</head>
<body>
	<?php
	echo 'This is what has been recorded<br />';
	echo "Number 1: {$num1}<br />";
	echo "Number 2: {$num2}<br />";
	echo "Number 3: {$num3}<br />";
	echo "Number 4: {$num4}<br /><br />";
	
	echo "operation 1: {$op1}<br />";
	echo "operation 2: {$op2}<br />";
	echo "operation 3: {$op3}<br /><br /><br />";
	
	echo "The answer should be <b>{$evaled}</b> <br /><br />";
	
	
	?>
	<a href="Centering.html">Go back to the other page</a>
</body>
</html>