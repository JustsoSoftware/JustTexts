/**
 * Model class for a text
 *
 * @copyright  2014-today Justso GmbH, Frankfurt, Germany
 * @author     j.schirrmacher@justso.de
 *
 * @package    Generator
 */

/*global define*/

define([ "jquery", "backbone" ], function ($, Backbone) {
    'use strict';

    return Backbone.Model.extend({
        defaults: {
            basecontent: ""
        }
    });
});
