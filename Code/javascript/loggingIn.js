// from stack overflow: http://stackoverflow.com/a/6524584/1310505
$.fn.pressEnter = function(fn) {  
    return this.each(function() {  
        $(this).bind('enterPress', fn);
        $(this).keyup(function(e){
            if(e.keyCode == 13)
            {
              $(this).trigger("enterPress");
            }
        })
    });
 };

function SaltHashSubmit() {
    var nonce = $("#nonce").html();
    var password = $("#pass").val();            // save value of typed password
    var hash = CryptoJS.SHA256(nonce+password); // combine password and hidden characters then scramble
    $("#realInput").val(hash);                  // set scrambled value
    $("form").submit();                         // send scrambled response
}

$(window).ready(function() {
    $("#realInput").val("");        // empty out the hidden input field
    $("input:first").focus();       // focus on the password textbox
    
    $("#fauxSubmit").click(SaltHashSubmit);
    $("#pass").pressEnter(SaltHashSubmit);
});