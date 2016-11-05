<span id="analysis_name_block">
  <?php
    $analyses_files = glob('Analyses/*.txt');
    $analysis_names = array();
      
    foreach($analyses_files as $analyses_file){
      $trimmed_filename = str_replace("Analyses/","",$analyses_file);
      $trimmed_filename = str_replace(".txt",     "",$trimmed_filename);
      $analysis_names[] = $trimmed_filename;
    }
  ?>
  
  <input type="text" id="analysis_name" placeholder="Current Analysis name">
  
  <select id="analyses_list">
    <option>-Select Analysis-</option>
    <?php
      foreach ($analysis_names as $analysis_name) {
        echo "<option>$analysis_name</option>";
      }
    ?>
  </select>
  
  <button type="button" class="collectorButton" id="load_button">Load</button>
  <button type="button" class="collectorButton" id="delete_button">Delete</button>
  <span id="saving_area" style="display:none">Saved</span>
  
  <script>
    function update_analysis(){
      var newName = $("#analysis_name").val();
      
      if (newName.trim() === '') return;
      
      var json_text = $("#analysis_json_textarea").val();  
      
      $.post(
        'AjaxSave.php',
        { filename   : newName,
          filetext   : json_text
        } , 
        function(returned_data) {
          $("#saving_area").html("GUI Script and Output Saved").fadeIn(400).fadeOut(2000);  
        }
      );
    }
  
    list_of_analyses = <?= json_encode($analysis_names) ?>;
    
    $("#analysis_name").on("blur",function(){
      update_analysis();
      
      if(list_of_analyses.indexOf($("#analysis_name").val())== -1){
        list_of_analyses[list_of_analyses.length]=$("#analysis_name").val();
      }
      
      // add option to analyses list
      update_analysis_list(list_of_analyses);
      // check if option is on list or not yet.
    });
    
    
    function update_analysis_list(list_of_analyses){
      var options = "<option>-Select Analysis-</option>";
      
      for (var i=0; i<list_of_analyses.length; ++i) {
        option_list += "<option>" + list_of_analyses[i] + "</option>";
      }
      
      $("#analyses_list").html(option_list);
    }      
      
    
    $("#load_button").on("click",function(){
      analysis_to_load = $("#analyses_list").val();
      
      $.post(
        'AjaxLoad.php',
        {
          filename  : analysis_to_load
        },
        function(returned_data){
          analysis_json = JSON.parse(returned_data);
          $("#analysis_name").val(analysis_to_load);
          update_json();
        }
      )
    });
  
  </script>
</span>
