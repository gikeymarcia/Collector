<div id="images_table" class="element_table"></div>

<script>
if(typeof(element_gui) == "undefined"){
    //console.dir(typeof(element_gui));
    element_gui={};
  } 
  element_gui['image'] = {
    
    my_arr : ["stimuli","width","height"],
    
    write_html: function() {
      
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
      
      $("#image_stimuli").on("input",function(){
        
        var new_string = $(this).val();
        
        
        // check whether this is a valid image using ajax
        // solution by 
        
        $("iFrame").contents().find("#"+selected_element_id).html("image:"+new_string);
        console.dir("hello hello");
        
        trial_management.update_temp_trial_type_template();                
      });      
    },
    
    process_image_style: function(this_input) {
      $("#images_table").show();
      for (var i=0; i<this.my_arr.length; ++i) {
        if(this.my_arr[i] == "html"){
          global_var = this_input;
          $("#image_stimuli").val(this_input[0].html);  
        } else {
          $("#image_" + this.my_arr[i]).val(this_input.css(this.my_arr[i]));
        }
      }
    },
  };
  
  /*
   The code below should be useful for working out how to control what the user sees when they have an innacurate image
  
  function testImage(url, timeoutT) {
    return new Promise(function (resolve, reject) {
        var timeout = timeoutT || 5000;
        var timer, img = new Image();
        img.onerror = img.onabort = function () {
            clearTimeout(timer);
            reject("error");
            return "nooooo";
        };
        img.onload = function () {
            clearTimeout(timer);
            resolve("success");
            return ("YAYAY");
        };
        timer = setTimeout(function () {
            // reset .src to invalid URL so it stops previous
            // loading, but doesn't trigger new load
            img.src = "//!!!!/test.jpg";
            reject("timeout");            
            return "nooooo";
        }, timeout);
        img.src = url;
    });
  }
  function record(url, result) {
    
    console.dir(url);
    console.dir(result);
    if(result=="success"){
      global_image_var =  url;
    } else {
      global_image_var = "https://dl.dropbox.com/s/m5g0e6zfk1kiqwb/Picture.png?dl=0";
    }
    //alert(result+":"+url);
    
//    document.body.innerHTML += "<span class='" + result + "'>" + 
//        result + ": " + url + "</span><br>";

  }   

  function runImage(url) {
      testImage(url).then(record.bind(null, url), record.bind(null, url));
      return global_image_var;
  }
  
  */
  

  element_gui.image.write_html();
</script>

