survey_address = Trial.get_input('Text');

alert(survey_address.indexOf("https"));

if(survey_address.indexOf("https") == -1){
    survey_address = "../../../Experiments/_Common/Surveys/"+survey_address;
} 
<!-- Warn if try to edit with GUI !-->