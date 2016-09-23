var Experiment = function (exp_data, $container, trial_page, trial_types, media_path) {
    this.data = {
        stimuli: exp_data.stimuli,
        procedure: this.parse_procedure(exp_data.procedure),
        responses: []
    }

    this.exp_data   = exp_data;
    this.trial_page = trial_page;
    this.media_path = media_path;
    this.position   = exp_data.position;
    this.load_trial_types(trial_types);
    this.create_iframe($container);
}


Experiment.prototype = {
    load_trial_types: function(trial_types_data) {
        this.trial_types = {};

        for (var type in trial_types_data) {
            var lower_type = type.toLowerCase();
            this.trial_types[lower_type] = trial_types_data[type];
        }
    },

    get_trial_type: function(type) {
        return this.trial_types[type.toLowerCase()];
    },

    parse_procedure: function(proc_data) {
        var self = this;
        return proc_data.map(function(trial_row) {
            return self.parse_trial_row_to_trial_set(trial_row);
        });
    },

    parse_trial_row_to_trial_set(trial_row) {
        var trials = [];

        for (var column in trial_row) {
            var col_info = column.match(/^Post ([0-9]+) (.+)/);
            var post_number, column_name;

            if (col_info === null) {
                post_number = 0;
                column_name = column;
            } else {
                post_number = parseInt(col_info[1]);
                column_name = col_info[2];
            }

            if (typeof trials[post_number] === "undefined") {
                trials[post_number] = {};
            }
            trials[post_number][column_name] = trial_row[column];
        }

        return trials;
    },

    get_trial_inputs_and_type: function(position) {
        var trial_inputs = this.get_trial_inputs(position);

        var type = trial_inputs.procedure["Trial Type"]; // maybe add error handling here

        return {
            inputs: trial_inputs,
            type: this.get_trial_type(type)
        }
    },

    get_trial_inputs: function(position) {
        //@TODO: handle when recieving a bad 'position'
        if (typeof position == 'undefined') {
            position = this.position;
        }
        var row = position[0];
        var post_pos = position[1];

        var trial_set = this.data.procedure[row];
        var this_proc = this.data.procedure[row][post_pos];

        var items = this.get_item(trial_set, post_pos);
        var stimuli = this.get_stimuli(items);
        // var responses = [];
        return {
            procedure: trial_set[post_pos],
            stimuli: stimuli,
            //@TODO: return associated responses for this trial
        }
    },

    get_item: function(trial_set, post_pos) {
        var item;
        var proc_info = trial_set[post_pos];

        if (typeof proc_info['Item'] === "undefined") {
            if (post_pos > 0 && typeof trial_set[0]["Item"] !== "undefined") {
                item = trial_set[0]["Item"];
            } else {
                return [];
            }
        } else {
            item = proc_info['Item'];
        }

        return this.item_to_array(item);    // will return a list
    },

    get_stimuli: function(item_list) {
        var stimuli = [];
        var self = this;
        item_list.forEach(function(item) {
            if (typeof self.data.stimuli[item-2] !== "undefined") {
                stimuli.push(self.data.stimuli[item-2]);
            }
        });
        var stimuli = this.rows_to_columns(stimuli);
        return stimuli;
    },

    item_to_array: function(item_contents) {
        var range_match = item_contents.match(/\[(.+)\]/);

        if (range_match === null) return [item_contents];

        var range_text = range_match[1];    // from "[1, 2, 3 - 6]", get "1, 2, 3 - 6"

        // if we find anything but: [,-] literal chars then we return the contents
        if (range_text.match(/[^0-9, \-]/) !== null) return [item_contents];

        return range_text                   // "1, 2, 3 - 6"
            .split(',')                     // ["1", " 2", " 3 - 6"]
            .map(str => str.trim())         // ["1", "2", "3 - 6"]
            .map(function(range) {
                return range
                    .split('-')             // [["1"], ["2"], ["3 ", " 6"]]
                    .map(str => str.trim())
                    .map(x => parseInt(x)); // [[1], [2], [3, 6]]
            })
            .map(range_from_array)          // [[1], [2], [3, 4, 5, 6]]
            .reduce(concat_arrays);         // [1, 2, 3, 4, 5, 6]
    },

    rows_to_columns: function(list_of_stimuli) {
        var stim_cols = {};

        list_of_stimuli.forEach(function(stimuli) {
            for (var column_name in stimuli) {
                if (typeof stim_cols[column_name] === "undefined") {
                    stim_cols[column_name] = [];
                }
            }
        });
        list_of_stimuli.forEach(function(stimuli) {
            for (var column_name in stim_cols) {
                if (typeof stimuli[column_name] === "undefined") {
                    stim_cols[column_name].push(null);
                } else {
                    stim_cols[column_name].push(stimuli[column_name]);
                }
            }
        });

        return stim_cols;
    },

    create_iframe: function($container) {
        this.iframe = $("<iframe>");
        this.iframe.appendTo($container);
    },

    run_trial(position) {
        if (typeof position == "undefined") {
            position = this.position;
        }
        this.position = position;
        var doc = this.iframe[0].contentDocument;
        doc.open();
        doc.write(this.trial_page);
        doc.close();
    },

    end_trial: function(data, position) {
        if (typeof position == 'undefined') {
            position = this.position;
        }

        this.record_trial(data);

        var next_position = this.get_next_trial_position();

        if (next_position) {
            this.run_trial(next_position);
        } else {
            this.iframe.detach();
            $("#ExperimentContainer").append("<h1>Done!</h1>");
        }
    },

    record_trial: function(data) {
        var pos = this.position;
        var trial_set  = pos[0];
        var post_trial = pos[1];
        var resp = this.data.responses;

        if (typeof resp[trial_set] === "undefined") resp[trial_set] = [];
        resp[trial_set][post_trial] = {
            recorded: false,
            position: pos,
            data: {
                stimuli: this.get_trial_inputs()['stimuli'],
                procedure: this.get_trial_inputs()['procedure'],
                responses: data,
            }
        };

    },

    get_next_trial_position: function() {
        var pos = this.position;
        var trial_set  = pos[0];
        var post_trial = pos[1];
        var proc = this.data.procedure;
        var trial_type;
        ++post_trial;

        while (typeof proc[trial_set] !== "undefined") {
            while (typeof proc[trial_set][post_trial] !== "undefined") {
                trial_type = proc[trial_set][post_trial];

                if (trial_type !== '') {
                    return [trial_set, post_trial];
                }

                ++post_trial;
            }

            post_trial = 0;
            ++trial_set;
        }

        return false;
    }
}

// polyfill for String.trim, from https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/String/trim#Browser_compatibility
if (!String.prototype.trim) {
    String.prototype.trim = function () {
        return this.replace(/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g, '');
    };
}

function range_from_array(arr) {
    if (arr.length === 1) return arr;

    var first = arr[0];
    var last  = arr[arr.length-1];

    if (first === last) return [first];

    var step = (first < last) ? 1 : -1;
    var diff = (last - first) * step;

    var range = [];

    for (var i=0; i<=diff; ++i) {
        range.push(first + i*step);
    }

    return range;
}

function concat_arrays(array1, next_array) {
    return array1.concat(next_array);
}
