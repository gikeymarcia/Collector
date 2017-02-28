<?php

  
?>


<select id="interactive_hide_element_target_list">
  <option>-- select an element to hide --</option>
</select>
<br>
What do you want to make the element hide?
<select id="interactive_hide_element_trigger_list">
  <option>Clicking another element</option>
  <option>After an amount of time</option>
</select> 
<br>
Which element do you want to click to hide the element?
  
<select id="interactive_hide_element_clicked_list">
</select>

<div id="hide_timer">
  After how much time do you want the element to be hidden?
  <input type="time">
</div>

<script>
  var hide_lists = ['interactive_hide_element_target_list',"interactive_hide_element_clicked_list"];

  
  element_management.new_item(hide_lists);
  
  interaction_manager.int_funcs.hide=hide_lists;

  // actions depending on what was 
  
</script>