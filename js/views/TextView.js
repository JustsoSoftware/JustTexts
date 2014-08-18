/**
 * View class for Texts
 *
 * @copyright  2014-today Justso GmbH, Frankfurt, Germany
 * @author     j.schirrmacher@justso.de
 *
 * @package    Generator
 */

define([ "jquery", "backbone", "text!templates/TextEntry.html" ], function($, Backbone, template) {
    return Backbone.View.extend({
        tagName: 'li',
        className: "list-group-item",

        initialize: function() {
            this.model.on('change', this.render, this);
        },

        render: function(text) {
            this.$el.attr("id", text.cid);
            this.$el.html(_.template(template, text.attributes));
            return this.el;
        }
    });
});
