<?php
if (!rex::isBackend() or !rex::getUser()) {
    return;
}

if ($this->getConfig('generatefiles')) {
    nvModulepreviewCollections::generateCss();
    $this->removeConfig('generatefiles');
}

if (file_exists($this->getAssetsPath("css/nv_modulepreview_collections.css"))) {
    rex_view::addCssFile($this->getAssetsUrl("css/nv_modulepreview_collections.css"));
}

nvModulepreviewCollections::addSliceFromCollection();

rex_extension::register('STRUCTURE_CONTENT_SLICE_MENU', ['nvModulepreviewCollections', 'addButtons']);

rex_extension::register('NV_MODULEPREVIEW_MODULESELECT', ['nvModulepreviewCollections', 'addCollectionsToMoudleSelect']);
