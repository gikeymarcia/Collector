element_management = {
  
  selected_element : '',
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
      
      var this_option = $('<option></option>').attr("value", "None").text("None");
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
      
    trial_type_children=$("iFrame").contents().find("#canvas_in_iframe").children();
    preview_trial="";
    
    current_script_no=-1;
    for(var i=0;i<trial_type_children.length;i++){
      
      if(trial_type_children[i].nodeName !== "SCRIPT"){ // this may be redundant and need tidying.
        preview_trial += this.processing_canvas_children(trial_type_children[i])+"\r\n";        
      }
    }
    $("#temp_trial_type_template").val(preview_trial);
    
    
  },
  processing_canvas_children: function(child){     
    
    
    
    if(child.className == "script_element"){  
      current_script_no++;
      // code for replacing span with script //
      
      var this_script = interaction_manager.current_trial_type_script;
    
      return "<script>"+this_script+"</script>";
    } else {
      var stim_style=this.process_element_style(child);
    }
    
    // stimuli
    
    if(child.className == "text_element"){        
      return "<span id='"+child.id+"' style='"+stim_style+"'>"+child.innerHTML+"</span>";
    } 
    if(child.className == "image_element"){        
      return "<img id='"+child.id+"' src='"+child.innerHTML+"' style='"+stim_style+"'>";
    }
    if(child.className == "video_element"){    
      return "<video id='"+child.id+"' src='"+child.innerHTML+"' style='"+stim_style+"'>";
    }    
    if(child.className == "audio_element"){
      return "<audio id='"+child.id+"' src='"+child.innerHTML+"' style='"+stim_style+"'>";
    }
    
    //inputs
    
    if(child.type == "button"){        
      return "<input id='"+child.id+"' type='button' name='"+child.name+"' value='"+child.value+"' style='"+stim_style+"'>";
    }
    
    if(child.type == "text"){        
      return "<input type='text' id='"+child.id+"' name='"+child.name+"' placeholder='"+child.placeholder+"' style='"+stim_style+"'>";
    }
    if(child.type == "number"){        
      return "<input type='number' id='"+child.id+"' name='"+child.name+"' style='"+stim_style+"'>";
    }    
     if(child.type == "date"){
      return "<input type='date' id='"+child.id+"' name='"+child.name+"' style='"+stim_style+"'>";
    }     

    
    
    // rules here for re_integrating scripts
    // rules here for replacing images from stimuli with values in stimuli column
    
    
  },
  process_element_style:function(child){
    clean_classname=child.className.replace("_element","");
    //if the clean_classname == ""
    if(clean_classname==""){ // then this is an input?
      global_child = child;
      clean_classname = child.type;
    }
    var these_props = element_gui.properties[clean_classname];
    var stim_style='';
    if(these_props[0]=="stimuli"){
      start_i=1;
    } else {
      start_i=2;
    }
    for(var i=start_i;i<these_props.length;i++){
      stim_style += these_props[i]+":"+child.style[these_props[i]]+";";
    }
    return stim_style;
  },
  temp_trial_type_to_actual_trial_type:function(){
    
    
  },
  reintegrate_script:function(trial_type_html){
    
    var script_placeholder = "___script___";      
    trial_type_html = trial_type_html.replace(script_placeholder,"<script>"+interaction_manager.current_trial_type_script+"</script>");           
    return trial_type_html;
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
        gui_script=gui_script.replace("GUI_FUNCTIONS.run()","//GUI_FUNCTIONS.run()");
        
        //// note that i need to reimplement GUI_FUNCTIONS.run(); after all is done ///

        eval(gui_script); // creating temp_GUI_Var
        
        
        $("#interactive_gui").html("");     //wipe the interactive_gui

        var interactive_gui_html = '';
        console.dir("script length:" +temp_GUI_Var.length);

        for(i=0;i<temp_GUI_Var.length;i++){
            
            // code here to add spans
            
            
            interactive_gui_html +=   "<span id='gui_interactive_span_"+i+"'><input type='button' id='gui_button"+i+"' class='gui_button_unclicked int_button' onclick='interaction_manager.gui_button_click(\""+[i]+"\")' value='"+temp_GUI_Var[i]['gui_function']+" : "+temp_GUI_Var[i]['target']+"'>"+      
                                        "<input type='button' class='collectorButton' value='delete' onclick='interaction_manager.delete_script("+i+")'>"+
                                    "</span>"+
          "<br>";
          // update max width
            
        }
        
        $("#interactive_gui").html(interactive_gui_html);
        
    }
    global_script_received = script_received;
    
    interaction_manager.update_buttons("temp_fix");
}

interaction_manager = {
    current_trial_type_script:'',
    int_funcs: {},
    curr_int_no: -1,
    update_int_target: function(int_function,input_id){        
        $("#gui_button"+interaction_manager.curr_int_no).html(int_function+" : "+$("#"+input_id).val());          
    },    
    delete_script:function(this_id){
        
        temp_GUI_Var.splice(this_id,1);                           
        this.update_current_script();                       
        gui_script_read(this.current_trial_type_script);       
        
    },
    gui_button_click: function(interactive_no){
  
        //remove clicked class from all elements with unclicked class
        $(".gui_button_unclicked").removeClass("gui_button_clicked");

        $("#gui_button"+interactive_no).toggleClass("gui_button_clicked");
        $("#select_interactive_function").show();
        var this_function = temp_GUI_Var[interactive_no]['gui_function'];
        $("#select_interactive_function").val(this_function);
        $(".interactive_divs").hide();
        $("#interactive_"+this_function).show();

        interaction_manager.curr_int_no = interactive_no;
        interaction_manager.update_interfaces(this_function);

        //update values depending on which function
    },   
    update_buttons:function(temp_fix){
        var new_max_width = Math.max.apply(Math, $('.int_button').map(function(){ return $(this).width(); }).get());
        if(temp_fix == "temp_fix"){
            new_max_width = 500;
        }        
        if(new_max_width < 300){
            $(".int_button").width(new_max_width);
        } else {
            $(".int_button").width(300);
        }
    },  
    update_interfaces: function(curr_int_funcs){
        if(typeof(this.int_funcs[curr_int_funcs]) == "undefined"){
            for(h=0; h<this.int_funcs_list.length;h++){
                for(var i=0;i<this.int_funcs[this.int_funcs_list[h]].length;i++){
                    var this_list = this.int_funcs[this.int_funcs_list[h]][i]; 
                    $("#"+this_list).val("");                      
                }             
            }
        } else {
            
            console.dir(curr_int_funcs);
            console.dir(this.curr_int_no);
        
            for(var i=0;i<this.int_funcs[curr_int_funcs].length;i++){
                var this_list = this.int_funcs[curr_int_funcs][i]; 
                var this_var = this.clean_variable(this_list,curr_int_funcs);
                console.dir("----");
                console.dir(this_list);
                console.dir(this_var);
                console.dir(temp_GUI_Var[this.curr_int_no][this_var]);
                $("#"+this_list).val(temp_GUI_Var[this.curr_int_no][this_var]);                      
            }
        }
    },
    update_temp_GUI_Var: function(current_input){
        var curr_int_func = temp_GUI_Var[this.curr_int_no]["gui_function"];
        clean_current_input=this.clean_variable(current_input,curr_int_func); 
        temp_GUI_Var[this.curr_int_no][clean_current_input] = $("#"+current_input).val();
        this.update_current_script();
    },
    clean_variable:function(input_variable,curr_int_funcs){
        var this_var  = input_variable;
        this_var = this_var.replace("_list","");
        this_var = this_var.replace("_element_","");
        this_var = this_var.replace("interactive_","");
        this_var = this_var.replace(curr_int_funcs,"");
        return this_var;      
    },
    update_current_script:function(){
        var start_splitter = "// --- START GUI FUNCTION ---";
        var end_splitter   = "// --- END GUI FUNCTION ---";
        var these_splitters= [start_splitter,end_splitter];
        var this_split_script = this.current_trial_type_script.split(new RegExp(these_splitters.join("|"), "g"));
        this_split_script[1] = "GUI_FUNCTIONS.settings = "+JSON.stringify(temp_GUI_Var, null, 2)+"\n\r  GUI_FUNCTIONS.run()";
        var first_merge = this_split_script[0] + "// --- START GUI FUNCTION --- \n\r" + this_split_script[1];
        if(typeof(this_split_script[2] == "undefined")){
            this_split_script[2]='';
        }
        this.current_trial_type_script = first_merge + "\n\r // --- END GUI FUNCTION ---" +this_split_script[2];    
        trial_management.update_temp_trial_type_template(); //update preview of code
    }
}

canvas_drawing = {
    new_element_type:'',
    current_x_co:-1,
    current_y_co:-1,
    
    activate_canvas_mouseframe:function(){
      var iframepos = $("iFrame").position(); 

      $('iFrame').contents().find('html').on('mousemove', function (e) { 
        canvas_drawing.current_x_co = e.clientX; 
        canvas_drawing.current_y_co = e.clientY;                
      });         
   
      $('iFrame').contents().find('html').on('click', function (e) { 
        canvas_drawing.draw_new_element();
      });
    },
    
    draw_new_element:function(){
      // needs to redraw the image

      // test to check whether we should proceed;
        if(this.new_element_type !== ""){
            
            console.dir("trying to draw");
            
            
            
            var this_location = 'position:absolute; left:'+this.current_x_co+'px; top:'+this.current_y_co+'px;';
            
            // create pipeline for creating different elements depending on what the button said
            
            var element_type = this.new_element_type;
                           
            var new_element_id = $("iFrame")[0].contentWindow.generate_new_id();
            new_element_content = new_element_template[element_type].create_element(new_element_id,this_location); 
            element_management.canvas_elements_update();
            
            var iframeBody = $("#canvas_iframe").contents().find("#canvas_in_iframe");
            iframeBody.append(new_element_content); 

            canvas_drawing.new_element_type='';            
            add_buttons_reset();
        }
        trial_management.update_temp_trial_type_template();

    }
};
