<div id="videos_table" class="element_table" style="display:none"></div>

<script>
if(typeof(element_gui) == "undefined"){
    element_gui={};
  } 
  /*
  element_gui['video'] = {
    
     my_arr : ["stimuli","position","left","top","width","height"],
    
    write_html: function() {
      
      for (var i=0; i<this.my_arr.length; ++i) {
        $("#videos_table").append(
          "<tr>"+
            "<td>"+this.my_arr[i]+"</td>"+
            "<td><input id='video_"+this.my_arr[i]+"'></td>"+
          "</tr>");
      }
      $("#videos_table").append("</table>");
      
      for (var i=1; i<this.my_arr.length; i++){ // skip src
        $("#video_"+this.my_arr[i]).on("input",function(){
          var new_style = $(this).val();
          var property_selected = this.id.replace("video_","");
          $("iFrame").contents().find("#"+selected_element_id).css(property_selected,new_style); 
          trial_management.update_temp_trial_type_template();          
        }); 
      };
      
      $("#video_stimuli").on("input",function(){
        
        var new_string = $(this).val();
        
        
        // check whether this is a valid image using ajax
        // solution by 
        
        $("iFrame").contents().find("#"+selected_element_id).html("video:"+new_string);        
        
        trial_management.update_temp_trial_type_template();                
      });      
    }, 
    
    process_style: function(this_input) {
      $("#videos_table").show();
      for (var i=0; i<this.my_arr.length; ++i) {
        console.dir(this.my_arr[i]);
        if(this.my_arr[i] == "stimuli"){
          global_var = this_input;          
          var clean_stim = this_input[0].innerHTML.replace("video:","");                    
          $("#video_stimuli").val(clean_stim);
        } else {
          $("#video_" + this.my_arr[i]).val(this_input.css(this.my_arr[i]));
        }
      }
    },
  };
  */
  //element_gui.video.write_html();
</script>

