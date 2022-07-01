<?php

class rex_api_module_preview_get_modules_gridblock extends rex_api_function
{
    public function execute()
    {

        $iColId = rex_request('colid', 'int');
        $sUid = rex_request('uid');
        $aSessionParams = rex_session("nv_modulepreview_".$iColId);
        //rex_unset_session("nv_modulepreview_".$iColId);
        $aParams = $aSessionParams["epparams"];
        $aModules = $aSessionParams["modules"];
        $moduleList = '';

        $moduleList .= '<div class="nv-fixed"><!-- nv-modal-header start --><div class="nv-modal-header"><div class="nv-modal-header-label">'.rex_i18n::msg('nv_modulepreview_modules_choose').'</div>';

        if (rex_config::get('nv_modulepreview', 'show_search')) {
            $moduleList .= '<div class="form-group">';
            $moduleList .= '<label class="control-label" for="module-preview-search"><input class="form-control" name="module-preview-search" type="text" id="module-preview-search" value="" placeholder="'.rex_i18n::msg('nv_modulepreview_modules_start_searching').'" /></label>';
            $moduleList .= '</div>';
        }
        
        $moduleList .= '</div><!-- nv-modal-header-end --></div>';

        $moduleList .= '<div class="container nv-scrollable-content"><br />';
        $moduleList .= '<ul class="module-list gridblock-moduleselector" role="menu" data-colid="' . $aParams["colid"] . '" data-uid="' . $aParams["uid"] . '">';


        if ($aParams["copiedmodule"]) {
            $copUID = @$aParams["copiedmodule"]['uid'];
            $copCOLID = @intval($aParams["copiedmodule"]['colid']);
            $copSLID = @intval($aParams["copiedmodule"]['sliceid']);
            $copMODID = @intval($aParams["copiedmodule"]['modid']);
            if (rex_article_content_gridblock::checkCopyAvailable($copUID, $copCOLID, $copSLID) && $copMODID > 0 && rex::getUser()->getComplexPerm('modules')->hasPerm($copMODID)) {
                $module = @$_SESSION['gridAllowedModules'][$copMODID];

                $modName = nvMaskChar($module['name']);
                $moduleList .= '<li class="column large  nv-copy"><a data-copyid="' . $copUID . '" data-modid="' . $copMODID . '" data-modname="' . $modName . '">';
                $moduleList .= '<div class="header">';
                $moduleList .= '<i class="fa fa-clipboard" aria-hidden="true"></i>';
                $moduleList .= '<span>' . str_replace(array("###modname###", "###modid###"), array($modName, $copMODID), rex_i18n::rawmsg('nv_modulepreview_mod_copy_insertmodul'));
                $moduleList .= '</div>';
                $moduleList .= '</a></li>';
            }
        }

        $moduleList .= nvModulepreview::getPreview($aModules);
        $moduleList .= '</ul>';
        $moduleList .= '<br /></div>';

        header('Content-Type: text/html; charset=UTF-8');
        echo $moduleList;
        exit();
    }
}
