<?php
// Anthony's data analysis //

$json_columns = json_encode($columns);
$stats_options = [];

if ($handle = opendir('Stats')) { //found on http://stackoverflow.com/questions/6497833/php-opendir-to-list-folders-only by Jason McCreary
  $blacklist = array('.', '..', 'somedir', 'somefile.php');
  while (false !== ($file = readdir($handle))) {
      if (!in_array($file, $blacklist)) {
          array_push($stats_options,$file);
      }
  }
  closedir($handle);
}
$json_stats_options = json_encode($stats_options);

?>
        
<script>
  var getdata_columns  = <?= $json_columns ?>;
  var stats_options   = <?= $json_stats_options ?>;
  
  var data_by_cols = {};
  
  for (var j=0; j<getdata_columns.length; ++j) {
    data_by_cols[getdata_columns[j]] = [];
  }
  
  for (var i=1; i<data.length; i++) {
    for (var j=0; j<getdata_columns.length; ++j) {
      var col = getdata_columns[j];
      data_by_cols[col][i-1] = data[i][j];
    }
  }
  
  $(document).ready(function() {
    var local_data = data;
    var table_html = '<table><thead><tr><th>' + getdata_columns.join('</th><th>') + '</th></tr></thead><tbody>';
  
    for (var i=1; i<local_data.length; i++) {
      table_html += '<tr><td>' + local_data[i].join('</td><td>') + '</td></tr>';
    }
  
    table_html += '</tbody></table>';
    
    $("#tableArea").html(table_html);
  });
</script>

<link rel="stylesheet" type="text/css" href="GetDataStyle.css">
  <h1> Online Analyses </h1>
  <h2> Using javascript from jStat (https://github.com/jstat/jstat) </h2>
  
  <script src = "//cdn.jsdelivr.net/jstat/latest/jstat.min.js" ></script>
  
<div id="tableArea"></div>
  
<span> 
    <input class="interface_button" type="button" id="gui_interface_button"       value="GUI">
    <input class="interface_button" type="button" id="script_interface_button"    value="Script">
    <input class="interface_button" type="button" id="custom_interface_button"    value="Custom">
</span> 
<div id="AnthonyInterface">
   
  <div class="interface" id="GUI_interface">
    <h3>GUI</h3>

      <span>
      <input class="GUI_type_button" type="button"    value="Descriptives">
      <input class="GUI_type_button" type="button"    value="T-Tests">
      <input class="GUI_type_button" type="button"    value="Regression">
      <input class="GUI_type_button" type="button"    value="Frequencies">
      <input class="GUI_type_button" type="button"    value="Other">
    </span>  

    <div id="Descriptives"  class="GUI_type">
    descriptives: <br>
    <select id="descriptive_variable">
      <option>select variable</option>
    </select>
    - update this to allow selection of multiple variables -
    <table id="descriptive_table"></table>
    
    
    <script>

    function clean_array (input_array){
      for(var i=0;i<input_array.length;i++){
        input_array[i]  = parseInt(input_array[i]);
      }
      return input_array;

    }

    
    // populate descriptive list
    
      var x = document.getElementById('descriptive_variable');
      for(i=0; i<getdata_columns.length; i++){
        var option = document.createElement("option");
        option.text = getdata_columns[i];
        x.add(option);    
      }    
      
      descriptive_attributes=["Number of responses","Empty responses","Mean","Stdev","SE","Maximum value","Minimum value"];
      
      descriptive_attributes_calculations=["no_present_in_array","no_empty_in_array","mean","sd","se","max","min"];
      
      for(i=0;i<descriptive_attributes.length; i++){
      
        var stats_scrpt = document.createElement('script');    
        stats_scrpt.src='Stats/'+descriptive_attributes_calculations[i]+'/stats.js';
        document.head.appendChild(stats_scrpt);
        
        
      }

      
      $("#descriptive_variable").change(function(){
              
        data_by_cols[descriptive_variable.value] = clean_array(data_by_cols[descriptive_variable.value]);      
              
        // Find a <table> element with id="myTable":
        var table = document.getElementById("descriptive_table");

        // clear table - not done
        

        // Create a header row
        var row = table.insertRow(0);
        
        cell0           = row.insertCell(0);
        cell0.innerHTML = "<b>Variable Name</b>";
        
        cell1           = row.insertCell(1);
        cell1.innerHTML = "<b>"+descriptive_variable.value+"</b>";
       

        for(i=0;i<descriptive_attributes.length; i++){
        
        
          var row = table.insertRow(i+1);
          this["cell"+0] = row.insertCell(0);
          this["cell"+0].innerHTML = descriptive_attributes[i];
          

          var function_name     = "calculate_"+descriptive_attributes_calculations[i];
          console.dir(window[function_name]);
          output_from_test  = window[function_name](data_by_cols[descriptive_variable.value]);

          this["cell"+1] = row.insertCell(1);
          this["cell"+1].innerHTML = output_from_test; 

        }
      });
    
    </script>
    
    </div>
    <div id="T-Tests"       class="GUI_type">
      <select id="t_test_selection">
        <option value="select">Select an option</option>
        <option value="independent">Independent Samples</option>
        <option value="paired">Paired Samples</option>
        <option value="one_sample">One-Sample</option>
      </select>
    </div>
    
    <div class="t_test_div" id="t_test_independent"><?php require ("Stats/t_test_independent/display.html"); ?></div>
    <div class="t_test_div" id="t_test_paired"><?php require ("Stats/t_test_paired/display.html"); ?></div>
    <div class="t_test_div" id="t_test_one_sample"><?php require ("Stats/t_test_one_sample/display.html"); ?></div>
    
    
    <script>
      //call js files for each t-test
      
      var t_tests=['t_test_independent','t_test_paired','t_test_one_sample'];
      
      for(i=0;i<t_tests.length;i++){
        var display_scrpt = document.createElement('script');    
        display_scrpt.src='Stats/'+t_tests[i]+'/display.js';
        document.head.appendChild(display_scrpt);

        var stats_scrpt = document.createElement('script');    
        stats_scrpt.src='Stats/'+t_tests[i]+'/stats.js';
        document.head.appendChild(stats_scrpt);        
      }
      

      
      
      $(".t_test_div").hide();
      $("#t_test_selection").change(function(){
        alert($("#t_test_selection").val());
        $(".t_test_div").hide();
        $("#t_test_"+$("#t_test_selection").val()).show();
      });
    </script>
    
    <div id="Regression"    class="GUI_type">
    regression
    </div>
    <div id="Frequencies"   class="GUI_type">
    frequencies
    </div>
    <div id="Other"         class="GUI_type">
    sheet_management
    </div>

  </div>
  <div class="interface" id="Script_interface">
    <h3>Script</h3>
  </div>
  <div class="interface" id="Custom_interface">
    <h3>Custom</h3>

    <input id="stats_select" type="text" placeholder="what stats test do you want to run?">
    <div id="ajax_stats">
      <!-- here's where display files will be presented !-->
    </div>


  </div>
  
  </div>

</div>

<script>
  $(".interface").hide();
  $(".interface_button").click(function(){
    $(".interface").hide();
    $("#"+this.value+"_interface").show();
  });
  
  $(".GUI_type").hide();
  $(".GUI_type_button").click(function(){
    $(".GUI_type").hide();
    $("#"+this.value).show();
  });
</script>

<br><br>

  <div id="outputArea">
  <h3> Output </h3>
  </div>
  <div> List of functions we want:
<br>Renaming variables   
<br>paired t-test
<br>helper bar on right   
<br>handle multiple sheets  
<br>allow users to add custom scripts. Needs HTML part and javascript component. 
<br>allow custom version of our analyses: and comparisons of whether the custom version is quicker. Test if they get the same results 1000 times. If so, then e-mail us to investigate implementing it   
<br>output has its own space and style
<br>allow users to use script to rerun analysis (so it's bidirectional)
<br>create new columns in data - that are presented on the screen
<br>a list of dataframes and variables
<br>allow languages to be used, e.g. R syntax?
</div>

<script>

var returned_script;

$("#stats_select").on('input',function(){ // ajax in Stats tool
  var stats_selected = $("#stats_select").val().toLowerCase();
  if(stats_options.indexOf(stats_selected) !=-1){
    //alert(stats_selected);
    $.get(
      'Stats/'+stats_selected+'/display.html',
      {
        first: 'first param',
        second: 'second param'
      },
      function(returned_data) {
        //console.dir(returned_data);
        $("#ajax_stats").html(returned_data);
        
      }
    );
    
    var display_scrpt = document.createElement('script');    
    display_scrpt.src='Stats/'+stats_selected+'/display.js';
    document.head.appendChild(display_scrpt);
    
    var stats_scrpt = document.createElement('script');    
    stats_scrpt.src='Stats/'+stats_selected+'/stats.js';
    document.head.appendChild(stats_scrpt);



  }
});
</script>
