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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title><?php echo opTranslation::getTranslation('_filebrowser', 'opFileManager'); ?></title>
        <link href="/core/simplebrowser/css/default.css" rel="stylesheet" type="text/css" />
        <script src="/themes/opAdmin/js/jquery-1.3.2.min.js" type="text/javascript"></script>
        <script src="/themes/opAdmin/js/jquery.cookies.2.1.0.js" type="text/javascript"></script>
        <script src="/themes/opAdmin/js/jCollapse.js" type="text/javascript"></script>
        <script src="/core/simplebrowser/js/opAccordion.js" type="text/javascript"></script>
        <script src="/core/simplebrowser/js/simplebrowser.js" type="text/javascript"></script>
        <?php
        if (!isset($_SESSION['adminsb_form']) || $_SESSION['adminsb_form'] != true) {
            echo '<script src="/themes/opAdmin/js/tiny_mce/tiny_mce_popup.js" type="text/javascript"></script>';
            echo '<script src="/core/simplebrowser/js/tinymce_init.js" type="text/javascript"></script>';
        }
        ?>
    </head>
    <body>
        <table cellpadding="0" cellspacing="0" border="0" width="100%" height="100%">
            <tr>
                <td class="table-data-shadow" valign="top">
                    <div id="folderList" class="jCollapse">
                        <div style="padding-left:10px;"><img src="/core/simplebrowser/icons/server.png" class="table-icon" /> <a href="/admin/simplebrowser/sort/0"<?php echo (!isset($folderID) || !$folderID) ? ' style="font-weight:bold;"' : '' ?>><?php echo opTranslation::getTranslation('_fileserver', 'opFileManager'); ?></a></div>
                            <?php echo $opFolders; ?>
                        <?php
                        if ($fileType != 'image' && $fileType != 'media') {
                        ?>
                        <div style="padding-left:10px;"><img src="/core/simplebrowser/icons/sitemap.png" class="table-icon" /> <a href="/admin/simplebrowser/sort/-1"<?php echo (isset($folderID) && $folderID == -1) ? ' style="font-weight:bold;"' : '' ?>><?php echo opTranslation::getTranslation('_sitemap', 'opFileManager'); ?></a></div>
                        <?php } ?>
                    </div>
                </td>
                <td valign="top">
                    <table cellpadding="0" cellspacing="0" border="0" class="scheme">
                        <?php
                        if (isset($folderID) && $folderID == -1) {
                        ?>
                        <thead>
                            <td><?php echo opTranslation::getTranslation('_sitemap', 'opFileManager'); ?></td>
                        </thead>
                        <tbody>
                            <td>
                            <?php
                            echo $opSitemap;
                            ?>
                            </td>
                        </tbody>
                        <?php
                        } else {
                        ?>
                        <thead>
                            <td width="75"><?php echo opTranslation::getTranslation('_preview', 'opFileManager'); ?></td>
                            <td><?php echo opTranslation::getTranslation('_files', 'opFileManager'); ?></td>
                            <td align="right"><?php echo opTranslation::getTranslation('_file_size', 'opFileManager'); ?></td>
                        </thead>
                        <tbody>
                            <?php
                            $i = 0;
                            foreach ($opFiles as $file) {
                                $f = opFileFactory::identify(DOCUMENT_ROOT.$file['filepath'].$file['filename']);
                                $css = ($i % 2) ? 'odd' : 'even';
                                $fileName = $f->getFilename();
                                $returnFunction = (isset($_SESSION['adminsb_form']) && isset($_SESSION['adminsb_element']) && $_SESSION['adminsb_form'] == true) ? 'addImage('.$file['id'].', \''.$_SESSION['adminsb_element'].'\');' : 'FileBrowserDialogue.mySubmit(\''.$f->getRelativePath().$f->getFilename().'\');';
                                switch ($fileType) {
                                    case 'image':
                                        if ($f instanceof opGraphicsFile) {
                                            echo '<tr class="'.$css.'">';
                                            echo '<td><a href="javascript:'.$returnFunction.'"><img src="'.$f->getThumbnail(75).'" title="'.$fileName.'" alt="'.$fileName.'" /></a></td>';
                                            echo '<td><a href="javascript:'.$returnFunction.'">'.$fileName.'</a></td>';
                                            echo '<td align="right">'.$f->getSizeAsString().'</td>';
                                            echo '</tr>';
                                        }
                                        break;
                                    case 'media':
                                    default:
                                        if ($f instanceof opGraphicsFile) {
                                            echo '<tr class="'.$css.'">';
                                            echo '<td><a href="javascript:'.$returnFunction.'"><img src="'.$f->getThumbnail(75).'" title="'.$fileName.'" alt="'.$fileName.'" /></a></td>';
                                            echo '<td><a href="javascript:'.$returnFunction.'">'.$fileName.'</a></td>';
                                            echo '<td align="right">'.$f->getSizeAsString().'</td>';
                                            echo '</tr>';
                                        } else {
                                            echo '<tr class="'.$css.'">';
                                            echo '<td><a href="#" onclick="'.$returnFunction.'"><img src="/core/simplebrowser/images/no_preview.jpg" title="'.$fileName.'" alt="'.$fileName.'" /></a></td>';
                                            echo '<td><a href="#" onclick="'.$returnFunction.'">'.$fileName.'</a></td>';
                                            echo '<td align="right">'.$f->getSizeAsString().'</td>';
                                            echo '</tr>';
                                        }
                                }
                                $i++;
                            }
                            ?>
                        </tbody>
                        <?php } ?>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>