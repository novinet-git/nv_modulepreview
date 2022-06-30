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

            $aUrlParams = [
                "colid" => $aParams["colid"],
                "uid" => $aParams["uid"]
            ];

            $aSessionParams = [
                'modules' => $aModules,
                'epparams' => $aParams,
            ];
            rex_set_session("nv_modulepreview_" . $aParams["colid"], $aSessionParams);

            $sHtml = '<div class="dropdown btn-block">';
            $sHtml .= '<a class="btn btn-default btn-block btn-choosegridmodul show-module-preview" data-url="' . rex_url::backendPage("content/edit", $aUrlParams + rex_api_module_preview_get_modules_gridblock::getUrlParams()) . '">';
            $sHtml .= '<strong>Inhaltsblock hinzufügen</strong> ';
            $sHtml .= '<i class="fa fa-plus-circle"></i>';
            $sHtml .= '</a>';
            $sHtml .= '</div>';
            $sHtml .= '<script>';
            $sHtml .= "$('body').trigger('rex:ready', [$('.btn-choosegridmodul')]);";
            #$sHtml .= "$(document).trigger('ready');";
            #$sHtml .= "$(document).trigger('pjax:success');";
            $sHtml .= '</script>';
            return $sHtml;
        }

        foreach ($aItems as $aItem) {
            $sHtml .= self::getPreviewMarkup($aItem);
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
            $sHtml = '<li class="column large" id="' . $aData["key"] . '"><div class="nv-category"><strong>' . $aData["label"] . '</strong>';
            if (isset($aData["description"])) {
                if ($aData["description"] != "") {
                    $sHtml .= '<br><span class="text-muted"><small>' . $aData["description"] . '</small></span>';
                }
            }
            $sHtml .= '</div></li>';
            return $sHtml;
        }

        if ($aData["type"] == "module") {
            $iModuleId = $aData["module_id"];
            $aModule = $aData["module"];
            $sModName = nvMaskChar($aModule['name']);

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


            $sDataCategory = "";
            if (isset($aData["category"])) {
                $sDataCategory = $aData["category"];
            }

            $sDataGridblock = "";
            if (isset($aData["module"]["gridblock"])) {
                $sDataGridblock = $aData["module"]["gridblock"];
            }

            $sShowAsList = "";
            if (rex_config::get('nv_modulepreview', 'show_as_list')) {
                $sShowAsList = "large nv-show-as-list";
            }


            $sHtml = '<li class="card column ' . $sShowAsList . '"><a style="position:relative" class="module" data-gridblock="' . $sDataGridblock . '" data-category="' . $sDataCategory . '" data-modid="' . $iModuleId . '" data-modname="' . $sModName . '"';
            if (isset($aData["module"]["href"])) {
                if ($aData["module"]["href"] != "") {
                    $sHtml .= 'href="' . $aData["module"]["href"] . '" data-href="' . $aData["module"]["href"] . '" ';
                }
            }
            $sHtml .= '>';
            $sHtml .= '<div class="header">' . $sModName . '</div>';

            $sHideImages = "nv-hide-images";
            if (!rex_config::get('nv_modulepreview', 'hide_images')) {
                $sHtml .= '<div class="image"><div>';
                $sHtml .= $thumbnail;
                $sHtml .= '</div></div>';
                $sHideImages = "";
            }

            if ($sql->getValue('nv_modulepreview_description')) {
                $sHtml .= '<div class="' . $sHideImages . '">';
                $sHtml .= '<div class="description ">' . $sql->getValue('nv_modulepreview_description') . '</div>';
                $sHtml .= '</div>';
            }

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

    public static function hasClipboardContents(): bool
    {
        $cookie = self::getClipboardContents();

        if ($cookie) {
            return true;
        }

        return false;
    }

    public static function getClipboardContents()
    {
        return @json_decode(rex_request::cookie('rex_bloecks_cutncopy', 'string', ''), true);
    }

    public static function getSliceDetails($sliceId, $clangId)
    {
        if ($sliceId && $clangId) {
            $sql = rex_sql::factory();
            $sql->setQuery('select ' . rex::getTablePrefix() . 'article_slice.article_id, ' . rex::getTablePrefix() . 'article_slice.module_id, ' . rex::getTablePrefix() . 'module.name from ' . rex::getTablePrefix() . 'article_slice left join ' . rex::getTablePrefix() . 'module on ' . rex::getTablePrefix() . 'article_slice.module_id=' . rex::getTablePrefix() . 'module.id where ' . rex::getTablePrefix() . 'article_slice.id=? and ' . rex::getTablePrefix() . 'article_slice.clang_id=?', [$sliceId, $clangId]);
            return $sql->getArray()[0];
        }
    }

    public static function generateCss()
    {
        $oAddon = self::getAddon();
        $compiler = new rex_scss_compiler();
        $compiler->setRootDir($oAddon->getPath('scss/'));
        $compiler->setScssFile([$oAddon->getPath("scss/nv_modulepreview.scss")]);
        $compiler->setCssFile($oAddon->getAssetsPath('css/nv_modulepreview.css'));
        $compiler->compile();
    }

    public static function clearModules($ep) {
        $aParams = $ep->getParams();
        $iModulesId = $aParams["id"];

        $oDb = rex_sql::factory();
        $oDb->setQuery("SELECT * FROM " . rex::getTable("nv_modulepreview_categories") . " ORDER BY prio ASC");
        foreach ($oDb as $oItem) {
            $sModules = $oItem->getValue("modules");
            $aModules = explode("|",substr($sModules,1,-1));
            $aNewModules = array();
            foreach($aModules AS $iX => $iModuleId) {
                $aNewModules[] = $iModuleId;
            }
            $sModules = "|".implode("|",$aNewModules)."|";
            $oDb2 = rex_sql::factory();
            $oDb2->setQuery("UPDATE " . rex::getTable("nv_modulepreview_categories") . " SET modules = :modules WHERE id = :id",["modules" => $sModules, "id" => $oItem->getValue("id")]);
        }
    }
}
