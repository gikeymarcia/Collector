  
  function report_script(input){
    console.dir(input);

    new Function (input)();    
    
    outputArea.innerHTML += "<br>" + input ;
  }
  
