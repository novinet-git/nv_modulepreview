<?php
if (!rex::isBackend() or !rex::getUser()) {
    return;
}

nvModulepreviewCollections::addSliceFromCollection();

rex_extension::register('STRUCTURE_CONTENT_SLICE_MENU', ['nvModulepreviewCollections', 'addButtons']);

rex_extension::register('NV_MODULEPREVIEW_MODULESELECT', ['nvModulepreviewCollections', 'addCollectionsToMoudleSelect']);
