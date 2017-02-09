<div id="div_span_table"></div>

<script>

  
  if(typeof(element_gui) == "undefined"){
    //console.dir(typeof(element_gui));
    element_gui={};
  } 
  element_gui['span_or_div'] = {
    my_arr : ["color","background-color","font-size","width","height","border-radius"],
    
    write_html: function() {
      $("#div_span_table").append("<table>");
      for (var i=0; i<this.my_arr.length; ++i) {
        $("#div_span_table").append(
          "<tr>"+
            "<td>"+this.my_arr[i]+"</td>"+
            "<td><input id='text_"+this.my_arr[i]+"'></td>"+
          "</tr>");
      }
      $("#div_span_table").append("</table>");
      
      for (var i=0; i<this.my_arr.length; i++){
        
         $("#text_"+this.my_arr[i]).on("input",function(){
           
          var new_style = $(this).val();
          
          var property_selected = this.id.replace("text_","");
          
          $("iFrame").contents().find("#"+selected_element_id).css(property_selected,new_style);
          
        }); 
      }
    },
    
    process_text_style: function(this_input) {
      for (var i=0; i<this.my_arr.length; ++i) {
        $("#text_" + this.my_arr[i]).val(this_input.css(this.my_arr[i]));
      }
    },
};

  element_gui.span_or_div.write_html();

  
  
</script>