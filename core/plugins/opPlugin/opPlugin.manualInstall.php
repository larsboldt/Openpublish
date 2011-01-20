<?php
defined('_OP') or die('Access denied');
/**
 *  Copyright (C) 2009 Lars Boldt
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
 ?>
<h3><?php echo opTranslation::getTranslation('_manual_install', $opPluginName) ?> | <?php echo opTranslation::getTranslation('_plugins', $opPluginName) ?>
    <span class="heading-icon"><img src="<?php echo $opPluginPath ?>icons/puzzle.png" width="16" height="16" alt="" title="" class="table-icon" /></span>
</h3>
<div id="content-plugin">
    <form id="adminForm" method="post" action="/admin/opPlugin/pluginManualInstall">
        <h5><?php echo opTranslation::getTranslation('_plugin_installation', $opPluginName) ?></h5>
        <label><?php echo opTranslation::getTranslation('_class_name', $opPluginName) ?></label>
        <span class="input-shadow"><input class="form_txt" type="text" name="class_name" /></span>
        <div id="btn"><a class="form_btn" href="#" onclick="$('#adminForm').submit();" title="<?php echo opTranslation::getTranslation('_install', $opPluginName) ?>"><span><img src="<?php echo $opPluginPath ?>icons/tick.png" width="16" height="16" border="0" alt="<?php echo opTranslation::getTranslation('_install', $opPluginName) ?>" class="table-icon" /> <?php echo opTranslation::getTranslation('_install', $opPluginName) ?></span></a><a class="form_btn" href="/admin/opPlugin" title="<?php echo opTranslation::getTranslation('_back', $opPluginName) ?>"><span><img src="<?php echo $opPluginPath ?>icons/arrow-180-medium.png" width="16" height="16" border="0" alt="<?php echo opTranslation::getTranslation('_back', $opPluginName) ?>" class="table-icon" /> <?php echo opTranslation::getTranslation('_back', $opPluginName) ?></span></a></div>
    </form>
</div>