<?php header("Content-type: text/css");
//this sets up the colors for the core missioncontrol template
require('../../css/color-vars.php');
?>

#toolbar .button .spinner {padding-left:22px; width:auto;height:auto;color:<?php echo $tab_text_color; ?>;font-style:normal;}
#toolbar .button .spinner-32 {background-image:url(spinner-16.gif);background-repeat: no-repeat;background-position:3px 5px;}
#toolbar .button .spinner:hover, #toolbar .button .spinner:active, #toolbar .spinner:focus {border:0;}
.input-append .add-on {margin-left:-5px;z-index:1}
.input-prepend input, .input-append input {z-index:5;position:relative;}
.input-prepend .add-on, .input-append .add-on {padding: 3px 4px 3px 5px;}
