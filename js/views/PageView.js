/**
 * View class for Pages
 *
 * @copyright  2014-today Justso GmbH, Frankfurt, Germany
 * @author     j.schirrmacher@justso.de
 *
 * @package    Generator
 */

define([ "jquery", "backbone" ], function($, Backbone) {
    var template = '<li class="list-group-item" data-id="<%- data_id %>">' +
            '<span class="name" contenteditable="true"><%= name %></span>' +
            '<span class="template" contenteditable="true"><%= template %></span>' +
            '<span class="btn delete glyphicon glyphicon-remove"></span>' +
            '</li>';

    return Backbone.View.extend({
        initialize: function() {
            this.listenTo(this.collection, 'add', this.render);
        },

        render: function(page) {
            var pagesList = $("#pages"),
                existing = pagesList.find('li data[data-id="' + page.id + '"]'),
                data = {
                    data_id:  page.id || ('_' + page.cid),
                    name:     page.get("name"),
                    template: page.get("template")
                },
                html = _.template(template, data);

            if (existing.length) {
                existing.replace(html);
            } else {
                pagesList.append(html);
            }
            return this;
        }
    });
});
