<?php
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
<div id="container">

    <h3>Installation wizard | 1 of 3
        <span class="heading-icon"><img src="/themes/opAdmin/images/icons/box.png" width="16" height="16" alt="" title="" class="table-icon" /></span>
    </h3>

    <div id="content-plugin">

        <table cellpadding="0" cellspacing="0" border="0" width="100%" class="requirements">
            <thead>
                <tr>
                    <td width="400"><h1>System requirements</h1></td>
                    <td width="200"><strong>Required</strong></td>
                    <td width="200"><strong>Your setup</strong></td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>PHP</td>
                    <td class="check">5.2+</td>
                    <td class="<?php echo ($opServerCheck->passPHPVersion()) ? 'success' : 'failed'?>"><?php echo $opServerCheck->phpVersion(); ?></td>
                </tr>
                <tr>
                    <td>GD library</td>
                    <td class="check">2.0+</td>
                    <td class="<?php echo ($opServerCheck->passGDVersion()) ? 'success' : 'failed'?>"><?php echo $opServerCheck->gdVersion(); ?></td>
                </tr>
                <tr>
                    <td>Standard PHP Library</td>
                    <td class="check">Yes</td>
                    <td class="<?php echo ($opServerCheck->isSPL()) ? 'success' : 'failed'?>"><?php echo ($opServerCheck->isSPL()) ? 'Yes' : 'No'?></td>
                </tr>
                <tr>
                    <td>PDO</td>
                    <td class="check">Yes</td>
                    <td class="<?php echo ($opServerCheck->isPDO()) ? 'success' : 'failed'?>"><?php echo ($opServerCheck->isPDO()) ? 'Yes' : 'No'?></td>
                </tr>
                <tr>
                    <td>Curl</td>
                    <td class="check">Yes</td>
                    <td class="<?php echo ($opServerCheck->isCURL()) ? 'success' : 'failed'?>"><?php echo ($opServerCheck->isCURL()) ? 'Yes' : 'No'?></td>
                </tr>
                <tr>
                    <td>Hash</td>
                    <td class="check">Yes</td>
                    <td class="<?php echo ($opServerCheck->isHASH()) ? 'success' : 'failed'?>"><?php echo ($opServerCheck->isHASH()) ? 'Yes' : 'No'?></td>
                </tr>
                <tr>
                    <td>Json</td>
                    <td class="check">Yes</td>
                    <td class="<?php echo ($opServerCheck->isJSON()) ? 'success' : 'failed'?>"><?php echo ($opServerCheck->isJSON()) ? 'Yes' : 'No'?></td>
                </tr>
                <tr>
                    <td>MCRYPT</td>
                    <td class="check">Yes</td>
                    <td class="<?php echo ($opServerCheck->isMCRYPT()) ? 'success' : 'failed'?>"><?php echo ($opServerCheck->isMCRYPT()) ? 'Yes' : 'No'?></td>
                </tr>
                <tr>
                    <td>SimpleXML</td>
                    <td class="check">Yes</td>
                    <td class="<?php echo ($opServerCheck->isSimpleXML()) ? 'success' : 'failed'?>"><?php echo ($opServerCheck->isSimpleXML()) ? 'Yes' : 'No'?></td>
                </tr>
                <tr>
                    <td>Multibyte String</td>
                    <td class="check">Yes</td>
                    <td class="<?php echo ($opServerCheck->isMbString()) ? 'success' : 'failed'?>"><?php echo ($opServerCheck->isMbString()) ? 'Yes' : 'No'?></td>
                </tr>
                <tr>
                    <td>Files directory writable</td>
                    <td class="check">Yes</td>
                    <td class="<?php echo ($opServerCheck->isFilesWritable()) ? 'success' : 'failed'?>"><?php echo ($opServerCheck->isFilesWritable()) ? 'Yes' : 'No'?></td>
                </tr>
                <tr>
                    <td>Plugins directory writable</td>
                    <td class="check">Yes</td>
                    <td class="<?php echo ($opServerCheck->isPluginsWritable()) ? 'success' : 'failed'?>"><?php echo ($opServerCheck->isPluginsWritable()) ? 'Yes' : 'No'?></td>
                </tr>
                <tr>
                    <td>Translations directory writable</td>
                    <td class="check">Yes</td>
                    <td class="<?php echo ($opServerCheck->isTranslationsWritable()) ? 'success' : 'failed'?>"><?php echo ($opServerCheck->isTranslationsWritable()) ? 'Yes' : 'No'?></td>
                </tr>
            </tbody>
        </table>

        <div id="page-btn-next">
            <a class="form_btn" href="/install?step=phpinfo"><span><img src="icons/script-code.png" width="16" height="16" border="0" alt="" class="table-icon" /> PHPinfo</span></a>
            <?php
            if (! $pass) {
                echo '<a class="form_btn" href="/install"><span><img src="/themes/opAdmin/images/icons/arrow-circle-double-135.png" width="16" height="16" border="0" alt="" class="table-icon" /> Check</span></a>';
            } else {
                echo '<a class="form_btn" href="/install/index.php?step=2"><span>Next <img src="/themes/opAdmin/images/icons/arrow-000-medium.png" width="16" height="16" border="0" alt="" class="table-icon" /></span></a>';
            }
            ?>
        </div>

    </div><!--END content-plugin-->

</div><!--END container-->