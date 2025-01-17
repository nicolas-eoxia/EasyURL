<?php
/* Copyright (C) 2025 EVARISK <technique@evarisk.com>
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
 * \file    public/shortener/public_shortener.php
 * \ingroup easyurl
 * \brief   Public page to assign shortener
 */

if (!defined('NOTOKENRENEWAL')) {
    define('NOTOKENRENEWAL', 1);
}
if (!defined('NOREQUIREMENU')) {
    define('NOREQUIREMENU', 1);
}
if (!defined('NOREQUIREHTML')) {
    define('NOREQUIREHTML', 1);
}
if (!defined('NOLOGIN')) {      // This means this output page does not require to be logged.
    define('NOLOGIN', 1);
}
if (!defined('NOCSRFCHECK')) {  // We accept to go on this page from external website.
    define('NOCSRFCHECK', 1);
}
if (!defined('NOIPCHECK')) {    // Do not check IP defined into conf $dolibarr_main_restrict_ip.
    define('NOIPCHECK', 1);
}
if (!defined('NOBROWSERNOTIF')) {
    define('NOBROWSERNOTIF', 1);
}

// Load EasyURL environment
if (file_exists('../../easyurl.main.inc.php')) {
    require_once __DIR__ . '/../../easyurl.main.inc.php';
} elseif (file_exists('../../../easyurl.main.inc.php')) {
    require_once __DIR__ . '/../../../easyurl.main.inc.php';
} else {
    die('Include of easyurl main fails');
}

// Load Dolibarr libraries
require_once DOL_DOCUMENT_ROOT.'/product/stock/class/productlot.class.php';

// Load EasyURL libraries
require_once __DIR__ . '/../../../easyurl/class/shortener.class.php';

// Global variables definitions
global $conf, $db, $hookmanager, $langs;

// Load translation files required by the page
saturne_load_langs();

// Get parameters
$track_id = GETPOST('track_id', 'alpha');
$entity   = GETPOST('entity');

// Initialize technical objects
$shortener  = new Shortener($db);
$productLot = new ProductLot($db);

$hookmanager->initHooks(['publicshortener', 'saturnepublicinterface']); // Note that conf->hooks_modules contains array

if (!isModEnabled('multicompany')) {
    $entity = $conf->entity;
}

$conf->setEntityValues($db, $entity);

// Load object


/*
 * View
 */

$title = $langs->trans('PublicShortener');

$conf->dol_hide_topmenu  = 1;
$conf->dol_hide_leftmenu = 1;

saturne_header(1, '', $title,  '', '', 0, 0, [], [], '', 'page-public-card page-signature');

print '<form id="public-shortener-form" method="POST" action="' . $_SERVER['PHP_SELF'] . '?entity=' . $entity . '">';
print '<input type="hidden" name="token" value="' . newToken() . '">'; ?>

<div class="public-card__container" data-public-interface="true">
    <?php if (getDolGlobalInt('SATURNE_ENABLE_PUBLIC_INTERFACE')) : ?>
        <div class="public-card__header">
            <div class="header-information">
                <div class="information-title">
                    <h1><?php echo $langs->transnoentities('AssignQRCode'); ?></h1>
                </div>
            </div>
        </div>
        <div class="public-card__content">
            <?php
                $productLotArrays = [];
                $productLots      = saturne_fetch_all_object_type('ProductLot');
                if (is_array($productLots) && !empty($productLots)) {
                    foreach ($productLots as $productLot) {
                        $productLotArrays[$productLot->id] = $productLot->batch;
                    }
                }
                print Form::selectarray('fk_element', $productLotArrays);

                $shortenerArrays = [];
                $shorteners      = $shortener->fetchAll('', '', 0, 0, ['customsql' => 't.status = ' . Shortener::STATUS_VALIDATED]);
                if (is_array($shorteners) && !empty($shorteners)) {
                    foreach ($shorteners as $shortener) {
                        $shortenerArrays[$shortener->id] = $shortener->label;
                    }
                }
                print Form::selectarray('label', $shortenerArrays);
            ?>
        </div>
        <div class="public-card__footer">
            <button type="submit" class="wpeo-button"><?php echo $langs->trans('Assign'); ?></button>
        </div>
    <?php else :
        print '<div class="center">' . $langs->trans('PublicInterfaceForbidden', $langs->transnoentities('OfAssignShortener')) . '</div>';
    endif; ?>
</div>
<?php print '</form>';

llxFooter('', 'public');
$db->close();
