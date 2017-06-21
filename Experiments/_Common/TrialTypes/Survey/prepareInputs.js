survey_address = Trial.get_input('Text');

if(survey_address.indexOf("https") == -1){
    survey_address = "../../../Experiments/_Common/Surveys/"+survey_address;
} 
<!-- Warn if try to edit with GUI !--><!-- Warn if try to edit with GUI !-->