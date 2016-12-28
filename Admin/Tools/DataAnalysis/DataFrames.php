<td id="variable_list_td">
  <div id="variable_list_area">
    <h3>Data Frames</h3>
    Current Data:
    <span id="data_frames_span"></span>
    <br>
    <button type="button" id="load_data_frame" class="collectorButton">Add Data</button> 
    <button type="button" id="view_variables" class="collectorButton" style="display:none">View Variables</button>
    <div id="data_frame_variable">
      <h3>Columns</h3>
      <div id="column_list"></div>
      <h3>Local Variables</h3>
      <div id="variable_list">
      </div>
    </div>
    <div id="new_data_frame" style="display:none">
    
      Select data:
      <span id="user_data_span"></span> <br>
      <button id="load_user_data_button" class="collectorButton">Load</button>
      <br>
      Load data from web:
      <input type="text" id="web_data_to_load" placeholder="url of data/csv file">
      <button id="load_web_data_button" class="collectorButton">Load</button>
    </div>
  </div>
</td>

<script>
  $("#load_data_frame").on("click",function(){
    $("#data_frame_variable").hide();
    $("#new_data_frame").show();
    $("#load_data_frame").hide();
    $("#view_variables").show();
  });
  $("#view_variables").on("click",function(){
    $("#data_frame_variable").show();
    $("#new_data_frame").hide();
    $("#load_data_frame").show();
    $("#view_variables").hide();

  });
  
  data_frame_list = ['Practice Data'];

  update_data_frame_list(data_frame_list);
  
  function update_data_frame_list(data_frame_list){
    var data_frames_select_content = '<select id="data_frames_select" onchange="update_data_arrays()">';
    for(var i=0; i<data_frame_list.length; i++){
        data_frames_select_content += '<option>'+data_frame_list[i]+  '</option>';
      }
      data_frames_select_content += '</select>';
    $("#data_frames_span").html(data_frames_select_content);    
  }
  

  current_data_frame = "Practice Data";

  function update_data_arrays(){ // cannot jquery this because the select list keeps getting rewritten.
    JSON.parse(JSON.stringify(data_by_columns));
    current_data_frame                  = $("#data_frames_select").val();
    data_by_columns                     = JSON.parse(JSON.stringify(all_data_arrays[current_data_frame]));
  }
  
  
  var user_data_files = ['responses.csv']; // should be reading this using php once new_structure is in place
  
  var user_data_select = '<select id="user_data">';

  for(var i=0; i<user_data_files.length; i++){
    user_data_select += '<option>'+user_data_files[i]+  '</option>';
  }
  user_data_select += '</select>';
  $("#user_data_span").html(user_data_select);
  
  
  $("#load_user_data_button").on("click",function(){
    csv_file_location="temp/"+$("#user_data").val();
    create_new_data_frame(csv_file_location);    
  });
  
  $("#load_web_data_button").on("click",function(){
    csv_file_location = $("#web_data_to_load").val();
    create_new_data_frame(csv_file_location);
  });
  
  function create_new_data_frame(){
    var new_data_frame_name = prompt("What name do you want to give your new data frame?");
    if(all_data_arrays[new_data_frame_name] == undefined){
      ajax_data(csv_file_location,new_data_frame_name);
    } else {
      alert("select a unique name for your new dataFrame");
    }
  }
  
  all_data_arrays = {};
  all_data_arrays['Practice Data'] = data_by_columns;
  
  function ajax_data(csv_file_location,new_data_frame_name){
    console.dir(csv_file_location);
    $.post(
      'AjaxData.php',
      {
        filename : csv_file_location
      },
      function(return_data){
        console.dir(return_data);
        all_data_arrays[new_data_frame_name]=raw_table_to_columns(JSON.parse(return_data));
      }
       
    )
    data_frame_list.push(new_data_frame_name);
    update_data_frame_list(data_frame_list);
    
    var script = "ajax_data('"+csv_file_location+"','"+new_data_frame_name+"')";
    console.dir(script);
    script_array.push(script);
    
    if(typeof data_frame_no == "undefined"){
      data_frame_no=0;
    } else {
      data_frame_no++;
    }
    var container = $("<div>");
    var id = 'data_frame' + data_frame_no;
    
    container.attr('id', id);
    
    var this_title = "<h2>Output </h2>";
    
    container.html(this_title+"<br><div class='graphArea'></div><br><div class='histArea'></div><br>"+
    "<button type='button' id='data_frame"+data_frame_no+"' onclick='add_to_script("+(script_array.length-1)+")'>Add to script</button>"+
    "<button type='button' onclick='remove_from_output(\"data_frame"+data_frame_no+"\")'>Remove from output</button>"+
    "<hr style='background-color:black'></hr>");     
    
    container.appendTo("#output_area");
    
  }
  
  

</script>