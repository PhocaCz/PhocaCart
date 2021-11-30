/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(function () {
  "use strict";
  /**
   * Javascript to insert the link
   * View element calls jSelectPhocacartitems when a product is clicked
   * jSelectPhoca creates the link tag, sends it to the editor,
   * and closes the select frame.
   */

  window.jSelectPhocacartitem = function (id, title, catid, object, link, lang) {
    var hreflang = '',
        tag,
        editor;

    if (!Joomla.getOptions('xtd-phocacartitems')) {
      // Something went wrong!
      window.parent.jModalClose();
      return false;
    }

    editor = Joomla.getOptions('xtd-phocacartitems').editor;

    if (lang !== '') {
      hreflang = ' hreflang = "' + lang + '"';
    }

    tag = '<a' + hreflang + ' href="' + link + '">' + title + '</a>';
    /** Use the API, if editor supports it **/

    if (window.parent.Joomla && window.parent.Joomla.editors && window.parent.Joomla.editors.instances && window.parent.Joomla.editors.instances.hasOwnProperty(editor)) {
      window.parent.Joomla.editors.instances[editor].replaceSelection(tag);
    } else {
      window.parent.jInsertEditorText(tag, editor);
    }

    window.parent.jModalClose();
  };

  document.addEventListener('DOMContentLoaded', function () {
    // Get the elements
    var elements = document.querySelectorAll('.select-link');

    for (var i = 0, l = elements.length; l > i; i++) {
      // Listen for click event
      elements[i].addEventListener('click', function (event) {
        event.preventDefault();
        var functionName = event.target.getAttribute('data-function');

        if (functionName === 'jSelectPhocacartitem') {
          // Used in xtd_phocacartitems
          window[functionName](event.target.getAttribute('data-id'), event.target.getAttribute('data-title'), null, null, event.target.getAttribute('data-uri'), event.target.getAttribute('data-language'), null);
        } else {
          // Used in com_menus
          window.parent[functionName](event.target.getAttribute('data-id'), event.target.getAttribute('data-title'), null, null, event.target.getAttribute('data-uri'), event.target.getAttribute('data-language'), null);
        }
      });
    }
  });
})();