new_element_template = {
  Text: {
    create_element:function(elementNumber,this_location){
      var new_text_element = "<div id='element"+elementNumber+"' style='"+this_location+"'>This is a text div</div>";
      return new_text_element;
    }
  },
  
  /*
  image: {
    
  },
  */
  
  
}