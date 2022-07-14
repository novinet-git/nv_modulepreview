
<?php
/**
 * Theme
 *
 * @var rex_addon $this
 */
/*
echo rex_view::title($this->i18n('nv_modulepreview_key'));

if (rex::getUser()->isAdmin()) {
    echo rex_view::info("An dieser Stelle können nur Collections bearbeitet werden. Zur Bearbeitung der Modulvorschau geht es hier lang: <a href=\"index.php?page=modules/nv_modulepreview\">Module</a>");
}
$subpage = rex_be_controller::getCurrentPagePart(2);
rex_be_controller::includeCurrentPageSubPath([$subpage]);
*/

header("Location:index.php?page=modules/nv_modulepreview");
exit;
