Which keys do you want to be valid? <input type="text" id="interactive_keyboard_response_element_accepted_keys" oninput='interaction_manager.update_temp_GUI_Var("interactive_keyboard_response_element_accepted_keys")'><br>
What do you want the name of the variable to store the keyboard response in?
<input type="text" id="interactive_keyboard_response_element_response_name" oninput='interaction_manager.update_temp_GUI_Var("interactive_keyboard_response_element_response_name")'>

Do you want the task the task to proceed once you've pressed a key response?
<label>
    <input type="radio" name="keyboard_proceed" value="Yes" class="keyboard_proceed_choice">Yes
</label>
<label>
    <input type="radio" name="keyboard_proceed" value="No" class="keyboard_proceed_choice">No
</label>
<input type="hidden" id="interactive_keyboard_response_element_keyboard_proceed" oninput='interaction_manager.update_temp_GUI_Var("interactive_keyboard_response_element_keyboard_proceed")'>

  
<script>
    $("input[name='keyboard_proceed']").on("click",function(){
        $("#interactive_keyboard_response_element_keyboard_proceed").val(this.value);
        interaction_manager.update_temp_GUI_Var("interactive_keyboard_response_element_keyboard_proceed");
    });
    var keyboard_response_lists = [];
  
    var keyboard_response_inputs = keyboard_response_lists;
  
    keyboard_response_inputs.push("interactive_keyboard_response_element_accepted_keys","interactive_keyboard_response_element_response_name",
    "interactive_keyboard_response_element_keyboard_proceed");
  
    element_management.new_item(keyboard_response_lists);
  
    interaction_manager.int_funcs.keyboard_response=keyboard_response_inputs;

</script>