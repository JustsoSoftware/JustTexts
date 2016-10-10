/**
 * Main configuration for justso content administration
 *
 * @copyright  2014-today Justso GmbH, Frankfurt, Germany
 * @author     j.schirrmacher@justso.de
 *
 * @package    Generator
 */

'use strict';

/* global require, window */

require.config({
    waitSeconds: 30,
    baseUrl: ".",
    paths: {
        "jquery":     "/components/jquery/jquery.min",
        "underscore": "/components/underscore/underscore-min",
        "backbone":   "/components/backbone/backbone-min",
        "text":       "/vendor/text",
        "i18n":       "/vendor/i18n"
    },

    shim: {
        "backbone": {
            "deps": [ "underscore", "jquery" ],
            "exports": "Backbone"
        }
    }
});

require([ "jquery", "backbone", "mainRouter" ], function ($, Backbone, Router) {
    // Instantiates a new Backbone.js Router
    window.router = new Router();
});
