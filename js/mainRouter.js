/**
 * Justso content administration main router
 *
 * @copyright  2014-today Justso GmbH, Frankfurt, Germany
 * @author     j.schirrmacher@justso.de
 *
 * @package    Generator
 */

define(["jquery","backbone", "collections/PageCollection", "views/PageView", "models/Page",
        "collections/TextCollection", "views/TextView", "models/Text"],
    function($, Backbone, PageCollection, PageView, Page, TextCollection, TextView, Text) {
    var view,
        pageList = $("#pages"),
        textsList = $("#page-texts"),
        textsListContainer = $("#page-texts-container"),
        textView,
        getModel = function($li, view) {
            var id = $li.attr("data-id");
            return view.collection.get(id.match(/^_(.*)/) ? RegExp.$1 : id);
        },
        showError = function(request, message) {
            alert(message + ": " + request.responseText);
        },
        deleteListItem = function(button, msg, view) {
            var $li = $(button).parent("li");
            if (!$li.text() || confirm(msg)) {
                getModel($li, view).destroy({success: function() {
                        $li.remove();
                    }
                });
            }
        };

    return Backbone.Router.extend({
        initialize: function() {
            textsListContainer.hide();
            Backbone.history.start();
        },

        routes: {
            "": "index",
            "/": "index",
            "*x": "index"
        },

        index: function() {
            var currentPage;

            view = new PageView({ collection: new PageCollection });
            view.collection.fetch();

            pageList
                .on("focus", "li", function() {
                    var pageId = $(this).attr("data-id");
                    if (!pageId.match(/^_/) && currentPage !== pageId) {
                        $('li[data-id="' + currentPage + '"]').removeClass("active");
                        $(this).addClass("active");
                        currentPage = pageId;
                        textView = new TextView({ collection: new TextCollection({page: pageId}) });
                        textView.collection.fetch();
                        textsListContainer.show();
                    }
                })
                .on("blur", "li", function() {
                    var model = getModel($(this), view);

                    model.set({
                        name: $(this).find(".name").text(),
                        template: $(this).find(".template").text()
                    });
                    if (model.changedAttributes() && model.get("name") && model.get("template")) {
                        model.save().fail(showError);
                    }
                })
                .on("click", ".delete", function() {
                    deleteListItem(this, "Do you really want to delete this page? All texts on it will be lost!", view);
                });

            $("#add-new-page").on("click", function() {
                view.collection.add(new Page({ id: null, name: '', template: '' }));
                return false;
            });

            textsList
                .on("blur", "li", function() {
                    var model = getModel($(this), textView);
                    model.set({
                        name: $(this).find(".name").text(),
                        content: $(this).find(".content").html()
                    });
                    if (model.changedAttributes() && model.get("name")) {
                        model.save().fail(showError);
                    }
                })
                .on("click", ".delete", function() {
                    deleteListItem(this, "Do you really want to delete this text?", textView);
                });

            $("#add-new-text").on("click", function() {
                textView.collection.add(new Text({ id: null, name: '', content: '' }));
                return false;
            });

            $("#flush").on("click", function() {
                $.get("/api/justtexts/cache/flush")
                    .then(function() {
                        view.collection.sync();
                    })
                    .fail(function(error, message) {
                        alert(message + ': ' + error.responseText);
                    });
                return false;
            });
        }
    });
});
