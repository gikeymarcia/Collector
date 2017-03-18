new_element_template = {
  
  count_number_of_elements:function(){
    
  },
  
  image_style: "width:50px;height:50px; border-color:blue;border-style:solid;border-width:2px;",
  video_style: "width:50px;height:50px; border-color:purple;border-style:solid;border-width:2px;",
  audio_style: "width:50px;height:50px; border-color:red;border-style:solid;border-width:2px;",
  
  
  Text: {
    create_element:function(element_name,this_location){
      var new_text_element = "<div id='"+element_name+"' style='"+this_location+"' class='text_element'>This is a text div</div>";
      return new_text_element;
    }
  },
    
  Image: {
    create_element:function(element_name,this_location){
      
      //use generic image like e-prime does?
      
      var new_stim_element = "<div id='"+element_name+"' style='"+new_element_template.image_style+this_location+"' class='image_element' ></div>";
      return new_stim_element;
    }
  },
  
  
  Audio: {
    create_element:function(element_name,this_location){
      
      //use generic image like e-prime does          
      
      var new_stim_element = "<div id='"+element_name+"' style='"+new_element_template.audio_style+this_location+"' class='audio_element' ></div>";
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
      var new_text_element = "<input type='button' id='"+element_name+"' style='"+this_location+"' class='button_element' value='Button'>";
      return new_text_element;
    }
  },
  
  String: {
    create_element:function(element_name,this_location){
      var new_text_element = "<input type='string' id='"+element_name+"' style='"+this_location+"' class='string_element' placeholder='Text/String'>";
      return new_text_element;
    }
  },
  
  Number: {
    create_element:function(element_name,this_location){
      var new_text_element = "<input type='number' id='"+element_name+"' style='"+this_location+"' class='number_element' placeholder='Text/String'>";
      return new_text_element;
    }
  },
  
  Date: {
    create_element:function(element_name,this_location){
      var new_text_element = "<input type='Date' id='"+element_name+"' style='"+this_location+"' class='date_element'>";
      return new_text_element;
    }
  },
  /*
  Questionnaire buttons will be included in later release
  
  Checkbox:{
    create_element:function(element_name,this_location){
      var new_text_element = "<input type='Checkbox' style='"+this_location+"' id='"+element_name+"' class='checkbox_element'>";
      return new_text_element;
      
      
      //these labels can be implemented later
      
      // <label style='"+this_location+"'><span id='"+element_name+"_span'>"+element_name+"</span>
      //</label>
    }
  },
  Radio:{
    create_element:function(element_name,this_location){
      var new_text_element = "<input type='radio' style='"+this_location+"' id='"+element_name+"' class='radio_element'>";
      return new_text_element;
      //<label style='"+this_location+"'><span id='"+element_name+"_span'>"+element_name+"</span>
      //these labels can be implemented later
      //</label>
    }
  }
  */
}