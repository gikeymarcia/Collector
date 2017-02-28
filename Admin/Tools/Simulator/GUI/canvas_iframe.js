function sort_gui_info() {
      var gui_info = parent.$("#gui_info");
      
      var children_array = [];
      
      var children = gui_info.children().detach().each(function() {
          children_array.push(this);
      });
      console.dir(children_array);
      
      children_array.sort(function(a, b) {
          return $(a).data("parent_count") - $(b).data("parent_count");
      });
      
      children_array.forEach(function(el) {
          gui_info.append(el);
      });
  }
    
function generate_new_id() {
  var count = 0;
  
  while (document.getElementById("element" + count) !== null) {
      ++count;
  }
  
  return "element" + count;
}

var lock_gui_info = false;

$("#canvas_in_iframe").on("mouseenter", "*", function() {
  if (lock_gui_info) return;
  
  if (this.id === "") this.id = generate_new_id();
  
  var class_name = "list_" + this.id;
  
  var gui_info_el = $("<div class='" + class_name + "'>" + this.id + "</div>");
  gui_info_el.data("target", this);
  
  var parent_count = parent.$(this).parents().length;
  gui_info_el.data("parent_count", parent_count);
  
  parent.$("#gui_info").append(gui_info_el);
  
  sort_gui_info();
}).on("mouseleave", "*", function() {
    if (lock_gui_info) return;
    var el = this;
    
    setTimeout(function() {
        if (lock_gui_info) return;
        var class_name = "list_" + el.id;
        
        parent.$("." + class_name).remove();
    }, 50);
}).on("contextmenu", function() {
  //window.parent.$("#gui_interface_edit_element").show();
  
    lock_gui_info = !lock_gui_info;
    
    if (!lock_gui_info) $("#gui_info").html("");
    
    if($("#gui_info").html()==""){
      parent.$("#gui_interface_add_element").show();
      parent.$("#gui_interface_edit_element").hide();
    } else {
      parent.$("#gui_interface_add_element").hide();
      parent.$("#gui_interface_edit_element").show();  
    }        
    
    return false;
}); 
function edit_script(x){
//  console.dir(parent.current_trial_types_script_array[x]);
  parent.gui_script_read(parent.current_trial_types_script_array[x]);
  
}

