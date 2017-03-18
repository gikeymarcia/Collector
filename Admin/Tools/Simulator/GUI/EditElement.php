<script src="GUI/GUINewElements.js"></script>

<div id="gui_style">
  <h3 id="selected_element_id"></h3>
  <?php require("Interfaces/all.php"); ?>
           
</div>
<div id="gui_info"></div>  


<script>
    
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
    
    
    selected_element_id = target[0].id;      
    $("#selected_element_id").html(selected_element_id);
    $(target).removeClass("canvasHighlight");
    
    // check the classes first -- in the case of stimuli
    console.dir("detecting target class");
    console.dir(target[0]);
    console.dir(target[0].className);
    
    
    // try to collapse these into a single if statement
    
    
    if(element_gui.accepted_classes.indexOf(target[0].className) !== -1){
      var clean_class = target[0].className.replace("_element","");
      $(".element_table").hide();
      element_gui.process_style(target,clean_class);  
      $("#"+[clean_class]+"_table").show();  
      $(".GUI_divs").hide(); 
    } else {
      alert("Error: not recognising the type of element you are trying to edit.");
    }
    
    

    
    ///////
    // Do the same for other element types, e.g. span_element, div_element, etc.
    //////
    
    
   /*  
    if(target.is("div")|target.is("span")){
      $(".element_table").hide();
      element_gui.span_or_div.process_text_style(target); 
    }
    if(target.is("img")){
      
    } */
     
    // here is where the identification process is
    
    //console.dir($(target).css("color"));
    
  });;;
  
  
  

/* $(window).on("load", function() {
    GUI_FUNCTIONS.run();
}); */
     
</script>

