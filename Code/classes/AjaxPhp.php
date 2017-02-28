<?php

/// php master

$self_address = 

function ajax_email(){
  
}

/* require_once('PHPMailer-master/class.phpmailer.php');
	require_once('PHPMailer-master/class.smtp.php');
	require('PHPMailer-master/PHPMailerAutoload.php');
  $email = new PHPMailer(true);
					
	$email->From    ='labmotivation@gmail.com';
	$email->FromName='Motivation Lab';
	$email->Subject ='Requested paper (Kou Murayama)';
	$email->Body    ='Thank you for requesting the paper attached to this e-mail. If you have any queries, please contact Kou Murayama (k.murayama@reading.ac.uk).';
	$email->AddAddress($_POST['RecAddress']);
	
	$file_to_attach = '/home/m-sk/documents/'.$_SESSION[paper];

	$email->AddAttachment( $file_to_attach , $_SESSION[paper] );
	
	#return $email->Send();
	

	#This is meant to let the user know whether or not their e-mail has gone through. I've not made this work yet.
	if($email->Send()) {
		 echo "Your paper has been sent to ".$_POST['RecAddress'].". If you have received an e-mail without the attachment please contact Anthony Haffey (a.haffey@reading.ac.uk). Please note that it may take a few minutes for the e-mail to come through.";
		 
		#Update list of which users have downloaded papers
		
		$downloadedCount = csv_to_array("/home/m-sk/documents/downloads.csv");
		$rowNos= count($downloadedCount);
		$currentDate= new DateTime();
		$downloadedCount[$rowNos+1][Paper]=$_SESSION[paper];
		$downloadedCount[$rowNos+1][emails]=$_POST['RecAddress'];
		$downloadedCount[$rowNos+1][Time]=$currentDate->format('Y-m-d H:i:s'). "\n";
		
		$fp=fopen('/home/m-sk/documents/downloads.csv','w');

		fputcsv($fp, array_keys($downloadedCount[0]));

	
		foreach ($downloadedCount as $row){
		fputcsv($fp, $row);
		}
		fclose($fp);
		 
		
	} else {
		 echo "Message could not be sent";
	}
 */
?>