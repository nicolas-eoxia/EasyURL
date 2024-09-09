<?php
/* Copyright (C) 2023 EVARISK <technique@evarisk.com>
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
 */

/**
 * \file    view/easyurltools.php
 * \ingroup easyurl
 * \brief   Tools page of EasyURL top menu
 */

// Load EasyURL environment
if (file_exists('../easyurl.main.inc.php')) {
    require_once __DIR__ . '/../easyurl.main.inc.php';
} elseif (file_exists('../../easyurl.main.inc.php')) {
    require_once __DIR__ . '/../../easyurl.main.inc.php';
} else {
    die('Include of easyurl main fails');
}

// Load EasyURL libraries
require_once __DIR__ . '/../class/shortener.class.php';
require_once __DIR__ . '/../class/exportshortenerdocument.class.php';
require_once __DIR__ . '/../lib/easyurl_function.lib.php';

// Global variables definitions
global $conf, $db, $langs, $user;

// Load translation files required by the page
saturne_load_langs();

// Get parameters
$action = (GETPOSTISSET('action') ? GETPOST('action', 'aZ09') : 'view');

// Initialize view objects
$form = new Form($db);

// Security check - Protection if external user
$permissionToRead = $user->rights->easyurl->adminpage->read;
$permissionToAdd  = $user->rights->easyurl->shortener->write;
saturne_check_access($permissionToRead);

/*
 * Actions
 */

if ($action == 'generate_url' && $permissionToAdd) {
    $urlParameters = '';
    $error = 0;
    $json = json_decode(file_get_contents('php://input', true));
    if ($json != null) {
        $urlMethode    = $json->url_methode;
        $NbUrl         = $json->nb_url;
        $originalUrl   = $json->original_url;
        $urlParameters = $json->url_parameters;
        $currentNb     = $json->current_nb;
        $exportId      = $json->export_id;

        if ($currentNb == 0) {
            $export = new ExportShortenerDocument($db);
            $export->create($user);
            $exportId = $export->id;
        }
        if (dol_strlen($originalUrl) > 0 || dol_strlen(getDolGlobalString('EASYURL_DEFAULT_ORIGINAL_URL')) > 0) {
            $shortener = new Shortener($db);
            $shortener->ref = $shortener->getNextNumRef();
            $shortener->fk_export_shortener = $exportId;
            if (dol_strlen($originalUrl) > 0) {
                $shortener->original_url = $originalUrl . $urlParameters;
            } else {
                $shortener->original_url = getDolGlobalString('EASYURL_DEFAULT_ORIGINAL_URL') . $urlParameters;
            }
            $shortener->methode = $urlMethode;

            $shortener->create($user);

            // UrlType : none because we want mass generation url (all can be use but need to change this code)
            $result = set_easy_url_link($shortener, 'none', $urlMethode);
            if (!empty($result) && is_object($result)) {
                $urlParameters .= '?success=false&message=' . $result->message;
                $error++;
            }

            if ($currentNb == $NbUrl - 1 && !$error) {
                $export = new ExportShortenerDocument($db);
                $export = $export->fetchAll('', '', 0, 0, ['t.rowid' => $exportId]);

                if ($export != -1 && is_array($export) && count($export) > 0) {
                    $export = current($export);
                    $export->generateFile();
                }
                $urlParameters .= '?success=true&nb_url=' . $NbUrl;
            }
        }
        if (!strlen($urlParameters))
            $urlParameters .= '?export_id=' . $exportId . '&success=true&current_nb=' . $currentNb;
    }
    header('Location: ' . $_SERVER['PHP_SELF'] . $urlParameters);
    exit;
}

/*
 * View
 */

$title   = $langs->trans('Tools');
$helpUrl = 'FR:Module_EasyURL';

saturne_header(0,'', $title, $helpUrl);

print load_fiche_titre($title, '', 'wrench');

if (!getDolGlobalString('EASYURL_DEFAULT_ORIGINAL_URL')) : ?>
    <div class="wpeo-notice notice-warning">
        <div class="notice-content">
            <div class="notice-title">
                <a href="<?php echo dol_buildpath('/custom/easyurl/admin/setup.php', 1); ?>"><strong><?php echo $langs->trans('DefaultOriginalUrlConfiguration'); ?></strong></a>
            </div>
        </div>
    </div>
<?php endif;

print '<div class="global-infos">';
if (GETPOST('success') != '') {
    if (GETPOST('success') == true) {
        print '<div class="wpeo-notice notice-success">';
    } else {
        print '<div class="wpeo-notice notice-error">';
    }
    print '<div class="notice-content">';
    print '<div class="notice-title">';
    if (GETPOST('success') == true) {
        print $langs->trans('Success');
    } else {
        print $langs->trans('Error');
    }
    print '</div>';
    if (GETPOST('message')) {
        print GETPOST('message');
    } elseif (GETPOST('current_nb') != '') {
        print $langs->trans('ExportGenerating', GETPOST('current_nb') + 1);
    } elseif (GETPOST('nb_url') != '') {
        print $langs->trans('ExportSuccess', GETPOST('nb_url'));
    }
    print '</div></div>';
}
print '</div>';

print load_fiche_titre($langs->trans('GenerateUrlManagement'), '', '');

print '<form name="generate-url-from" id="generate-url-from" action="' . $_SERVER['PHP_SELF'] . '" method="POST">';
print '<input type="hidden" name="token" value="' . newToken() . '">';
print '<input type="hidden" name="action" value="generate_url">';
if (GETPOST('export_id')) {
    print '<input type="hidden" name="export_id" value="' . GETPOST('export_id') . '">';
}

print '<table class="noborder centpercent">';
print '<tr class="liste_titre">';
print '<td>' . $langs->trans('Parameters') . '</td>';
print '<td>' . $langs->trans('Description') . '</td>';
print '<td>' . $langs->trans('Value') . '</td>';
print '</tr>';

$urlMethode = ['yourls' => 'YOURLS', 'wordpress' => 'WordPress'];
print '<tr class="oddeven"><td>';
print $langs->trans('UrlMethode');
print '</td><td>';
print $langs->trans('UrlMethodeDescription');
print '<td>';
print $form::selectarray('url_methode', $urlMethode, 'yourls');
print '</td></tr>';

print '<tr class="oddeven"><td><label for="nb_url">' . $langs->trans('NbUrl') . '</label></td>';
print '<td>' . $langs->trans('NbUrlDescription') . '</td>';
print '<td><input class="minwidth100" type="number" name="nb_url" min="0"></td>';
print '</tr>';

print '<tr class="oddeven"><td><label for="original_url">' . $langs->trans('OriginalUrl') . '</label></td>';
print '<td>' .  $langs->trans('OriginalUrlDescription') . (getDolGlobalString('EASYURL_DEFAULT_ORIGINAL_URL') ? $langs->trans('OriginalUrlMoreDescription', getDolGlobalString('EASYURL_DEFAULT_ORIGINAL_URL')) : '') . '</td>';
print '<td><input class="minwidth300" type="text" name="original_url"></td>';
print '</tr>';

print '<tr class="oddeven"><td><label for="url_parameters">' . $langs->trans('UrlParameters') . '</label></td>';
print '<td>' . $langs->trans('UrlParametersDescription') . '</td>';
print '<td><input class="minwidth300" type="text" name="url_parameters"></td>';
print '</tr>';

print '</table>';
print '<div class="right">';
print $form->buttonsSaveCancel('Generate', '', [], true);
print '</div>';
print '</form>';

print load_fiche_titre($langs->trans('GeneratedExport'), '', '');
print '<table class="noborder centpercent tab-export">';

print '<tr class="liste_titre">';
print '<td>' . $langs->trans('ExportId') . '</td>';
print '<td>' . $langs->trans('ExportNumber') . '</td>';
print '<td>' . $langs->trans('ExportStart') . '</td>';
print '<td>' . $langs->trans('ExportEnd') . '</td>';
print '<td>' . $langs->trans('ExportDate') . '</td>';
print '<td>' . $langs->trans('ExportOrigin') . '</td>';
print '<td>' . $langs->trans('ExportConsume') . '</td>';
print '<td></td>';
print '</tr>';

$exportDoc = new ExportShortenerDocument($db);
$exportDocs = $exportDoc->fetchAll('DESC', 'rowid', 0, 0, ['customsql' => 't.type="' . $exportDoc->element . '"']);

if ($exportDocs != -1) {
    foreach ($exportDocs as $row) {
        $shortener = new Shortener($db);
        $shorteners = $shortener->fetchAll('', '', 0, 0, ['t.fk_export_shortener' => $row->id]);

        if ($shorteners == -1) {
            echo '<pre>';
            print_r($row->id);
            echo '</pre>';
            exit;
        }

        print '<tr class="oddeven">';
        print '<td>' . $row->ref . '</td>';
        print '<td>' . count($shorteners) . '</td>';
            print '<td>' . current($shorteners)->id ?? '-' . '</td>';
            print '<td>' . end($shorteners)->id ?? '-' . '</td>';
        print '<td>' . dol_print_date($row->date_creation, 'dayhour') . '</td>';
            print '<td><a href="' . current($shorteners)->original_url . '"><span class="fas fa-external-link-alt paddingrightonly" style=""></span><span>' . current($shorteners)->original_url ?? '-' . '<span></a></td>';
        print '<td>' . count(array_filter($shorteners, function ($elem) {return $elem->status == 0;})) . '</td>';

        $uploadDir = $conf->easyurl->multidir_output[$conf->entity ?? 1];
        $fileDir = $uploadDir . '/' . $row->element;
        if (dol_is_file($fileDir . '/' . $row->last_main_doc)) {
            $documentUrl = DOL_URL_ROOT . '/document.php';
            $fileUrl = $documentUrl . '?modulepart=easyurl&file=' . urlencode($row->element . '/' . $row->last_main_doc);
            print '<td><div><a class="marginleftonly" href="' . $fileUrl . '" download>' . img_picto($langs->trans('File') . ' : ' . $row->last_main_doc, 'fa-file-csv') . '</a></div></td>';
        } else {
            print '<td><div class="wpeo-loader"><span class="loader-spin"></span></div></td>';
        }
        print '</tr>';
    }
}
print '</table>';

// End of page
llxFooter();
$db->close();
