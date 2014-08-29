/**
 * Main configuration for justso content administration
 *
 * @copyright  2014-today Justso GmbH, Frankfurt, Germany
 * @author     j.schirrmacher@justso.de
 *
 * @package    Generator
 */

require.config({
    waitSeconds: 30,
    baseUrl: ".",
    paths: {
        "jquery":       "/vendor/jquery-1.11.1.min",
        "jquerymobile": "/vendor/jquery-mobile/jquery.mobile-1.4.3.min",
        "underscore":   "/vendor/underscore-min",
        "backbone":     "/vendor/backbone-min",
        "text":         "/vendor/text",
        "i18n":         "/vendor/i18n"
    },

    shim: {
        "backbone": {
            "deps": [ "underscore", "jquery" ],
            "exports": "Backbone"
        }
    }
});

require([ "jquery", "backbone", "mainRouter" ], function($, Backbone, Router) {
    // Instantiates a new Backbone.js Router
    this.router = new Router();
});
