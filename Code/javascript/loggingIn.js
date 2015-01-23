var nonce, password, hash;

$(window).ready(function() {
    nonce = $("#nonce").html();     // grab hidden characters that will be mixed with password
    $("#realInput").val("");        // empty out the hidden input field
    $("input:first").focus();       // focus on the password textbox
    
    $("#fauxSubmit").click( function() {            // when the submit button is clicked (it is a fake button)
        var password = $("#pass").val();            // save value of typed password
        var hash = CryptoJS.SHA256(nonce+password); // combine password and hidden characters then scramble
        $("#realInput").val(hash);                  // set scrambled value
        $("form").submit();                         // send scrambled response
    });
});