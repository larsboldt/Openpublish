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

<div class="opDesignModeIconHead">
    <img src="/core/dm/images/icon_pin.png" alt="" width="8" height="11" id="opPinButtonWest" />
</div>
<ul class="opDesignModeIconHolder">
    <li><a title="<?php echo opTranslation::getTranslation('_exit_designmode', 'opLayout') ?>" href="/admin/opLayout" class="designModeLink"><span><img src="/core/dm/icons/layout_back.png" class="opAdminToolbarIcon" width="16" height="16" border="0" alt="" /></a></span></li>
</ul>
<ul class="opDesignModeIconHolder">
    <li><a title="<?php echo opTranslation::getTranslation('_toggle_outlines', 'opLayout') ?>" href="/admindm/toggleOutlines" class="designModeLink"><span><img src="/core/dm/icons/selection.png" class="opAdminToolbarIcon" width="16" height="16" border="0" alt="" /></a></span></li>
    <li><a title="<?php echo opTranslation::getTranslation('_toggle_tags', 'opLayout') ?>" href="/admindm/toggleTags" class="designModeLink"><span><img src="/core/dm/icons/tag.png" class="opAdminToolbarIcon" width="16" height="16" border="0" alt="" /></a></span></li>
    <li><a title="<?php echo opTranslation::getTranslation('_customize_outline_colors', 'opLayout') ?>" href="#" id="colorPickerBtn" class="designModeLink"><span><img src="/core/dm/icons/color.png" class="opAdminToolbarIcon" width="16" height="16" border="0" alt="" /></a></span></li>
</ul>
<ul class="opDesignModeIconHolder">
    <li><a title="<?php echo opTranslation::getTranslation('_toggle_toolbars', 'opLayout') ?>" href="#" id="opDesignModeLayoutToggleBtn" class="designModeLink"><span><img src="/core/dm/icons/ui-toolbar.png" class="opAdminToolbarIcon" width="16" height="16" border="0" alt="" /></a></span></li>
</ul>
<input type="hidden" id="opDesignModeColor" name="opDesignModeColor" value="<?php echo $oColor ?>" />