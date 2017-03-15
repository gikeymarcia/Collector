
<div id="gui_style">
  <h3 id="selected_element_id"></h3>
  <?= require("Interfaces/span_div_present.php") ?>          
  <?= require("Interfaces/images_present.php") ?>          
  <?= require("Interfaces/video.php") ?>          
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
    
    if(target[0].className == "text_element"){
      $(".element_table").hide();
      element_gui.span_or_div.process_text_style(target);  
      $("#text_table").show();      
    }
    if(target[0].className == "image_element"){
      $(".element_table").hide();
      element_gui.image.process_style(target);  
      $("#images_table").show();      
    }
    if(target[0].className == "video_element"){
      $(".element_table").hide();
      element_gui.video.process_style(target);  
      $("#images_table").show();      
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

