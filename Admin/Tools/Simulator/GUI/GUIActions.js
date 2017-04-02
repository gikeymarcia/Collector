$("#gui_to_trialtype_button").on("click",function(){
  var current_trial_type = $("#trial_type_select").val();
  var gui_content = $("#temp_trial_type_template").val();
  $("#"+current_trial_type+"template_textarea").val(gui_content);
  $("#"+current_trial_type+"template_textarea").addClass("modified");
  editor.setValue(gui_content);
  
});


$("#gui_to_trialtype_save_button").on("click",function(){
  $("#gui_to_trialtype_button").click();
  save_trial_types();
});


$(".GUI_headers").on("click",function(){
    $(".GUI_divs").hide();
    var this_div = this.id.replace("header","div");
    $("#"+this_div).show();
    $("#gui_interface_edit_element").hide();
});
$("#gui_create_trialtype_button").on("click",function(){
    $("#new_trial_type_button").click();
});

 
$("#select_interactive_function").on("change",function(){
    $(".interactive_divs").hide();
    $("#interactive_"+this.value).show(); 
    
    var this_script_no = interaction_manager.curr_int_no;
    
    temp_GUI_Var[this_script_no]["gui_function"] = this.value;
    // create new row in table above
    
    
    interaction_manager.update_int_target(this.value,"none");
    
    
    interaction_manager.update_current_script();
});


$(".new_element_button").on("click",function(){
    canvas_drawing.new_element_type = this.value;
    add_buttons_reset();    
    this.className = "gui_button_clicked new_element_button";
    ready_to_add();
});


function ready_to_add(){

    $('#canvas_iframe').contents().find('html').on('mousemove', function (e) { 
        canvas_drawing.current_x_co = e.clientX;// + iframepos.left; 
        canvas_drawing.current_y_co = e.clientY;// + iframepos.top;
    });
      
    $('#canvas_iframe').contents().find('html').on('click', function (e) {
        canvas_drawing.draw_new_element();
    });
}