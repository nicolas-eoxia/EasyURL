<?php
/* Copyright (C) 2024-2024 EVARISK <technique@evarisk.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    class/exportshortenerdocument.class.php
 * \ingroup easyurl
 * \brief   This file is a CRUD class file for ExportShortener (Create/Read/Update/Delete)
 */

// Load Saturne libraries
require_once __DIR__ . '/../../saturne/class/saturnedocuments.class.php';

/**
 * Class for ExportShortener
 */
class ExportShortenerDocument extends SaturneDocuments
{
    /**
     * @var string Module name
     */
    public $module = 'easyurl';

    /**
     * @var string Element type of object
     */
    public $element = 'export_shortener_document';

    /**
     * Constructor.
     *
     * @param DoliDb $db Database handler
     */
    public function __construct(DoliDB $db)
    {
        parent::__construct($db, $this->module, $this->element);
    }

    /**
     * Generate file for Shortener Export
     *
     * @throws Exception
     */
    public function generateFile()
    {
        global $conf, $user;

        require_once __DIR__ . '/../class/shortener.class.php';

        $data       = json_decode($this->json, true);
        $shortener  = new Shortener($this->db);
        $shorteners = $shortener->fetchAll('', '', $data['number_shortener_url'], 0, ['customsql' => 't.rowid >=' . $data['first_shortener_id']]);

        if (is_array($shorteners) && !empty($shorteners)) {
            $upload_dir = $conf->easyurl->multidir_output[$conf->entity] . '/' . $this->element;
            if (!dol_is_dir($upload_dir)) {
                dol_mkdir($upload_dir);
            }
            $fileName = dol_print_date(dol_now(), 'dayhourxcard') . '_' . $this->element . '.csv';
            $fp       = fopen($upload_dir . '/' . $fileName, 'w');
            fputcsv($fp, [1 => 'ref' . ';' . 'label' . ';' . 'original_url' . ';' . 'short_url']);
            foreach ($shorteners as $key => $shortener) {
                fputcsv($fp, [$key => $shortener->ref . ';' . str_replace(' ', '-', $shortener->label) . ';' . $shortener->original_url . ';' . $shortener->short_url]);
            }
            fclose($fp);
            $this->last_main_doc = $fileName;
            $this->update($user);
        }
    }

}
