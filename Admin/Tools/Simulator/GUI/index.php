<style>
  .GUI_headers:hover{
    color:green;
  }
  .GUI_headers{
    width:200px;
  }
  .GUI_divs{
    display:none;
  }
</style>

<head>
    <title>Tests</title>
    <meta charset="utf-8">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>    
    <link rel="stylesheet" type="text/css" href="GUI/GuiStyle.css" media="screen" />               

</head>
<body>

<script src="GUI/GuiFunctions.js"></script>

<table id="gui_table">
  <tr>
    <td id="current_trial_type_name">
      <h3 id="gui_trialtype_header">[Select a trialtype to edit or create a trialtype]</h3>
      <input type="button" value="Create" class="collectorButton" id="gui_create_trialtype_button">
      <input type="button" value="Apply" class="collectorButton" id="gui_to_trialtype_button">
      <input type="button" value="Apply and Save" class="collectorButton" id="gui_to_trialtype_save_button">
    
    </td>
  </tr>
  <tr id="entire_gui_interface" style="display:none">
    <td>
      <div id="canvas">
        <iframe id="canvas_iframe">          
        </iframe>
      </div>
    </td>
    <td>
      <div id="gui_interface">
        
        <table>
          <tr>
            <td><h3 id="gui_add_element_header" class="GUI_headers"> Add Element </h3></td>
            <td><h3 id="gui_edit_script_header" class="GUI_headers"> Interactive interface </h3></td>
            <td><h3 id="gui_code_preview_header" class="GUI_headers">Code Preview</h3></td>            
          </tr>
        </table>
        
        <div id="gui_add_element_div" class="GUI_divs" style="display:none">                  
          <?php require("AddElement.php") ?>   
        </div>
        
        <div id="gui_interface_edit_element">
          <?php require("EditElement.php") ?> <!-- better place to put this? -->
        </div>

          
        <div id="gui_edit_script_div" class="GUI_divs">                  
          <?php require("Interactive.php") ?>
        </div>
        <div id="gui_code_preview_div" class="GUI_divs">
          <textarea id="temp_trial_type_template" readonly></textarea>
          <br>
          
        </div>        
      </div>
    </td>
  </tr>
</table>

</body>
<script>        

$("#gui_to_trialtype_button").on("click",function(){
  var current_trial_type = $("#trial_type_select").val();
  var gui_content = $("#temp_trial_type_template").val();
  $("#"+current_trial_type+"template_textarea").val(gui_content);
  $("#"+current_trial_type+"template_textarea").addClass("modified");
});

$("#gui_to_trialtype_save_button").on("click",function(){
  $("#gui_to_trialtype_button").click();
  save_trial_types();
});

$(".GUI_headers").on("click",function(){
  $(".GUI_divs").hide();
  console.dir(this.id);
  var this_div = this.id.replace("header","div");
  $("#"+this_div).show();
  $("#gui_interface_edit_element").hide();
});
/* 
$("#elements_editor_header").on("click",function(){
  $("#gui_interface_add_element").show();
});
$("#gui_interface_add_element").on("click",function(){
  $("#gui_edit_script").show();
});
 */

$("#gui_create_trialtype_button").on("click",function(){
    $("#new_trial_type_button").click();
});

$("#select_interactive_function").on("change",function(){
    $("#interactive_"+this.value).show(); 
    
    console.dir(typeof(temp_GUI_Var));
    console.dir(temp_GUI_Var[0]["gui_function"]);
    console.dir("look up");
    if(typeof(temp_GUI_Var[0]["gui_function"]) == "undefined"){
        interaction_manager.curr_int_no=0;
        
        temp_GUI_Var = {
        }
        temp_GUI_Var[0] = {
            gui_function:"",
        }    
        
    // need to draw in span to alert the task that there is script to be written in!
    
    $("#canvas")
    
    var iframe_width = $("iFrame").width();
    var mouseover_mouseout = "onmouseover='this.style.color=\"black\"' "+
                               "onmouseout='this.style.color=\"white\"' ";
      
    script_style="style='position:absolute;bottom:0px;left:0px;width:"+iframe_width+"px;background-color:blue;color:white;opacity:90%;padding:0px;text-align:center'";
    
    
    var script_span = "<span "+mouseover_mouseout+" "+script_style+" onclick='edit_script(0)' class='script_element' id='gui_script'>___script0___</span>";
    
    var iframeBody = $("#canvas_iframe").contents().find("#canvas_in_iframe");
    iframeBody.append(script_span);
    
    
    $("iFrame").append("<div>hello</div>");
        
    }
    console.dir(temp_GUI_Var[0]["gui_function"]);
    
    var this_script_no = interaction_manager.curr_int_no;
    console.dir("scrip no = "+this_script_no);
    
    
    temp_GUI_Var[this_script_no]["gui_function"] = this.value;
    
    // create new row in table above
    new_int_row =   "<span id='gui_interactive_span_"+this_script_no+"'>"+this_script_no+
                        "<span id='gui_button"+this_script_no+"' class='gui_button_unclicked' onclick='interactive_gui_button_click(\""+[this_script_no]+"\")'>"+temp_GUI_Var[this_script_no]['gui_function']+" : "+temp_GUI_Var[this_script_no]['target']+"</span>"+      
                            "<input type='button' class='collectorButton' value='delete'>"+
                    "</span>"+
                    "<br>";
    
    
    $("#interactive_gui").append(new_int_row);

    
    interaction_manager.update_current_script();
});


  $(".new_element_button").on("click",function(){
    //console.dir(this.textContent);
    canvas_drawing.new_element_type = this.textContent;
    add_buttons_reset();    
    this.className = "gui_button_clicked new_element_button";
    
  });
  
  function add_buttons_reset(){
    $(".new_element_button").removeClass("gui_button_clicked");
    $(".new_element_button").addClass("gui_button_unclicked");
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
        
        
        console.dir(this.new_element_type);
        var element_type = this.new_element_type;
        
        // safe way to work out number
        
                       
        var new_element_id = $("iFrame")[0].contentWindow.generate_new_id();
        new_element_content = new_element_template[element_type].create_element(new_element_id,this_location); 
        element_management.canvas_elements_update();
        
        
        
        
        var iframeBody = $("#canvas_iframe").contents().find("#canvas_in_iframe");
        iframeBody.append(new_element_content); 

        canvas_drawing.new_element_type='';            
        add_buttons_reset();
      }
      trial_management.update_temp_trial_type_template();

      
      //var new_element_content = this.new_element_type;
      //$("#canvas_iframe").append(new_element_content);
    }
  };
  
  var iframepos = $("#canvas_iframe").position(); 

  $('#canvas_iframe').contents().find('html').on('mousemove', function (e) { 
    canvas_drawing.current_x_co = e.clientX;// + iframepos.left; 
    canvas_drawing.current_y_co = e.clientY;// + iframepos.top;
    //console.log(x + " " + y);
  });
  
  $('#canvas_iframe').contents().find('html').on('click', function (e) { 
    console.dir(canvas_drawing.current_x_co + " " + canvas_drawing.current_y_co);
    canvas_drawing.draw_new_element();
  });
  
  
</script>