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

        $moduleList = '<div class="container"><div class="form-group">';
        $moduleList .= '<label class="control-label" for="module-preview-search"><input class="form-control" name="module-preview-search" type="text" id="module-preview-search" value="" placeholder="suchbegriff eingeben" /></label>';
        $moduleList .= '</div></div>';


        $moduleList .= '<div class="container">';
        $moduleList .= '<ul class="module-list" style="background:white">';

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

        if(nvModulepreview::hasClipboardContents()) {
            $clipBoardContents = nvModulepreview::getClipboardContents();
            $sliceDetails = nvModulepreview::getSliceDetails($clipBoardContents['slice_id'], $clipBoardContents['clang']);
            $context->setParam('source_slice_id', $clipBoardContents['slice_id']);

            if($sliceDetails['article_id']) {
                $moduleList .= '<li class="column large">';
                    $moduleList .= '<a href="'.$context->getUrl(['module_id' => $sliceDetails['module_id']]).'" data-href="'.$context->getUrl(['module_id' => $sliceDetails['module_id']]).'" class="module" data-name="'.$sliceDetails['module_id'].'.jpg">';
                        $moduleList .= '<div class="header">';
                            if($clipBoardContents['action'] === 'copy') {
                                $moduleList .= '<i class="fa fa-clipboard" aria-hidden="true" style="margin-right: 5px;"></i>';
                            }
                            elseif($clipBoardContents['action'] === 'cut') {
                                $moduleList .= '<i class="fa fa-scissors" aria-hidden="true" style="margin-right: 5px;"></i>';
                            }
                            $moduleList .= '<span>'.rex_addon::get('bloecks')->i18n('insert_slice', $sliceDetails['name'], $clipBoardContents['slice_id'], rex_article::get($sliceDetails['article_id'])->getName()).'</span>';
                        $moduleList .= '</div>';
                    $moduleList .= '</a>';
                $moduleList .= '</li>';
            }
        }

        $context->setParam('source_slice_id', '');






        $module = rex_sql::factory();
        $aModules = array();
        $modules = $module->getArray('select * from ' . rex::getTablePrefix() . 'module order by name');

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

        header('Content-Type: text/html; charset=UTF-8');
        echo $moduleList;
        exit();
    }
}
