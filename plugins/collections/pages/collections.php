<div id="nvmsg"></div>
<?php
$func = rex_request('func', 'string');
$list = rex_request('list', 'string');
$id = rex_request('id', 'integer');

if ($id) {

    $oDb = rex_sql::factory();
    $oDb->setQuery("SELECT * FROM " . rex::getTable("nv_modulepreview_collections") . " WHERE id = :id Limit 1", ["id" => $id]);
    if (!$oDb->getRows()) {
        echo rex_view::error("Collection nicht gefunden");
        $func = '';
    }

    if ($oDb->getRows()) {
        if (!rex::getUser()->getComplexPerm('modules')->hasPerm($oDb->getValue("module_id"))) {
            echo rex_view::error("Keine Berechtigung zur Bearbeitung dieser Collection");
            $func = '';
        }
    }
}

if ($func == "sort") {
    ob_end_clean();
    if (isset($_POST["recordsArray"])) {
        foreach ($_POST["recordsArray"] as $iX => $iId) {
            $iPrio = $iX + 1;
            $oDb = rex_sql::factory();
            $oDb->setQuery("UPDATE " . rex::getTable("nv_modulepreview_collections") . " SET prio = :prio WHERE id = :id Limit 1", ["prio" => $iPrio, "id" => $iId]);
        }
    }
    echo rex_view::success("Reihenfolge gespeichert");
    exit;
}

if ($func == 'setstatus') {
    $status = (rex_request('oldstatus', 'int') + 1) % 2;
    rex_sql::factory()
        ->setTable(rex::getTable("nv_modulepreview_collections"))
        ->setWhere(['id' => $id])
        ->setValue('status', $status)
        ->addGlobalUpdateFields()
        ->update();
    echo rex_view::success("Status gespeichert");
    $func = '';
}


if ($func == 'delete') {
    dump(rex_sql::factory()
        ->setTable(rex::getTable("nv_modulepreview_collections"))
        ->setWhere(['id' => $id])
        ->delete());
    echo rex_view::success("Collection gelöscht");
    $func = '';
}



if ($func == 'edit' || $func == 'add') {
    $fieldset = $func == 'edit' ? $this->i18n('nv_modulepreview_collections_edit') : $this->i18n('nv_modulepreview_collections_add');
    $id = rex_request('id', 'int');
    $form = rex_form::factory(rex::getTable("nv_modulepreview_collections"), '', 'id=' . $id);

    $aSaveFromSlice = array();

    if ($func == 'add') {
        $slice_id = rex_request('slice_id', 'int');
        $module_id = rex_request('module_id', 'int');
        if ($slice_id) {

            $oSlice = rex_sql::factory();
            $oSlice->setQuery("SELECT * FROM " . rex::getTable("article_slice") . " WHERE id = :id Limit 1", ["id" => $slice_id]);
            if (!$oSlice->getRows()) {
                return;
            }

            $aProperties = array(
                "revision" => $oSlice->getValue("revision"),
            );

            for ($iX = 1; $iX <= 20; $iX++) {
                $aProperties["value_" . $iX] = $oSlice->getValue("value" . $iX);
            }

            for ($iX = 1; $iX <= 10; $iX++) {
                $aProperties["media_" . $iX] = $oSlice->getValue("media" . $iX);
            }
            for ($iX = 1; $iX <= 10; $iX++) {
                $aProperties["media_" . $iX] = $oSlice->getValue("medialist" . $iX);
            }
            for ($iX = 1; $iX <= 10; $iX++) {
                $aProperties["links_" . $iX] = $oSlice->getValue("link" . $iX);
            }
            for ($iX = 1; $iX <= 10; $iX++) {
                $aProperties["linklists_" . $iX] = $oSlice->getValue("linklist" . $iX);
            }

            $aSaveFromSlice = array(
                "module_id" => $module_id,
                "properties" => json_encode($aProperties),
            );
        }
    }


    $field = $form->addTextField('title');
    $field->setLabel($this->i18n('nv_modulepreview_collections_title'));
    $field->getValidator()->add('notEmpty', $this->i18n('nv_modulepreview_collections_title_validate_empty'));

    #$field = $form->addSelectField('modules', $value=null, ['class' => 'form-control selectpicker']);
    $field = $form->addSelectField('module_id', $value = null, ['class' => 'form-control selectpicker']);
    if ($aSaveFromSlice["module_id"]) {
        $field->setValue($aSaveFromSlice["module_id"]);
    }
    $field->setLabel($this->i18n('nv_modulepreview_collections_modules'));
    $select = $field->getSelect();
    $select->setSize(3);
    $field->getValidator()->add('notEmpty', $this->i18n('nv_modulepreview_collections_modules_validate_empty'));



    $oDb = rex_sql::factory();
    $oDb->setQuery("SELECT * FROM " . rex::getTable("module") . " ORDER BY name ASC");
    foreach ($oDb as $oItem) {
        if (rex::getUser()->getComplexPerm('modules')->hasPerm($oItem->getValue("id"))) {
            $select->addOption($oItem->getValue("name"), $oItem->getValue("id"));
        }
    }

    $field = $form->addTextField('description');
    $field->setLabel($this->i18n('nv_modulepreview_collections_description'));

    $field = $form->addPrioField('prio');
    $field->setLabel($this->i18n('nv_modulepreview_collections_prio'));
    $field->setAttribute('class', 'selectpicker form-control');
    $field->setLabelField('title');

    $field = $form->addMediaField('thumbnail');
    $field->setLabel(rex_i18n::msg('nv_modulepreview_thumbnail'));
    $field->setNotice("16:9 Format, wird skaliert auf 600x338px");
    $field->setTypes('jpg,jpeg,png,gif');

    $field = $form->addTextAreaField('properties');
    if ($aSaveFromSlice["properties"]) {
        $field->setValue($aSaveFromSlice["properties"]);
    }
    $field->setLabel($this->i18n('nv_modulepreview_collections_properties'));

    if ($func == 'edit') {
        $form->addParam('id', $id);
    }

    $field = $form->addSelectField('status', $value = null, ['class' => 'selectpicker form-control']);
    $field->setLabel($this->i18n('nv_modulepreview_collections_status'));
    $select = $field->getSelect();
    $select->addOption($this->i18n('nv_modulepreview_collections_status_active'), "1");
    $select->addOption($this->i18n('nv_modulepreview_collections_status_inactive'), "0");

    $content = $form->get();
    $fragment = new rex_fragment();
    $fragment->setVar('class', 'edit', false);
    $fragment->setVar('title', "$fieldset");
    $fragment->setVar('body', $content, false);
    $content = '<div id="nv_bemails">' . $fragment->parse('core/page/section.php') . '</div>';
    echo $content;
    echo '<script>';
    echo '$(".nv-modules").selectize({
        plugins: ["drag_drop"],
        delimiter: "|",
        persist: false,

      });</script>';
    echo '<style>.btn.btn-apply,.btn.btn-delete { display:none}</style>';
}

if ($func == '') {
    if (isset($_SESSION["categories_show_msg"])) {
        if ($_SESSION["categories_show_msg"]) {
            echo $_SESSION["categories_show_msg"];
            unset($_SESSION["categories_show_msg"]);
        }
    }

    $query = "SELECT collection.id AS collection_id,collection.title,CONCAT(module.name, \" [ID: \", module.id,\"]\") AS fullname,collection.prio,collection.status FROM " . rex::getTable("nv_modulepreview_collections") . " AS collection JOIN " . rex::getTable("module") . " AS module ON collection.module_id = module.id ORDER BY collection.prio ASC";
    $list = rex_list::factory($query, 10000);
    $list->addTableAttribute('class', 'table-striped table-hover sortable-list');
    $list->setRowAttributes(["id" => "recordsArray_###collection_id###"]);
    $list->addTableColumnGroup(['1%', '30%']);
    $thIcon = '<a class="rex-link-expanded" href="' . $list->getUrl(['func' => 'add']) . '"><i class="rex-icon rex-icon-add-user"></i></a>';
    $tdIcon = '<i class="rex-icon fa fa-bars sort-icon"></i>';
    $list->addColumn($thIcon, $tdIcon, 0, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon sort-handle">###VALUE###</td>']);
    $list->removeColumn('collection_id');

    $list->setColumnLabel('title', "Collection");
    $list->setColumnParams('title', ['func' => 'edit', 'id' => '###collection_id###']);

    $list->setColumnLabel('fullname', "Modul");
    #$list->setColumnSortable('title');
    $list->removeColumn('prio');
    #$list->setColumnLabel('prio', "Priorität");
    #$list->setColumnSortable('prio');


    $list->setColumnLabel('updatedate', "Aktualisiert");
    #$list->setColumnSortable('updatedate');
    $list->setColumnFormat('updatedate', 'custom', function ($params) {
        $list = $params['list'];
        $sStr = date("d.m.Y H:i", strtotime($list->getValue(updatedate)));
        return $sStr;
    });

    $list->setColumnLabel('status', "Status");
    #$list->setColumnSortable('status');

    $list->setColumnParams('status', ['func' => 'setstatus', 'oldstatus' => '###status###', 'id' => '###collection_id###']);
    $list->setColumnLayout('status', ['<th class="rex-table-action">###VALUE###</th>', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnFormat('status', 'custom', function ($params) {
        /** @var rex_list $list */
        $list = $params['list'];
        if ($list->getValue('status') == 1) {
            $str = $list->getColumnLink('status', '<span class="rex-online"><i class="rex-icon rex-icon-online"></i> ' . $this->i18n('nv_modulepreview_collections_status_active') . '</span>');
        } else {
            $str = $list->getColumnLink('status', '<span class="rex-offline"><i class="rex-icon rex-icon-offline"></i> ' . $this->i18n('nv_modulepreview_collections_status_inactive') . '</span>');
        }
        return $str;
    });

    $list->addColumn("Funktion", "Bearbeiten");
    $list->setColumnLayout("Funktion", ['<th class="rex-table-action" colspan="3">###VALUE###</th>', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams("Funktion", ['func' => 'edit', 'id' => '###collection_id###']);

    $list->addColumn('delete', "Löschen", -1, ['', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams('delete', ['func' => 'delete', 'id' => '###collection_id###']);
    $list->addLinkAttribute('delete', 'onclick', "return confirm('Wirklich unwiderruflich löschen?');");


    $sContent .= $list->get();




    $oFragment = new rex_fragment();
    $oFragment->setVar('title', "Liste der angelegten Collections");
    $oFragment->setVar('content', $sContent, false);
    $sOutput = $oFragment->parse('core/page/section.php');
    echo $sOutput;
} ?>
<script type="text/javascript">
    $(document).on('rex:ready', function() {
        $(function() {
            $(".sortable-list tbody").sortable({
                handle: '.sort-handle',
                opacity: 0.6,
                cursor: 'move',
                update: function() {
                    var order = $(this).sortable("serialize") + '&func=sort';
                    $.post("<?= rex_url::backendPage(rex_be_controller::getCurrentPage()) ?>", order, function(data) {
                        $('#nvmsg').html(data);
                    });
                }
            });
        });
    });
</script>