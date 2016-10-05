Trial.scoring = function(data) {
    var answer = Trial.get_stimuli('answer')[0];

    if (typeof data.Response !== 'undefined'
        && typeof answer !== 'null'
    ) {
        var resp = data.Response;
        var stem = Trial.get_input('answer stem');
        var noStemAcc = calculate_percent_similar(resp       , answer);
        var StemAcc   = calculate_percent_similar(stem + resp, answer);

        var Accuracy = Math.max(noStemAcc, StemAcc);

        // if prepending the stem increases accuracy then
        // save the data.Response as stem + response
        // e.g., if I write 'mpsons' when the correct answer is
        // 'simpsons' then I will save my response as:
        // 'simpsons' even though I only typed 'mpsons'
        if (StemAcc > noStemAcc) data.Response = stem + resp;

        data['Accuracy']   = Accuracy;
        data['strictAcc']  = (Accuracy == 1)   ? 1 : 0;
        data['lenientAcc'] = (Accuracy >= .75) ? 1 : 0;
    }
    return data;
}
