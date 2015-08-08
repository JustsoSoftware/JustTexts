/**
 * View class for Pages
 *
 * @copyright  2014-today Justso GmbH, Frankfurt, Germany
 * @author     j.schirrmacher@justso.de
 *
 * @package    Generator
 */

/* global define */

define([ "jquery", "backbone" ], function ($, Backbone) {
    'use strict';

    var compiled = _.template($("#PageEntry").html());

    return Backbone.View.extend({
        tagName: 'li',
        className: "list-group-item",

        render: function (page) {
            this.$el.attr("id", page.cid);
            this.$el.html(compiled(page.attributes));
            return this.el;
        }
    });
});
