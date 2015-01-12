/**
 * View class for Pages
 *
 * @copyright  2014-today Justso GmbH, Frankfurt, Germany
 * @author     j.schirrmacher@justso.de
 *
 * @package    Generator
 */

define([ "jquery", "backbone" ], function($, Backbone) {
    var t = _.template($("#PageEntry").html());

    return Backbone.View.extend({
        tagName: 'li',
        className: "list-group-item",

        render: function(page) {
            this.$el.attr("id", page.cid);
            this.$el.html(t(page.attributes));
            return this.el;
        }
    });
});
