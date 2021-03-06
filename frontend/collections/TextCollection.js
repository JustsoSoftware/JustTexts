/**
 * Collection of Text objects
 *
 * @copyright  2014-today Justso GmbH, Frankfurt, Germany
 * @author     j.schirrmacher@justso.de
 *
 * @package    Generator
 */

'use strict';

/* global define */

define([ "jquery", "backbone", "models/Text" ], function ($, Backbone, Text) {
    return Backbone.Collection.extend({
        model: Text,

        initialize: function (options) {
            this.url = '/api/justtexts/page/' + options.page.replace(/\*/, '') + '/text/' + options.language;
        }
    });
});
