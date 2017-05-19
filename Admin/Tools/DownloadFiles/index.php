<form method='post' action='download.php'>                
<?php
    require '../../initiateTool.php';
    
    $data_path = $FILE_SYS->get_path("Data");
    
    //echo $data_path;
    // list directories in pathinfo
    $studies_array =array_diff(scandir($data_path),array('..', '.','index.php','sess'));
    
    foreach($studies_array as $study){
        echo "<h2>$study</h2>";
        echo "<h3>participants</h3>";
        $participants = array_diff(scandir("$data_path/$study"),array('..', '.','RandomAssignments.txt'));
        //print_r($participants);
        foreach($participants as $participant){
            echo "<h4>$participant</h4>";
            $these_files = array_diff(scandir("$data_path/$study/$participant"),array('..', '.','RandomAssignments.txt'));
            foreach($these_files as $this_file){
                chmod("$data_path/$study/$participant/$this_file",0444);
                echo "<button class='collectorButton' name='fileurl' value='$data_path/$study/$participant/$this_file'>$this_file</button><br>";
            }
        }
    }
?>
</form>