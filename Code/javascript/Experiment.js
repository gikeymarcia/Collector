var Experiment = function (exp_data, $container, trial_page, trial_types, server_paths) {
    this.data = {
        stimuli: exp_data.stimuli,
        procedure: this.parse_procedure(exp_data.procedure),
        globals: exp_data.globals,
        responses: []
    }

    this.exp_data   = exp_data;
    this.trial_page = trial_page;
    this.ajax_tools_path = server_paths.ajax_tools;    
    this.media_path = server_paths.media_path;
    this.root_path  = server_paths.root_path;
    this.data.globals.position   = exp_data.globals.position;
    this.load_trial_types(trial_types);
    this.container = $container;
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

    get_trial_data: function(position) {
        var trial_inputs = this.get_trial_inputs(position);

        var type = trial_inputs.procedure["Trial Type"]; // maybe add error handling here

        return {
            inputs: trial_inputs,
            type: this.get_trial_type(type),
            globals: this.data.globals,
        }
    },

    get_trial_inputs: function(position) {
        
        if(parent.set_position.on_off=="on"){
            position=parent.set_position.position;
            parent.User_Data.Experiment_Data.globals.position=parent.set_position.position;
            parent.set_position.on_off="off";
        }
        
        //@TODO: handle when recieving a bad 'position'
        if (typeof position == 'undefined') {
            position = this.data.globals.position;
        }
        var row = position[0];
        var post_pos = position[1];

        var trial_set = this.data.procedure[row];
        var this_proc = this.data.procedure[row][post_pos];

        var items = this.get_item(trial_set, post_pos);
        var stimuli = this.get_stimuli(items);

        // var responses = [];
        var inputs = {
            procedure: trial_set[post_pos],
            stimuli: stimuli,
            //@TODO: return associated responses for this trial
        }

        return inputs;
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


	run_trial(position,buffer) {
        if (typeof position == "undefined") {
            position = this.data.globals.position;           
        }
        
        if(typeof buffer == "undefined"){
            buffer = 1;
        }
        console.dir(position);
        buffer_positions=[];
        buffer_positions[0]=position;
        console.dir(buffer_positions);
        
        /*
        
        for(var i=0; i<buffer; i++){
            buffer_positions[i+1]= JSON.parse(JSON.stringify(buffer_positions[i]));
            buffer_positions[i][0]++;
//            this_position = buffer_positions[i]; //one too few - right?
        } 
        console.dir(buffer_positions);
        */
        
        
        for(var i=0; i<buffer; i++){
            this_position = position;

            $("#ExperimentContainer").children().remove();
            var new_iframe = $("<iframe>");
            new_iframe.appendTo("#ExperimentContainer");
            var doc = new_iframe[0].contentDocument;
        
            this.data.globals.position = this_position;
            
            
        
            doc.open();
            doc.write(this.trial_page);
            doc.close();
        }
    },
    
    end_trial: function(data, inputs, globals) {
        position = this.data.globals.position;
        this.data.globals = globals;

        if (!Array.isArray(data)) data = [data];

        this.save_trial(data, inputs);


        // you can use this to skip trials OR
        // if you set Trial.global_data.next_position = false
        // it will end the experiment
        if (typeof this.data.globals.next_position !== "undefined") {
            var next_position = this.data.globals.next_position;
        } else {
            var next_position = this.get_next_trial_position();
        }

        if (next_position) {
            this.record_if_ready(next_position[0]); // if we have enough completed rows of data (including post trials)
            this.run_trial(next_position);
        }
        else {
            this.record_remaining_trials();
            if(typeof(parent.completion_code) !== "undefined"){
                $("#ExperimentContainer").html("<h1>"+parent.completion_code+"</h1>");
            } else {
                $("#ExperimentContainer").html("<h1>Done</h1>");
            }
        }
    },

    // find all the procedure rows that have all their data and havent been recorded yet
    record_if_ready: function(next_position) {
        var unrecorded = this.get_unrecorded_trials(next_position);

        if(unrecorded.length > 0) {
            this.record_to_server(unrecorded);
        }
    },

    get_unrecorded_trials: function(current_row_index) {
        var responses = this.data.responses;

        // dont try to record trial rows that we are still working on
        if (typeof current_row_index !== "undefined") {
            responses = responses.filter(function(trial_set, trial_set_index) {
                if (trial_set_index !== current_row_index) return true;
            });
        }

        // find all the saved responses that haven't yet been recorded to the server
        return responses.filter(function(trial_set) {
            for (var index in trial_set) {
                return (trial_set[index]['recorded'] === false);
            }
        });
    },

    record_remaining_trials: function() {
        this.record_to_server(this.get_unrecorded_trials());
    },

    save_trial: function(data, inputs) {
        var pos = this.data.globals.position;
        var trial_set  = pos[0];
        var post_trial = pos[1];
        var resp = this.data.responses;

        if (typeof resp[trial_set] === "undefined") resp[trial_set] = [];
        resp[trial_set][post_trial] = {
            recorded: false,
            position: pos,
            data: {
                stimuli:   inputs['stimuli'],
                procedure: inputs['procedure'],
                responses: data,
            }
        };

    },

    get_value_as_string: function(value) {
        if (value === null) {
            return '';
        } else if (typeof value === 'object') {
            if (Array.isArray(value)) {
                return value.join('|');
            } else {
                var obj_join = '';

                for (var prop in value) {
                    if (obj_join !== '') obj_join += '|';

                    obj_join += value[prop];
                }

                return obj_join;
            }
        } else if (typeof value === 'string') {
            return value;
        } else {
            return value.toString();
        }
    },

    get_trial_output: function(trial) {
        var trial_info = {
            "Trial": trial.position[0] + "." + trial.position[1]
        };

        for (var prop in trial.data.procedure) {
            trial_info["Proc_" + prop] = trial.data.procedure[prop];
        }

        // trial.data.stimuli is an object with the keys
        // being columns in the stim file and the values
        // being the array of values used for this trial
        for (var stim_col in trial.data.stimuli) {
            trial_info["Stim_" + stim_col] = trial.data.stimuli[stim_col].join('|');
        }

        var self = this;

        var response_rows = trial.data.responses.map(function(responses) {
            var row = {};

            for (var resp_col in responses) {
                row["Resp_" + resp_col] = self.get_value_as_string(responses[resp_col]);;
            }

            return row;
        });

        return response_rows.map(function(responses) {
            for (var prop in trial_info) {
                responses[prop] = trial_info[prop];
            }

            return responses;
        });
    },

    get_trial_set_output: function(set) {
        var self = this;

        var trial_outputs = set.map(function(trial) {
            return self.get_trial_output(trial);
        });

        var number_of_rows = 0;

        for (var post_level in trial_outputs) {
            number_of_rows = Math.max(number_of_rows, trial_outputs.length);
        }

        var rows_in_recording_form = [];

        for (var i=0; i<number_of_rows; ++i) {
            var output_row = {};

            for (post_level in trial_outputs) {
                if (typeof trial_outputs[post_level][i] !== 'undefined') {
                    var response_index = i;
                } else if (trial_outputs[post_level].length === 1) {
                    var response_index = 0;
                } else {
                    var response_index = null;
                }

                if (response_index !== null) {
                    for (var prop in trial_outputs[post_level][response_index]) {
                        if (post_level > 0) {
                            var col = "Post_" + post_level + "_" + prop;
                        } else {
                            var col = prop;
                        }

                        var val = trial_outputs[post_level][response_index][prop];
                        output_row[col] = val;
                    }
                }
            }

            rows_in_recording_form.push(output_row);
        }

        return rows_in_recording_form;
    },

    record_to_server: function(trial_sets) {
        trial_sets.forEach(function(set) {
            set.forEach(function(trial) {
                trial.recorded = true;
            });
        });

        var self = this;
        var rows = trial_sets
                  .map(function(set) { return self.get_trial_set_output(set); })
                  .reduce(concat_arrays, [])
                  .map(function(row) {
                    row["Username"] = User_Data.Username;
                    row["ID"]       = User_Data.ID;
                    row["Exp_Name"] = User_Data.Exp_Name;
                    return row;
                  });

        var rows    = JSON.stringify(rows);
        var globals = JSON.stringify(this.data.globals);
        
        $.ajax({
            url: this.root_path + '/Code/trialRecord.php',
            type: 'POST',
            dataType: 'text',
            data: {
                trial_data: rows,
                globals: globals,
            },
        })
        .fail(function(data, text, error) {
            console.dir(data);
            console.dir(text);
            console.dir(error);
            console.dir("error!");
        })
        .done(function(data, textStatus, jqXHR) {
            if (data !== 'success') {
                console.dir(data);
            }
        });
    },

    get_next_trial_position: function() {
        var pos = this.data.globals.position;
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
