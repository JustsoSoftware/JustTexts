/**
 * Model class for a text
 *
 * @copyright  2014-today Justso GmbH, Frankfurt, Germany
 * @author     j.schirrmacher@justso.de
 *
 * @package    Generator
 */

'use strict';

/*global define*/

define([ "jquery", "backbone" ], function ($, Backbone) {
    return Backbone.Model.extend({
        defaults: {
            basecontent: ""
        }
    });
});
