var Trial = {
    element_selectors: {
        content:         '#content',
        duration:        '#Duration',
        focus:           '#Focus',
        first_timestamp: '#First_Input_Time',
        last_timestamp:  '#Last_Input_Time',
    },

    /* * * * * * * * * * *
     * Initialization functions
     * these take inputs from /Code/trialContent.html
     */
    load_inputs: function(inputs) {
        this.inputs = {};

        for (var category in inputs) {
            var lower_category = category.toLowerCase();
            this.inputs[lower_category] = {};

            for (var column in inputs[category]) {
                this.inputs[lower_category][column.toLowerCase()] = inputs[category][column];
            }
        }
    },

    load_trial_type: function(type) {
        this.type = type;

        if(this.type.scoring !== null) {
            eval(this.type.scoring);
        }
    },

    load_globals: function(global_data) {
        this.global_data = global_data;
    },

    /* * * * * * * * * * *
     * Trial Execution (basically, a Table of Contents)
     */
    run: function() {
        var self = this;

        // load the trial information
        this.define_defaults();
        this.run_custom_inputs_calculation();
        this.apply_trial_type_template();

        // prepare the page for display
        $(document).ready(function() {
            self.apply_force_numeric();
            self.prevent_autocomplete();
            self.prevent_back_nav();
            self.prepare_to_catch_action_timestamps();
            self.prepare_form_submit();
            self.start_checking_focus();
        });

        // start the trial
        $(window).on('load', function() {
            self.fit_content();
            self.control_timing( // uses min and max time from the procedure
                self.get_input('Min Time', ['Procedure']),
                self.get_input('Max Time', ['Procedure'])
            );
            self.page_timer = new self.Timer();
            self.page_timer.start();
            self.display_trial();
            self.focus_first_input();

            if (typeof self.start === 'function') {
                self.start();
            }
        });
    },

    /* * * * * * * * * * *
     * Scoring functions
     */
    scoring: function(data) {
        var answer = Trial.get_stimuli('answer')[0];

        if (typeof data.Response !== 'undefined'
            && typeof answer !== 'null'
        ) {
            var response = data.Response;
            var accuracy = calculate_percent_similar(response, answer);
            data['Accuracy']   = accuracy;

            // if () is true ? 'do this' : 'else do this'
            data['strictAcc']  = (accuracy === 1)  ? 1 : 0;
            data['lenientAcc'] = (accuracy >= .75) ? 1 : 0;
        }
        return data;
    },

    unserialize: function(serialized_data) {
        data = {};
        var list = serialized_data.split('&');

        for (var item in list) {
            var key_val = list[item].split('=');
            var key = decodeURIComponent(key_val[0]);
            var val = decodeURIComponent(key_val[1]);

            if (key.substring(key.length-2) === '[]') {
                if (typeof data[key] === 'undefined') data[key] = [];
                data[key].push(val);
            } else {
                data[key] = val;
            }
        }
        return data;
    },

    /* * * * * * * * * * *
     * Page Setup
     */
    define_defaults: function() {
        this.media_path = window.parent.Collector_Experiment.media_path;
    },

    run_custom_inputs_calculation: function() {
        this.inputs.extra = {};

        // run trial's prepareInputs.js (if it exists)
        var prepare_inputs_js = this.type.prepare_inputs;
        if (prepare_inputs_js !== null) {
            eval(prepare_inputs_js);
        }
    },

    apply_trial_type_template: function() {
        // make a placeholder <div> containing the template.html
        var template = $('<div>' + this.type.template + '</div>');

        // remove all script elements from template.html
        var scripts = template.find('script').replaceWith('__COLLECTOR__SCRIPT__');
        scripts = $.makeArray(scripts);

        // fill template with replacements for [values] and {custom inputs}
        var content = this.fill_template(template.html());

        // put back all <script> elements in template.html
        content = content.replace(/__COLLECTOR__SCRIPT__/g, function(match) {
            return scripts.shift().outerHTML;
        });

        // put the tempalte on the page.
        $('#content').html(content);
    },

    fill_template: function(template) {
        var self = this;

        // the first replacement matches things like [Cue] and [Text]
        // the second matches things like {custom stuff} and {my calculated value}
        return template
            .replace(/\[[^\]]+\]/g, function(match) { return self.replace_template_match(match, ['procedure', 'stimuli']); })
            .replace(/{[^}]+}/g,    function(match) { return self.replace_template_match(match, ['extra']); });
    },

    replace_template_match: function(keyWithBrackets, categories) {
        var key = keyWithBrackets.substr(1, keyWithBrackets.length-2); // pull off the brackets
        var replacement = this.get_input_as_string(key, categories);
        if (replacement !== null) return replacement;

        return keyWithBrackets; // if we didn't find a match just return the original [text]/{text}
    },

    // if it doesn't begin with http then add path to media directory
    prepare_media_link: function(value) {
        return (value.substring(0, 4) === 'http')
             ? value
             : this.media_path + '/' + value;
    },

    add_input: function(key, val, category) {
        if (typeof category === 'undefined') category = 'extra';

        key      = key     .trim().toLowerCase();
        category = category.trim().toLowerCase();

        this.inputs[category][key] = val;
    },

    get_input: function(key, categories) {
        if(typeof categories === 'string') categories = [categories.toString()];
        if (typeof categories === 'undefined' || categories.length === 0)
            categories = ['procedure', 'stimuli', 'extra'];

        key = key.trim().toLowerCase();

        for (var i=0; i<categories.length; ++i) {
            var category = categories[i].trim().toLowerCase();
            if (typeof this.inputs[category] === 'undefined') continue;

            if (typeof this.inputs[category][key] !== 'undefined') {
                return this.inputs[category][key];
            }
        }

        return null;
    },

    get_input_as_string: function(input_request_string, categories) {
        // input_request comes back with .name .url and .index
        var input_request = this.decipher_input_request(input_request_string);

        var val_raw = this.get_input(input_request.name, categories);
        var val = this.get_input_request_index(
            val_raw,
            input_request.index
        );
        if (val === null) return null;
        if (typeof val === 'object') val = val.join(' ');

        // if input was in form [Cue:url] then return proper media link
        return (input_request.url)
             ? this.prepare_media_link(val)
             : val;
    },

    decipher_input_request(input_request) {
        var url = false, index = null, name = null;

        var request_split = input_request.split(':');
        while (request_split.length > 1) {
            var last = request_split.pop().trim().toLowerCase();

            if (last === 'url') {
                url = true;
            } else if (last === 'all' || $.isNumeric(last)) {
                index = last;
            } else {
                break;
            }
        }

        if (index === null) index = '0';
        name = request_split.join(':');
        return {
            name:  name,
            url:   url,
            index: index
        }
    },

    get_input_request_index: function(value, index) {
        if (value === null || typeof value === 'string')
            return value;

        if (index === 'all') {
            return value;
        } else {
            index = parseInt(index);

            if (typeof value[index] === 'undefined') {
                return null;
            } else {
                return value[index];
            }
        }
    },

    get_stimuli:   function(key) { return this.get_input(key, ['stimuli']);   },
    get_procedure: function(key) { return this.get_input(key, ['procedure']); },
    get_extra:     function(key) { return this.get_input(key, ['extra']);     },

    /* * * * * * * * * * *
     * Page control
     */
    // these functions control when submission of a trial is enabled/disabled
    submit: function() {
        this.submit_conditions = {}; // wipe out all submit conditions, force submit
        this.el('content').submit();
    },

    submit_conditions: {},

    set_submit_condition: function(name, val) {
        this.submit_conditions[name] = val;
        this.get_form_submits().prop('disabled', !this.ready_to_submit());
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

            if (typeof self.end === 'function') {
                self.end();
            }
        });
    },

    prepare_to_catch_action_timestamps: function() {
        var self = this;

        $(':input').on('keypress click', function() {
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
     *  '' , '' , -0        // submit whenever you want (no limit)
     *  '' , usr, -0        // submit whenever you want (no limit)
     *
     *  005, usr, -n        // submit anytime after 5 seconds
     *  005, 010, -n -t     // submit between 5-10s (auto-submit @ 10s)
     *  usr, 010, -t        // submit whenever you want (auto-submit @ 10s)
     *  '' , 010, -t -x     // you may not submit (auto-submit @ 10s)
     */
    control_timing: function(min, max) {
        var self = this;

        max = $.isNumeric(max) ? parseFloat(max) : 'user';
        min = $.isNumeric(min) ? parseFloat(min) : (min === '' ? null : 0);

        // max time
        if (typeof max === 'number') {
            this.max_timer = this.setTimeout(max, function() {
                self.submit();
            });

            if (min === null || min >= max) {
                this.get_form_submits().hide();
                this.set_submit_condition('Collector Timer', false);
            }
        }

        // min time
        if (min > 0) {
            this.set_submit_condition('Collector Timer', false);

            this.min_timer = this.setTimeout(min, function() {
                self.set_submit_condition('Collector Timer', true);
            });
        }
    },

    get_elapsed_time: function() {
        return this.page_timer.elapsed();
    },

    // Trial.el('property name') will return the jQuery object
    // as specified in Trial.element_selectors
    el: function(element_name) {
        return $(this.element_selectors[element_name]);
    },

    get_form_submits: function(val) {
        return this.el('content').find(':submit');
    },


    /* * * * * * * * * * *
     * Utility Functions
     * They do what their method names suggest
     */
    display_trial: function() {
        this.el('content').removeClass('invisible');
    },

    fit_content: function() {
        var checkSize = function() {
            var window_size  = $(window).height();
            var content_size = 0;
            $('body').children().each(function (){
                content_size += $(this).height();
            });
            var flex_prop = (window_size <= content_size) ? 'flex-start' : 'center ';
            $('body').css('justify-content', flex_prop);
        }
        checkSize();

        $(window).resize( checkSize() ); // @q: should this have the ()?
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
        $('form').attr('autocomplete', 'off');
    },

    apply_force_numeric: function() {
        $('.forceNumeric').forceNumeric();
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
        this.myFocusChecker = new this.FocusChecker();
    },

    FocusChecker: function() {
        this.start();
    }
}

/* * * *
 * Trial subclasses
 */
Trial.Timer.prototype = {
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
            var self   = this;
            setTimeout(function() { self.runTimer() }, wait);
        }
    },

    stop: function() {
        this.end = this.now();
        this.error = this.end - this.goal;
        this.stopped = true;
    },

    remaining: function() {
        return Math.floor((this.goal - this.now()));
    },

    elapsed: function () {
        return Math.floor((this.now() - this.startTimestamp));
    },

    set_timer_function: function() {
        if (typeof performance.now === 'function') {
            return function() { return performance.now() }
        } else if(typeof Date.now === 'function') {
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
        var self = this;
        var waitTime = (typeof waitTime === 'undefined') ? 50 : waitTime;
        setTimeout(function() { self.show($showElement) }, waitTime);
    },

    formatTime: function(rawTime) {
        var formatted = Math.round(rawTime/100) / 10;
        if (Math.round(formatted) == formatted) {
            formatted += '.0';
        } return formatted;
    }
}

Trial.FocusChecker.prototype = {
    checks: 0,
    passes: 0,
    proportion: null,

    start: function() {
        var self = this;
        setTimeout(function() { self.start() }, 250);
        this.checks++;
        if (document.hasFocus()) this.passes++;
        this.proportion = Math.round((this.passes/this.checks)*1000) / 1000;
    }
}


/* * * *
 * jQuery Extensions
 */
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



/* * * * * * * * * * *
 * Custom Scoring functions
 */
function calculate_percent_similar(given, answer) {
    // var wrong = dom_lev_text(given, answer);
    // return wrong/(answer.length);
    var given  = given.trim().toLowerCase();
    var answer = answer.trim().toLowerCase();

    var chars = answer.split('');
    var wrong = 0;
    for (var char in chars) {
        if (answer[char] != given[char]) {
            wrong++;
        }
    }

    if (wrong > 0) {
        return wrong/(answer.length);
    } else {
        return 1;
    }
}
