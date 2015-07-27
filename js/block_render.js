/**
 * @file
 * Renders Blocks onto a page.
 */

/**
 * Global Drupal object.
 *
 * @global
 *
 * @namespace
 */
window.Drupal = window.Drupal || {};

(function (JSON) {

  "use strict";

  /**
   * @namespace
   */
  Drupal.block_render = {

    /**
     * Render a Block.
     *
     * @param {string} base_url
     * @param {object} options
     */
    render: function(base_url, options) {
      var request = new XMLHttpRequest();

      request.onreadystatechange = function() {
        if (request.readyState != XMLHttpRequest.DONE ) {
          return;
        }

        if (request.status != 200) {
          return;
        }

        var data = JSON.parse(request.responseText);

        // Insert the 'content' from the request.
        for (var block_id in options.blocks) {
          if (typeof options.blocks[block_id].element == 'object') {
            var insert = options.blocks[block_id]
          }
          else {
            var insert = document.getElementById(options.blocks[block_id]);
          }
          insert.innerHTML = data.content[block_id];
        }

        // Handle the CSS/JS Assets.
        if (data.assets) {
          for (var position in data.assets) {
            if (data.assets.hasOwnProperty(position)) {
              for (var i = 0; i < data.assets[position].length; i++) {

                // Build the element.
                var item = data.assets[position][i];
                var element = document.createElement(item.tag);

                if (item.value) {
                  element.textContent = item.value;
                }

                for (var attribute in item.attributes) {
                  if (item.attributes.hasOwnProperty(attribute)) {
                    element[attribute] = item.attributes[attribute];
                  }
                }

                if (item.tag == 'script' && !element.type) {
                  element.type = 'text/javascript';
                  element.async = false;
                }

                // Inject the asset onto the page.
                if (position == 'header') {
                  document.head.appendChild(element);
                }
                else {
                  document.body.appendChild(element);
                }

              }
            }
          }
        }

      }

      // Base URL.
      var url = base_url + '?_format=json';

      // Append all of the requested blocks.
      if (options.blocks) {
        for (var block_id in options.blocks) {
          url = url + '&blocks[]=' + encodeURIComponent(block_id);
        }
      }

      // Append all of the loaded libraries.
      if (options.loaded) {
        for (var i = 0; i < options.loaded.length; i++) {
          url = url + '&loaded[]=' + encodeURIComponent(options.loaded[i]);
        }
      }

      // Send the request.
      request.open('GET', url);
      request.send();

    }

  }

})(window.JSON);
