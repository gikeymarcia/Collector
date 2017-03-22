<select onchange='interaction_manager.update_temp_GUI_Var("interactive_hide_element_target_list"),interaction_manager.update_int_target("hide","interactive_hide_element_target_list")' id="interactive_hide_element_target_list">
  <option>-- select an element to hide --</option>
</select>
<br>
Which element do you want to click to hide the element?
  
<select onchange='interaction_manager.update_temp_GUI_Var("interactive_hide_element_trigger_list")' id="interactive_hide_element_trigger_list">
</select>

After how much time do you want the element to be hidden?
  <input oninput='interaction_manager.update_temp_GUI_Var("interactive_hide_element_delay")' type="number" id="interactive_hide_element_delay">

<script>

  var hide_lists = ['interactive_hide_element_target_list',"interactive_hide_element_trigger_list"];
  
  var hide_inputs = hide_lists;
  
  hide_inputs.push("interactive_hide_element_delay");
  
  element_management.new_item(hide_lists);
  
  interaction_manager.int_funcs.hide=hide_inputs;

  // actions depending on what was 
  
</script>