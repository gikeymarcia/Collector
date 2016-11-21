function get_script_loader() {
    var loaded_scripts = [];
    
    return function(script_src) {
        if (loaded_scripts.indexOf(script_src) === -1) {
            var script = document.createElement('script');
            script.src = script_src;
            document.head.appendChild(script);
            
            loaded_scripts.push(script_src);
        }
    }
}

var load_script = get_script_loader();

function add_to_script(this_script){
  alert(this_script);
  $("#javascript_script").val($("#javascript_script").val()+this_script);
}

function remove_from_output(output_id){
  console.dir(output_id);
  
  $("#"+output_id).hide();
//  document.getElementById("descriptive_table1")
//  descriptive_table1
  
}
