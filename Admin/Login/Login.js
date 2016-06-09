function submitPassword() {
    // first, hash the password
    var pass = $("#PasswordInput").val();
    
    pass = CryptoJS.SHA256(pass);
    
    for (var i=0, len=hashIterations; i<len; ++i) {
        pass = CryptoJS.SHA256(pass + nonce);
    }
    
    // then, create a form, add the hashed password, and submit
    var form = $("<form method='POST' style='display: none;'>");
    form.append("<input name='password' type='hidden' value='"+pass+"'>");
    $("body").append(form);
    form.submit();
}

// both pressing Enter and clicking the submit button will submit the password
$(document).ready(function() {
    $("#PasswordSubmitButton").click(submitPassword);
    $("#PasswordInput").keypress(function(e) {
        if (e.which === 13) submitPassword();
    });
});
