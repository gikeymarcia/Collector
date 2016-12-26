<style>
  td{
    padding:5px;
  }
  .top_row{
    font-weight:bold;
    border: 1px solid black;
    background-color:#B0ACE8;
  }
  .middle_rows{
    border: 1px solid black;
    background-color:#CECDE2;
  }
  #data_table_properties{
  
  }
</style>

<div id="data_table"></div>
<script>
  
  var top_row="<table id='data_table_properties'><tr>";
  for(var i=0;i<Collector_data_raw[0].length;i++){
    top_row += "<td class='top_row'>"+Collector_data_raw[0][i] +"</td>";
  }
  top_row += "</tr>";
  middle_rows = '';
  for(var h=1;h<Collector_data_raw.length;h++){
    middle_rows+='<tr>';
    for(var i=0;i<Collector_data_raw[h].length;i++){
    middle_rows += "<td class='middle_rows'>"+Collector_data_raw[h][i] +"</td>";
    }
    middle_rows+='</tr>';
  }

  complete_table = top_row + middle_rows+ "</table>";

  $("#data_table").html(complete_table);
  
  
</script>
