<button id="manuscript_view" value="view" class="manuscript_button collectorButton">Manuscript</button>
<button id="manuscript_output" value="output" class="manuscript_button collectorButton">HTML</button>

<div id="manuscript_view_div" class="manuscript_divs">
  <textarea id="notes_script"></textarea>
  <button id="new_formula_button" value="new_formula">Add result</button>
</div>
<div id="manuscript_output_div" class="manuscript_divs">- you have not written anything into the manuscript</div>

<div id="manuscript_formulas"></div>

<script>
  
  $("#notes_script").on("keyup",function(){
    var this_manuscript_script = $("#notes_script").val();
    this_manuscript_script = this_manuscript_script.split(/<|>/);
    for(i=0;i<this_manuscript_script.length;i++){
      try{
        var this_subsection = eval(this_manuscript_script[i]);
        this_manuscript_script[i] = "<b>"+this_subsection+"</b>";
      }
      catch (err){
        // Do nothing - right?
      }
    }    
    this_manuscript_script = this_manuscript_script.join("");
    $("#manuscript_output_div").html(this_manuscript_script);
  });
  
  var report_output = {};
  
  var formulas_array = ['hello'];
  
  $(".manuscript_button").on("click",function(){
    $(".manuscript_divs").hide();
    $("#manuscript_"+this.value+"_div").show();    
  });
  
  $("#new_formula_button").on("click",function(){
    alert("this will allow you to identify what script is required to generate the results you are reporting");
  });
  
  function store_results(these_results){
    report_output[Object.keys(report_output).length]=(these_results);    
  }
</script>

<!-- security is an issue --- obvs !-->