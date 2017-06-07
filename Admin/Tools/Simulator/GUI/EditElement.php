<script src="GUI/GUINewElements.js"></script>

<div id="gui_style">
  <table>
    <tr>
      <td><h3 id="selected_element_test"></h3></td>
      <td><input id="delete_element_button" type="button" class="collectorButton" value="delete" style="display:none"></td>
    </tr>
  </table>
  <?php require("Interfaces.php"); ?>
           
</div>
<div id="gui_info"></div>  


<script>
  
$("#delete_element_button").on("click",function(){
    var sel_elem = element_management.selected_element;
    delete_confirm = confirm("Are you sure you want to delete: "+element_management.selected_element);
    if(delete_confirm == true){
        alert ("deleting");
      
        $("iFrame").contents().find("#"+sel_elem).remove();
        $(".element_table").hide();
        $("#gui_info").html("");        
        interaction_manager.update_current_script();                       
        gui_script_read(interaction_manager.current_trial_type_script);
      
    } else {
      alert ("not deleting");
    }
});
  
  $("#gui_info").on("mouseenter", "*", function() {    
    var this_class = $(this)[0].className;
    $("."+this_class).addClass("canvasHighlight");
      
  }).on("mouseleave", "*", function() {
    
    // fix - but secondary if I cannot access data within iframe //
    
    var this_class = $(this)[0].className;
    $("."+this_class).removeClass("canvasHighlight");
  }).on("click", "*" , function(){
      
    this_class = $(this)[0].className;
    this_class = this_class.replace("list_","");
    this_class = this_class.replace(" canvasHighlight","");
    var target = $("iFrame").contents().find("#"+this_class);
    
    
    element_management.selected_element = target[0].id;      
    $("#selected_element_test").html(element_management.selected_element);
    $(target).removeClass("canvasHighlight");
    
    
    if(element_gui.accepted_classes.indexOf(target[0].className) !== -1){
      var clean_class = target[0].className.replace("_element","");
      $(".element_table").hide();
      element_gui.process_style(target,clean_class);  
      $("#"+[clean_class]+"_table").show();  
      $(".GUI_divs").hide(); 
      $("#delete_element_button").show();
      //turn off "adding" of elements??
      
      
      
    } else {
      var this_target_type = target[0].type+"_element";
      if(element_gui.accepted_classes.indexOf(this_target_type) !== -1){
        // processing here
        
        var clean_class = target[0].type;
        clean_class = clean_class.replace("text","string"); // as this as an input
        
        $(".element_table").hide();
        element_gui.process_style(target,clean_class);  
        $("#"+[clean_class]+"_table").show();  
        $(".GUI_divs").hide();
        $("#delete_element_button").show();
        
        
      } else {
        alert("Error: not recognising the type of element you are trying to edit.");
      }
      
    }
    
    
    
  });;;
  

/* $(window).on("load", function() {
    GUI_FUNCTIONS.run();
}); */
     
</script>

