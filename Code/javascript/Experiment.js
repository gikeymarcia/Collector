var Experiment = function (exp_data, $container, trial_page) {
    this.data = {
        Stimuli: exp_data.Stimuli,
        Procedure: this.parse_Procedure(exp_data.Procedure),
    }
    this.position = exp_data.Position;

    this.exp_data = exp_data;
    
    this.create_iframe($container);
    this.trial_page = trial_page;
    
    this.run_trial();
}


Experiment.prototype = {
    parse_Procedure: function(proc_data) {
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

    get_trial: function(position) {
        //@TODO: handle when recieving a bad 'position'
        if (typeof position == 'undefined') {
            position = this.position;
        }
        var row = position[0]-1;
        var post_pos = position[1];

        var trial_set = this.data.Procedure[row];
        var this_proc = this.data.Procedure[row][post_pos];

        var items = this.get_item(trial_set, post_pos);
        var stimuli = this.get_stimuli(items);
        var responses = [];
        return {
            Procedure: trial_set[post_pos],
            Stimuli: stimuli,
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
            if (typeof self.data.Stimuli[item-2] !== "undefined") {
                stimuli.push(self.data.Stimuli[item-2]);
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
    
    run_trial() {
        var doc = this.iframe[0].contentDocument;
        doc.open();
        doc.write(this.trial_page);
        doc.close();
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
