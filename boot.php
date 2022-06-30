<?php
if (!rex::isBackend() or !rex::getUser()) {
    return;
}
if ($this->getConfig('generatefiles')) {
    nvModulepreview::generateCss();
    $this->removeConfig('generatefiles');
}

if (file_exists($this->getAssetsPath("css/novinet.css"))) {
    rex_view::addCssFile($this->getAssetsUrl("css/novinet.css"));
}

if (file_exists($this->getAssetsPath("css/nv_modulepreview.css"))) {
    rex_view::addCssFile($this->getAssetsUrl("css/nv_modulepreview.css"));
}

if (file_exists($this->getAssetsPath("js/script.js"))) {
    rex_view::addJSFile($this->getAssetsUrl('js/script.js'));
}

if (!rex_plugin::get('ui_tools', 'selectize')->isAvailable()) {
    rex_view::addCssFile($this->getAssetsUrl('vendor/selectize/selectize/dist/css/selectize.css'));
    rex_view::addCssFile($this->getAssetsUrl('vendor/selectize/selectize/dist/css/selectize.bootstrap3.css'));
    rex_view::addJsFile($this->getAssetsUrl('vendor/selectize/selectize/dist/js/standalone/selectize.min.js'));
    rex_view::addJsFile($this->getAssetsUrl('vendor/selectize/rex_selectize.js'));
}

require_once($this->getPath("lib/functions.php"));


if ('index.php?page=content/edit' == rex_url::currentBackendPage()) {

    $bloecksDragIsInstalled = false;

    if (rex_addon::exists('bloecks')) {
        $addons = rex_addon::getInstalledAddons();

        if (isset($addons['bloecks'])) {
            $bloecksDragIsInstalled = $addons['bloecks']->getPlugin('dragndrop')->isAvailable();
        }
    }


    rex_extension::register('STRUCTURE_CONTENT_MODULE_SELECT', function (rex_extension_point $ep) use ($bloecksDragIsInstalled) {
        $clang = rex_request('clang', 'int');
        $clang = rex_clang::exists($clang) ? $clang : rex_clang::getStartId();
        $category_id = rex_request('category_id', 'int');
        $article_id = rex_request('article_id', 'int');

        $params = [
            'clang' => $clang,
            'category_id' => $category_id,
            'article_id' => $article_id,
            'slice_id' => $ep->getParam('slice_id'),
            'ctype' => $ep->getParam('ctype'),
            'buster' => time()
        ];

        $html = '<div class="btn-block ' . ($bloecksDragIsInstalled && $ep->getParam('slice_id') !== -1 ? 'bloecks' : '') . '">';
        $html .= '<button class="btn btn-default btn-block show-module-preview" type="button" ';
        if (rex_addon::get("gridblock")->isAvailable() && $this->getConfig('show_only_gridblock') && !nvModulepreview::hasClipboardContents()) {
            #$html .= 'data-gridblock="show" ';
        }
        $html .= 'data-slice="' . $ep->getParam('slice_id') . '" data-url="' . rex_url::currentBackendPage($params + rex_api_module_preview_get_modules::getUrlParams()) . '">';
        $html .= '<strong>Block hinzufügen</strong> ';
        $html .= '<i class="fa fa-plus-circle" aria-hidden="true"></i>';
        $html .= '</button>';
        $html .= '</div>';
        /*$html .= '<script>';
        $html .= "$('body').trigger('rex:ready', [$('body')]);
    $(document).trigger('ready');
    $(document).trigger('pjax:success');";
        $html .= '</script>';*/

        $ep->setSubject($html);
    });
}

if (rex_addon::get("gridblock")->isAvailable() && $this->getConfig('overwrite_gridblock')) {
    rex_extension::register('GRIDBLOCK_MODULESELECTOR_ADD', array('nvModulepreview', 'runExtenstionPointGridblock'), rex_extension::LATE);
}

rex_extension::register('OUTPUT_FILTER', static function (rex_extension_point $ep) {
    $output = '<div id="module-preview" data-pjax-container="#rex-js-page-main-content"><div class="close"><span aria-hidden="true">&times;</span></div>';
    $output .= '<div class="inner"></div>';
    $output .= '</div>';

    if ($output) {
        $ep->setSubject(
            str_ireplace(
                ['</body>'],
                [$output . '</body>'],
                $ep->getSubject()
            )
        );
    }
});

rex_extension::register('MODULE_DELETED', array('nvModulepreview', 'clearModules'), rex_extension::LATE);
