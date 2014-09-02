# JustTexts

Multi language text administration tool.

The purpose of this tool is to administer text containers for multiple languages via a browser and place it in i18n
compatible format in the file system to be used in all kinds of programming languages, either backend or frontend.

With JavaScript, the texts can be used via i18n.js (see: http://github.com/requirejs/i18n for details).

## Setup

This package requires JustAPI as REST API backend, so checkout JustAPI into vendor/justso/justapi.
Checkout this package into vendor/justso/justtexts
Add a 'language' attribute to config.json file from JustAPI containing a list of language codes you want to use.

Example:

```
"language": ["de", "en", "fr"]
```

Alter your Apache config file to point the '/justtexts' directory to the JavaScript part of the package and for easy
access to required libraries:

```
Alias /justtexts /path/to/my/project/vendor/justso/justtexts/frontend
Alias /vendor /path/to/my/project/vendor
```

After reloading the apache configuration ("service apache2 reload") the application should be accessible via
http://your-server.example.com/justtexts/

## Usage of text administration

Texts are grouped as 'pages'. So, at first, create a page in the list and give it a name. It is good practice to use
the same name as the html page's name (not the title), so if your URL is http://your-server.example.com/my-page.html, it
would be easy for you to find the corresponding texts later if you name the page 'my-page'.

Creating a page is easy: just press the "Add page" button and click on the "Name" field in the empty list row. After
entering a name and leaving the field (by clicking somewhere else or pressing the tab button) the page is created.
Changing the name is easy as well: just click on the name and change it. Again, the change is made persisting when you
leave the field. By moving the mouse over the row, a 'x' icon is displayed at the right of the row, allowing you to
delete the page.

After creating a page, a second list is displayed right of the first one. There you see all texts yet defined in this
page - none. With the "Add text" button you can create new text blocks for the page, each having a unique name and the
text itself. The text is later accessed via its unique name. Creating, altering or deleting texts is just the same as
with pages. Just WYSIWYG.

## Accessing texts

Texts are placed in a directory 'htdocs/nls' in JavaScript files compatible to i18n.js (see above) and can be accessed
with this package, which is a require.js plugin. So, just require "i18n!my-page" in your AMD module to access texts for
your page with the name 'my-page'. The names of the text containers are the names of the parameter's attributes. i18n
takes care of loading the texts in the language your user prefers - if it exists. Else, it defaults to the language
defined first in your config.json file.

## Translation

Translation means calling the administration and changing the language to the desired one. The text in the base language
(this is the language defined first in your config.json file) is displayed side by side for each text container to make
translation easier.

JustTexts takes care on texts changed after translations. So, if your text in the base language is changed, the texts in
the container of all other languages is marked as outdated. In the administration, outdated texts are marked in red, so
it is relatively easy to find such texts and re-translate them if necessary.

## Support & More

If you need support, please contact us: http://justso.de