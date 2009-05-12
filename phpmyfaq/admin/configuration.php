<?php
/**
 * The main configuration frontend
 *
 * @package    phpMyFAQ
 * @subpackage Administration
 * @author     Thorsten Rinne <thorsten@phpmyfaq.de>
 * @author     Matteo Scaramuccia <matteo@scaramuccia.com>
 * @since      2005-12-26
 * @copyright  2005-2009 phpMyFAQ Team
 * @version    SVN: $Id$
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

if (!defined('IS_VALID_PHPMYFAQ_ADMIN')) {
    header('Location: http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME']));
    exit();
}

if (!$permission['editconfig']) {
    exit();
}

// actions defined by url: user_action=
$userAction = PMF_Filter::filterInput(INPUT_GET, 'config_action', FILTER_SANITIZE_STRING, 'listConfig');

// Save the configuration
if ('saveConfig' == $userAction) {
	
	$checks     = array('filter' => FILTER_SANITIZE_STRING,
	                    'flags'  => FILTER_REQUIRE_ARRAY);
	$editData   = PMF_Filter::filterInputArray(INPUT_POST, array('edit' => $checks));
    $message    = '';
    $userAction = 'listConfig';

    // Set the new values into $PMF_CONF
    $forbidden_values = array('{', '}', '$');
    foreach ($editData['edit'] as $key => $value) {
        $PMF_CONF[$key] = str_replace($forbidden_values, '', $value);
    }
    // Hacks
    if (is_array($editData['edit'])) {
        foreach ($PMF_CONF as $key => $value) {
            // Fix checkbox values: they are not returned as HTTP POST values...
            if (!array_key_exists($key, $editData['edit'])) {
                $PMF_CONF[$key] = 'false';
            }
        }
    }

    $faqconfig->update($PMF_CONF);
}
// Lists the current configuration
if ('listConfig' == $userAction) {
    $message    = '';
    $userAction = 'listConfig';
?>

<h2><?php print $PMF_LANG['ad_config_edit']; ?></h2>

<div id="user_message"><?php print $message; ?></div>

<form id="config_list" name="config_list" action="?action=config&amp;config_action=saveConfig" method="post">
    <fieldset>
        <legend><a href="#" onclick="javascript:toggleConfig('Main');"><?php print $PMF_LANG['mainControlCenter']; ?></a></legend>
        <div id="configMain" style="display: none;"></div>
    </fieldset>
    <fieldset>
        <legend><a href="#" onclick="javascript:toggleConfig('Records');"><?php print $PMF_LANG['recordsControlCenter']; ?></a></legend>
        <div id="configRecords" style="display: none;"></div>
    </fieldset>
    <fieldset>
        <legend><a href="#" onclick="javascript:toggleConfig('Spam');"><?php print $PMF_LANG['spamControlCenter']; ?></a></legend>
        <div id="configSpam" style="display: none;"></div>
    </fieldset>
    <p align="center">
        <input class="submit" type="submit" value="<?php print $PMF_LANG['ad_config_save']; ?>" />
        <input class="submit" type="reset" value="<?php print $PMF_LANG['ad_config_reset']; ?>" />
    </p>
</form>

<script type="text/javascript">
/* <![CDATA[ */

function getConfigList()
{
    $.get("index.php", {action: "ajax", ajax: "config_list", conf: "main" }, function(data) { $('#configMain').append(data); });
    $.get("index.php", {action: "ajax", ajax: "config_list", conf: "records" }, function(data) { $('#configRecords').append(data); });
    $.get("index.php", {action: "ajax", ajax: "config_list", conf: "spam" }, function(data) { $('#configSpam').append(data); });
}

getConfigList();

/* ]]> */
</script>

<?php
}
