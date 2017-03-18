<div id="buttons_table" class="element_table" style="display:none"></div>

<script>
  new_element_template["Button"]= {    
    create_element:function(element_name,this_location){
      var new_text_element = "<input type='button' id='"+element_name+"' style='"+this_location+"' class='button_element' value='Button'>";
      return new_text_element;
    }
  };
  
  
  /*
  element_gui['button'] = {
    
     my_arr : ["stimuli","position","left","top","color","background-color"],
    
    write_html: function() {
      
      for (var i=0; i<this.my_arr.length; ++i) {
        $("#buttons_table").append(
          "<tr>"+
            "<td>"+this.my_arr[i]+"</td>"+
            "<td><input id='button_"+this.my_arr[i]+"'></td>"+
          "</tr>");
      }
      $("#buttons_table").append("</table>");
      
      for (var i=1; i<this.my_arr.length; i++){ // skip src
        $("#button_"+this.my_arr[i]).on("input",function(){
          var new_style = $(this).val();
          var property_selected = this.id.replace("button_","");
          $("iFrame").contents().find("#"+selected_element_id).css(property_selected,new_style); 
          trial_management.update_temp_trial_type_template();          
        }); 
      };
      
      $("#button_stimuli").on("input",function(){
        
        var new_string = $(this).val();
                
        $("iFrame").contents().find("#"+selected_element_id).html("button:"+new_string);        
        
        trial_management.update_temp_trial_type_template();                
      });      
    }, 
    
    process_style: function(this_input) {
      $("#buttons_table").show();
      for (var i=0; i<this.my_arr.length; ++i) {
        if(this.my_arr[i] == "stimuli"){
          global_var = this_input;          
          var clean_stim = this_input[0].innerHTML.replace("button:","");                    
          $("#button_stimuli").val(clean_stim);
        } else {
          $("#button_" + this.my_arr[i]).val(this_input.css(this.my_arr[i]));
        }
      }
    },
  };
  */
  //element_gui.button.write_html();
</script>

