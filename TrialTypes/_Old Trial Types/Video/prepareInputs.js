//  @TODO JS functions for seek/start/stop to set start and stop times.
//  @TODO JS not needed for YouTube vids, use ?start=10&end=14
// 
// 
//  * Warning! This is a very primitive version of the Video trial type.
//  * We expect to make improvements in the future, but in the mean time,
//  * try using the YouTube trial type instead, and load your videos to
//  * YouTube rather than your server.



function isLocal(file_name) {
	var path = "../Experiments/_Common/Media/";
	if ((path + file_name) == true) {
		return true;
	}
    else {
    	return false;
    }
}



// function youtubeUrlCleaner(url, justReturnId = false) {
//     $urlParts = parse_url(stripUrlScheme($url));
// 
//     if ('youtu.be' === strtolower($urlParts['host'])) {
//         // share links: youtu.be/[VIDEO ID]
//         $id = ltrim($urlParts['path'], '/');
//     } elseif (stripos($urlParts['path'], 'watch') === 1) {
//         // watch links: youtube.com/watch?v=[VIDEO ID]
//         parse_str($urlParts['query']);
//         $id = $v;
//     } else {
//         // embed links: youtube.com/embed/[VIDEO ID]
//         // API links: youtube.com/v/[VIDEO ID]
//         $pathParts = explode('/', $urlParts['path']);
//         $id = end($pathParts);
//     }
// 
//     return $justReturnId ? $id : '//www.youtube.com/embed/'.$id;
// }
// 
// 
// function vimeoUrlCleaner($url, $justReturnId = false) {
//     $urlParts = parse_url(stripUrlScheme($url));
//     $pathParts = explode('/', $urlParts['path']);
//     $id = end($pathParts);
// 
//     return $justReturnId ? $id : '//player.vimeo.com/video/'.$id;
// }
// 
// 
// function stripUrlScheme($url) {
//     $stripped = preg_replace('@^(?:https?:)?//@', '//', $url);
//     if (0 !== strpos($stripped, '//')) {
//         $stripped = '//'.$stripped;
//     }
// 
//     return $stripped;
// }





var cue = Trial.get_input('Cue')[0];
var text = Trial.get_input('Text');

if (!isLocal(cue)) {
	if (cue.indexOf('youtube') != -1 || cue.indexOf('youtu.be') != -1) {
		var vidSource = youtubeUrlCleaner(cue);
		var param = 'autoplay=1&modestbranding=1&controls=0&rel=0&showinfo=0&iv_load_policy=3';
	}
	else if (cue.indexOf('vimeo') != -1) {
		var vidSource = vimeoUrlCleaner(cue);
		var param = 'autoplay=1&badge=0&byline=0&portrait=0&title=0';
	}
	else {
		throw 'The given video source is not supported. Please use Vimeo, YouTube, or a local file.';
	}
	var source = vidSource + param;
}
else {
	var source = cue;
}

Trial.add_input('source', source);
Trial.add_input('Text', text);