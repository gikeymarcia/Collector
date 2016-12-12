<span id="T-Tests_area"  class="GUI_type">
    
  <div id="T-Tests">
    <select id="t_test_selection">
      <option value="select">Select an option</option>
      <option value="independent">Independent Samples</option>
      <option value="paired">Paired Samples</option>
      <option value="one_sample">One-Sample</option>
    </select>

    <div class="t_test_div" id="t_test_one_sample"><?php require ("Stats/t_test_one_sample/display.html"); ?></div>
    
  </div>
  
  <script>
    //call js files for each t-test
    
    var t_tests=['t_test_one_sample']; //'t_test_independent','t_test_paired'
    
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
  </script>    
</span>