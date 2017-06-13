/* 
	* Transferring information to the html file with the data from excel Procedure/Stimuli flies
	* Using Trial object add_input function to transfer parameter values
*/


/*
	* Parses out the video ID of a YouTube video from a url
	* param url: the url of the YouTube video (can be many formats)
	* return: the video ID of the YouTube video desired
*/
function youtubeParser(url){
    var regExp = /^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#\&\?]*).*/;
    var match = url.match(regExp);
    return (match&&match[7].length==11)? match[7] : false;
}

var url = Trial.get_input('Cue')[0];
var id = youtubeParser(url);
Trial.add_input('videoID', id);