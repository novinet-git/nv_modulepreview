<?php class nvModulepreview
{

    private static function getAddon()
    {
        return rex_addon::get('nv_modulepreview');
    }

    public static function runExtenstionPointGridblock($ep)
    {

        $aParams = $ep->getParams();
        $aModules = $aParams["allowedmodules"];

        foreach ($aModules as $iKey => $aModule) {
            $aModules[$iKey]["gridblock"] = "active";
        }

        $aParams["gridblock"] = true;


        $sHtml = self::getPreview($aModules, $aParams);
        return $sHtml;
    }

    public static function getPreview($aModules, $aParams = array())
    {
        $aItems = self::parseModules($aModules);

        if (!count($aItems)) {
            return;
        }


        $sHtml = "";

        if (isset($aParams["gridblock"])) {

            $params = [
                'modules' => $aModules,
                'epparams' => $aParams,
            ];
            $sHtml = '<div class="dropdown btn-block">';
            $sHtml .= '<a class="btn btn-default btn-block btn-choosegridmodul show-module-preview" data-url="' . rex_url::backendPage("content/edit", $params + rex_api_module_preview_get_modules_gridblock::getUrlParams()) . '">';
            $sHtml .= '<strong>Inhaltsblock hinzufügen</strong> ';
            $sHtml .= '<i class="fa fa-plus-circle"></i>';
            $sHtml .= '</a>';
            $sHtml .= '</div>';
            $sHtml .= '<script>';
            $sHtml .= "$('body').trigger('rex:ready', [$('body')]);
        $(document).trigger('ready');
        $(document).trigger('pjax:success');";
            $sHtml .= '</script>';
            return $sHtml;
        }

        if (isset($aParams["gridblockAlt"])) {
            $sHtml .= '<div class="dropdown btn-block">';
            $sHtml .= '<a class="btn btn-default btn-block btn-choosegridmodul dropdown-toggle" data-toggle="dropdown" title="' . rex_i18n::msg('nv_modulepreview_mod_choose_modul') . '"><i class="fa fa-plus"></i>' . rex_i18n::msg('nv_modulepreview_mod_choose_modul') . ' <span class="caret"></span></a>';

            $sHtml .= '<ul class="dropdown-menu btn-block gridblock-moduleselector" role="menu" data-colid="' . $aParams["colid"] . '" data-uid="' . $aParams["uid"] . '">';

            if ($aParams["copiedmodule"]) {
                $copUID = @$aParams["copiedmodule"]['uid'];
                $copCOLID = @intval($aParams["copiedmodule"]['colid']);
                $copSLID = @intval($aParams["copiedmodule"]['sliceid']);
                $copMODID = @intval($aParams["copiedmodule"]['modid']);
                if (rex_article_content_gridblock::checkCopyAvailable($copUID, $copCOLID, $copSLID) && $copMODID > 0 && rex::getUser()->getComplexPerm('modules')->hasPerm($copMODID)) {
                    $module = @$_SESSION['gridAllowedModules'][$copMODID];

                    $modName = aFM_maskChar($module['name']);
                    $sHtml .= '<li class="gridblock-cutncopy-insert"><a data-copyid="' . $copUID . '" data-modid="' . $copMODID . '" data-modname="' . $modName . '">' . str_replace(array("###modname###", "###modid###"), array($modName, $copMODID), rex_i18n::rawmsg('nv_modulepreview_mod_copy_insertmodul')) . '</a></li>';
                }
            }
        }

        foreach ($aItems as $aItem) {
            $sHtml .= self::getPreviewMarkup($aItem);
        }
        if (isset($aParams["gridblockAlt"])) {
            $sHtml .= '</ul>';
            $sHtml .= '</div>';
        }
        return $sHtml;
    }

    private static function parseModules($aModules)
    {
        $aCategories = array();
        $oDb = rex_sql::factory();
        $oDb->setQuery("SELECT * FROM " . rex::getTable("nv_modulepreview_categories") . " WHERE status = '1' ORDER BY prio ASC");
        foreach ($oDb as $oItem) {
            $aCategories[] = array("id" => $oItem->getValue("id"), "label" => $oItem->getValue("title"), "description" => $oItem->getValue("description"), "prio" => $oItem->getValue("prio"), "modules" => $oItem->getValue("modules"));
        }


        $aArr = array();
        $aUsedModules = array();
        $bUsedCategories = false;
        foreach ($aCategories as $aCategory) {
            $bUsedCategory = false;
            $aCategoryModules = explode("|", $aCategory["modules"]);
            foreach ($aCategoryModules as $iCategoryModule) {
                if (isset($iCategoryModule)) {
                    if (isset($aModules[$iCategoryModule]) && rex::getUser()->getComplexPerm('modules')->hasPerm($iCategoryModule)) {
                        $bUsedCategories = true;
                        if (!$bUsedCategory) {
                            $aData = array(
                                "type" => "category",
                                "label" => $aCategory["label"],
                                "key" => "category-" . $aCategory["id"],
                                "description" => $aCategory["description"],
                            );
                            $aArr[] = $aData;
                            $bUsedCategory = true;
                        }

                        $aData = array(
                            "type" => "module",
                            "module_id" => $iCategoryModule,
                            "category" => "category-" . $aCategory["id"],
                            "module" => $aModules[$iCategoryModule],
                        );
                        $aArr[] = $aData;
                        $aUsedModules[] = $iCategoryModule;
                    }
                }
            }
        }

        $bUsedCategory = false;

        foreach ($aModules as $iModuleId => $aModule) {
            if (!in_array($iModuleId, $aUsedModules) && rex::getUser()->getComplexPerm('modules')->hasPerm($iModuleId)) {

                if (!$bUsedCategory && $bUsedCategories) {
                    $aData = array(
                        "type" => "category",
                        "key" => "without_category",
                        "label" => "Ohne Kategorie",
                    );
                    $aArr[] = $aData;
                    $bUsedCategory = true;
                }


                $aData = array(
                    "type" => "module",
                    "module_id" => $iModuleId,
                    "category" => "without_category",
                    "module" => $aModule,
                );
                $aArr[] = $aData;
                $aUsedModules[] = $iModuleId;
            }
        }

        return $aArr;
    }

    private static function getPreviewMarkup($aData)
    {
        if ($aData["type"] == "category") {
            $sHtml = '<li class="col-md-12 nv-category" style="background-color:#efefef;padding:10px" id="' . $aData["key"] . '"><strong>' . $aData["label"] . '</strong>';
            if ($aData["description"] != "") {
                $sHtml .= '<br><span class="text-muted"><small>' . $aData["description"] . '</small></span>';
            }
            $sHtml .= '</li>';
            return $sHtml;
        }

        if ($aData["type"] == "module") {
            $iModuleId = $aData["module_id"];
            $aModule = $aData["module"];
            $sModName = aFM_maskChar($aModule['name']);

            $sql = rex_sql::factory();
            $sql->setTable(rex::getTable('module'));
            $sql->setWhere(['id' => $iModuleId]);
            $sql->select();

            $fileUrl = rex_url::addonAssets('nv_modulepreview', 'images/na.png');
            if ($sql->getValue('nv_modulepreview_thumbnail') !== '') {
                $fileUrl = '/media/nv_modulepreview/' . $sql->getValue('nv_modulepreview_thumbnail');
            }
            $thumbnail = '<img src=\'' . $fileUrl . '\' alt=\'Thumbnail ' . $sql->getValue('nv_modulepreview_thumbnail') . '\'>';

            $description = '';
            if ($sql->getValue('nv_modulepreview_description') !== '') {
                $description = '<br /><br /><span class=\'text-muted\'><small>' . $sql->getValue('nv_modulepreview_description') . '</small></span>';
            }

            $oAddon = self::getAddon();
            $iItemsPerRow = 12 / ($oAddon->getConfig("items_per_row") ?: "2");
            $sHtml = '<li class="column"><a style="position:relative" class="module" data-gridblock="' . $aData["module"]["gridblock"] . '" data-category="' . $aData["category"] . '" data-modid="' . $iModuleId . '" data-modname="' . $sModName . '"';
            if ($aData["module"]["href"] != "") {
                #$sHtml .= 'onclick="window.location.href = \'' . $aData["module"]["href"] . '\'"';
                $sHtml .= 'href="' . $aData["module"]["href"] . '" data-href="' . $aData["module"]["href"] . '" ';
            }
            $sHtml .= '>';
            $sHtml .= '<div class="header">' . $sModName . '</div>';

            $sHtml .= '<div class="image"><div>';
            $sHtml .= $thumbnail;
            $sHtml .= '</div></div>';

            if ($sql->getValue('nv_modulepreview_description')) {
                $sHtml .= '<div class="description" style="position:absolute;width:100%;padding:5px;background:black;color:white;bottom:0">' . $sql->getValue('nv_modulepreview_description') . '</div>';
            }

            #$sHtml .= '<div class="row" style="padding:10px"><div class="col-md-6">' . $thumbnail . '</div><div class="col-md-6"><strong>' . $sModName . '</strong>' . $description . '</div></div></a></li>' . PHP_EOL;
            $sHtml .= '</a></li>' . PHP_EOL;
            return $sHtml;
        }
    }

    public static function parseModuleSelect($ep)
    {
        $sSubject = $ep->getSubject();
        #dump($sSubject);
        preg_match_all('/<li>(.*)<\/li>/ismu', $sSubject, $aMatches);
        dump($aMatches);
        $ep->setSubject($sSubject);
    }

    public static function hasClipboardContents(): bool {
        $cookie = self::getClipboardContents();

        if($cookie) {
            return true;
        }

        return false;
    }

    public static function getClipboardContents() {
        return @json_decode(rex_request::cookie('rex_bloecks_cutncopy', 'string', ''), true);
    }

    public static function getSliceDetails($sliceId, $clangId) {
        if($sliceId && $clangId) {
            $sql = rex_sql::factory();
            $sql->setQuery('select ' . rex::getTablePrefix() . 'article_slice.article_id, ' . rex::getTablePrefix() . 'article_slice.module_id, ' . rex::getTablePrefix() . 'module.name from ' . rex::getTablePrefix() . 'article_slice left join ' . rex::getTablePrefix() . 'module on ' . rex::getTablePrefix() . 'article_slice.module_id=' . rex::getTablePrefix() . 'module.id where ' . rex::getTablePrefix() . 'article_slice.id=? and ' . rex::getTablePrefix() . 'article_slice.clang_id=?', [$sliceId, $clangId]);
            return $sql->getArray()[0];
        }
    }
}