<textarea id="javascript_script"></textarea>
<button type="button" class="collectorButton" id="javascript_script_run_button">run</button>
<button type="button" class="collectorButton" id="javascript_script_clear_output_run_button">clear output and run</button>
<br>
<textarea id="single_line_console" rows=1></textarea>
<div id="console_area"></div>

<script>

  function script_to_console(this_script){
    
    try{                  
      
      if(typeof eval(this_script)== "undefined"){
        console_log+="<pre class='succesfull_code'>"+
                      this_script+
                      "</pre>";
        
      } else {
        console_log+="<pre class='succesfull_code'>"+
                      this_script+
                      "</pre>"+
                      "<pre class='succesfull_eval'>"+
                      eval(this_script)+
                      "</pre>";
      }            
      
      update_var_list();
          
    } catch(err){
      console_log+="<pre class='error_code'>"+err+"</pre>";
    }
    
    $("#console_area").html(console_log);
    $('#console_area').scrollTop($('#console_area')[0].scrollHeight);
  }
  
  //default variables
		var ctrPressed;
		var map = []; // Or you could call it "key"
    
    onkeydown = function(event){
      if(event.which == 13){
//        var input_console = $("#single_line_console");

        if (event.ctrlKey || event.metaKey) { // if SHIFT ENTER
          
          var textComponent = document.getElementById('javascript_script');
          ctrPressed=0;
          var selectedText;
          // IE version
          if (document.selection != undefined){
            textComponent.focus();
            var sel = document.selection.createRange();
            selectedText = sel.text;
          }
          
          // Mozilla version
          else if (textComponent.selectionStart !=  undefined){
            var startPos = textComponent.selectionStart;
            var endPos = textComponent.selectionEnd;
            selectedText = textComponent.value.substring(startPos, endPos)
          }
          
          script_to_console(selectedText);
        }
      }
    }
    
  
    $("#single_line_console").keyup(function(event){
      
      if(event.which == 13){
        var input_console = $("#single_line_console");

        if (event.shiftKey || event.metaKey) { // if SHIFT ENTER
    
          event.preventDefault();
          
          var current_rows = input_console.prop("rows");
          input_console.prop("rows",current_rows+1);
          
        } else { // no shift key
        
          var this_script = $("#single_line_console").val();

          script_to_console(this_script);
          $("#single_line_console").val("");
          input_console.prop("rows",1);
        }
      }

    });
    
    $("#javascript_script_clear_output_run_button").on("click",function(){
      
      $("#output_area").html("");
      
      $("#javascript_script_run_button").click();
    });
    
    
    $("#javascript_script_run_button").on("click",function(){
      
      this_script = $("#javascript_script").val();
      
      script_to_console(this_script);

      return;
   
      
    });  
    
     
  function add_to_script(script_no){
    if($("#javascript_script").val()==''){
      new_line='';
    } else {
      new_line='\n';
    }
    var new_script = $("#javascript_script").val() + new_line + script_array[script_no];
    $("#javascript_script").val(new_script);
    
    $("#script_button").click();// open script editor!!!  
    
  }
</script>