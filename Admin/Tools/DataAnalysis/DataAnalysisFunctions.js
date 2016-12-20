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


function create_histogram(sum_arrays,histogram_divs,container,plot_names,these_results){
  for(var h =0; h<sum_arrays.length; h++){
    
    var this_plotname      = plot_names[h];
    var this_sum_array     = sum_arrays[h];
    var this_histogram_div = histogram_divs[h];
    var hist_min           = jStat.min(this_sum_array);
    var hist_max           = jStat.max(this_sum_array);
    var hist_range         = hist_max - hist_min;
    var hist_bin_width     = hist_range/10;
    
    col_data={};
    for(i=0;i<10;i++){
      var this_bin_min = i*hist_bin_width+hist_min;
      var this_bin_max = (i+1)*hist_bin_width+hist_min;
      var valid_rows   = 0;
      for(j=0;j<this_sum_array.length;j++){
        if(this_sum_array[j] <= this_bin_max &
           this_sum_array[j] >= this_bin_min){
           valid_rows++;
           console.dir(this_sum_array[j]+"-"+this_bin_min+"-"+this_bin_max);
         }
      }
      col_data[this_bin_min+"-"+this_bin_max]={height:valid_rows};
    }
    col_data=JSON.stringify(col_data);
    
    y_axis = "Frequency";
    
    $.post(
        'histGenerate.php',
        {
            data: col_data,
            yAxis: y_axis
        },
        function(img_url) {
            if (img_url.substring(0, 5) === 'Error') {
              container.find(this_histogram_div).html(img_url);
                
            } else {
              container.find(this_histogram_div).html('<img src="' + img_url + '">');

            }
            window.image_url = img_url;
            these_results[this_plotname] = window.image_url;
        },
        'text'
    );
  }  
}