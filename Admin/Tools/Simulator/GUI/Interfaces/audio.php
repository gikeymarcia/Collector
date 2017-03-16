<div id="audios_table" class="element_table" style="display:none"></div>

<script>
if(typeof(element_gui) == "undefined"){
    element_gui={};
  } 
  element_gui['audio'] = {
    
    my_arr : ["stimuli","position","left","top"],
    
    write_html: function() {
      
      for (var i=0; i<this.my_arr.length; ++i) {
        $("#audios_table").append(
          "<tr>"+
            "<td>"+this.my_arr[i]+"</td>"+
            "<td><input id='audio_"+this.my_arr[i]+"'></td>"+
          "</tr>");
      }
      $("#audios_table").append("</table>");
      
      for (var i=1; i<this.my_arr.length; i++){ // skip src
        $("#audio_"+this.my_arr[i]).on("input",function(){
          var new_style = $(this).val();
          var property_selected = this.id.replace("audio_","");
          $("iFrame").contents().find("#"+selected_element_id).css(property_selected,new_style); 
          trial_management.update_temp_trial_type_template();          
        }); 
      };
      
      $("#audio_stimuli").on("input",function(){
        
        var new_string = $(this).val();
                
        $("iFrame").contents().find("#"+selected_element_id).html("audio:"+new_string);        
        
        trial_management.update_temp_trial_type_template();                
      });      
    },
    
    process_style: function(this_input) {
      $("#audios_table").show();
      for (var i=0; i<this.my_arr.length; ++i) {
        if(this.my_arr[i] == "stimuli"){
          global_var = this_input;          
          var clean_stim = this_input[0].innerHTML.replace("audio:","");                    
          $("#audio_stimuli").val(clean_stim);
        } else {
          $("#audio_" + this.my_arr[i]).val(this_input.css(this.my_arr[i]));
        }
      }
    },
  };
  
  element_gui.audio.write_html();
</script>

