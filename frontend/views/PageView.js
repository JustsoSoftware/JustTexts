/**
 * View class for Pages
 *
 * @copyright  2014-today Justso GmbH, Frankfurt, Germany
 * @author     j.schirrmacher@justso.de
 *
 * @package    Generator
 */

define([ "jquery", "backbone", "text!templates/PageEntry.html" ], function($, Backbone, template) {
    return Backbone.View.extend({
        tagName: 'li',
        className: "list-group-item",

        render: function(page) {
            this.$el.attr("id", page.cid);
            this.$el.html(_.template(template, page.attributes));
            return this.el;
        }
    });
});
