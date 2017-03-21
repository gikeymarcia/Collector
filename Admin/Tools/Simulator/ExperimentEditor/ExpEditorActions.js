$("#new_proc_button").on("click",function(){
  var data = new_experiment_data["Procedure"]["Procedure.csv"];
  var filetype = "Procedure";
  new_proc_stim_sheet(data,filetype);
});  

$("#new_stim_button").on("click",function(){
  var data = new_experiment_data["Stimuli"]["Stimuli.csv"];
  var filetype = "Stimuli";
  new_proc_stim_sheet(data,filetype);
});

$("#new_experiment_button").on("click",function(){
  show_run_stop_buttons();
  var new_name = prompt("What do you want to call your new experient?");
  
  if (typeof experiment_files[new_name] !== "undefined") {
    alert("That name already exists - choose another one");
    $("#new_experiment_button").click();
  } else {
    // contact server to create new structure
    $.post(
      "ExperimentEditor/AjaxNewExperiment.php",
      {
        new_name: new_name
      },
      function(returned_data){
        
        if (returned_data === 'success') {
          create_new_experiment(new_name);
        }
      }
    );
  }
  
  var conditions_data = new_experiment_data['Conditions.csv'];
  var stim_data       = new_experiment_data['Stimuli']['Stimuli.csv']; // this needs to just open the first sheet in list
  
  var proc_data       = new_experiment_data['Procedure']['Procedure.csv']; // this needs to just open the first sheet in list
  
  createExpEditorHoT(conditions_data,"Conditions");
  createExpEditorHoT(stim_data,"Stimuli");      
  createExpEditorHoT(proc_data,"Procedure");
  
});

$("#experiment_select").on("change",function(){
  show_run_stop_buttons();
  $("#experiment_name").val(this.value);
  
  current_stim_list = experiment_files[this.value]['Stimuli'];
  $("#stim_select").html('');
  for(i=0; i<current_stim_list.length;i++){
    $("#stim_select").append("<option>"+current_stim_list[i]+"</option>");      
  }    
  
  current_proc_list = experiment_files[this.value]['Procedures'];
  $("#proc_select").html('');
  for(i=0; i<current_proc_list.length;i++){
    $("#proc_select").append("<option>"+current_proc_list[i]+"</option>");      
  }    
         
  stim_proc_selection("Conditions","Conditions.csv");
  stim_proc_selection("Stimuli",current_stim_list[0]); //  open first file in list
  stim_proc_selection("Procedure",current_proc_list[0]); //  open first file in list
      
});

$("#stim_select").on("change",function(){
  stim_proc_selection("Stimuli",this.value);
}); 
$("#proc_select").on("change",function(){
  stim_proc_selection("Procedure",this.value);
}); 

$("#save_btn").on("click", function(){
  save_current_sheets();
});
   
