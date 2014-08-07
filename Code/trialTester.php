<?php
    require 'initiateCollector.php';
    
    include 'Header.php';
    
    include 'scanTrialTypes.php';
    $trialTypeOptions = '<option>' . implode('</option><option>', array_keys($trialTypes)) . '</option>';
    
?>
<style>
    iframe  {   border: 1px solid #000; width: 100%;    height: 800px; }
    .trialOption    {   text-align: center; margin: 15px;   display: inline-block;   }
</style>
<script>
    $(window).load(function(){
        $(":input").on("change", function(){
            $("iframe").attr("src", "trialLoader.php?" + 
                "trialType=" + encodeURIComponent($("select[name='trialType']").val()) + 
                "&Cue=" + encodeURIComponent($("input[name='Cue']").val()) + 
                "&Answer=" + encodeURIComponent($("input[name='Answer']").val()) + 
                "&Text=" + encodeURIComponent($("input[name='Text']").val())
            )
        });
    });
</script>
<div class="textcenter">
    <h2>Welcome to the trial type tester!</h2>
    <div>
        Please choose the settings you want.
        <form method="post" action="">
            <div class="trialOption">
                Trial Type<br>
                <select name="trialType">
                    <option hidden disabled selected></option>
                    <?= $trialTypeOptions ?>
                </select>
            </div>
            <div class="trialOption">
                Cue<br>
                <input type="text" name="Cue" />
            </div>
            <div class="trialOption">
                Answer<br>
                <input type="text" name="Answer" />
            </div>
            <div class="trialOption">
                Text<br>
                <input type="text" name="Text" />
            </div>
        </form>
    </div>
    <iframe src="trialLoader.php"></iframe>
</div>
<?php 
    
    include 'Footer.php';
    