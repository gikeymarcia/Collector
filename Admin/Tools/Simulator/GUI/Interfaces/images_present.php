<div id="images_table" class="element_table"></div>

<script>
if(typeof(element_gui) == "undefined"){
    //console.dir(typeof(element_gui));
    element_gui={};
  } 
  element_gui['image'] = {
    
    my_arr : ["src","width","height"],
    
    write_html: function() {
      $("#images_table").append("<table>");
      for (var i=0; i<this.my_arr.length; ++i) {
        $("#images_table").append(
          "<tr>"+
            "<td>"+this.my_arr[i]+"</td>"+
            "<td><input id='image_"+this.my_arr[i]+"'></td>"+
          "</tr>");
      }
      $("#images_table").append("</table>");
      
      for (var i=1; i<this.my_arr.length; i++){ // skip src
        $("#image_"+this.my_arr[i]).on("input",function(){
          var new_style = $(this).val();
          var property_selected = this.id.replace("image_","");
          $("iFrame").contents().find("#"+selected_element_id).css(property_selected,new_style); 
          trial_management.update_temp_trial_type_template();          
        }); 
      };
      
      $("#image_"+this.my_arr[0]).on("input",function(){
        var new_string = $(this).val();
        var property_selected = this.id.replace("image_","");
        $("iFrame").contents().find("#"+selected_element_id)[0].src=new_string;
        trial_management.update_temp_trial_type_template();                
      });      
    },
    
    process_image_style: function(this_input) {
      $("#images_table").show();
      for (var i=0; i<this.my_arr.length; ++i) {
        if(this.my_arr[i] == "src"){
          global_var = this_input;
          $("#image_src").val(this_input[0].src);  
        } else {
          $("#image_" + this.my_arr[i]).val(this_input.css(this.my_arr[i]));
        }
      }
    },
  };

  element_gui.image.write_html();
</script>

