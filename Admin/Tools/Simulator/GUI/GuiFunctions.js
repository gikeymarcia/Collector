element_management = {
  
  lists_to_update : [],
  canvas_elements: {},
  new_item: function(new_list){
    for(i=0;i<new_list.length;i++){
      this.lists_to_update.push(new_list[i]);
    }
  },
  canvas_elements_update: function(){
          
    this.canvas_elements = $("iFrame").contents().find("#canvas_in_iframe").children();
    
    var this_element_no = -1;
    var this_element_array = [];
    
    for(i=0; i<this.canvas_elements.length;i++){
      if(this.canvas_elements[i].id == ''){ // check if an id has been assigned
        //check if this is a script
        
        if(element_management.canvas_elements[i].innerHTML.indexOf("___script") == 0){
          // stylising or other processing here
          
          // potential duplication of other processing of script
          
        } else {
          var acceptable_id = false;
          while (acceptable_id == false){
            this_element_no++;
            this_element_id = "element"+this_element_no;
            if(this_element_array.indexOf(this_element_id)==-1){
              acceptable_id = true;
            }          
          }
          this.canvas_elements[i].id = this_element_id;
        }
      }
      this_element_array.push(this.canvas_elements[i].id);
    }
  },
  
  update_lists: function(){
    var lists_to_update = this.lists_to_update;    
    for (i=0;i<lists_to_update.length; i++){
      $("#"+lists_to_update[i]).empty();
      
      var this_option = $('<option></option>').attr("value", "option value").text("--select an option--");
      $("#"+lists_to_update[i]).append(this_option); 
      
      for(j=0; j<this.canvas_elements.length;j++){
        if(this.canvas_elements[j].id==""){
          // then it is a script and should be skipped
        } else {
          var this_option = $('<option></option>').attr("value", this.canvas_elements[j].id).text(this.canvas_elements[j].id);
          $("#"+lists_to_update[i]).append(this_option);
        }
      }
    }    
  },  
}

trial_management = {
  update_temp_trial_type_template:function (){
    var trial_type_html = $("iFrame").contents().find("#canvas_in_iframe").html();
    $("#temp_trial_type_template").val(trial_type_html);
  },
  temp_trial_type_to_actual_trial_type:function(){
    // rules here for re_integrating scripts
    // rules here for replacing images from stimuli with values in stimuli column
  }
}




function gui_script_read(script_received){
  $("#raw_script").val(script_received);
  if(script_received.indexOf("GUI_FUNCTIONS") == -1){
  
      // missing GUI file - do appropriate actions
  } else {
    first_split= script_received.split("// --- START GUI FUNCTION ---");
    second_split= first_split[1].split("// --- END GUI FUNCTION ---");
    gui_script = second_split[0];
    
    gui_script=gui_script.replace("GUI_FUNCTIONS.settings","temp_GUI_Var");
    gui_script=gui_script.replace("GUI_FUNCTIONS.run();","//GUI_FUNCTIONS.run();");
    
    //// note that i need to reimplement GUI_FUNCTIONS.run(); after all is done ///

    eval(gui_script); // creating temp_GUI_Var
    
    $("#interactive_gui").html("");     //wipe the interactive_gui

    var interactive_gui_html = '';
    for(i=0;i<temp_GUI_Var.length;i++){
      interactive_gui_html += i+"<span id='gui_button"+i+"' class='gui_button_unclicked' onclick='interactive_gui_button_click(\""+[i]+"\")'>"+temp_GUI_Var[i]['gui_function']+" : "+temp_GUI_Var[i]['target']+"</span>"+      
//      i+"<input class='collectorButton' type='button' onclick='interactive_gui_button_click(\""+[i]+"\")' value='"+temp_GUI_Var[i]['gui_function']+" : "+temp_GUI_Var[i]['target']+"'>"+
      "<input type='button' class='collectorButton' value='delete'>"+
      "<br>";
    }
    $("#interactive_gui").html(interactive_gui_html);
  }
  global_script_received = script_received;
  
  
  if(script_received.indexOf("detect experiment name") == -1){
    alert("no script");
  } else {
          
  }
}

interaction_manager = {
  int_funcs: {},
  curr_int_no: -1,
  update_temp_GUI_Var: function(curr_int_funcs){
    for(var i=0;i<this.int_funcs[curr_int_funcs].length;i++){
      var this_list = this.int_funcs[curr_int_funcs][i];      
      var this_var  = this_list.replace("interactive_","");
      this_var = this_var.replace("_list","");
      this_var = this_var.replace("_element_","");
      this_var = this_var.replace(curr_int_funcs,"");
      console.dir("update temp console dir");
      
      console.dir(this_list);
      console.dir(this_var);
      console.dir(temp_GUI_Var[this.curr_int_no][this_var]);

      $("#"+this_list).val(temp_GUI_Var[this.curr_int_no][this_var]);
      
    }
  }
}

function interactive_gui_button_click(interactive_no){
  
  //remove clicked class from all elements with unclicked class
  $(".gui_button_unclicked").removeClass("gui_button_clicked");
  
  $("#gui_button"+interactive_no).toggleClass("gui_button_clicked");
  $("#select_interactive_function").show();
  var this_function = temp_GUI_Var[interactive_no]['gui_function'];
  $("#select_interactive_function").val(this_function);
  $(".interactive_divs").hide();
  $("#interactive_"+this_function).show();
  
  interaction_manager.curr_int_no = interactive_no;
  interaction_manager.update_temp_GUI_Var(this_function);
  
  //update values depending on which function
  
  
};