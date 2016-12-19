<br>
<span id="gui_types_buttons">

  <input class="GUI_type_button collectorButton" type="button"    value="Preprocessing" title="To manipulate data in the table, add columns, etc.">
  <input class="GUI_type_button collectorButton" type="button"    value="Descriptives">
  <input class="GUI_type_button collectorButton" type="button"    value="T-Tests">
  <input class="GUI_type_button collectorButton" type="button"    value="Regressions">

  <!-- not yet implemented

  <input class="GUI_type_button collectorButton" type="button"    value="ANOVA">
  <input class="GUI_type_button collectorButton" type="button"    value="Frequencies">

  -->
</span> 
    <br>  <br>
<script>
  
  $(".GUI_type").hide(); // don't know why this isn't hiding all the elements
    
    
  $(".GUI_type_button").on("click",function(){
    $(".GUI_type").hide();
    $("#"+this.value+"_area").show();
  });
      

</script>
  <span class="interface" id="GUI_interface">
    
    <?php require("Preprocessing.php")   ?>
    <?php require("Descriptives.php") ?>
    <?php require("TTests.php")       ?>
    <?php require("Regression.php")   ?>
    
  </span>