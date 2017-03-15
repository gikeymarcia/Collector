<div id="text_table" class="element_table" style="display:none"></div>

<script>

  
  if(typeof(element_gui) == "undefined"){
    //console.dir(typeof(element_gui));
    element_gui={};
  } 
  element_gui['span_or_div'] = {
    
    my_arr : ["html","color","background-color","font-size","width","height","padding","border-radius"],
    
    write_html: function() {
      $("#text_table").append("<table>");
      for (var i=0; i<this.my_arr.length; ++i) {
        $("#text_table").append(
          "<tr>"+
            "<td>"+this.my_arr[i]+"</td>"+
            "<td><input id='text_"+this.my_arr[i]+"'></td>"+
          "</tr>");
      }
      $("#text_table").append("</table>");
      
      for (var i=1; i<this.my_arr.length; i++){ // skip html
        $("#text_"+this.my_arr[i]).on("input",function(){
          var new_style = $(this).val();
          var property_selected = this.id.replace("text_","");
          $("iFrame").contents().find("#"+selected_element_id).css(property_selected,new_style); 
          trial_management.update_temp_trial_type_template();          
        }); 
      };
      
      $("#text_"+this.my_arr[0]).on("input",function(){
        var new_string = $(this).val();
        var property_selected = this.id.replace("text_","");
        $("iFrame").contents().find("#"+selected_element_id).html(new_string);trial_management.update_temp_trial_type_template();                
      });      
    },
    
    process_text_style: function(this_input) {
      $("#text_table").show();
      for (var i=0; i<this.my_arr.length; ++i) {
        if(this.my_arr[i] == "html"){
          global_var = this_input;
          $("#text_html").val(this_input[0].innerHTML);  
        } else {
          $("#text_" + this.my_arr[i]).val(this_input.css(this.my_arr[i]));
        }
      }
    },
  };

  element_gui.span_or_div.write_html();

  
  
</script>