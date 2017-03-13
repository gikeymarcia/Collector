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
    <td>
      <div id="canvas">
        <iframe id="canvas_iframe">          
        </iframe>
      </div>
    </td>
    <td>
      <div id="gui_interface">
        <h3> Interface </h3>
        <h4>
          <input type="button" value="Add" id="add_element_button" class="collectorButton">
          <input type="button" value="Edit" id="edit_element_button" class="collectorButton">
        </h4>
        <br>
        <div id="gui_edit_script">
          <h4> Interactive interface </h4>
          <div id="interactive_gui"></div>
          <input id="add_interactive_button" type='button' class='collectorButton' value='Add'>          
          <textarea id="raw_script"></textarea> 
          <script>
            $("#add_interactive_button").on("click",function(){
              $("#select_interactive_function").val("--- select a function ---");
              $("#select_interactive_function").show();
              $(".interactive_divs").hide();
              // create a new button below the lowest;
              // buttons should be highlighted depending on which script is being edited!
              
            });
          </script>
          <select id='select_interactive_function' style="display:none">
          <div id="gui_script_editor">
            <?php 
              $dir = "GUI/Interactive";
              $interactive_functions = array_diff(scandir($dir), array('.', '..'));
              
              print_r($interactive_functions);
            echo "<option>--- select a function ---</option>";
            foreach ($interactive_functions as $interactive_function){
              $interactive_function = str_ireplace(".php","",$interactive_function);
              echo "<option>$interactive_function</option>";
            }
            ?>
          </select>            
            
            <?php
            foreach ($interactive_functions as $interactive_function){
              $this_div_name = str_ireplace(".php","",$interactive_function);
              echo "<div id='interactive_$this_div_name' class='interactive_divs'>";
                require("GUI/Interactive/$interactive_function");
              echo "</div>";
            }
             
            ?>
          </div>
          
        </div>
        
        <script>
          $("#select_interactive_function").on("change",function(){
            console.dir(this);
            console.dir(this.value);
            $("#interactive_"+this.value).show();            
          });
        
        </script>
        
        <div id="gui_interface_add_element" style="display:none">
          <table id="gui_interface_add_element_table">
            <tr>
              <td colspan="4"><h4>Stimuli</h5></td>
            </tr>
            <tr>
              <td><span class="gui_button_unclicked new_element_button" id="gui_button_new_text">Text</span></td>
              <td><span class="gui_button_unclicked new_element_button" id="gui_button_new_Image">Image</span></td>
              <td><span class="gui_button_unclicked new_element_button" id="gui_button_new_Audio">Audio</span></td>
              <td><span class="gui_button_unclicked new_element_button" id="gui_button_new_Video">Video</span></td>              
            </tr>
            <tr>
              <td colspan="4"><h4>Inputs</h5></td>
            </tr>
            <tr>
              <td><span class="gui_button_unclicked new_element_button" id="gui_button_new_Button">Button</span></td>
              <td><span class="gui_button_unclicked new_element_button" id="gui_button_new_String">String</span></td>
              <td><span class="gui_button_unclicked new_element_button" id="gui_button_new_Number">Number</span></td>
              <td><span class="gui_button_unclicked new_element_button" id="gui_button_new_Date">Date</span></td>              
            </tr>
            <tr>
              <td colspan="4"><h4>Survey buttons</h5></td>
            </tr>
            <tr>
              <td></td>
              <td><span class="gui_button_unclicked new_element_button" id="gui_button_new_Likert">Checkbox</span></td>
              <td><span class="gui_button_unclicked new_element_button" id="gui_button_new_Radio">Radio</span></td>
              
            </tr>
          </table>          
        </div>
        
        
        <script src="GUI/GUINewElements.js"></script>
        <script>
          $(".new_element_button").on("click",function(){
            //console.dir(this.textContent);
            canvas_drawing.new_element_type = this.textContent;
            
          });
          
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
                var testingthis = iframeBody.append(new_element_content);                
              }

              
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
        
        <div id="gui_interface_edit_element">
          <div id="gui_style">
            <h3 id="selected_element_id"></h3>
            <?= require("Interfaces/span_div_present.php") ?>          
          </div>
          <div id="gui_info">
          </div>        
        </div>
      </div>
    </td>
  </tr>
</table>

<script>

  $("#add_element_button").on("click",function(){
    $("#gui_interface_add_element").show();
    $("#gui_interface_edit_element").hide();
  });
  $("#edit_element_button").on("click",function(){
    $("#gui_interface_add_element").hide();
    $("#gui_interface_edit_element").show();
  });
   
    
  $("#gui_info").on("mouseenter", "*", function() {


  // fix the bug that stops this being read!!! //





  
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
    
    if(target.is("div")|target.is("span")){
      element_gui.span_or_div.process_text_style(target);  
    }
     
    // here is where the identification process is
    
    //console.dir($(target).css("color"));
    
  });;;
  
  
  

/* $(window).on("load", function() {
    GUI_FUNCTIONS.run();
}); */
     
</script>


</body>