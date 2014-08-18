/**
 * View class for Texts
 *
 * @copyright  2014-today Justso GmbH, Frankfurt, Germany
 * @author     j.schirrmacher@justso.de
 *
 * @package    Generator
 */

define([ "jquery", "backbone" ], function($, Backbone) {
    var template = '<li class="list-group-item" data-id="<%- data_id %>">' +
            '<span class="name" contenteditable="true"><%= name %></span>' +
            '<span class="content" contenteditable="true"><%= content %></span>' +
            '<span class="btn delete glyphicon glyphicon-remove"></span>' +
            '</li>',
        textsList = $("#page-texts");

    return Backbone.View.extend({
        initialize: function() {
            textsList.find('li[data-id]').remove();
            this.listenTo(this.collection, 'add', this.render);
        },

        render: function(page) {
            var existing = textsList.find('li data[data-id="' + page.id + '"]'),
                data = {
                    data_id: page.id || ('_' + page.cid),
                    name:    page.get("name"),
                    content: page.get("content")
                },
                html = _.template(template, data);

            if (existing.length) {
                existing.replace(html);
            } else {
                textsList.append(html);
            }
            return this;
        }
    });
});
