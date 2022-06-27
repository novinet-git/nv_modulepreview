<?php

class rex_api_module_preview_get_modules_gridblock extends rex_api_function
{
    public function execute()
    {

        $aParams = $_GET["epparams"];
        $aModules = $_GET["modules"];
        
        $moduleList = '';

        if (rex_config::get('nv_modulepreview', 'show_search')) {
            $moduleList = '<div class="container"><div class="form-group">';
            $moduleList .= '<label class="control-label" for="module-preview-search"><input class="form-control" name="module-preview-search" type="text" id="module-preview-search" value="" placeholder="suchbegriff eingeben" /></label>';
            $moduleList .= '</div></div>';
        }

        $moduleList .= '<div class="container">';
        $moduleList .= '<ul class="module-list gridblock-moduleselector" role="menu" style="background:white" data-colid="' . $aParams["colid"] . '" data-uid="' . $aParams["uid"] . '">';


        if ($aParams["copiedmodule"]) {
            $copUID = @$aParams["copiedmodule"]['uid'];
            $copCOLID = @intval($aParams["copiedmodule"]['colid']);
            $copSLID = @intval($aParams["copiedmodule"]['sliceid']);
            $copMODID = @intval($aParams["copiedmodule"]['modid']);
            if (rex_article_content_gridblock::checkCopyAvailable($copUID, $copCOLID, $copSLID) && $copMODID > 0 && rex::getUser()->getComplexPerm('modules')->hasPerm($copMODID)) {
                $module = @$_SESSION['gridAllowedModules'][$copMODID];

                $modName = aFM_maskChar($module['name']);
                $moduleList .= '<li class="column large"><a data-copyid="' . $copUID . '" data-modid="' . $copMODID . '" data-modname="' . $modName . '">';
                $moduleList .= '<div class="header">';
                $moduleList .= '<i class="fa fa-clipboard" aria-hidden="true" style="margin-right: 5px;"></i>';
                $moduleList .= '<span>' . str_replace(array("###modname###", "###modid###"), array($modName, $copMODID), rex_i18n::rawmsg('nv_modulepreview_mod_copy_insertmodul'));
                $moduleList .= '</div>';
                $moduleList .= '</a></li>';
            }
        }

        $moduleList .= nvModulepreview::getPreview($aModules);
        $moduleList .= '</ul>';
        $moduleList .= '</div>';

        header('Content-Type: text/html; charset=UTF-8');
        echo $moduleList;
        exit();
    }
}
