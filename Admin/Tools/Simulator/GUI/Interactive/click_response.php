Which element do you want to click to identify the response:
<select onchange='interaction_manager.update_temp_GUI_Var("interactive_click_response_element_trigger_list"),interaction_manager.update_int_target("click_response","interactive_click_response_element_trigger_list")' id="interactive_click_response_element_trigger_list">
  <option>-- select an element to click --</option>
</select>


What do you want the name of the variable to store the click response in?
<input type="text" id="interactive_click_response_element_response_name" oninput='interaction_manager.update_temp_GUI_Var("interactive_click_response_element_response_name")'>

What do you want the value of the response to be?
<input type="text" id="interactive_click_response_element_response_value" oninput='interaction_manager.update_temp_GUI_Var("interactive_click_response_element_response_value")'>

Do you want the task the task to proceed once you've clicked on the element?
<label>
    <input type="radio" name="click_proceed" value="Yes" class="click_proceed_choice">Yes
</label>
<label>
    <input type="radio" name="click_proceed" value="No" class="click_proceed_choice">No
</label>
<input type="hidden" id="interactive_click_response_element_click_proceed" oninput='interaction_manager.update_temp_GUI_Var("interactive_click_response_element_click_proceed")'>

  
<script>
    $("input[name='click_proceed']").on("click",function(){
        $("#interactive_click_response_element_click_proceed").val(this.value);
        interaction_manager.update_temp_GUI_Var("interactive_click_response_element_click_proceed");
    });
    var click_response_lists = ["interactive_click_response_element_trigger_list"];
  
    var click_response_inputs = click_response_lists;
  
    click_response_inputs.push("interactive_click_response_element_response_name","interactive_click_response_element_response_value",
    "interactive_click_response_element_click_proceed");
  
    element_management.new_item(click_response_lists);
  
    interaction_manager.int_funcs.click_response=click_response_inputs;

</script>