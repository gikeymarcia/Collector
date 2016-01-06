var DMT = {
    settings : {
        animationTiming : 500,  // time, in milliseconds, to spend raising the score bar, lowering, etc.
        maxSingleScore  : 90,   // this is the max capacity of the "Current" tank, used in calculating the animationTiming
                                // for example, if this is set to 90, and they earn 45 points, then the Current tank will fill 50%
                                // you should make sure that this is, at minimum, the max score they could possibly receive in one trial
        goal            : .8,   // used by calculateGoal(), performance level they need to achieve goal
        initialHistory  : [0,0,0,0,0,1,1,1,1,1],    // shuffled by begin().  if you want a longer starting history, make this history longer
        btnCodes        : [0,1],// if you want to add more buttons, you need to add additional values to assign to those buttons here
                                // more than one button can have the same code, so a btnCodes value of [0,1,0,1] is fine, to make a task where
                                // two buttons use the "0" reward structure, and two use "1"
                                // the button assignments are shuffled before being applied, in begin()
        showScore       : false // set this to true if you want numbers to appear over the Current and Cumulative tanks
    },
    
    // you can modify the values in reward to change the task
    // you can also add a new option, but make sure to make it an object with initial, history, and bonus set to some number
    reward  : {
        0 : {
            immediate : 30,   // amount given immediately when chosen
            history   : 10,   // how many trials this option affects in the future (use 0 if this reward has no effect on future trials)
            delayed   :  5,   // how many points are added to future trials (you can use negative numbers)
            st_dev    :  0    // sd, to randomly generate values around the immediate value
        },
        
        1 : {
            immediate : 40,
            history   :  0,
            delayed   :  0,
            st_dev    :  0
        },
    },
    
    // you only need to modify goal.task, as the rest are calculated and changed at the start of the task
    goal    : {
        total   : 0,    // set by calculateGoal(), total points they are trying to get
        percent : 0,    // set by calculateGoal(), percentage of max score they are trying to get
        max     : 0,    // set by calculateGoal(), max score possible
        min     : 0     // set by calculateGoal(), min score possible
    },
    
    rounds  : 0,        // this needs to be set in display.php, after the number of rounds has been determined
    
    current : {
        round     : 0,
        history   : [],
        score     : 0,
        time      : 0,
        btnValues : []
    },
    
    calculateGoal : function() {
        var me  = this;
        var cur = me.current;
        
        var i, j, k, l;
        
        var min, max;
        
        // calculate premade history reward that will be awarded regardless of choice
        var historyBonus = 0;
        
        for (i=0; i<cur.history.length; ++i) {
            j = cur.history[i];                                 // which reward is it
            k = me.reward[j].history;                           // how many future trials does this reward normally effect
            l = Math.max(0, 1 - cur.history.length + i + k);    // how many trials will this actually effect
            
            historyBonus += l * me.reward[j].delayed;
        }
        
        min = historyBonus;
        max = historyBonus;
        
        var minTemp, maxTemp;
        
        for (i=0; i<me.rounds; ++i) {
            minTemp = Infinity;
            maxTemp = 0;
            
            for (j in me.reward) {
                k = Math.min(me.reward[j].history, me.rounds - i - 1); // the number of rounds this choice would affect
                l = k * me.reward[j].delayed                           // reward from history effects of this choice
                l += me.reward[j].immediate;                           // add in the immediate reward
                
                if (minTemp > l) minTemp = l;
                if (maxTemp < l) maxTemp = l;
            }
            
            min += minTemp;
            max += maxTemp;
        }
        
        me.goal.max     = max;                                  // goal is not just 80% of max, but 80% of optimal behavior,
        me.goal.min     = min;                                  // and since even the worst option will usually give some points,
        me.goal.total   = min + (max - min) * me.settings.goal; // the actual goal percent is usually a bit higher, like 90% of max
        me.goal.percent = me.goal.total / me.goal.max;
        
        me.goalBar.css("top", (1 - me.goal.percent) * 100 + "%");
    },
    
    calculateReward : function(code) {
        var me  = this;
        var cur = me.current;
        var i, j, k;
        var reward = 0;
        
        for (i=0; i<cur.history.length; ++i) {      // calculate effect of history
            j = cur.history[i];                     // which reward is it
            k = me.reward[j].history;               // how many future trials does this reward normally effect
            if (i - cur.history.length + k >= 0) {  // check if this history item's history effect extends to this trial
                reward += me.reward[j].delayed;
            }
        }
        
        // add reward that the current choice gives immediately
        if (me.reward[code].st_dev == 0) {
            reward += me.reward[code].immediate;
        } else {
            reward += rnorm(me.reward[code].immediate, me.reward[code].st_dev);
        }
        
        return reward;
    },
    
    animateReward : function (score, btn) {
        var me  = this;
        var cur = me.current;
        
        $(btn).addClass("chosenOption");
        
        var time = me.settings.animationTiming;
        
        var currentHeight    = (score            * 100 / me.settings.maxSingleScore) + "%";
        var cumulativeHeight = (me.current.score * 100 / me.goal.max)                + "%";
        
        me.currentLevel.animate({height: currentHeight}, time).delay(time).animate({height: 0}, time);
        animateNumber(time, me.currentPoints, score);
        COLLECTOR.timer(time*2/1000, function() {
            animateNumber(time, me.currentPoints, 0);
            me.cumulativeLevel.animate({height: cumulativeHeight}, time);
            animateNumber(time, me.cumulativePoints, me.current.score);
        });
        COLLECTOR.timer(time*3/1000, function() {
            $(".chosenOption").removeClass("chosenOption");
            $(":focus").blur();
        });
    },
    
    responses : {
        choice     : [],
        choiceCode : [],
        score      : [],
        RT         : []
    },
    
    begin         : function() {
        var me  = this;
        
        me.options          = $(".dmtOption");
        me.goalBar          = $(".goalBar");
        me.currentLevel     = $(".currentLevel");
        me.currentPoints    = $(".currentPoints");
        me.cumulativeLevel  = $(".cumulativeLevel");
        me.cumulativePoints = $(".cumulativePoints");
        
        var cur = me.current;
        cur.history = me.settings.initialHistory;
        cur.history = shuffle(cur.history);
        
        cur.btnValues = me.settings.btnCodes;
        cur.btnValues = shuffle(cur.btnValues);
        
        $("form").append("<input type='hidden' name='Initial_History' value='" + cur.history  .join(',') + "' />")
                 .append("<input type='hidden' name='Btn_Assignment'  value='" + cur.btnValues.join(',') + "' />");
        
        for (var i=0; i<me.options.length; ++i) {
            me.options.eq(i).data("code", cur.btnValues[i]);
        }
        
        
        me.calculateGoal();
        
        me.current.time = Date.now();
        
        /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * /
         * The function below is called every time the participant
         * makes a choice.
         *
         * To implement changes that happen during the task, such as 
         * making the buttons switch codes, or changing the points
         * offered for each choice, add that functionality into the 
         * function below.
         *
         * However, if you are changing the reward structure in
         * dramatic ways, then make sure that you update the 
         * calculateGoal() function, either by incorporating the
         * change, or by simply having it return constants that 
         * you pre-define.
         *
         */
        
        me.options.on("click", function() {
            me.options.prop("disabled", true);
            var RT = Date.now() - me.current.time;
            
            var btn  = this.innerHTML;
            var code = $(this).data("code");
            
            var score = me.calculateReward(code);
            
            ++me.current.round;         // if you want to add an event on a certain round, use this variable
            
            me.current.score += score;  // to add an event based on them reaching a certain score (such as the goal), use this value
            
            // cur.history.shift();     // take off the earliest item in the history ; changed my mind, now history is infinite!
            cur.history.push(code);     // add in the just-chosen item
            
            me.animateReward(score, this);
            
            me.responses.choice.push(btn);
            me.responses.choiceCode.push(code);
            me.responses.score.push(score);
            me.responses.RT.push(RT);
            
            COLLECTOR.timer(me.settings.animationTiming*3/1000, function() {
                if (me.current.round >= me.rounds) {
                    $("form").append("<input type='hidden' name='Choice'     value='" + me.responses.choice    .join(',') + "' />")
                             .append("<input type='hidden' name='ChoiceCode' value='" + me.responses.choiceCode.join(',') + "' />")
                             .append("<input type='hidden' name='Score'      value='" + me.responses.score     .join(',') + "' />")
                             .append("<input type='hidden' name='dmtRT'      value='" + me.responses.RT        .join(',') + "' />");
                    
                    $("#FormSubmitButton").click();
                } else {
                    me.options.prop("disabled", false);
                    me.current.time = Date.now();
                }
            });
        });
    }
};

function animateNumber(time, jqObj, number) {
    
    if (!DMT.settings.showScore) return true;
    
    var goal = Date.now() + time;
    var orig = jqObj.html();
    if ($.isNumeric(orig)) {
        orig = parseInt(orig);
    } else {
        orig = 0;
    }
    var diff = number - orig;
    
    function instance() {
        var now = Date.now();
        
        if (now > goal) {
            if (number === 0) {
                jqObj.html("&nbsp;");
            } else {
                jqObj.html(number);
            }
            return true;
        }
        
        var elapsed = 1 - (goal - now) / time;
        
        var curNum = Math.round(orig + diff * elapsed);
        
        jqObj.html(curNum);

        // run the timer again, using a percentage of the time remaining
        setTimeout(function() { instance(); }, 30);
    }

    // start the timer
    instance();
}
        
//+ Jonas Raoni Soares Silva
//@ http://jsfromhell.com/array/shuffle [v1.0]
function shuffle(o){ //v1.0
    for(var j, x, i = o.length; i; j = Math.floor(Math.random() * i), x = o[--i], o[i] = o[j], o[j] = x);
    return o;
};

// user5084 on stackoverflow provided the next two functions to use the Box-Muller transformation to generate random numbers
// source http://stackoverflow.com/questions/75677/converting-a-uniform-distribution-to-a-normal-distribution
// I added an extra step to round the result to an integer, which might create some bias? I am no statistician, so I don't know
/*
 * Returns member of set with a given mean and standard deviation
 * mean: mean
 * standard deviation: std_dev 
 */
function rnorm(mean,std_dev){
    return Math.round(mean + (gaussRandom()*std_dev));
}

/*
 * Returns random number in normal distribution centering on 0.
 * ~95% of numbers returned should fall between -2 and 2
 */
function gaussRandom() {
    var u = 2*Math.random()-1;
    var v = 2*Math.random()-1;
    var r = u*u + v*v;
    /*if outside interval [0,1] start over*/
    if(r == 0 || r > 1) return gaussRandom();

    var c = Math.sqrt(-2*Math.log(r)/r);
    return u*c;

    /* todo: optimize this algorithm by caching (v*c) 
     * and returning next time gaussRandom() is called.
     * left out for simplicity */
}
