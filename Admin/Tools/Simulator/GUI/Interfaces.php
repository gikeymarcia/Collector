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

<style>
    .interface_td{
        padding:5px;
    }
    .interface_on_button,.interface_off_button{
        border-radius:10px;
        padding:10px;
        border-color:blue;
    }
    .interface_on_button{
        background-color    :   blue;
        color               :   white;
        display             :   none;
    }
    .interface_on_button:hover{
        background-color    :   white;
        color               :   blue;        
    }
    .interface_off_button{
        background-color    :   white;
        color               :   blue;
    }
    .interface_off_button:hover{
        background-color    :   blue;
        color               :   white;
    }
</style>



<script>




function on_off_interface_button(type,on_off){
    //alert(type);
    //alert(on_off);
    $("#"+type+"_"+on_off).hide();
    if(on_off=="off"){
        new_on_off = "on"
        $("#"+type+"_on_span").show();
        $("#"+type+"_checkbox").prop("checked",true);
    } else {
        new_on_off = "off"
        $("#"+type+"_on_span").hide();
        $("#"+type+"_checkbox").prop("checked",false);
    }
    $("#"+type+"_"+new_on_off).show();
    
    
    
    // tick or untick the checkbox!!!
    
    
    
}
    
  element_gui={
    
    placeholder:{
      //button:"n/a",
      string:"whatever you want your placeholder to be",
      number:"numbers only, no characters",
      date  :"YYYY-MM-DD",
    },
    
    properties: {
      // stimuli
      text:     ["stimuli","position","left","top","color","background-color","font-size","width","height","padding","border-radius"],
      image:    ["stimuli","position","left","top","width","height"],
      video:    ["stimuli","position","left","top","width","height"],
      audio:    ["stimuli","position","left","top","audio_autoplay","audio_button","color","background-color","width","height"],
      
      //inputs
      button:   ["value","name","position","left","top","color","background-color","font-size","width","height"],
      string:   ["value","name","position","left","top","color","background-color","width","height"],
      number:   ["value","name","position","left","top","color","background-color","width","height"],
      date:     ["value","name","position","left","top","color","background-color","width","height"],
      
      //questionnaire  - next release
/*
      radio:    ["value"],
      checkbox: ["value"],
*/      
    },
    accepted_classes:["text_element","image_element","video_element","audio_element","button_element","string_element","number_element","date_element"], //,"radio_element","checkbox_element"
    
    
    
    write_html:function(element_type){
        var this_header = "<tr><td colspan='2'><h4>"+element_type+"</h4></td></tr>";
        $("#"+element_type+"_table").append(this_header);
        for (var i=0; i<element_gui.properties[element_type].length; ++i) {
            //exceptions 
                // audio exceptions
            skip_normal_proceesing = 0;
            if(element_gui.properties[element_type][i].indexOf("audio_") == 0){
                // audio processing?
                
                var audio_type = element_gui.properties[element_type][i].replace("audio_","");
                
                var new_table_code = "<tr>"+
                        "<td class='interface_td'>"+audio_type+"</td>"+
                        "<td class='interface_td'>"+
                            "<input type='button' id='audio_"+audio_type+"_on' class='interface_on_button' value='On' onclick='on_off_interface_button(\"audio_"+audio_type+"\",\"on\")'>"+
                            "<input type='button' id='audio_"+audio_type+"_off' class='interface_off_button' value='Off' onclick='on_off_interface_button(\"audio_"+audio_type+"\",\"off\")'>"+
                            "<span style='display:none' id='"+element_type+"_"+audio_type+"_on_span'> <input type='checkbox' id='"+element_type+"_"+audio_type+"_checkbox'>";
                
                if(element_gui.properties[element_type][i].indexOf("autoplay") !== -1){
                    new_table_code += "after <input type='number' id='"+element_type+"_"+audio_type+"_time' min ='0' value='0.0' style='width:60px'>secs</span>";
                    skip_normal_proceesing = 1; 
                }
                if(element_gui.properties[element_type][i].indexOf("button") !== -1){
                    
                
                    /*

                        new_table_code +=   "<br><input type='text' id='"+element_type+"_"+audio_type+"_value' placeholder='Value'><br>" + 
                                        "<input type='text' id='"+element_type+"_width' placeholder='Width'><br>" +
                                        "<input type='text' id='"+element_type+"_height' placeholder='Height'><br>" +
                    "</span>";
                    */

                
                /*
                    $("#"+element_type+"_"+element_gui.properties[element_type][i]).on("input",function(){
                        var new_style = $(this).val();
                        var property_selected = this.id.replace(element_type+"_","");
                        $("iFrame").contents().find("#"+element_management.selected_element).css(property_selected,new_style); 
                        trial_management.update_temp_trial_type_template();          
                    });
                */
                
                }
                
                
                new_table_code += "</td></tr>";                    
                
                $("#"+element_type+"_table").append(new_table_code);                    
                skip_normal_proceesing = 1;                    
            } 
            
            if(skip_normal_proceesing == 0){
                $("#"+element_type+"_table").append(
                    "<tr>"+
                        "<td class='interface_td'>"+element_gui.properties[element_type][i]+"</td>"+
                        "<td class='interface_td'><input id='"+element_type+"_"+element_gui.properties[element_type][i]+"'></td>"+
                    "</tr>"
                );                    
            }
            
            $(".interface_on_button,.interface_off_button").hover(function(){
                if(this.id.indexOf("off") !== -1){
                    temp_label = "On";                        
                }
                if(this.id.indexOf("on") !== -1){
                    temp_label = "Off";                        
                }
                $("#"+this.id).val(temp_label);
            });
            
            // when leaving hover zone
            $(".interface_on_button,.interface_off_button").mouseleave(function(){
                if(this.id.indexOf("off") !== -1){
                    temp_label = "Off";
                }
                if(this.id.indexOf("on") !== -1){
                    temp_label = "On";
                }
                $("#"+this.id).val(temp_label);
            });
        }
        $("#"+element_type+"_table").append("</table>");
      
        if(element_gui.properties[element_type][0]=="stimuli"){
            start_i = 1;
            $("#"+element_type+"_stimuli").on("input",function(){
            
                var new_string = $(this).val();
                      
                $("iFrame").contents().find("#"+element_management.selected_element).html(new_string);
              
                trial_management.update_temp_trial_type_template();                
            });
      
        } else {
        start_i = 2;
        $("#"+element_type+"_value").on("input",function(){
        
          var new_string = $(this).val();
                  
          $("iFrame").contents().find("#"+element_management.selected_element)[0].value=new_string;
          
          trial_management.update_temp_trial_type_template();                
        });
        $("#"+element_type+"_name").on("input",function(){
        
          var new_string = $(this).val();
                  
          $("iFrame").contents().find("#"+element_management.selected_element)[0].name=new_string;
          
          trial_management.update_temp_trial_type_template();                
        });
      }
      
      
      for (var i=start_i; i<element_gui.properties[element_type].length; i++){ // skip src
        $("#"+element_type+"_"+element_gui.properties[element_type][i]).on("input",function(){
          var new_style = $(this).val();
          var property_selected = this.id.replace(element_type+"_","");
          $("iFrame").contents().find("#"+element_management.selected_element).css(property_selected,new_style); 
          trial_management.update_temp_trial_type_template();          
        }); 
      };
      
       
      /* 
      placeholder code in next release
      
      if(element_gui.properties[element_type][0]=="value"){
        
        if(element_type != "button"){
          $("#"+element_type+"_value").attr("placeholder",element_gui["placholder"][element_type]);          
        }
        
          $("#"+element_type+"_value").on("input",function(){
        
          var new_string = $(this).val();
                  
          $("iFrame").contents().find("#"+element_management.selected_element).val(new_string);
          
          trial_management.update_temp_trial_type_template();                
        });

        
      } */
      
    },
    
    process_style: function(this_input,this_class) {
      $("#"+this_class+"_table").show();
      
      // deal with "this.my_arr problem"
      element_gui.properties[this_class]
      
      if(element_gui.properties[this_class][0] == "stimuli"){
        var clean_stim = this_input[0].innerHTML.replace(this_class+":","");
        $("#"+this_class+"_stimuli").val(clean_stim);
        
        start_i=1;
      } else { // assuming that it is VALUE
        global_var = this_input;          
        var clean_stim = this_input[0].value.replace(this_class+":","");
        $("#"+this_class+"_value").val(clean_stim);
        var this_name = this_input[0].name;
        
        $("#"+this_class+"_name").val(this_name);
        start_i=2;
      }
      
      
      for (var i=start_i; i<element_gui.properties[this_class].length; ++i) {
        $("#"+this_class+"_" + element_gui.properties[this_class][i]).val(this_input.css(element_gui.properties[this_class][i]));
      }
    },
  }; 
  for(var i=0;i<element_gui.accepted_classes.length;i++){
    var clean_class=element_gui.accepted_classes[i].replace("_element","");
    element_gui.write_html(clean_class);
  }
</script>