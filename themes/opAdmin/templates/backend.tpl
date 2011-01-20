<div id="wrapper">
    <div id="adminbar">
        <!--<div class="inner">-->
        <ul id="topbar-left">
            <li><a href="http://www.openpublish.org" target="_blank" title="Openpublish.org"><img src="{themePath}images/adminbar_logo.png" width="117" height="40" border="0" alt="Openpublish" /></a></li>
            <li>{version}</li>
        </ul>
        <ul id="topbar-right">
            <li class="top-sitename"><a href="{siteurl}" target="_blank" title="{sitename}"><span><img src="{themePath}images/icons/globe--arrow.png" class="btnIcon" alt="{sitename}" /> {sitename}</span></a></li>
            <li id="languageDropdown">{language}</li>
            <li class="top-admin">{siteadmin}</li>
            <li class="top-logout">{logout}</li>
        </ul>
        <!--</div>-->
    </div><!--END adminbar-->
    <div id="header">
        {menu}
    </div><!--END header-->
    {message}
    <div id="container">{adminContent}</div><!--END container-->
    <div id="statusbar">&nbsp;</div>
</div><!--END wrapper-->
