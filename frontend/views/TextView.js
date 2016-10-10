/**
 * View class for Texts
 *
 * @copyright  2014-today Justso GmbH, Frankfurt, Germany
 * @author     j.schirrmacher@justso.de
 *
 * @package    Generator
 */

'use strict';

/* global define */
/* jslint nomen: true */

define([ "jquery", "backbone", "underscore", "text!templates/TextEntry.html" ], function ($, Backbone, _, template) {
    var compiled = _.template(template);

    return Backbone.View.extend({
        tagName: 'li',
        className: "list-group-item",

        initialize: function () {
            this.model.on('change', this.render, this);
        },

        render: function (text) {
            this.$el.attr("id", text.cid);
            this.$el.html(compiled(text.attributes));
            return this.el;
        }
    });
});
