<?php

if (rex_request('func', 'string') !== "edit") {


    $list = rex_list::factory("SELECT id,name,nv_modulepreview_thumbnail,nv_modulepreview_description  FROM " . rex::getTable("module") . " ORDER BY name ASC");
    #dump($list);
    $list->addTableColumnGroup(['30%', '20%', '30%', '*']);




    // Optionen der Liste
    $list->addTableAttribute('class', 'table-hover');
    // Columns
    $list->removeColumn('nv_modulepreview_thumbnail');
    $list->removeColumn('id');
    $list->setColumnLabel('id', rex_i18n::msg('nv_modulepreview_module_id'));
    $list->setColumnLabel('name', rex_i18n::msg('nv_modulepreview_module_name'));
    // Preview-Column setzen
    $list->addColumn(rex_i18n::msg('nv_modulepreview_thumbnail'), '', 2, ['<th>###VALUE###</th>', '<td><img src="'.rex_url::addonAssets('nv_modulepreview', 'images/thumbnails/').'###nv_modulepreview_thumbnail###" width="150" alt="Thumbnail ###name###"></td>']);
    // Description
    $list->setColumnLabel('nv_modulepreview_description', rex_i18n::msg('nv_modulepreview_description'));
    // Funktionen der Liste
    $list->setColumnLabel('edit', '');
    $list->addColumn('edit', rex_i18n::msg('nv_modulepreview_module_edit'));
    $list->setColumnParams('name', ['func' => 'edit', 'id' => '###id###', 'start' => rex_request('start', 'int', 0)]);
    $list->setColumnParams('edit', ['func' => 'edit', 'id' => '###id###', 'start' => rex_request('start', 'int', 0)]);
    // Holzhammer: Leere Bilder suchen und durch Platzhalter ersetzen
    $list = $list->get();
    $list = str_replace('src="'.rex_url::addonAssets('nv_modulepreview', 'images/thumbnails/').'"', 'src="' . rex_url::addonAssets('nv_modulepreview', 'images/na.png') . '"', $list);
    // Ins Fragment packen
    $fragment = new rex_fragment();
    $fragment->setVar('title', "Liste der angelegten Module", false);
    $fragment->setVar('content', $list, false);
    echo $fragment->parse('core/page/section.php');
} // Eo if not edit

// If edit
if (rex_request('func', 'string') === "edit" && rex_request('id', 'int') !== "") {
    rex_extension::register('REX_FORM_SAVED', ['nvModulePreview', 'handleThumbnailUploads']);

    $id = rex_request('id', 'int');

    $form = rex_form::factory(rex::getTable('module'), '', 'id=' . $id);

    $sModuleName = "";
    $oDb = rex_sql::factory();
    $oDb->setQuery("SELECT * FROM " . rex::getTable("module") . " WHERE id = :id Limit 1", ["id" => $id]);
    if ($oDb->getRows()) {
        $sModuleName = $oDb->getValue("name");
    }

    $formLabel = $sModuleName . ' | ID: ' . rex_get('id') . ' | ' . rex_i18n::msg('nv_modulepreview_module_edit');

    $field = $form->addTextField('nv_modulepreview_description');
    $field->setLabel(rex_i18n::msg('nv_modulepreview_description'));

    $field = $form->addInputField('file', "nv_modulepreview_thumbnail", $value = null, ["accept" => "image/png, image/gif, image/jpeg"]);
    $field->setLabel(rex_i18n::msg('nv_modulepreview_thumbnail'));
    $field->setNotice("16:9 Format, wird skaliert auf 600x338px");

    if ($form->getSql()->getValue("nv_modulepreview_thumbnail")) {
        $field = $form->addRawField('<input type="hidden" name="thumbnail_current" value="' . $form->getSql()->getValue("nv_modulepreview_thumbnail") . '">');
        $field = $form->addRawField('<dl class="rex-form-group form group"><dt></dt><dd><img src="' . rex_url::addonAssets('nv_modulepreview', 'images/thumbnails/' . $form->getSql()->getValue("nv_modulepreview_thumbnail")) . '" style="display:block;max-width:200px;margin-bottom:20px"><input type="checkbox" name="thumbnail_delete" value="1"> ' . rex_i18n::msg("nv_modulepreview_thumbnail_delete") . '</dd></dl>');
    }

    $form->addParam('id', $id);

    $sForm = $form->get();

    $sForm = str_replace("<form ", "<form enctype=\"multipart/form-data\" ", $sForm);

    $content = $sForm;

    $fragment = new rex_fragment();
    $fragment->setVar('class', 'edit', false);
    $fragment->setVar('title', $formLabel, false);
    $fragment->setVar('body', $content, false);
    echo $fragment->parse('core/page/section.php');

    // Bisschen hacky den Löschen-Button ausblenden
    echo '<style>.btn.btn-apply, #rex-addon-editmode .btn-delete{display: none !important;}</style>';
}
