<style>
  body { 
    color: black; 
    background-color: white; 
  }
  #header { 
    font-size: 180%; 
    text-align: center; 
    margin: 10px 0 40px; 
  }
 
  .tableArea {
    display: block;
    width: 100%;
    box-sizing: border-box;
    padding: 10px 30px;
    vertical-align: top;
  }
  textarea { 
    border-radius: 5px;
    border-color: #E8E8E8  ;
  }
  
  
  .helpType_Col { display: none; }
  #helpTypeDefault { display: block; }
  
  #interfaceBar {
    background-color: #EFE;
    border: 2px solid #6D6;
    border-radius: 8px;
    box-sizing: border-box;
    padding: 2px;
    z-index: 5; 
  }
  
  
  #TableForm {
    display: inline-block;
    width: 75%;
    box-sizing: border-box;
  }
  
  .show_hide_checkbox{
    display:none;
  }
  .hide_show_radio_choices{
    display:none;
  }
  
  .typeHeader{
    color:blue;
  }
  .typeHeader:hover{
    color:green ;
  }
  #hide_show_table td{
    padding:2px;
  }
  #interface_title:hover{
    font-weight:      bold;
    cursor:pointer;
  }
  
  .show_hide_button_select{
    background-color:red;
    color:white;
    padding:2px;
    border-radius:4px;
  }
  .show_hide_button_unselect{
    background-color:blue;
    color:white;
    padding:2px;
    border-radius:4px;
  }
  

    input:checked + .show_hide_span {
        background-color : blue;
        color: white;
    }
    .show_hide_span{
        background-color:transparent;
        border-radius:3px;
        padding:2px;
        color:blue;
    }
  
    .show_hide_span:hover {
        background-color : blue; color:white
    }
    input:checked + .show_hide_span:hover {
        background-color : transparent;
        color:blue;
    }
  
  
  
</style>

<!-- the helper bar !-->
  
<div id="interfaceBar">
    <button id="interfaceActivateButton" class="collectorButton"> Interfaces </button>
  

  
    <div id="InterfaceArea" style="display:none">
        <h1 id="interface_title"> Interfaces </h1>

        <div id="hide_show_control">
            <table id="hide_show_table">
            <?php
                //$simulator_on_off is defined in index of ExperimentEditor
                if(isset($simulator_on_off)){
                    if($simulator_on_off == "on"){
                        
                    } else {
                        $hide_show_elements = ["Conditions","Stimuli","Procedure"];
                    }                    
                } else {
                    $hide_show_elements = ["Presentation","Conditions","Stimuli","Procedure","TrialTypes"];
                }

          
                foreach($hide_show_elements as $hide_show_element){
                    echo "<tr>
                        <td>$hide_show_element</td>
                        <td>
                            <label>
                  
                  
                                <input class='show_hide_checkbox show_hide_button' type='checkbox' id='hide_show_".$hide_show_element."_check' name='hide_show_check' value='$hide_show_element' checked>
                                <span id='show_hide_check_unselect_$hide_show_element' class='show_hide_span'>Show</span>
                            </label>
                        </td>
                        <td>
                            <label>
                
                                <input class='hide_show_radio_choices show_hide_button' type='radio' id='hide_show_".$hide_show_element."_radio' name='hide_show_radio' value='$hide_show_element'>
                                <span id='show_hide_radio_select_$hide_show_element' class='show_hide_span'>Only</span>
                            </label>
                        </td>
                    </tr>";
                }
            
            ?>
            
            
          </table>
        </div>  
    
    <script>
    
$("#interface_title").on("click",function(){
  $("#interface_title").hide();
  $("#InterfaceArea").hide();
  $("#interfaceActivateButton").show();
});

$("#interfaceActivateButton").on("click",function(){
  $("#interface_title").show();
  $("#InterfaceArea").show();
  $("#interfaceActivateButton").hide();
});
    
      var element_show_list = <?= json_encode ($hide_show_elements) ?>;
    
    
    $(".show_hide_button").on("change",function(){
      
      if (this.type === "radio"){
        $(".show_hide_checkbox").prop("checked",false);
        
        
        $(this).closest("tr").find("input[type='checkbox']").
        prop("checked",true);
        
      } else { // it's a checkbox, so radios should be off
        $(".hide_show_radio_choices").prop("checked",false);

      }
      $(".show_hide_checkbox").each(function(){
        var target = $("#" + this.value);
        
        
        if(this.checked){
          target.show();
        } else {
          target.hide();
        }
      });
    });
      
      $(".hide_show_radio_choices").on("change",function(){
        $(".hide_show_elements").hide();
        $("#"+this.value).show();
      });
    
    </script>
    
  
</div>