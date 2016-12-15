<?php
    require '../../initiateTool.php';
?>
<style>
    #img_gen_page_container { padding: 10px; }
</style>

<div id="img_gen_page_container">

<div>
    <textarea id="col_data"></textarea> <br>
    <input id="y_axis_label"> <br>
    <button type="button" id="img_generation_btn">Get Image</button>
</div>

<div id="img_area"></div>

<script>
    $("#img_generation_btn").on("click", function() {
        var col_data = $("#col_data").val();
        var y_axis   = $("#y_axis_label").val();
        
        $.post(
            'imgGenerate.php',
            {
                data: col_data,
                yAxis: y_axis
            },
            function(img_url) {
                if (img_url.substring(0, 5) === 'Error') {
                    $("#img_area").html(img_url);
                } else {
                    $("#img_area").html("<img src='" + img_url + "'>");
                }
            },
            'text'
        );
    });
</script>

</div>
