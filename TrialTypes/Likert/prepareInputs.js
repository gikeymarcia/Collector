var texts = Trial.get_procedure('Text');
Trial.add_input('question', texts);





var labels = Trial.get_procedure('Labels');
var label = labels.split('|');
for (var i = 0, n = label.length; i < n; i++) {
		Trial.add_input(('label' + i.toString()), label[i]);
}





var settings = Trial.get_procedure('Options');
var setting = settings.split('::');

var likertStart = parseFloat(setting[0].substr(-1));
var likertEnd = parseFloat(setting[1]);


if (likertStart == null) {
	likertStart = 1.0;
}
if (likertEnd == null) {
	likertEnd = 7.0;
}

// Surround this in a try-catch
var extraSettings = settings.split(',');
if (extraSettings[1] == null) {
	var stepSize = 1.0;
}
else {
	var stepSize = parseFloat(extraSettings[1]);
}

var j = 0;
for (i = likertStart, n = likertEnd; i <= n; i+=stepSize) {
	Trial.add_input(('option' + j.toString()), i.toString());
	++j;
}




labelWidth = (Math.floor(1000 / Math.max(1, label.length)) / 10);
optionWidth = (Math.floor(1000 / setting.length) / 10);

Trial.add_input('labelWidth', labelWidth.toString());
Trial.add_input('optionWidth', optionWidth.toString());



/*
 * Determines which text-alignment class to use during the display of the Likert
 * labels.
 * 
 * @param array $texts
 * @param int   $i     Current iteration in the foreach loop.
 * 
 * @return string The class name to use.
 */
// $determineClass = function ($texts, $i) {
//     if (text.length == 2) {
//         if (i == 0) {
//             class = 'textleft';
//         } else {
//             class = 'textright';
//         }
//     } elseif (text.length == 3) {
//         if ($i === 0) {
//             $class = 'textleft';
//         } elseif ($i === 1) {
//             $class = 'textcenter';
//         } else {
//             $class = 'textright';
//         }
//     } else {
//         $class = 'textcenter';
//     }
// 
//     return $class;
// }