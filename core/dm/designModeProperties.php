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
<div class="opLayoutProperties">
    <h1 id="opLayoutPropertiesHeader">
        <span class="head-icon"><?php echo opTranslation::getTranslation('_properties', 'opLayout') ?></span>
        <a href="javascript: opLayoutInner.hide('south')" class="opCloseBtnProperties designModeLink" title="Close"><span><?php echo opTranslation::getTranslation('_close', 'opLayout') ?></span></a>
    </h1>
    <table class="opLayoutPropertiesTable" cellspacing="0" cellpadding="0" border="0" width="100%">
        <tr>
            <td width="60"><strong><?php echo opTranslation::getTranslation('_name', 'opLayout') ?>:</strong></td>
            <td id="opLayoutProperties_contentData"></td>
        </tr>
        <tr>
            <td><strong><?php echo opTranslation::getTranslation('_plugin', 'opLayout') ?>:</strong></td>
            <td><a href="" id="opLayoutProperties_contentPluginLink" class="designModeLink"><span id="opLayoutProperties_contentPlugin"></span></a></td>
        </tr>
        <tr>
            <td><strong><?php echo opTranslation::getTranslation('_layout', 'opLayout') ?>:</strong></td>
            <td><a href="" id="opLayoutProperties_layoutOwnerLink" class="designModeLink"><span id="opLayoutProperties_layoutOwner"></span></a></td>
        </tr>
        <tr>
            <td><strong><?php echo opTranslation::getTranslation('_weight', 'opLayout') ?>:</strong></td>
            <td><div id="opContentWeightSlider"></div><span id="opContentWeightSliderIndicator">0</span>/50</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td><a href="#" id="opContentWeightSliderSaveBtn" class="designModeLinkSave"><?php echo opTranslation::getTranslation('_save', 'opLayout') ?></a> <a href="" id="opLayoutProperties_contentRemove" class="designModeLinkRemove"><?php echo opTranslation::getTranslation('_remove', 'opLayout') ?></a></td>
        </tr>
    </table>
</div>