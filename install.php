<?php //Datenbank-Einträge vornehmen
rex_sql_table::get(rex::getTable('nv_modulepreview_categories'))
    ->ensureColumn(new rex_sql_column('id', 'int(11)', false, null, 'auto_increment'))
    ->ensureColumn(new rex_sql_column('prio', 'int(11)'))
    ->ensureColumn(new rex_sql_column('title', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('description', 'text'))
    ->ensureColumn(new rex_sql_column('modules', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('status', 'varchar(10)'))
    ->ensureGlobalColumns()
    ->setPrimaryKey('id')
    ->ensure();

// Tabelle Module um zwei Felder erweitern
rex_sql_table::get(rex::getTable('module'))
    ->ensureColumn(new rex_sql_column('nv_modulepreview_thumbnail', 'text', true))
    ->ensureColumn(new rex_sql_column('nv_modulepreview_description', 'text'))
    ->alter();


if (!$this->hasConfig()) {
    $this->setConfig([
        "show_search" => "1",
        "overwrite_gridblock" => "1",
    ]);
}

$this->setConfig('run_update',true);