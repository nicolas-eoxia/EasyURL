/* Copyright (C) 2024-2024 EVARISK <technique@evarisk.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * Library javascript to enable Browser notifications
 */

/**
 * \file    js/publicinterface.js
 * \ingroup easyurl
 * \brief   JavaScript publicinterface file for module EasyURL
 */

'use strict';

/**
 * Init publicinterface JS
 *
 * @memberof EasyURL_PublicInterface
 *
 * @since   1.1.0
 * @version 1.1.0
 *
 * @type {Object}
 */
window.easyurl.publicinterface = {};

/**
 * ExportShortener init
 *
 * @memberof EasyURL_PublicInterface
 *
 * @since   1.1.0
 * @version 1.1.0
 *
 * @returns {void}
 */
window.easyurl.publicinterface.init = function() {
  window.easyurl.publicinterface.event();

  $(document).ready(function() {
    $('#shortener').select2({
      matcher: function(params, data) {
        if ($.trim(params.term) === '') {
          return data;
        }
        let term = params.term.replace(/^https?:\/\//, '').split('/').pop().toLowerCase();
        term = term.replace(/-/g, ' ').replace(/ /g, '');

        let normalizedText = data.text.replace(/-/g, ' ').replace(/ /g, '').toLowerCase();

        if (!term) return null;

        if (normalizedText.includes(term)) {
          return data;
        }

        return null;
      }
    });
  });

};

/**
 * ExportShortener event
 *
 * @memberof EasyURL_PublicInterface
 *
 * @since   1.1.0
 * @version 1.1.0
 *
 * @returns {void}
 */
window.easyurl.publicinterface.event = function() {
};
