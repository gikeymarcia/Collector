
function ajaxSave(){
  
  $("#save_status").html("saving");  
  
  var stimuli = JSON.stringify(stimTable.getData());
  
  $.get(
    'AjaxSave.php',
    { file    : currentFileLocation,
      content : stimuli,        
    } , 
    function(returned_data) {
      $("#save_status").html("Updates completed");  
    }
  );
  
}
