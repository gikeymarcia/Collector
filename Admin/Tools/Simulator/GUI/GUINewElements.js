new_element_template = {
  
  count_number_of_elements:function(){
    
  },
  
  image_style: "width:50px;height:50px; border-color:blue;border-style:solid;border-width:2px;",
  video_style: "width:50px;height:50px; border-color:purple;border-style:solid;border-width:2px;",
  
  
  Text: {
    create_element:function(element_name,this_location){
      var new_text_element = "<div id='"+element_name+"' style='"+this_location+"'>This is a text div</div>";
      return new_text_element;
    }
  },
    
  Image: {
    create_element:function(element_name,this_location){
      
      //use generic image like e-prime does       
      

//background: color url('https://dl.dropbox.com/s/m5g0e6zfk1kiqwb/Picture.png?dl=0') others;      
//
      
      var new_stim_element = "<div id='"+element_name+"' style='"+new_element_template.image_style+this_location+"' class='image_element' ></div>";
      return new_stim_element;
    }
  },
  
  
  Audio: {
    create_element:function(element_name,this_location){
      
      //use generic image like e-prime does          
      
      var new_stim_element = "<img id='"+element_name+"' src='https://dl.dropbox.com/s/1ayf8oyr31qbn5q/Audio.png?dl=0' style='"+this_location+"width:100px;height:100px;'>";
      return new_stim_element;
    }
  },
  Video: {
    create_element:function(element_name,this_location){
      
      //use generic image like e-prime does          
      
      var new_stim_element = "<div id='"+element_name+"' style='"+new_element_template.video_style+this_location+"' class='video_element' ></div>";
      return new_stim_element;
    }
  },
  Button: {    
    create_element:function(element_name,this_location){
      var new_text_element = "<input type='button' id='"+element_name+"' style='"+this_location+"' value='Button'>";
      return new_text_element;
    }
  },
  String: {
    create_element:function(element_name,this_location){
      var new_text_element = "<input type='string' id='"+element_name+"' style='"+this_location+"' placeholder='Text/String'>";
      return new_text_element;
    }
  },
  Number: {
    create_element:function(element_name,this_location){
      var new_text_element = "<input type='string' id='"+element_name+"' style='"+this_location+"'>";
      return new_text_element;
    }
  },
  Date: {
    create_element:function(element_name,this_location){
      var new_text_element = "<input type='Date' id='"+element_name+"' style='"+this_location+"'>";
      return new_text_element;
    }
  },
  Checkbox:{
    create_element:function(element_name,this_location){
      var new_text_element = "<label style='"+this_location+"'><span id='"+element_name+"_span'>"+element_name+"</span><input type='Checkbox' id='"+element_name+"'></label>";
      return new_text_element;
    }
  },
  Radio:{
    create_element:function(element_name,this_location){
      var new_text_element = "<label style='"+this_location+"'><span id='"+element_name+"_span'>"+element_name+"</span><input type='radio' id='"+element_name+"'></label>";
      return new_text_element;
    }
  }
}