<?php header("Content-type: text/css");
//this sets up the colors for the core missioncontrol template
require('../../css/color-vars.php');
?>
div#toolbar-box {background:#F5F5F5 !important;width:100%;}
div#content-box {border:0 !important;}
div#toolbar-box div.m {border:0 !important;padding:0 !important;}
#mc-title table.toolbar, #mc-title table.toolbar a.toolbar {margin-top:0;}
#mc-toolbox-wrap {width:100%;position:relative;}
#mc-submenu {margin-top:20px;margin-bottom:-30px;}
#mc-submenu ul#submenu {background:none;line-height:inherit;margin-left:50px;}
#mc-submenu ul#submenu li {padding-top:7px !important;padding-bottom:6px !important;}
#mc-submenu ul#submenu a {border:0;padding: 0 15px;font-size:12px;margin-right:0 !important;}
#mc-submenu ul#submenu a:hover {text-decoration:underline;}
#mc-component {border-radius:0;padding:0;}

form input[type="radio"], form input[type="checkbox"] {height:14px;}
