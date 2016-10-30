<div id="Descriptives"  class="GUI_type">
  <br>
  <span>
      <input class="GUI_type_button collectorButton" type="button"    value="Descriptives">
      <input class="GUI_type_button collectorButton" type="button"    value="T-Tests">
      <input class="GUI_type_button collectorButton" type="button"    value="ANOVA">
      <input class="GUI_type_button collectorButton" type="button"    value="Regression">
      <input class="GUI_type_button collectorButton" type="button"    value="Frequencies">
      <input class="GUI_type_button collectorButton" type="button"    value="Table" title="To manipulate data in the table, add columns, etc.">
      
    </span> 
    <span class="interface" id="GUI_interface">
      
      <span id="descriptives"  class="GUI_type">
      
        <script>
        var columns = Object.keys(data_by_columns);
        descriptive_html ='';
        for(i=0;i<columns.length;i++){
          descriptive_html+="<div><label>"+columns[i]+"<input type='checkbox' name='columns_descriptive' value='"+columns[i]+"' </label></div>";
        }
        $("#descriptives").html(descriptive_html);
        
        </script>
        
        <button type="button" id="descriptive_button">Do descriptive!</button>
        
        <script>
        
        $("#descriptive_button").on("click",function(){
          var selected_columns=[];

          $("input[name='columns_descriptive']:checked").each(function(){
            selected_columns.push(this.value);
          });
          
          console.dir(selected_columns);
        
        });
        
        function clean_array (input_array){
          for(var i=0;i<input_array.length;i++){
            input_array[i]  = parseFloat(input_array[i]);
          }
          return input_array;
        }
        
        descriptive_attributes=["Number of responses","Empty responses","Mean","Stdev","SE","Maximum value","Minimum value"];
        
        descriptive_attributes_calculations=["no_present_in_array","no_empty_in_array","mean","sd","se","max","min"];

        for(i=0;i<descriptive_attributes_calculations.length; i++){
        
          load_script('Stats/'+descriptive_attributes_calculations[i]+'/stats.js');
          
        }
        
        
        </script>
      
      </span>
    </span>
</div>