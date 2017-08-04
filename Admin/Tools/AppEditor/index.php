<?php
    require '../../initiateTool.php';
    $app_path = $FILE_SYS-> get_path("Apps");
    $app_files = glob("$app_path/*.html");
    foreach($app_files as &$file){
        $filename = explode("/",$file);
        $file = $filename[count($filename) -1];        
    }

    $_SESSION['overwrite_permission'] = "on"; // needs to be more sophisticated
    
?>
<style type="text/css" media="screen">
    #ACE_editor { 
        height:100%;
        width: 100%;
        display:none;
    }
    #canvas_iframe{
        height: 100%;
        width:  100%;
    }
</style>
<h2> App editor </h2>
    
<table style="height:80%; width:100%">
    <tr>
        <td colspan="2">
            <span id="app_select_span"></span>
            
            <button id="rename_app" style="display:none">Rename</button>
            <button id="regenerate_page" style="display:none">
                Preview Page
            </button>
            <button id="save_app" style="display:none">
                Save
            </button>
            <span id="save_status"></span>
            <button id="new_app">New App</button>
            
            
        </td>
    </tr>
    <tr style="height:100%; width:100%">
        <td style="height:50%;width:60%">
            <iframe id="canvas_iframe"></iframe>
        </td>
        <td style="height:50%;width:40%">
            <div id="ACE_editor"></div>
        </td>
    </tr>
</table>

<script src="https://cdn.jsdelivr.net/ace/1.2.6/min/ace.js" type="text/javascript" charset="utf-8"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.2.7/ext-language_tools.js" type="text/javascript" charset="utf-8"></script>

<script>

var app_files = <?= json_encode($app_files) ?>;

function update_app_files(new_app){
    if(new_app !== "-initiate-"){
        if(app_files.indexOf(new_app) == -1){
            app_files.push(new_app);
            $("#ACE_editor").show();
        }
    }
    if(new_app == "-initiate-" | app_files.indexOf(new_app) !== -1){
        app_files_html = '<select id="app_select"><option selected="true" disabled>Select an App</option>';
        app_files.forEach(function(element){
            app_files_html += "<option>"+element+"</option>";
        });
        app_files_html += "</select>";   

        $("#app_select_span").html(app_files_html); 
               
    }
    $("#app_select").on("change",function(){
        //alert($("#app_select").val());
        console.dir(this.value);
        
        ajax_json_read_write(this.value,"","read","app");    
        
        $("#regenerate_page").show(500);
        $("#save_app").show(500);
        $("#rename_app").show(500);
        // load app into ace editor  
        $("#ACE_editor").show();
    });
    
}
update_app_files("-initiate-");



$("#new_app").on("click",function(){
    var new_app_name = prompt("What would you like to call your new app?");
    if(new_app_name !== null){
        ajax_json_read_write (new_app_name,"","write","app");
        
        // update list of apps and select newly created one
        
        
        
    }   
});

parent.ajax_json_location = "../../../Code/classes/Ajax_Json.php";

function save_completed(returned_data){
    $("#save_status").html("saved");
    $("#save_status").fadeIn(1000);
    setTimeout(function(){
        $("#save_status").fadeOut(1000);        
    },3000);
    update_app_files(returned_data);
}

$("#save_app").on("click",function(){
   var this_file = $("#app_select").val();
   var this_content = editor.getValue();   
   ajax_json_read_write (this_file,this_content,"write","app");
});

$(window).bind('keydown', function(event) {
    if (event.ctrlKey || event.metaKey) {
        switch (String.fromCharCode(event.which).toLowerCase()) {
            case 's':
                event.preventDefault();
                $("#save_app").click();
            break;
        }
    }
  
});


$("#regenerate_page").on("click",function(){
    var content = editor.getValue();
    var doc = document.getElementById('canvas_iframe').contentWindow.document;
    doc.open();
    doc.write(content);
    doc.close();
});

function ajax_json_read_write(file,data,read_write_list,json_app){    
    var ajax_path = parent.ajax_json_location;
    $.post(
        ajax_path,
        {
            file:                   file,
            data:                   data,            
            read_write_list:        read_write_list,    
            json_app:               json_app,
        },
        //callback_function,
        function (returned_data){
            if(read_write_list.toLowerCase() == "read"){
                load_completed(returned_data);
            }
            if(read_write_list.toLowerCase() == "write"){
                save_completed(returned_data); // redundant - no saving should be occurring!
            }
            if(read_write_list.toLowerCase() == "list"){                
                list_completed(returned_data); // redundant - no saving should be occurring!
            }
        },
        //document.getElementById(ajax_textarea).value=return_data,
        'text'
    );  
}

function load_completed(returned_data){
    console.dir(returned_data);
    editor.setValue(returned_data);
    
    // write returned_data into iframe
    var doc = document.getElementById('canvas_iframe').contentWindow.document;
    doc.open();
    doc.write(returned_data);
    doc.close();
}



var editor = ace.edit("ACE_editor");
    editor.setTheme("ace/theme/chrome");       
    editor.getSession().setMode("ace/mode/html");
    
    editor.setOptions({
        enableBasicAutocompletion: true,
        enableSnippets: false,
        enableLiveAutocompletion: true,  
        wrap:true,
    });
    
    langTools = ace.require('ace/ext/language_tools'); 
    
    
    editor.completers.push({
        getCompletions: function(editor, session, pos, prefix, callback) {
            callback(null, [
                {value: "get_new_data", score: 1000, meta: "COAST"},
                {value: "load_json_index_file", score: 1000, meta: "COAST"}
            ]);
        }
    })
    
    
    
</script>