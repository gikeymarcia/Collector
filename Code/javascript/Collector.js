var Collector = {
    // inputs will hold min and max time
    inputs: {},

    element_selectors: {
        content:         "#content",
        duration:        "#Duration",
        focus:           "#Focus",
        first_timestamp: "#First_Input_Time",
        last_timestamp:  "#Last_Input_Time",
    },


    run: function() {
        var self = this;

        $(document).ready(function() {
            self.insert_spreadsheet_variables();
            self.apply_force_numeric();
            self.prevent_autocomplete();
            self.prevent_back_nav();
            self.prepare_to_catch_action_timestamps();
            self.prepare_form_submit();
            self.start_checking_focus();
        });

        $(window).load(function() {
            self.fit_content();
            self.control_timing(
                self.inputs.min,
                self.inputs.max
            );
            self.page_timer = new self.Timer();
            self.page_timer.start();
            self.display_trial();
            self.focus_first_input();

            if (typeof self.start === "function") {
                self.start();
            }
        });
    },
    
    insert_spreadsheet_variables: function() {
        var template = this.el('content').html();
        var self = this;
        
        template = template.replace(/\[[^\]]+\]/g, function(keyWithBrackets) {
            var key = keyWithBrackets.substr(1, keyWithBrackets.length-2); // pull off the brackets
            key = key.toLowerCase();
            
            if (typeof self.inputs.proc[key] !== "undefined") {
                return self.inputs.proc[key];
            } else if (typeof self.inputs.stim[key] !== "undefined") {
                return self.inputs.stim[key];
            } else {
                return keyWithBrackets;
            }
        }).replace(/{[^}]+}/g, function(keyWithBrackets) {
            var key = keyWithBrackets.substr(1, keyWithBrackets.length-2); // pull off the brackets
            key = key.toLowerCase();
            
            if (typeof self.inputs.extra[key] !== "undefined") {
                return self.inputs.extra[key];
            } else {
                return keyWithBrackets;
            }
        });
        
        this.el('content').html(template);
    },

    // these functions control when submission of a trial is enabled/disabled
    submit: function() {
        this.submit_conditions = {}; // wipe out all submit conditions, force submit
        this.el('content').submit();
    },

    submit_conditions: {},

    set_submit_condition: function(name, val) {
        this.submit_conditions[name] = val;
        this.get_form_submits().prop("disabled", !this.ready_to_submit());
    },

    ready_to_submit: function() {
        for (var prop in this.submit_conditions) {
            if (!this.submit_conditions[prop]) return false;
        }

        return true;
    },

    prepare_form_submit: function() {
        var self = this;

        this.el('content').submit(function(e) {
            if (!self.ready_to_submit()) {
                e.preventDefault();
                return false;
            }

            self.el('content').hide();

            self.el('duration').val(
                self.get_elapsed_time()
            );

            self.el('focus').val(
                self.myFocusChecker.proportion
            );

            if (typeof self.end === "function") {
                self.end();
            }
        });
    },

    prepare_to_catch_action_timestamps: function() {
        var self = this;

        $(":input").on("keypress click", function() {
            var el_first_timestamp = self.el('first_timestamp');
            var el_last_timestamp  = self.el('last_timestamp');
            var timestamp          = self.get_elapsed_time();

            if (el_first_timestamp.val() === '-1') {
                el_first_timestamp.val(timestamp);
            }

            el_last_timestamp.val(timestamp);
        });
    },


    /*  Timer adherence behavior
     *  -n: prevent submit until min_time // also disable button
     *      ::re-enable after min_time
     *  -x: prevent submit until max_time
     *  -t: timeout and submit form after max_time
     *  -0: no manipulation needed
     *
     *  min, max, behavior
     *  usr, usr, -0        // submit whenever you want (no limit)
     *  "" , "" , -0        // submit whenever you want (no limit)
     *  "" , usr, -0        // submit whenever you want (no limit)
     *
     *  005, usr, -n        // submit anytime after 5 seconds
     *  005, 010, -n -t     // submit between 5-10s (auto-submit @ 10s)
     *  usr, 010, -t        // submit whenever you want (auto-submit @ 10s)
     *  "" , 010, -t -x     // you may not submit (auto-submit @ 10s)
     */
    control_timing: function(min, max) {
        var self = this;

        max = $.isNumeric(max) ? parseFloat(max) : "user";
        min = $.isNumeric(min) ? parseFloat(min) : (min === '' ? null : 0);

        // max time
        if (typeof max === "number") {
            this.max_timer = this.setTimeout(max, function() {
                self.submit();
            });

            if (min === null || min >= max) {
                this.get_form_submits().hide();
                this.set_submit_condition("Collector Timer", false);
            }
        }

        // min time
        if (min > 0) {
            this.set_submit_condition("Collector Timer", false);

            this.min_timer = this.setTimeout(min, function() {
                self.set_submit_condition("Collector Timer", true);
            });
        }
    },

    get_elapsed_time: function() {
        return this.page_timer.elapsed();
    },

    // Collector.el("property name") will return the jQuery object
    // as specified in Collector.element_selectors
    el: function(element_name) {
        return $(this.element_selectors[element_name]);
    },

    get_form_submits: function(val) {
        return this.el('content').find(":submit");
    },


    /* * * * Utility Functions * * * *
     * They do what their method names suggest * */
    display_trial: function() {
        this.el('content').removeClass("invisible");
    },

    fit_content: function() {
        var checkSize = function() {
            var window_size  = $(window).height();
            var content_size = 0;
            $("body").children().each(function (){
                content_size += $(this).height();
            });
            var flex_prop = (window_size <= content_size) ? 'flex-start' : 'center ';
            $("body").css("justify-content",flex_prop);
        }
        checkSize();

        $(window).resize( checkSize() );
    },

    focus_first_input: function() {
        $(':input:not(:radio):enabled:visible:first').focusWithoutScrolling();
    },

    // prevent the backspace key from navigating back.
    // http://stackoverflow.com/questions/1495219/how-can-i-prevent-the-backspace-key-from-navigating-back
    // known issue: in chrome, if you open the dropdown menu of a select input, and then press backspace,
    // it doesn't propagate to either the select or the document, so it cant be caught and prevented
    prevent_back_nav: function() {
        $(document).on('keydown', function (event) {
            if (event.keyCode === 8) {
                var doPrevent,
                    d    = event.srcElement || event.target,
                    tag  = d.tagName.toUpperCase(),
                    type = d.type && d.type.toUpperCase();

                if (tag === 'TEXTAREA' ||
                   (tag === 'INPUT'
                    && (type === 'DATE'
                     || type === 'DATETIME'
                     || type === 'DATETIME-LOCAL'
                     || type === 'EMAIL'
                     || type === 'MONTH'
                     || type === 'NUMBER'
                     || type === 'PASSWORD'
                     || type === 'SEARCH'
                     || type === 'TEL'
                     || type === 'TEXT'
                     || type === 'TIME'
                     || type === 'WEEK'
                     || type === 'URL')
                   )
                ) {
                    doPrevent = d.readOnly || d.disabled;
                } else {
                    doPrevent = true;
                }
                if (doPrevent) event.preventDefault();
            }
        });
    },

    prevent_autocomplete: function() {
        $("form").attr('autocomplete', 'off');
    },

    apply_force_numeric: function() {
        $(".forceNumeric").forceNumeric();
    },


    setTimeout: function(timeUp, callback) {
        var timer = new this.Timer(timeUp, callback);
        timer.start();
        return timer;
    },

    Timer: function(timeUp, callback) {
        this.callback = callback;
        this.timeUp = timeUp;
        this.now = this.set_timer_function();
    },


    start_checking_focus: function() {
        this.myFocusChecker = new Collector.FocusChecker();
    },

    FocusChecker: function() {
        this.start();
    }
}

Collector.Timer.prototype = {
    start: function () {
        this.startTimestamp = this.now();
        this.goal           = this.startTimestamp + (this.timeUp*1000);
        this.stopped = false;
        this.runTimer();
    },

    runTimer: function() {
        if (this.stopped) { return; }

        if (this.remaining() < 8) {
            while(true) {
                if (this.remaining() <= 1) {
                    this.stop();
                    this.callback();
                    break;
                }
            }
            return;
        } else {
            var wait   = this.remaining()*.5;
            var _this  = this;
            setTimeout(function() { _this.runTimer() }, wait);
        }
    },

    stop: function() {
        this.end = this.now();
        this.error = this.end - this.goal;
        this.stopped = true;
    },

    remaining: function() {
        return (this.goal - this.now());
    },

    elapsed: function () {
        return (this.now() - this.startTimestamp);
    },

    set_timer_function: function() {
        if (typeof performance.now === "function") {
            return function() { return performance.now() }
        } else if(typeof Date.now === "function") {
            return function() { return Date.now() }
        } else {
            return function() { return new Date().getTime() }
        }
    },

    show: function($showElement, waitTime) {
        if ((this.stopped) || (this.remaining() < 0)) { return; }
        if ($showElement.is('input')) {
            $showElement.val( this.formatTime( this.remaining() ) );
        } else {
           $showElement.html( this.formatTime( this.remaining() ) );
        }
        var _this = this;
        var waitTime = (typeof waitTime === 'undefined') ? 50 : waitTime;
        setTimeout(function() { _this.show($showElement) }, waitTime);
    },

    formatTime: function(rawTime) {
        var formatted = Math.round(rawTime/100) / 10;
        if (Math.round(formatted) == formatted) {
            formatted += '.0';
        } return formatted;
    }
}

Collector.FocusChecker.prototype = {
    checks: 0,
    passes: 0,
    proportion: null,

    start: function() {
        var _this = this;
        setTimeout(function() { _this.start() }, 250);
        this.checks++;
        if (document.hasFocus()) this.passes++;
        this.proportion = Math.round((this.passes/this.checks)*1000) / 1000;
    }
}

Collector.run();



jQuery.fn.focusWithoutScrolling = function() {
    if ($(this).length === 0) return this;

    var parents = [], parentScrolls = [];
    var currentElement = $(this);

    while (currentElement[0] !== document) {
        currentElement = currentElement.scrollParent();
        parents.push(currentElement);
        parentScrolls.push(currentElement.scrollTop());
    }

    this.focus();

    while (parents.length > 0) {
        currentElement = parents.pop();
        currentElement.scrollTop(parentScrolls.pop());
    }
    return this; //chainability
};

jQuery.fn.forceNumeric = function () {
    // http://weblog.west-wind.com/posts/2011/Apr/22/Restricting-Input-in-HTML-Textboxes-to-Numeric-Values
    return this.each(function () {
        $(this).keydown(function (e) {
            var key = e.which || e.keyCode;

            if (!e.shiftKey && !e.altKey && !e.ctrlKey &&
             // numbers
                key >= 48 && key <= 57 ||
             // Numeric keypad
                key >= 96 && key <= 105 ||
             // comma, period and minus, . on keypad
             // key == 190 || key == 188 || key == 109 || key == 110 ||
                key == 190 || key == 110 ||
             // Backspace and Tab and Enter
                key == 8 || key == 9 || key == 13 ||
             // Home and End
                key == 35 || key == 36 ||
             // left and right arrows
                key == 37 || key == 39 ||
             // Del and Ins
                key == 46 || key == 45)
                return true;

            return false;
        });
    });
};