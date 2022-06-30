<?php

class rex_api_module_preview_get_modules extends rex_api_function
{
    public function execute()
    {
        /*
        $hideSearch = \rex_config::get('module_preview', 'hide_search');
        $modulePreview = new module_preview();
        $output = '';
        if (!$hideSearch) {
            $output .= $modulePreview->getSearch();
        }
        $output .= $modulePreview->getModules();
*/


        $moduleList = '';

        $moduleList .= '<div class="nv-modal-header"><div class="nv-modal-header-label">'.rex_i18n::msg('nv_modulepreview_modules_choose').'</div>';

        $iCollections = false;
        if (rex_plugin::get('nv_modulepreview','collections')->isAvailable()) {
            $iCollections = count(nvModulepreviewCollections::getCollections());
        }

        if (rex_config::get('nv_modulepreview', 'show_search') && (!rex_config::get('nv_modulepreview', 'show_only_gridblock') OR $iCollections)) {
            $moduleList .= '<div class="form-group">';
            $moduleList .= '<label class="control-label" for="module-preview-search"><input class="form-control" name="module-preview-search" type="text" id="module-preview-search" value="" placeholder="'.rex_i18n::msg('nv_modulepreview_modules_start_searching').'" /></label>';
            $moduleList .= '</div>';
        }
        
        $moduleList .= '</div>';


        $moduleList .= '<div class="container">';
        $moduleList .= '<ul class="module-list">';

        $articleId = rex_request('article_id', 'int');
        $categoryId = rex_request('category_id', 'int');
        $clang = rex_request('clang', 'int');
        $ctype = rex_request('ctype', 'int');

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
        if (rex_config::get('nv_modulepreview', 'show_only_gridblock')) {
            $modules = $module->getArray('select * from ' . rex::getTablePrefix() . 'module where name = "01 - Gridblock" order by name');
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

        $moduleList .= nvModulepreview::getPreview($aModules);
        $moduleList .= '</ul>';
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