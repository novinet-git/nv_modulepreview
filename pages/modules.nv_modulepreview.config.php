<?php

$form = rex_config_form::factory($this->getProperty('package'));

$field = $form->addCheckboxField('show_search');
$field->setLabel($this->i18n('nv_modulepreview_config_show_search'));
$field->addOption("Aktiv", "1");

$field = $form->addCheckboxField('hide_images');
$field->setLabel($this->i18n('nv_modulepreview_config_hide_images'));
$field->addOption("Aktiv", "1");

$field = $form->addCheckboxField('show_as_list');
$field->setLabel($this->i18n('nv_modulepreview_config_show_as_list'));
$field->addOption("Aktiv", "1");

if (rex_addon::get('gridblock')->isAvailable()) {
    $field = $form->addCheckboxField('overwrite_gridblock');
    $field->setLabel($this->i18n('nv_modulepreview_config_overwrite_gridblock'));
    $field->addOption("Aktiv", "1");
    
    $field = $form->addCheckboxField('show_only_gridblock');
    $field->setLabel($this->i18n('nv_modulepreview_config_show_only_gridblock'));
    $field->addOption("Aktiv", "1");
}

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', rex_i18n::msg('nv_modulepreview_config'), false);
$fragment->setVar('body', $form->get(), false);
echo $fragment->parse('core/page/section.php');