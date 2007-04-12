<?php
/**
 * $Id: search.php,v 1.22 2007-04-12 07:19:07 thorstenr Exp $
 *
 * The fulltext search page
 *
 * @author       Thorsten Rinne <thorsten@phpmyfaq.de>
 * @author       Periklis Tsirakidis <tsirakidis@phpdevel.de>
 * @since        2002-09-16
 * @copyright    (c) 2002-2007 phpMyFAQ Team
 *
 * The contents of this file are subject to the Mozilla Public License
 * Version 1.1 (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations
 * under the License.
 */

if (!defined('IS_VALID_PHPMYFAQ')) {
    header('Location: http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME']));
    exit();
}

Tracking("fulltext_search",0);

// Search only on current language (default)
$allLanguages = false;
if (isset($_GET['langs'])) {
    $allLanguages = ('all' == $_GET['langs']);
}

// HACK: (re)evaluate the Category object w/o passing the user language
//       so the result set of a Search will have the Category Path
//       for any of the multilanguage faq records and the Category list
//       on the left pane will not be affected
if ($allLanguages) {
    $category = new PMF_Category();
    $category->transform(0);
}

$searchCategory = isset($_GET['searchcategory']) && is_numeric($_GET['searchcategory']) ? (int)$_GET['searchcategory'] : '%';
$searchterm = '';
$printResult = $PMF_LANG['help_search'];

//
// Handle the Tagging ID
//
if (isset($_GET['tagging_id']) && is_numeric($_GET['tagging_id'])) {
    $tag_id = (int)$_GET['tagging_id'];
    $tagging = new PMF_Tags($db, $LANGCODE);
    $searchterm = $tagging->getTagNameById($tag_id);
    $printResult = searchEngine($searchterm, $searchCategory, $allLanguages);
}

//
// Handle the full text search stuff
//
if (isset($_GET['suchbegriff']) || isset($_GET['search'])) {
    if (isset($_GET['suchbegriff'])) {
        $searchterm = $db->escape_string(strip_tags($_GET['suchbegriff']));
    }
    if (isset($_GET['search'])) {
        $searchterm = $db->escape_string(strip_tags($_GET['search']));
    }
    $printResult = searchEngine($searchterm, $searchCategory, $allLanguages);
    $searchterm = stripslashes($searchterm);
}

// Change a little bit the $searchCategory value;
$searchCategory = ('%' == $searchCategory) ? 0 : $searchCategory;

Tracking('fulltext_search', $searchterm);

$category->buildTree();

$openSearchLink = sprintf('<a class="searchplugin" href="#" onclick="window.external.AddSearchProvider(\'%s/opensearch.php\');">%s</a>',
    PMF_Link::getSystemUri('/index.php'),
    $PMF_LANG['opensearch_plugin_install']);

$tpl->processTemplate('writeContent', array(
    'msgSearch'             => $PMF_LANG['msgSearch'],
    'searchString'          => PMF_htmlentities($searchterm, ENT_QUOTES, $PMF_LANG['metaCharset']),
    'searchOnAllLanguages'  => $PMF_LANG['msgSearchOnAllLanguages'],
    'checkedAllLanguages'   => $allLanguages ? ' checked="checked"' : '',
    'selectCategories'      => $PMF_LANG['msgSelectCategories'],
    'allCategories'         => $PMF_LANG['msgAllCategories'],
    'printCategoryOptions'  => $category->printCategoryOptions($searchCategory),
    'writeSendAdress'       => $_SERVER['PHP_SELF'].'?'.$sids.'action=search',
    'msgSearchWord'         => $PMF_LANG['msgSearchWord'],
    'printResult'           => $printResult,
    'openSearchLink'        => $openSearchLink));

$tpl->includeTemplate('writeContent', 'index');
