new_element_template = {
  Text: {
    create_element:function(elementNumber,this_location){
      var new_text_element = "<div id='element"+elementNumber+"' style='"+this_location+"'>This is a text div</div>";
      return new_text_element;
    }
  },
    
  Image: {
    create_element:function(elementNumber,this_location){
      
      //use generic image like e-prime does          
      
      var new_img_element = "<img id='element"+elementNumber+"' src='https://dl.dropbox.com/s/m5g0e6zfk1kiqwb/Picture.png?dl=0' style='"+this_location+"width:100px;height:100px;'>";
      return new_img_element;
    }
  },
  Audio: {
    create_element:function(elementNumber,this_location){
      
      //use generic image like e-prime does          
      
      var new_img_element = "<img id='element"+elementNumber+"' src='https://dl.dropbox.com/s/1ayf8oyr31qbn5q/Audio.png?dl=0' style='"+this_location+"width:100px;height:100px;'>";
      return new_img_element;
    }
  },
  Video: {
    create_element:function(elementNumber,this_location){
      
      //use generic image like e-prime does          
      
      var new_img_element = "<img id='element"+elementNumber+"' src='https://dl.dropbox.com/s/ilssbb4a5ikmn6i/Movie.png?dl=0' style='"+this_location+"width:100px;height:100px;'>";
      return new_img_element;
    }
  },
  Button: {    
    create_element:function(elementNumber,this_location){
      var new_text_element = "<input type='button' id='element"+elementNumber+"' style='"+this_location+"' value='Button'>";
      return new_text_element;
    }
  },
  String: {
    create_element:function(elementNumber,this_location){
      var new_text_element = "<input type='string' id='element"+elementNumber+"' style='"+this_location+"' placeholder='Text/String'>";
      return new_text_element;
    }
  },
  Number: {
    create_element:function(elementNumber,this_location){
      var new_text_element = "<input type='string' id='element"+elementNumber+"' style='"+this_location+"'>";
      return new_text_element;
    }
  },
  Date: {
    create_element:function(elementNumber,this_location){
      var new_text_element = "<input type='Date' id='element"+elementNumber+"' style='"+this_location+"'>";
      return new_text_element;
    }
  },
  Checkbox:{
    placeholder:0
  },
  Radio:{
    placeholder:0
  }
  
  
  
}