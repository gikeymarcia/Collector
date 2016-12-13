<span id="T-Tests_area"  class="GUI_type">
    
  <div id="T-Tests">
    <select id="t_test_selection">
      <option value="select">Select an option</option>
      <option value="independent">Independent Samples</option>
      <option value="paired">Paired Samples</option>
      <option value="one_sample">One-Sample</option>
    </select>

    <div class="t_test_div" id="t_test_one_sample"><?php require ("Stats/t_test_one_sample/display.html"); ?></div>
    
    <div class="t_test_div" id="t_test_paired"><?php require ("Stats/t_test_paired/display.html"); ?></div>

    <div class="t_test_div" id="t_test_independent"><?php require ("Stats/t_test_independent/display.html"); ?></div>

    
  </div>
  
  <script>
    
    
    //call js files for each t-test
    
    var t_tests=['t_test_one_sample','t_test_paired','t_test_independent'];
    
    
    for(i=0;i<t_tests.length;i++){
      var display_scrpt = document.createElement('script');    
      display_scrpt.src='Stats/'+t_tests[i]+'/display.js';
      document.head.appendChild(display_scrpt);

      var stats_scrpt = document.createElement('script');    
      stats_scrpt.src='Stats/'+t_tests[i]+'/stats.js';
      document.head.appendChild(stats_scrpt);        
    }
    
    $(".t_test_div").hide();
    $("#t_test_selection").change(function(){
      $(".t_test_div").hide();
      $("#t_test_"+$("#t_test_selection").val()).show();
    });
    
    function bold_p_value(p_value){
      if(p_value<.05){ // if significant
        bold_sig_on="<b>";
        bold_sig_off="</b>";
      } else {                    // if not significant
        bold_sig_on="";
        bold_sig_off="";
      }      
    }
  </script>    
</span>