/**
 * Justso content administration main router
 *
 * @copyright  2014-today Justso GmbH, Frankfurt, Germany
 * @author     j.schirrmacher@justso.de
 *
 * @package    Generator
 */

define(["jquery","backbone", "i18n!nls/messages", "collections/PageCollection", "views/PageView", "models/Page",
        "collections/TextCollection", "views/TextView", "models/Text"],
    function($, Backbone, messages, PageCollection, PageView, Page, TextCollection, TextView, Text) {
    var pageListView,
        pageList = $("#pages"),
        textsList = $("#page-texts"),
        textsListContainer = $("#page-texts-container"),
        textListView,
        getModel = function($li, view) {
            return view.collection.get($li.attr("id"));
        },
        showError = function(request, message) {
            alert(message + ": " + request.responseText);
        },
        deleteListItem = function(button, msg, view) {
            var $li = $(button).parent("li");
            if (!$li.text().trim() || confirm(msg)) {
                getModel($li, view).destroy();
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
            var currentPage,
                self = this,
                setListenHandler = function(listView, viewClass, container) {
                    self.listenTo(listView.collection, 'add', function(model) {
                        var entryView = new viewClass({ model: model });
                        container.append(entryView.render(model));
                    });
                    self.listenTo(listView.collection, 'destroy', function(model) {
                        var $li = container.find('li[id="' + model.cid + '"]');
                        if ($li.hasClass("active")) {
                            textsListContainer.hide();
                        }
                        $li.remove();
                    });
                },
                showTextList = function (pageCId) {
                    var pageId = pageListView.collection.get(pageCId).id,
                        languageswitch = $("#language-switch");
                    if (pageId) {
                        textListView = new Backbone.View({ collection: new TextCollection({page: pageId, language: languageswitch.val()}) });

                        setListenHandler(textListView, TextView, textsList);
                        textsList.find('li[id]').remove();
                        textListView.collection.fetch();
                        textsListContainer.toggleClass("showTranslation", languageswitch[0].selectedIndex > 0);
                        textsListContainer.show();
                    } else {
                        textsListContainer.hide();
                    }
                },
                blurHandler = function($domObj, view, setFunction) {
                    var model = getModel($domObj, view),
                        valid = setFunction(model);

                    if (model.changedAttributes() && valid) {
                        model.save().fail(showError);
                    }
                };

            $.get('/api/justtexts/language')
                .then(function(data) {
                    var box = $("#language-switch");
                    $.each(data, function() {
                        box.append('<option>' + this + '</option>');
                    });
                })
                .fail(showError);
            $("#language-switch").on("change", function() {
                showTextList(pageList.find('li.active').attr("id"));
            });

            pageListView = new Backbone.View({ container: pageList, collection: new PageCollection });
            $.getScript("/api/justtexts/loadPlugins")
                .then(function() {
                    setListenHandler(pageListView, PageView, pageList);
                    pageListView.collection.fetch();
                });

            pageList
                .on("focus", "li", function() {
                    var pageId = $(this).attr("id");
                    if (currentPage !== pageId) {
                        $('li[id="' + currentPage + '"]').removeClass("active");
                        $(this).addClass("active");
                        currentPage = pageId;
                        showTextList(pageId);
                    }
                })
                .on("blur", "li", function() {
                    var $li = $(this);
                    blurHandler($li, pageListView, function(model) {
                        var variables = $li.find("*[contenteditable]"),
                            allSet = true;
                        $.each(variables, function() {
                            var val = $(this).text().trim();
                            model.set($(this).attr("class"), val);
                            allSet &= !!val;
                        });
                        return allSet;
                    });
                })
                .on("click", ".delete", function() {
                    deleteListItem(this, messages.confirmDeletePage, pageListView);
                });

            $("#add-new-page").on("click", function() {
                pageListView.collection.add(new Page({ id: null, name: '', template: '' }));
                return false;
            });

            textsList
                .on("blur", "li", function() {
                    var $li = $(this);
                    blurHandler($li, textListView, function(model) {
                        model.set({
                            name: $li.find(".name").text().trim(),
                            content: $li.find(".content").html()
                        });
                        return model.get("name");
                    });
                })
                .on("click", ".delete", function() {
                    deleteListItem(this, messages.confirmDeleteText, textListView);
                });

            $("#add-new-text").on("click", function() {
                textListView.collection.add(new Text({ id: null, name: '', content: '', outdated: false }));
                return false;
            });
        },

        refreshPageListView: function() {
            pageListView.collection.sync();
        }
    });
});