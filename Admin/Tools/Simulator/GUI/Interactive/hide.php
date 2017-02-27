<?php

  
?>


<select id="interactive_hide_element_hidden_list">
  <option>-- select an element to hide --</option>
</select>
<br>
What do you want to make the element hide?
<select id="interactive_hide_trigger_list">
  <option>Clicking another element</option>
  <option>After an amount of time</option>
</select>

<div id="interactive_hide_element_clicked_list">
  Which element do you want to click to hide the element?
  
</div>

<div id="hide_timer">
  After how much time do you want the element to be hidden?
  <input type="time">
</div>

<script>
  update_all_lists.lists_to_update.push("interactive_hide_element_hidden_list");
  update_all_lists.lists_to_update.push("interactive_hide_element_clicked_list");

  // actions depending on what was 
  
</script>