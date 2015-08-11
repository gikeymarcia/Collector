<?php
    // access control
    require_once 'loginFunctions.php';
    adminOnly();                            // only allow tool use when logged in
?>
<!-- This is some sample css code. You can modify it and/or add to it if you'd like -->

<style type="text/css">
    .space {
        margin-bottom: 100px;;
    }
    .namingIsYourBusiness {
        font-size: 100%;
    }
</style>

<!-- Tinker is a place for you to test how HTML/CSS/PHP all work -->
<!-- Checkout and modify the HTML we've written -->
<h1>This is a header 1</h1>

<h2>This is a header 2</h2>

<p class="namingIsYourBusiness">This is a paragrpah. In the code note how this
paragraph also has a class, <code>class="namingIsYourBusiness"</code> If you 
look above at the CSS you'll see how we can reference a class by putting a <code>.</code>
before the name (e.g., <code>.namingIsYourBusiness { }</code>)</p>

<p class="namingIsYourBusiness">Cascading Style Sheets (CSS) are used to control the look of HTML elements on a page. By keeping 
the strcutre (HTML) and appearance (CSS) of a page separate it makes it easier to change the look of a page
without having to completely rewrite the HTML. CSS is usually written so that each line sets a different property following the style
of <code>property: value;</code>.<br>That was a line break. It allows the following text 
to be on a new line but without the space, <code>margin: 15px;</code>, associated with 
a new paragraph.<br> Follow the steps below to edit your first CSS code.</p>


<span>This is how you make an ordered list</span>
<ol>
    <li>Open up the code in <code>Experiment/Tools/Tinker/Tinker.php</code></li>
    <li>Find the CSS style that defines the class <code>namingIsYourBusiness</code></li>
    <li>Add the property <code>width: 600px;</code></li>
    <li>Save the file then reload this page. Looks better, right?</li>
    <li>Add a second property <code>margin: 20px auto;</code>. Save and refresh again</li>
    <li><code>margin:</code> controls the distance between elements on the page. There 
        are multiple ways to set the margins:
        <ul>
            <li><code>margin: value;</code> sets top/bottom/right/left to the same size</li>
            <li><code>margin: top/bottom left/right;</code> sets top/bottom to one value and left/right to another.</li>
            <li><code>margin: top right bottom left;</code> sets each of the 4 margins in clockwise order starting at top</li>
            <li>Margins can also be set one-by-one <code>maring-top: 10px;</code></li>
        </ul>
    </li>
    <li>When an element has a set <code>width</code> and left/right margin of <code>auto</code>
        It is automatically centered within the page.
    </li>
    <li>Now, the real magic of CSS. Add the class <code>namingIsYourBusiness</code> to 
        the second paragraph. Save the code and refresh the page. Ta-da! Being able to reuse
        the styles you make is precisely why CSS is helpful. If you are having trouble
        adding the class to the second paragraph look how it was done on the 1st paragraph as reference.
        <br><em>Tip: elements can have multiple classes. For example, <code>class="class1 class2"</code></em>
    </li>
    <li>Try adding the class <code>space</code> to this ordered list. Before you refresh 
        look at the CSS and ask yourself what will happen when the code refreshes.</li>
</ol>

<ul>
    This is an <b>UN</b>ordered list ("ul")
    <li>Each item on the list is given the tag "li" for list-item</li>
    <li>These items are getting the same "li" tag as items on an ordered list 
        but they are not given a number becasue they belong to a "ul" instead of an "ol"</li>
    <li>If you look at the code you'll see that HTML ignores           more than one      
        space    at       a           time. This is why you can type paragraphs across 
        multiple lines 
        within 
        the 
        code.
    </li>
</ol>


<?php
    #### Use this area to test out your php code ideas.
    
    // this is the most basic way you can tell PHP to output some HTML to the page
    echo '<h1> This is from PHP</h1>';
    
    // this is a variable and I'm giving it the value of my name
    $name = 'Mikey';
    
    // we can insert variables into the HTML we output
    echo '<h2> Hi ' . $name . '! Welcome to PHP</h2>';
    
    // we can also use IF, ELSEIF, and ELSE statements to give different HTML depending on variable values
    if ($name == 'Mikey') {
        echo '<p>Welcome back creator</p>';
    } elseif ($name == 'Tyson') {
        echo '<p>Welcome back friend</p>';
    } else {
        echo '<p>Who are you?</p>';
    }
    
    // Change the $name to Tyson then save/refresh. Try giving a different name.
    
?>