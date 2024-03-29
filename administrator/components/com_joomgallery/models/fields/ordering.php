<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-2.0/JG/trunk/administrator/components/com_joomgallery/models/fields/ordering.php $
// $Id: ordering.php 3651 2012-02-19 14:36:46Z mab $
/****************************************************************************************\
**   JoomGallery  2                                                                     **
**   By: JoomGallery::ProjectTeam                                                       **
**   Copyright (C) 2008 - 2012  JoomGallery::ProjectTeam                                **
**   Based on: JoomGallery 1.0.0 by JoomGallery::ProjectTeam                            **
**   Released under GNU GPL Public License                                              **
**   License: http://www.gnu.org/copyleft/gpl.html or have a look                       **
**   at administrator/components/com_joomgallery/LICENSE.TXT                            **
\****************************************************************************************/

defined('_JEXEC') or die('Direct Access to this location is not allowed.');

/**
 * Renders an ordering select box form field
 *
 * @package JoomGallery
 * @since   2.0
 */
class JFormFieldOrdering extends JFormField
{
  /**
   * The form field type.
   *
   * @access  protected
   * @var     string
   * @since   2.0
   */
  var $type = 'Ordering';

  /**
   * Returns the HTML for an ordering select box form field
   *
   * @access  protected
   * @return  object    The ordering select box form field.
   * @since   2.0
   */
  function getInput()
  {
    $originalOrder  = $this->element['originalOrder'] ? (int) $this->element['originalOrder'] : 0;
    $originalParent = $this->element['originalParent'] ? (int) $this->element['originalParent'] : 0;
    $cats           = $this->element['orderings'] ? unserialize(base64_decode($this->element['orderings'])) : array();
    $parent_id      = $this->element['parent_id'] ? $this->element['parent_id'] : 'parent_id';

    $disabled = '';
    if((string) $this->element['readonly'] == 'true' || (string) $this->element['disabled'] == 'true')
    {
			$disabled = ' disabled="disabled"';
		}

    // Create all select options
    $orderings  = array();
    $ordcount   = array();
    $catcount   = count($cats);
    for($i = 0; $i < $catcount; $i++)
    {
      if(!isset($orderings[$cats[$i]->parent_id]))
      {
        // Add entry for 'First'
        $orderings[$cats[$i]->parent_id][] = JHTML::_('select.option', 'first-child', '0  '.JText::_('JOPTION_ORDER_FIRST'));
        $ordcount[$cats[$i]->parent_id]    = 0;
      }

      $ord = ++$ordcount[$cats[$i]->parent_id];
      $orderings[$cats[$i]->parent_id][] = JHTML::_('select.option', $cats[$i]->cid, $ord.'  ('.addslashes($cats[$i]->name).')');
    }
    // Add entry for 'Last'
    foreach($orderings as $key => $ordering)
    {
      $ord = ++$ordcount[$key];
      $orderings[$key][] = JHTML::_('select.option', 'last-child', $ord.'  '.JText::_('JOPTION_ORDER_LAST'));
    }

    // Create the javascript arrays with all orderings for each category
    $i = 0;
    $script = '';
    foreach($orderings as $k => $items)
    {
      foreach($items as $v)
      {
        if($k == 1)
        {
          // In Nested Sets having category with ID 1
          // as parent means being a main category
          $k = 0;
        }

        $script .= '
      orders['.$i++.'] = new Array("'.$k.'", "'.$v->value.'", "'.$v->text.'");';
      }
    }

    $html = '
    <script language="javascript" type="text/javascript">
      <!--
      var originalOrder   = '.$originalOrder.';
      var originalParent  = '.$originalParent.';
      var orders          = new Array();'.$script.'
      writeDynaList( \'class="inputbox" name="'.$this->name.'" id="'.$this->id.'"'.$disabled.' size="1"\', orders, originalParent, originalParent, originalOrder );
      window.addEvent(\'domready\', function() {
          document.getElementById(\''.$parent_id.'\').addEvent(\'change\', function() {
            changeDynaList(\''.$this->name.'\', orders, document.getElementById(\''.$parent_id.'\').value, originalParent, originalOrder);
          });
      });
      //-->
    </script>
    ';

    return $html;
  }
}