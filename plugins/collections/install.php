<?php
rex_sql_table::get(rex::getTable('nv_modulepreview_collections'))
->ensureColumn(new rex_sql_column('id', 'int(11)', false, null, 'auto_increment'))
->ensureColumn(new rex_sql_column('prio', 'int(11)'))
->ensureColumn(new rex_sql_column('title', 'varchar(255)'))
->ensureColumn(new rex_sql_column('description', 'text'))
->ensureColumn(new rex_sql_column('module_id', 'int(11)'))
->ensureColumn(new rex_sql_column('properties', 'text'))
->ensureColumn(new rex_sql_column('thumbnail', 'text'))
->ensureColumn(new rex_sql_column('status', 'varchar(10)'))
->ensureGlobalColumns()
->setPrimaryKey('id')
->ensure();

$this->setConfig('generatefiles',true);