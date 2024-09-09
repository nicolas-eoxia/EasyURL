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
 * \file    js/exportshortener.js
 * \ingroup easyurl
 * \brief   JavaScript exportshortener file for module EasyURL
 */

/**
 * Init exportshortener JS
 *
 * @memberof EasyURL_ExportShortener
 *
 * @since   1.1.0
 * @version 1.1.0
 *
 * @type {Object}
 */
window.easyurl.exportshortener = {};

/**
 * ExportShortener init
 *
 * @memberof EasyURL_ExportShortener
 *
 * @since   1.1.0
 * @version 1.1.0
 *
 * @returns {void}
 */
window.easyurl.exportshortener.init = function() {
  window.easyurl.exportshortener.event();
};

/**
 * ExportShortener event
 *
 * @memberof EasyURL_ExportShortener
 *
 * @since   1.1.0
 * @version 1.1.0
 *
 * @returns {void}
 */
window.easyurl.exportshortener.event = function() {
  $(document).on('click', '#generate-url-from .button-save', window.easyurl.exportshortener.buttonSave);
};

/**
 * ExportShortener button save
 *
 * @memberof EasyURL_ExportShortener
 *
 * @since   1.1.0
 * @version 1.1.0
 *
 * @returns {void}
 */
window.easyurl.exportshortener.buttonSave = function(e) {
  e.preventDefault();
  let form = new FormData($('#generate-url-from')[0]);
  form.set('current_nb', '0');
  let url = new URL(document.URL);
  url.searchParams.append('action', form.get('action'));
  url.searchParams.append('token', form.get('token'));

  window.easyurl.exportshortener.createLink(form, url, 0);
}

/**
 * ExportShortener create link
 *
 * @memberof EasyURL_ExportShortener
 *
 * @since   1.1.0
 * @version 1.1.0
 *
 * @returns {void}
 */
window.easyurl.exportshortener.createLink = function(form, url, current) {
  form.set('current_nb', current.toString());
  $.ajax({
    method: 'POST',
    url: url.toString(),
    data: JSON.stringify(Object.fromEntries(form)),
    processData: false,
    success: function (resp) {
      if (current == 0) {
        $('.tab-export tr:nth-child(1)').after($(resp).find('.tab-export tr:nth-child(2)'));
        let newFormData = new FormData($(resp).find('#generate-url-from')[0]);
        form.set('export_id', newFormData.get('export_id'));
      } else {
        $('.tab-export tr:nth-child(2)').replaceWith($(resp).find('.tab-export tr:nth-child(2)'));
      }
      $('.global-infos').replaceWith($(resp).find('.global-infos'));
      if (current < form.get('nb_url') - 1) {
        window.easyurl.exportshortener.createLink(form, url, current + 1);
      }
    }
  });
}


