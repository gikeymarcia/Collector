// read settings column from procedure
var settings_string = Trail.get_input("settings", "procedure");

// add curly braces (so it will be a valid JSON string)
var json_string = "{" + settings_string + "}";

// parse JSON values so we can do things like settings.question
var settings = JSON.parse(json_string);

// default values:
var default_question = "Should you pay close attention? (hint answer is in the text)";
var default_alternat = ["No.", "I can't read!", "...probably"];
var default_answer   = "Yes.";

// add {question} for the trial
if (typeof settings.question === "undefined") {
    Trial.add_input("question", default_question);
} else {
    Trial.add_input("question", settings.question);
}

// if no answer is defined in settings then use default_answer
if (typeof settings.answer === "undefined") {
    var answer = default_answer;
} else {
    var answer = settings.answer;
}

// if no alternatives are defined in settings then use default_alternat
if (typeof settings.alternatives === "undefined") {
    var alts = default_alternat;
} else {
    var alts = settings.alternatives;
}


// make and then add options
var ops = [];
ops.push({
    "correct": 1,
    "text": answer,
});
alts.map(function(alternative) {
    ops.push({
        "correct": 0,
        "text": alternative
    });
});
ops.shuffle_options();      // custom shuffle written below that

var options = "";
for var (item in ops) {
    if (ops[item]["correct"] === 1) {
        options += '<li class="MCbutton" id="correct">' + ops[item]["text"] + '</li>'
    } else {
        options += '<li class="MCbutton">'              + ops[item]["text"] + '</li>'
    }
}

Trial.add_input("options", options);


Array.prototype.shuffle_options = function() {
    var corrects = this.filter(function(item) {
        if (item.correct === 1) return true;
    }).length;

    if (corrects > 0 && this.length > corrects) {
        // randomly shuffle an array
        function fisherYates ( myArray ) {
          var i = myArray.length;
          if ( i == 0 ) return false;
          while ( --i ) {
             var j = Math.floor( Math.random() * ( i + 1 ) );
             var tempi = myArray[i];
             var tempj = myArray[j];
             myArray[i] = tempj;
             myArray[j] = tempi;
           }
        }
        fisherYates(this);
        while(this[0]['correct'] === 1) {
            fisherYates(this);
        }
    }
}
