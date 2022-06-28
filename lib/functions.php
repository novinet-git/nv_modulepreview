<?php if (!function_exists('nvMaskChar')) {
    function nvMaskChar($str)
    {    //Maskiert folgende Sonderzeichen: & " < > '
        $str = stripslashes($str);
        $str = htmlspecialchars($str, ENT_QUOTES);
        $str = trim($str);
        return $str;
    }
}
