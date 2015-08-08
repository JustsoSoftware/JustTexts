/**
 * Collection of Page objects
 *
 * @copyright  2014-today Justso GmbH, Frankfurt, Germany
 * @author     j.schirrmacher@justso.de
 *
 * @package    Generator
 */

/* global define */

define([ "jquery", "backbone", "models/Page" ], function ($, Backbone, Page) {
    'use strict';

    return Backbone.Collection.extend({
        model: Page,
        url: "/api/justtexts/page"
    });
});
