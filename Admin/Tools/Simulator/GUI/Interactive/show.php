<select onchange='interaction_manager.update_temp_GUI_Var("interactive_show_element_target_list"),interaction_manager.update_int_target("show","interactive_show_element_target_list")' id="interactive_show_element_target_list">
  <option>-- select an element to show --</option>
</select>
<br>
Which element do you want to click to show the element?
  
<select onchange='interaction_manager.update_temp_GUI_Var("interactive_show_element_trigger_list")' id="interactive_show_element_trigger_list">
</select>

After how much time do you want the element to be hidden?
  <input oninput='interaction_manager.update_temp_GUI_Var("interactive_show_element_delay")' type="number" id="interactive_show_element_delay">

<script>

  var show_lists = ['interactive_show_element_target_list',"interactive_show_element_trigger_list"];
  
  var show_inputs = show_lists;
  
  show_inputs.push("interactive_show_element_delay");
  
  element_management.new_item(show_lists);
  
  interaction_manager.int_funcs.show=show_inputs;

  // actions depending on what was 
  
</script>