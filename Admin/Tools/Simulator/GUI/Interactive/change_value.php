<select onchange='interaction_manager.update_temp_GUI_Var("interactive_change_value_element_target_list"),interaction_manager.update_int_target("change_value","interactive_change_value_element_target_list")' id="interactive_change_value_element_target_list">
  <option>-- select an element to change the value of --</option>
</select>
<br>
Which element do you want to click to change the value?
  
<select onchange='interaction_manager.update_temp_GUI_Var("interactive_change_value_element_trigger_list")' id="interactive_change_value_element_trigger_list">
</select>
<br>
What do you want to change the value to?
  <input oninput='interaction_manager.update_temp_GUI_Var("interactive_change_value_element_new_value")' type="text" id="interactive_change_value_element_new_value">

<script>

  var change_value_lists = ['interactive_change_value_element_target_list',"interactive_change_value_element_trigger_list"];
  
  var change_value_inputs = change_value_lists;
  
  change_value_inputs.push("interactive_change_value_element_new_value");
  
  element_management.new_item(change_value_lists);
  
  interaction_manager.int_funcs.change_value=change_value_inputs;

</script>