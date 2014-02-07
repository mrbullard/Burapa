<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-2.0/Module/JoomImages/trunk/helper.php $
// $Id: helper.php 3688 2012-03-07 06:12:12Z aha $
/****************************************************************************************\
 **   Module JoomImages for JoomGallery                                                  **
 **   By: JoomGallery::ProjectTeam                                                       **
 **   Released under GNU GPL Public License                                              **
 **   License: http://www.gnu.org/copyleft/gpl.html or have a look                       **
 **   at administrator/components/com_joomgallery/LICENSE.TXT                            **
\****************************************************************************************/

defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.html.html');
jimport('joomla.form.formfield');//import the necessary class definition for formfield


/**
 * Supports a ColorPicker by mooRainbow
 * @since 1.6
 */
class JFormFieldColor extends JFormFieldText
{
  /**
   * The form field type.
   *
   * @var string
   * @since 1.6
   */
  public $type = 'Color'; //the form field type

  /**
   * Method to build the color picker
   *
   * @return string html and javascript individually for each input box
   * @since 1.6
   */
  protected function getInput()
  {
    $doc = &JFactory::getDocument();
    $session = JFactory::getSession();

    static $id=0;
    $id++;

    // Get the current value to initialize the color picker
    // and transform in RGB values
    $color = substr($this->value,1);

    if (strlen($color) == 6)
    {
      list($r, $g, $b) = array($color[0].$color[1],
                               $color[2].$color[3],
                               $color[4].$color[5]);
    }
    else
    {
      list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
    }
    $r = hexdec($r);
    $g = hexdec($g);
    $b = hexdec($b);

    $colorstringrgb = "[".$r.",".$g.",".$b."]";

    if($id == 1)
    {
      //Include CSS, if you need the mini view choose the mini.css
      $doc->addStyleSheet(JURI::base().'../modules/mod_joomimg/fields/moorainbow/assets/default.css');

      // Include javascripts
      JHTML::_('behavior.mootools',false);
      JHTML::_('behavior.mootools',true);
      $doc->addScript(JURI::base().'../modules/mod_joomimg/fields/moorainbow/source/mooRainbow.js');
    }

    // Set html and js
    $html = parent::getInput();

    $html .= '<span name="modjicolor'.$id.'-show" style="float:left;margin-top:5px;height:15px;width:20px;border:1px solid #000;" id="modjicolor'.$id.'-show" />'."\n";
    $html .= '<script type="text/javascript">'."\n";
    $html .= '  var r'.$id.' = new MooRainbow("modjicolor'.$id.'-show", {'."\n";
    $html .= '    id: '.$id.','."\n";
    $html .= '    startColor: '.$colorstringrgb.','."\n";
    $html .= '    imgPath: "../modules/mod_joomimg/fields/moorainbow/Assets/images/",'."\n";
    $html .= '    onChange: function(color) {'."\n";
    $html .= '      this.element.setStyle("background-color", color.hex);'."\n";
    $html .= '      Slick.find(document, "#'.$this->id.'").value=color.hex'."\n";
    $html .= '    }'."\n";
    $html .= '  });'."\n";
    $html .= '</script>'."\n";

    return $html;
  }
}
