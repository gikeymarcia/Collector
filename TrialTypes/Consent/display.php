<?php
    if (($cue != '')
        AND (trim($text) == '')
    ){
        $text = $cue;
    }
    
    $fileDir = $_PATH->get('Common');
    $filePath = fileExists("$fileDir/$text");
    
    if ($filePath !== false AND strpos($text, '..') === false) {
        $pathinfo = pathinfo($filePath);
        if (isset($pathinfo['extension'])) {
            $ext = strtolower($pathinfo['extension']);
        } else {
            $ext = '';
        }
        
        if ($ext === 'pdf') {
            $consent = "<object data='$filePath' type='application/pdf' width='978px' height='800px'>"
                     .     "alt : <a href='$filePath'>consent.pdf</a>"
                     . "</object>";
        } elseif ($ext === 'html' || $ext === 'htm') {
            $consent = file_get_contents($filePath);
        } elseif ($ext === 'php') {
            unset($consent);
            require $filePath; // this file should create the $consent variable
            if (!isset($consent)) {
                $consent = $text;
            }
        } else {
            $consent = '<pre>' . file_get_contents($filePath) . '</pre>';
        }
    } else {
        $consent = $text;
    }
?>
<style>
    body > form { width: 1000px; }
    object { border: 1px solid #999; }
    pre {
        font-family: inherit;
        font-size: 110%;
        display: inline-block; 
        margin: 0 auto;
        text-align: left;
        min-width: 400px;
        max-width: 100%;
        overflow: auto;
        box-sizing: border-box;
        resize: both;
        max-height: 700px;
    }
    .consentFormArea {
        text-align: center;
    }
    .consentFormArea > div {
        font-family: "Palatino Linotype", "Book Antiqua", Palatino, serif;
        font-size: 120%;
        padding: 6px 8px;
        border: 2px solid #99A;
        background-color: #EEF;
        display: inline-block;
        text-align: left;
        min-width: 400px;
        max-width: 100%;
        box-sizing: border-box;
    }
</style>

<div class="consentFormArea"><div><?php echo $consent; ?></div></div>

<div class="textcenter">
    <button class="collectorButton collectorAdvance" id="FormSubmitButton">Next</button>
</div>

<script>
    $(window).load(function() {
        $(":submit").blur();
    });
</script>