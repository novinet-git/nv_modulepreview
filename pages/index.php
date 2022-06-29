<?php
/**
 * Theme
 *
 * @var rex_addon $this
 */

echo rex_view::title($this->i18n('title'));

header("location:index.php?page=modules/nv_modulepreview");
exit;
