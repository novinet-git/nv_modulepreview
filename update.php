<?php $compiler = new rex_scss_compiler();
$compiler->setScssFile([$this->getPath("scss/nv_modulepreview.scss")]);
$compiler->setCssFile($this->getAssetsPath('css/nv_modulepreview.css'));
$compiler->compile();