/*
 * JavaScript for form editing week conditions.
 *
 * @module moodle-availability_week-form
 */
// jshint undef:false, unused:false

M.availability_week = M.availability_week || {};

/*
 * @class M.availability_week.form
 * @extends M.core_availability.plugin
 */
M.availability_week.form = Y.Object(M.core_availability.plugin);

/*
 * Groupings available for selection (alphabetical order).
 *
 * @property weeks
 * @type Array
 */
M.availability_week.form.weeks = null;

/*
 * Initialises this plugin.
 *
 * @method initInner
 * @param {Array} standardFields Array of objects with .field, .display
 * @param {Array} customFields Array of objects with .field, .display
 */
M.availability_week.form.initInner = function(weeksfromstart) {
    this.weeks = weeksfromstart;
};

M.availability_week.form.getNode = function(json) {
    // Create HTML structure.
    var strings = M.str.availability_week;
    var html = '<span class="availability-group"><label>' + strings.conditiontitle;
    html += ' <select name="field">';
    html += '<option value="choose">' + M.str.moodle.choosedots + '</option>';
    var fieldInfo;
    for (var i = 0; i < this.weeks.length; i++) {
        fieldInfo = this.weeks[i];
        // String has already been escaped using format_string.
        html += '<option value="w_' + fieldInfo.field + '">' + fieldInfo.display + '</option>';
    }
    html += '</select></label></span>';
    var node = Y.Node.create('<span>' + html + '</span>');

    // Set initial values if specified.
    if (json.w !== undefined &&
            node.one('select[name=field] > option[value=w_' + json.w + ']')) {
        node.one('select[name=field]').set('value', 'w_' + json.w);
    }

    // Add event handlers (first time only).
    if (!M.availability_week.form.addedEvents) {
        M.availability_week.form.addedEvents = true;
        var updateForm = function(input) {
            var ancestorNode = input.ancestor('span.availability_week');
            var op = ancestorNode.one('select[name=op]');
            var novalue = (op.get('value') === 'isempty' || op.get('value') === 'isnotempty');
            ancestorNode.one('input[name=value]').set('disabled', novalue);
            M.core_availability.form.update();
        };
        var root = Y.one('#fitem_id_availabilityconditionsjson');
        root.delegate('change', function() {
             updateForm(this);
        }, '.availability_week select');
        root.delegate('change', function() {
             updateForm(this);
        }, '.availability_week input[name=value]');
    }

    return node;
};

M.availability_week.form.fillValue = function(value, node) {
    // Set field.
    var field = node.one('select[name=field]').get('value');
    if (field.substr(0, 3) === 'w_') {
        value.w = field.substr(3);
    }
};
