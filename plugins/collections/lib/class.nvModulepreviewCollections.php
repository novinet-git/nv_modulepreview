<?php class nvModulepreviewCollections
{
    private static function getPlugin()
    {
        return rex_plugin::get('nv_modulepreview', 'collections');
    }

    public static function addButtons(rex_extension_point $ep)
    {
        $sFunction = rex_request('function', 'string', null);
        $iSliceId = rex_request('slice_id', 'int', null);

        if (rex_be_controller::getCurrentPage() != "content/edit" or ($iSliceId && $iSliceId == $ep->getParam('slice_id') && ($sFunction == "add" or $sFunction == "edit"))) {
            return;
        }

        $aModules = array();
        $aModules[$ep->getParam("module_id")] = "modul";
        $aModules = nvModulepreview::getAvailableModules($aModules,$ep->getParam('article_id'),$ep->getParam('clang'),$ep->getParam('ctype'));

        if (!isset($aModules[$ep->getParam("module_id")])) {
            return;
        }

        if (!rex::getUser()->hasPerm('nv_modulepreview_collections[]')) {
            return;
        }

        static::addButton($ep, [
            'hidden_label' => 'In collection speichern',
            'url' => rex_url::backendController([
                'page' => 'nv_modulepreview/collections/',
                'func' => 'add',
                'article_id' => $ep->getParam('article_id'),
                'module_id' => $ep->getParam('module_id'),
                'slice_id' => $ep->getParam('slice_id'),
                'clang' => $ep->getParam('clang'),
                'ctype' => $ep->getParam('ctype')
            ]),
            'attributes' => [
                'class' => ['btn-default nv-collections'],
                'title' => 'Als Collection speichern',
                'data-pjax-no-history' => 'true',
            ],
            'icon' => 'package-addon',
        ]);
    }

    public static function addButton(rex_extension_point $ep, array $btn)
    {
        $items = (array) $ep->getSubject();
        $items[] = $btn;
        $ep->setSubject($items);
    }

    public static function addCollectionsToModuleSelect(rex_extension_point $ep)
    {
        #$slice_id = static::getCookie('slice_id', 'int', null);
        #$clang = static::getCookie('clang', 'int', null);
        #$revision = static::getCookie('revision', 'int', 0);
        #$action = static::getCookie('action', 'string', null);

        $sSubject = $ep->getSubject();

        $context = new rex_context([
            'page' => rex_be_controller::getCurrentPage(),
            'article_id' => $ep->getParam("article_id"),
            'clang' => $ep->getParam("clang"),
            'ctype' => $ep->getParam("ctype"),
            'category_id' => $ep->getParam("category"),
            'function' => 'add',
            'action' => 'addfromcollection'
        ]);

        $sHtml = '';
        $oDb = rex_sql::factory();
        $oDb->setQuery("SELECT * FROM " . rex::getTable("nv_modulepreview_collections") . " WHERE status = '1' ORDER BY prio ASC");
        if ($oDb->getRows()) {

            $sHtmlTabs = '<div class="container">';
            $sHtmlTabs .= '<ul class="nav nav-tabs tab-nav" role="tablist" id="nv-collections-tabs">';
            $sHtmlTabs .= '<li class="active">';
            $sHtmlTabs .= '<a href="#nv-collections-tab-modules" aria-controls="nv-collections-tab-modules" role="tab" data-toggle="tab" aria-expanded="true">Module</a>';
            $sHtmlTabs .= '</li>';
            $sHtmlTabs .= '<li>';
            $sHtmlTabs .= '<a href="#nv-collections-tab-collections" aria-controls="nv-collections-tab-collections" role="tab" data-toggle="tab">Collections</a>';
            $sHtmlTabs .= '</li>';
            $sHtmlTabs .= '</ul>';
            $sHtmlTabs .= '</div>';


            $sHtml .= '<!-- tab-content-collections start -->';
            $sHtml .= '<div role="tabpanel" class="tab-pane fade" id="nv-collections-tab-collections">';
            $sHtml .= '<!-- nv-modale-list-collections start --><ul class="module-list">';

            $sShowAsList = "";
            if (rex_config::get('nv_modulepreview', 'show_as_list')) {
                $sShowAsList = "large nv-show-as-list";
            }

            foreach ($oDb as $oItem) {

                $sql = rex_sql::factory();
                $sql->setTable(rex::getTable('module'));
                $sql->setWhere(['id' => $oItem->getValue("module_id")]);
                $sql->select();

                $context->setParam('collection_id', $oItem->getValue("id"));
                $sHtml .= '<li class="card column ' . $sShowAsList . '">';
                $sHtml .= '<a href="' . $context->getUrl(['module_id' => $oItem->getValue("module_id")]) . '" data-href="' . $context->getUrl(['module_id' => $oItem->getValue("module_id")]) . '" class="module" data-name="' . $oItem->getValue("module_id") . '.jpg" data-category="from_collection">';
                $sHtml .= '<div class="header">';

                $sHtml .= '<span>' . $oDb->getValue("title") . ' (Modul: ' . $sql->getValue("name") . ')</span>';
                $sHtml .= '</div>';

                $fileUrl = rex_url::addonAssets('nv_modulepreview', 'images/na.png');
                if ($oDb->getValue('thumbnail') !== '') {
                    $fileUrl = '/media/nv_modulepreview/' . $oDb->getValue('thumbnail');
                }
                $thumbnail = '<img src=\'' . $fileUrl . '\' alt=\'Thumbnail ' . $oDb->getValue('thumbnail') . '\'>';

                $sHideImages = "nv-hide-images";
                if (!rex_config::get('nv_modulepreview', 'hide_images')) {
                    $sHtml .= '<div class="image"><div>';
                    $sHtml .= $thumbnail;
                    $sHtml .= '</div></div>';
                    $sHideImages = "";
                }

                if ($oDb->getValue('description')) {
                    $sHtml .= '<div class="' . $sHideImages . '">';
                    $sHtml .= '<div class="description ">' . $oDb->getValue('description') . '</div>';
                    $sHtml .= '</div>';
                }

                $sHtml .= '</a>';
                $sHtml .= '</li>';
            }
        }

        $sHtml .= '</ul><!-- nv-modale-list-collections end -->';
        $sHtml .= '</div>';
        $sHtml .= '<!-- tab-content-collections end -->';



        $sSubject = str_replace("<!-- nv-modal-header-end -->", "<!-- nv-modal-header-end -->" . $sHtmlTabs, $sSubject);
        $sSubject = str_replace("<!-- tab-content-modules end -->", "<!-- tab-content-modules end -->" . $sHtml, $sSubject);



        return $sSubject;
    }

    public static function addSliceFromCollection()
    {

        $iCollectionId = rex_request('collection_id', 'int', null);
        $sAction = rex_request('action', 'string', null);

        if (!$iCollectionId or $sAction != "addfromcollection") {
            return;
        }


        $oCollection = rex_sql::factory();
        $oCollection->setQuery("SELECT * FROM " . rex::getTable("nv_modulepreview_collections") . " WHERE id = :id Limit 1", ["id" => $iCollectionId]);
        if (!$oCollection->getRows()) {
            return;
        }

        $aProperties = json_decode($oCollection->getValue("properties"), 1);
        $_NEW_REQUEST = [
            'save' => '1',
        ];

        $request = ['value' => 20, 'media' => 10, 'medialist' => 10, 'link' => 10, 'linklist' => 10];
        foreach ($request as $key => $max) {
            $_NEW_REQUEST['REX_INPUT_' . strtoupper($key)] = [];

            for ($i = 1; $i <= $max; ++$i) {
                $_NEW_REQUEST['REX_INPUT_' . strtoupper($key)][$i] = $aProperties[$key . "_" . $i];
            }
            unset($i);
        }
        unset($max, $key, $request);
        $_POST = array_replace($_POST, [
            'module_id' => $oCollection->getValue("module_id"),
        ]);
        $_REQUEST = array_replace($_REQUEST, $_NEW_REQUEST);
    }

    public static function getCollections()
    {
        $aArr = array();

        $oDb = rex_sql::factory();
        $oDb->setQuery("SELECT * FROM " . rex::getTable("nv_modulepreview_collections") . " WHERE status = '1' ORDER BY prio ASC");
        if ($oDb->getRows()) {
            $aArr[] = $oDb->getValue("id");
        }

        return $aArr;
    }

    public static function generateCss()
    {
        $oPlugin = self::getPlugin();
        $compiler = new rex_scss_compiler();
        $compiler->setRootDir($oPlugin->getPath('scss/'));
        $compiler->setScssFile([$oPlugin->getPath("scss/nv_modulepreview_collections.scss")]);
        $compiler->setCssFile($oPlugin->getAssetsPath('css/nv_modulepreview_collections.css'));
        $compiler->compile();
    }

    public static function clearModules($ep)
    {
        $aParams = $ep->getParams();
        $iModuleId = $aParams["id"];
        $oDb = rex_sql::factory();
        $oDb->setQuery("DELETE FROM " . rex::getTable("nv_modulepreview_collections") . " WHERE module_id = :module_id", ["module_id" => $iModuleId]);
    }
}
