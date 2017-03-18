<?php
  $element_types = ["image",
                    "text",
                    "video",
                    "audio",
                    "button",
                    "string",
                    "number",
                    "date",
                    "checkbox",
                    "radio"];
  for($i=0;$i<count($element_types);$i++){
    echo "<div id='".$element_types[$i]."_table' class='element_table' style='display:none'></div>";
  }
?>

<script>
  
  element_gui={
    
    properties: {
      // stimuli
      text:     ["stimuli","color","background-color","font-size","width","height","padding","border-radius"],
      image:    ["stimuli","position","left","top","width","height"],
      video:    ["stimuli","position","left","top","width","height"],
      audio:    ["stimuli","position","left","top"],
      
      //inputs
      button:   ["stimuli","position","left","top","color","background-color"],
      number:   ["stimuli","position","left","top","color","background-color"],
      
    },
    accepted_classes:["text_element","image_element","video_element","audio_element","button_element","string_element","number_element"],
    
    write_html:function(element_type){
      for (var i=0; i<element_gui.properties[element_type].length; ++i) {
        $("#"+element_type+"_table").append(
          "<tr>"+
            "<td>"+element_gui.properties[element_type][i]+"</td>"+
            "<td><input id='"+element_type+"_"+element_gui.properties[element_type][i]+"'></td>"+
          "</tr>");
      }
      $("#"+element_type+"_table").append("</table>");
      
      for (var i=1; i<element_gui.properties[element_type].length; i++){ // skip src
        $("#"+element_type+"_"+element_gui.properties[element_type][i]).on("input",function(){
          var new_style = $(this).val();
          var property_selected = this.id.replace(element_type+"_","");
          $("iFrame").contents().find("#"+selected_element_id).css(property_selected,new_style); 
          trial_management.update_temp_trial_type_template();          
        }); 
      };
      
      $("#"+element_type+"_stimuli").on("input",function(){
        
        var new_string = $(this).val();
                
        $("iFrame").contents().find("#"+selected_element_id).html(element_type+":"+new_string);
        
        trial_management.update_temp_trial_type_template();                
      });
    },
    
    process_style: function(this_input,this_class) {
      $("#"+this_class+"_table").show();
      
      // deal with "this.my_arr problem"
      element_gui.properties[this_class]
      
      for (var i=0; i<element_gui.properties[this_class].length; ++i) {
        console.dir(element_gui.properties[this_class][i]);
        if(element_gui.properties[this_class][i] == "stimuli"){
          global_var = this_input;          
          var clean_stim = this_input[0].innerHTML.replace(this_class+":","");
          $("#"+this_class+"_stimuli").val(clean_stim);
        } else {
          $("#"+this_class+"_" + element_gui.properties[this_class][i]).val(this_input.css(element_gui.properties[this_class][i]));
        }
      }
    },
  }; 
  for(var i=0;i<element_gui.accepted_classes.length;i++){
    var clean_class=element_gui.accepted_classes[i].replace("_element","");
    element_gui.write_html(clean_class);
  }
</script>