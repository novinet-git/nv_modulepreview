<?php if(rex_addon::get("nv_modulepresets")->isAvailable()) {
    rex_response::sendRedirect(rex_url::backendPage("nv_modulepresets"));
    return;
} 

$sContent = "Mit dem Addon nvModulePresets können ausgefüllte Modulvorlagen gespeichert und in neuen Artikeln verwendet werden.";

if(rex_addon::get("nv_modulepresets")->isInstalled()) {
    $sContent .= '<br><br>Addon ist installiert aber inaktiv.<br><br><a href="'.rex_url::backendPage('packages').'" class="btn btn-save">Addon aktivieren</a>';
} else {
    $sContent .= '<br><br>Addon ist nicht installiert.<br><br><a href="'.rex_url::backendPage('install/packages/add',['addonkey'=>'nv_modulepresets']).'" class="btn btn-save">Addon installieren</a>';
}

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', "nvModulePresets", false);
$fragment->setVar('body', $sContent, false);
echo $fragment->parse('core/page/section.php');