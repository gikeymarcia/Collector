Which element do you want to click on to make the trial proceed? If you only want it to be based on a timer, then select "None"
<select onchange='interaction_manager.update_temp_GUI_Var("interactive_proceed_element_target_list"),interaction_manager.update_int_target("proceed","interactive_proceed_element_target_list")' id="interactive_proceed_element_target_list">---Please select---</select><br>
After how much time do you want the trial to proceed?
<input oninput='interaction_manager.update_temp_GUI_Var("interactive_proceed_element_delay")' type="number" id="interactive_proceed_element_delay">



<script>

  var proceed_lists = ['interactive_proceed_element_target_list'];
  
  var proceed_inputs = proceed_lists;
  
  proceed_inputs.push("interactive_proceed_element_delay");
  
  element_management.new_item(proceed_lists);
  
  interaction_manager.int_funcs.proceed=proceed_inputs;

  // actions depending on what was 
  
</script>