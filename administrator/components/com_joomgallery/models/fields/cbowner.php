<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-2.0/JG/trunk/administrator/components/com_joomgallery/models/fields/cbowner.php $
// $Id: cbowner.php 3651 2012-02-19 14:36:46Z mab $
/****************************************************************************************\
**   JoomGallery 2                                                                      **
**   By: JoomGallery::ProjectTeam                                                       **
**   Copyright (C) 2008 - 2012  JoomGallery::ProjectTeam                                **
**   Based on: JoomGallery 1.0.0 by JoomGallery::ProjectTeam                            **
**   Released under GNU GPL Public License                                              **
**   License: http://www.gnu.org/copyleft/gpl.html or have a look                       **
**   at administrator/components/com_joomgallery/LICENSE.TXT                            **
\****************************************************************************************/

defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('owner');

/**
 * Renders an owner select box form field with checkbox in front of the label
 *
 * @package JoomGallery
 * @since   2.0
 */
class JFormFieldCbowner extends JFormFieldOwner
{
  /**
   * The form field type
   *
   * @access  protected
   * @var     string
   * @since   2.0
   */
  var  $type = 'Cbowner';

  /**
   * Method to get the field label markup
   *
   * @access  protected
   * @return  string  The field label markup
   * @since   2.0
   */
  protected function getLabel()
  {
    $label = '';

    $cbname     = $this->element['cbname'] ? $this->element['cbname'] : 'change[]';
    $cbvalue    = $this->element['cbvalue'] ? $this->element['cbvalue'] : $this->name;

    $cbid       = str_replace(array('[', ']'), array('', ''), $cbname.$cbvalue);
    $cbhtml     = '<input id="'.$cbid.'" type="checkbox" name="'.$cbname.'" value="'.$cbvalue.'" />';
    $label      = parent::getLabel();
    $insertpos  = strpos($label, '>');
    $label      = substr_replace($label, $cbhtml, $insertpos + 1, 0);

    return $label;
  }
}