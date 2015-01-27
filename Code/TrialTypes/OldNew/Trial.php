<?php
    $compTime = 8;                 // time in seconds to use for 'computer' timing
    
    // OldNew
    //
    // Does all sorts of stuff.
    // It's a demo for using JS in a trial type
    
    function trimExplode( $delimiter, $string ) {
        $output = array();
        $explode = explode( $delimiter, $string );
        foreach( $explode as $explosion ) {
            $output[] = trim($explosion);
     }
        return $output;
 }
    
    $texts = trimExplode( '|', $text );
    $texts = array_pad( $texts, 5, '' );
?>
<style>
    .OldNewCue    { font-size: 130%; margin: 10px auto 15px; }
    .OldNewArea    { text-align: center; }
    .OldNewArea input    { box-shadow: none !important; padding: 3px !important; margin: 2px !important; text-align: center !important; }
    .OldNewArea input[type='radio']    { display: none; }
    .OldNewQuestion    { font-size: 110%; margin: 25px auto 15px; }
    .choiceHolder    { display: inline-block !important; width: 100px !important; margin: 5px !important; border: 2px solid #fff; }
    .OldNewArea #FormSubmitButton    { visibility: hidden; margin-top: 10px !important; }
    
    .PathHolders        { white-space: nowrap; }
    .PathHolders> div    { display: inline-block; visibility: hidden; }
    .Pathway> div        { visibility: hidden; }
    
    .OldNewSelected    { border-color: #79F !important; }
    input:read-only    { cursor: not-allowed; background-color: #DDD; border-color: #DDD !important; color: #666; }
</style>

<section>
  <div class="OldNewArea">
    <div class="OldNewChoices">
      <div class="OldNewQuestion OldNewPrompt"><?php echo $texts[0] ?></div>
      <div class="OldNewCue"><?php echo $cue ?></div>
      <span class="choiceHolder"><input name="OldNewChoice" type="radio" value="Old">Old</span>
      <span class="choiceHolder"><input name="OldNewChoice" type="radio" value="New">New</span>
    </div>
    <div class="PathHolders">
      <div class="Pathway OldPath">
        <div class="OldTest">
          <div class="OldNewQuestion OldQuestion"><?php echo $texts[1] ?></div>
          <input name="Response" type="text">
          <input type="button" class="StageBtn collector-button" value="Next">
        </div>
        <div class="OldConfidence">
          <div class="OldNewQuestion OldConfidenceQuestion"><?php echo $texts[2] ?></div>
          <input name="OldConfidence" type="text" class="forceNumeric">
          <input type="button" class="StageBtn collector-button" value="Next">
        </div>
      </div>
      <div class="Pathway NewPath">
        <div class="NewGuess">
          <div class="OldNewQuestion NewQuestion"><?php echo $texts[3] ?></div>
          <input name="Guess" type="text">
          <input type="button" class="StageBtn collector-button" value="Next">
        </div>
          <div class="NewConfidence">
            <div class="OldNewQuestion NewConfidenceQuestion"><?php echo $texts[4] ?></div>
            <input name="NewConfidence" type="text" class="forceNumeric">
            <input type="button" class="StageBtn collector-button" value="Next">
        </div>
      </div>
    </div>
    <input class=collector-button  id=FormSubmitButton type=submit value="Submit">
  </div>
</section>
<script>
    COLLECTOR.trial.oldnew = function() {
        $(".StageBtn").prop("disabled", true);
        $("input[type='text']").on( "keyup", function() {
            if( this.value !== "" ) {
                $(this).parent().find(".StageBtn").prop("disabled", false);
         } else {
                $(this).parent().find(".StageBtn").prop("disabled", true);
         }
     });
        $(".StageBtn").on( "click", function() {
            $(this).parent().css("color", "gray");
            $(this).parent().find(":input").prop("readonly", true);
            $(this).prop("disabled", true);
            $(this).parent().next().css("visibility", "visible");
            if( $(this).parent().next().length === 0 ) {
                $("#FormSubmitButton").css("visibility", "visible");
         }
     });
        $(".choiceHolder").on( "click", function() {
            if( $(".choiceHolder").hasClass("OldNewSelected") ) { return false; }
            $(this).find("input")[0].checked = true;
            $('.OldNewChoices').css("color","gray");
            $(this).addClass("OldNewSelected");
            if( $(this).find("input")[0].value === "Old" ) {
                $(".OldPath").css("visibility", "visible").children().first().css("visibility", "visible");
                $(".NewPath").width("0px");
         } else {
                $(".NewPath").css("visibility", "visible").children().first().css("visibility", "visible");
                $(".OldPath").width("0px");
         }
     });
 }
</script>
