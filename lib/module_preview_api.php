<?php

class rex_api_module_preview_get_modules extends rex_api_function
{
    public function execute()
    {

        $articleId = rex_request('article_id', 'int');
        $categoryId = rex_request('category_id', 'int');
        $clang = rex_request('clang', 'int');
        $ctype = rex_request('ctype', 'int');

        $moduleList = '';

        $moduleList .= '<!-- nv-modal-header start --><div class="nv-modal-header"><div class="nv-modal-header-label">' . rex_i18n::msg('nv_modulepreview_modules_choose') . '</div><div class="close"><span aria-hidden="true">&times;</span></div>';

        $bEPShowSearch = false;
        $bEPShowSearch = rex_extension::registerPoint(new rex_extension_point('NV_MODULEPREVIEW_SHOWSEARCH', $bEPShowSearch, [
            'page' => rex_be_controller::getCurrentPage(),
            'article_id' => $articleId,
            'clang' => $clang,
            'ctype' => $ctype,
            'buster' => time()
        ]));

        if (rex_config::get('nv_modulepreview', 'show_search') && (!rex_config::get('nv_modulepreview', 'show_only_gridblock') or $bEPShowSearch)) {
            $moduleList .= '<div class="form-group">';
            $moduleList .= '<label class="control-label" for="module-preview-search"><input class="form-control" name="module-preview-search" type="text" id="module-preview-search" value="" placeholder="' . rex_i18n::msg('nv_modulepreview_modules_start_searching') . '" /></label>';
            $moduleList .= '</div>';
        }

        $moduleList .= '</div><!-- nv-modal-header-end -->';


        $moduleList .= '<div class="nv-scrollable-content-parent">';
        $moduleList .= '<div class="container nv-scrollable-content">';
        $moduleList .= '<!-- nv-scrollable-content start -->';
        $moduleList .= '<br />';
        $moduleList .= '<div class="tab-content">';
        $moduleList .= '<!-- tab-content start -->';

        $moduleList .= '<!-- tab-content-modules start -->';
        $moduleList .= '<div role="tabpanel" class="tab-pane fade active in" id="nv-modulepreview-tab-modules">';
        $moduleList .= '<!-- nv-modale-list-modules start --><ul class="module-list">';



        $context = new rex_context([
            'page' => rex_be_controller::getCurrentPage(),
            'article_id' => $articleId,
            'clang' => $clang,
            'ctype' => $ctype,
            'category_id' => $categoryId,
            'function' => 'add',
        ]);

        if (nvModulepreview::hasClipboardContents()) {
            $clipBoardContents = nvModulepreview::getClipboardContents();
            $sliceDetails = nvModulepreview::getSliceDetails($clipBoardContents['slice_id'], $clipBoardContents['clang']);
            $context->setParam('source_slice_id', $clipBoardContents['slice_id']);

            if ($sliceDetails['article_id']) {
                $moduleList .= '<li class="column large nv-copy">';
                $moduleList .= '<a href="' . $context->getUrl(['module_id' => $sliceDetails['module_id']]) . '" data-href="' . $context->getUrl(['module_id' => $sliceDetails['module_id']]) . '" class="module" data-name="' . $sliceDetails['module_id'] . '.jpg">';
                $moduleList .= '<div class="header">';
                if ($clipBoardContents['action'] === 'copy') {
                    $moduleList .= '<i class="fa fa-clipboard" aria-hidden="true"></i>';
                } elseif ($clipBoardContents['action'] === 'cut') {
                    $moduleList .= '<i class="fa fa-scissors" aria-hidden="true"></i>';
                }
                $moduleList .= '<span>' . rex_addon::get('bloecks')->i18n('insert_slice', $sliceDetails['name'], $clipBoardContents['slice_id'], rex_article::get($sliceDetails['article_id'])->getName()) . '</span>';
                $moduleList .= '</div>';
                $moduleList .= '</a>';
                $moduleList .= '</li>';
            }
        }

        $context->setParam('source_slice_id', '');






        $module = rex_sql::factory();
        $aModules = array();
        if (rex_config::get('nv_modulepreview', 'show_only_gridblock') && rex_addon::get('gridblock')->isAvailable()) {
            $modules = rex_gridblock::getGridblockModules();
        } else {
            $modules = $module->getArray('select * from ' . rex::getTablePrefix() . 'module order by name');
        }


        foreach ($modules as $aItem) {
            $iId = $aItem["id"];

            $aModules[$iId] = array(
                "id" => $aItem["id"],
                "name" => $aItem["name"],
                "href" => $context->getUrl(['module_id' => $aItem["id"]]),
                "context" => $context,
            );
        }
        $aModules = nvModulepreview::getAvailableModules($aModules, $articleId, $clang, $ctype);

        $moduleList .= nvModulepreview::getPreview($aModules);
        $moduleList .= '</ul><!-- nv-modale-list-modules end -->';
        $moduleList .= '</div>';
        $moduleList .= '<!-- tab-content-modules end -->';
        $moduleList .= '<!-- tab-content end -->';
        $moduleList .= '</div>';
        $moduleList .= '<br />';
        $moduleList .= '<!-- nv-scrollable-content end -->';
        $moduleList .= '</div>';
        $moduleList .= '</div>';

        $moduleList = rex_extension::registerPoint(new rex_extension_point('NV_MODULEPREVIEW_MODULESELECT', $moduleList, [
            'page' => rex_be_controller::getCurrentPage(),
            'article_id' => $articleId,
            'clang' => $clang,
            'ctype' => $ctype,
            'category_id' => $categoryId,
            'function' => 'add',
            'buster' => time()
        ]));

        header('Content-Type: text/html; charset=UTF-8');
        echo $moduleList;
        exit();
    }
}
