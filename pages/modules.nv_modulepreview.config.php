<?php

$form = rex_config_form::factory($this->getProperty('package'));


/*
$field = $form->addSelectField('items_per_row', $value = null, ['class' => 'form-control selectpicker']);
$field->setLabel($this->i18n('nv_modulepreview_config_items_per_row'));
$select = $field->getSelect();
$select->addOption("1", "1");
$select->addOption("2", "2");
$select->addOption("3", "3");
$select->addOption("4", "4");
*/

$field = $form->addSelectField('show_search', $value = null, ['class' => 'form-control selectpicker']);
$field->setLabel($this->i18n('nv_modulepreview_config_show_search'));
$select = $field->getSelect();
$select->addOption("Inaktiv", "0");
$select->addOption("Aktiv", "1");

$field = $form->addSelectField('hide_images', $value = null, ['class' => 'form-control selectpicker']);
$field->setLabel($this->i18n('nv_modulepreview_config_hide_images'));
$select = $field->getSelect();
$select->addOption("Inaktiv", "0");
$select->addOption("Aktiv", "1");

$field = $form->addSelectField('show_as_list', $value = null, ['class' => 'form-control selectpicker']);
$field->setLabel($this->i18n('nv_modulepreview_config_show_as_list'));
$select = $field->getSelect();
$select->addOption("Inaktiv", "0");
$select->addOption("Aktiv", "1");

if (rex_addon::get('gridblock')->isAvailable()) {
    $field = $form->addSelectField('overwrite_gridblock', $value = null, ['class' => 'form-control selectpicker']);
    $field->setLabel($this->i18n('nv_modulepreview_config_overwrite_gridblock'));
    $select = $field->getSelect();
    $select->addOption("Inaktiv", "0");
    $select->addOption("Aktiv", "1");

    $field = $form->addSelectField('show_only_gridblock', $value = null, ['class' => 'form-control selectpicker']);
    $field->setLabel($this->i18n('nv_modulepreview_config_show_only_gridblock'));
    $select = $field->getSelect();
    $select->addOption("Inaktiv", "0");
    $select->addOption("Aktiv", "1");
}



$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', rex_i18n::msg('nv_modulepreview_config'), false);
$fragment->setVar('body', $form->get(), false);
echo $fragment->parse('core/page/section.php');
