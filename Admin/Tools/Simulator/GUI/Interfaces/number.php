<div id="buttons_table" class="element_table" style="display:none"></div>

<script>
  new_element_template["Number"]={
    create_element:function(element_name,this_location){
      var new_text_element = "<input type='number' id='"+element_name+"' style='"+this_location+"' class='number_element'>";
      return new_text_element;
    }
  };
  
  /* 
  element_gui['number'] = {
    
    my_arr : ["stimuli","position","left","top","color","background-color"],
    
    write_html: function() {
      
      for (var i=0; i<this.my_arr.length; ++i) {
        $("#numbers_table").append(
          "<tr>"+
            "<td>"+this.my_arr[i]+"</td>"+
            "<td><input id='number_"+this.my_arr[i]+"'></td>"+
          "</tr>");
      }
      $("#numbers_table").append("</table>");
      
      for (var i=1; i<this.my_arr.length; i++){ // skip src
        $("#number_"+this.my_arr[i]).on("input",function(){
          var new_style = $(this).val();
          var property_selected = this.id.replace("number_","");
          $("iFrame").contents().find("#"+selected_element_id).css(property_selected,new_style); 
          trial_management.update_temp_trial_type_template();          
        }); 
      };
      
      $("#number_stimuli").on("input",function(){
        
        var new_string = $(this).val();
                
        $("iFrame").contents().find("#"+selected_element_id).html("number:"+new_string);        
        
        trial_management.update_temp_trial_type_template();                
      });      
    },
    
    process_style: function(this_input) {
      $("#numbers_table").show();
      for (var i=0; i<this.my_arr.length; ++i) {
        if(this.my_arr[i] == "stimuli"){
          global_var = this_input;          
          var clean_stim = this_input[0].innerHTML.replace("number:","");                    
          $("#number_stimuli").val(clean_stim);
        } else {
          $("#number_" + this.my_arr[i]).val(this_input.css(this.my_arr[i]));
        }
      }
    },
  }; */
  
  //element_gui.number.write_html();
</script>

