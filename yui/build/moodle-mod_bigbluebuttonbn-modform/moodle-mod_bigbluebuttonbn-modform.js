YUI.add('moodle-mod_bigbluebuttonbn-modform', function (Y, NAME) {

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/** global: M */
/** global: Y */

M.mod_bigbluebuttonbn = M.mod_bigbluebuttonbn || {};

M.mod_bigbluebuttonbn.modform = {

    bigbluebuttonbn: {},
    strings: {},

    /**
     * Initialise the broker code.
     *
     * @method init
     */
    init: function(bigbluebuttonbn) {
        this.bigbluebuttonbn = bigbluebuttonbn;
        this.strings = {
            as: M.str.bigbluebuttonbn.mod_form_field_participant_list_text_as,
            viewer: M.str.bigbluebuttonbn.mod_form_field_participant_bbb_role_viewer,
            moderator: M.str.bigbluebuttonbn.mod_form_field_participant_bbb_role_moderator,
            remove: M.str.bigbluebuttonbn.mod_form_field_participant_list_action_removee
        };
        this.update_instance_type_profile();
        this.participant_list_init();
    },

    update_instance_type_profile: function() {
        var selected_type = Y.one('#id_type');
        this.apply_instance_type_profile(this.bigbluebuttonbn.instance_type_profiles[
            selected_type.get('value')]);
    },

    apply_instance_type_profile: function(instance_type_profile) {
        var features = instance_type_profile.features;
        var show_all = features.includes('all');

        // Show room settings validation.
        this.show_fieldset('id_room', show_all || features.includes('showroom'));

        // Show recordings settings validation.
        this.show_fieldset('id_recordings', show_all || features.includes('showrecordings'));

        // Preuploadpresentation feature validation.
        this.show_fieldset('id_preuploadpresentation', show_all ||
            features.includes('preuploadpresentation'));

        // Participants feature validation.
        this.show_fieldset('id_permissions', show_all || features.includes('permissions'));

        // Schedule feature validation.
        this.show_fieldset('id_schedule', show_all || features.includes('schedule'));
    },

    show_fieldset: function(id, show) {
        // Show room settings validation.
        var fieldset = Y.DOM.byId(id);
        if (!fieldset) {
            return;
        }

        if (show) {
            Y.DOM.setStyle(fieldset, 'display', 'block');
            return;
        }

        Y.DOM.setStyle(fieldset, 'display', 'none');
    },

    participant_selection_set: function() {
        this.select_clear('bigbluebuttonbn_participant_selection');

        var type = document.getElementById('bigbluebuttonbn_participant_selection_type');
        for (var i = 0; i < type.options.length; i++) {
            if (type.options[i].selected) {
                var options = this.bigbluebuttonbn.participant_data[type.options[i].value].children;
                for (var option in options) {
                    if (options.hasOwnProperty(option)) {
                        this.select_add_option(
                            'bigbluebuttonbn_participant_selection', options[option].name, options[option].id
                        );
                    }
                }
                if (type.options[i].value === 'all') {
                    this.select_add_option('bigbluebuttonbn_participant_selection',
                        '---------------', 'all');
                    this.select_disable('bigbluebuttonbn_participant_selection');
                } else {
                    this.select_enable('bigbluebuttonbn_participant_selection');
                }
            }
        }
    },

    participant_list_init: function() {
        var selection_type_value;
        var selection_value;
        for (var i = 0; i < this.bigbluebuttonbn.participant_list.length; i++) {
            selection_type_value = this.bigbluebuttonbn.participant_list[i].selectiontype;
            selection_value = this.bigbluebuttonbn.participant_list[i].selectionid;

            // Add it to the form.
            this.participant_add_to_form(selection_type_value, selection_value);
        }
        // Update in the form.
        this.participant_list_update();
    },

    participant_list_update: function() {
        var participant_list = document.getElementsByName('participants')[0];
        participant_list.value = JSON.stringify(this.bigbluebuttonbn.participant_list).replace(/"/g, '&quot;');
    },

    participant_remove: function(selection_type_value, selection_value) {
        // Remove from memory.
        this.participant_remove_from_memory(selection_type_value, selection_value);

        // Remove from the form.
        this.participant_remove_from_form(selection_type_value, selection_value);

        // Update in the form.
        this.participant_list_update();
    },

    participant_remove_from_memory: function(selection_type_value, selection_value) {
        var selectionid = (selection_value === '' ? null : selection_value);
        for (var i = 0; i < this.bigbluebuttonbn.participant_list.length; i++) {
            if (this.bigbluebuttonbn.participant_list[i].selectiontype == selection_type_value &&
                this.bigbluebuttonbn.participant_list[i].selectionid == selectionid) {
                this.bigbluebuttonbn.participant_list.splice(i, 1);
            }
        }
    },

    participant_remove_from_form: function(selection_type_value, selection_value) {
        var id = 'participant_list_tr_' + selection_type_value + '-' + selection_value;
        var participant_list_table = document.getElementById('participant_list_table');
        for (var i = 0; i < participant_list_table.rows.length; i++) {
            if (participant_list_table.rows[i].id == id) {
                participant_list_table.deleteRow(i);
            }
        }
    },

    participant_add: function() {
        var selection_type = document.getElementById('bigbluebuttonbn_participant_selection_type');
        var selection = document.getElementById('bigbluebuttonbn_participant_selection');

        // Lookup to see if it has been added already.
        for (var i = 0; i < this.bigbluebuttonbn.participant_list.length; i++) {
            if (this.bigbluebuttonbn.participant_list[i].selectiontype == selection_type.value &&
                this.bigbluebuttonbn.participant_list[i].selectionid == selection.value) {
                return;
            }
        }

        // Add it to memory.
        this.participant_add_to_memory(selection_type.value, selection.value);

        // Add it to the form.
        this.participant_add_to_form(selection_type.value, selection.value);

        // Update in the form.
        this.participant_list_update();
    },

    participant_add_to_memory: function(selection_type_value, selection_value) {
        this.bigbluebuttonbn.participant_list.push({
            "selectiontype": selection_type_value,
            "selectionid": selection_value,
            "role": "viewer"
        });
    },

    participant_add_to_form: function(selection_type_value, selection_value) {
        var participant_list_table = document.getElementById('participant_list_table');
        var row = participant_list_table.insertRow(participant_list_table.rows.length);
        row.id = "participant_list_tr_" + selection_type_value + "-" + selection_value;
        var cell0 = row.insertCell(0);
        cell0.width = "125px";
        cell0.innerHTML = '<b><i>' + this.bigbluebuttonbn.participant_data[selection_type_value].name;
        cell0.innerHTML += (selection_type_value !== 'all' ? ':&nbsp;' : '') + '</i></b>';
        var cell1 = row.insertCell(1);
        cell1.innerHTML = '';
        if (selection_type_value !== 'all') {
            cell1.innerHTML = this.bigbluebuttonbn.participant_data[selection_type_value].children[selection_value].name;
        }
        var innerHTML;
        innerHTML = '&nbsp;<i>' + this.strings.as + '</i>&nbsp;';
        innerHTML += '<select id="participant_list_role_' + selection_type_value + '-' + selection_value + '"';
        innerHTML += ' onchange="M.mod_bigbluebuttonbn.modform.participant_list_role_update(\'';
        innerHTML += selection_type_value + '\', \'' + selection_value;
        innerHTML += '\'); return 0;" class="select custom-select"><option value="viewer" selected="selected">';
        innerHTML += this.strings.viewer + '</option><option value="moderator">';
        innerHTML += this.strings.moderator + '</option></select>';
        var cell2 = row.insertCell(2);
        cell2.innerHTML = innerHTML;
        var cell3 = row.insertCell(3);
        cell3.width = "20px";
        innerHTML = '<a onclick="M.mod_bigbluebuttonbn.modform.participant_remove(\'';
        innerHTML += selection_type_value + '\', \'' + selection_value;
        innerHTML += '\'); return 0;" title="' + this.strings.remove + '">x</a>';
        if (this.bigbluebuttonbn.icons_enabled) {
            innerHTML = '<a class="action-icon" onclick="M.mod_bigbluebuttonbn.modform.participant_remove(\'';
            innerHTML += selection_type_value + '\', \'';
            innerHTML += selection_value + '\'); return 0;"><img class="btn icon smallicon" alt="';
            innerHTML += this.strings.remove + '" title="' + this.strings.remove + '" src="';
            innerHTML += this.bigbluebuttonbn.pix_icon_delete + '"></img></a>';
        }
        cell3.innerHTML = innerHTML;
    },

    participant_list_role_update: function(type, id) {
        // Update in memory.
        var participant_list_role_selection = document.getElementById('participant_list_role_' + type + '-' + id);
        for (var i = 0; i < this.bigbluebuttonbn.participant_list.length; i++) {
            if (this.bigbluebuttonbn.participant_list[i].selectiontype == type &&
                this.bigbluebuttonbn.participant_list[i].selectionid == (id === '' ? null : id)) {
                this.bigbluebuttonbn.participant_list[i].role = participant_list_role_selection.value;
            }
        }

        // Update in the form.
        this.participant_list_update();
    },

    select_clear: function(id) {
        var select = document.getElementById(id);
        while (select.length > 0) {
            select.remove(select.length - 1);
        }
    },

    select_enable: function(id) {
        var select = document.getElementById(id);
        select.disabled = false;
    },

    select_disable: function(id) {
        var select = document.getElementById(id);
        select.disabled = true;
    },

    select_add_option: function(id, text, value) {
        var select = document.getElementById(id);
        var option = document.createElement('option');
        option.text = text;
        option.value = value;
        select.add(option, option.length);
    }

};


}, '@VERSION@', {"requires": ["base", "node"]});
