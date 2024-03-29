<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-2.0/JG/trunk/administrator/components/com_joomgallery/models/images.php $
// $Id: images.php 3651 2012-02-19 14:36:46Z mab $
/****************************************************************************************\
**   JoomGallery 2                                                                      **
**   By: JoomGallery::ProjectTeam                                                       **
**   Copyright (C) 2008 - 2012  JoomGallery::ProjectTeam                                **
**   Based on: JoomGallery 1.0.0 by JoomGallery::ProjectTeam                            **
**   Released under GNU GPL Public License                                              **
**   License: http://www.gnu.org/copyleft/gpl.html or have a look                       **
**   at administrator/components/com_joomgallery/LICENSE.TXT                            **
\****************************************************************************************/

defined('_JEXEC') or die('Direct Access to this location is not allowed.');

/**
 * Images model
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryModelImages extends JoomGalleryModel
{
  /**
   * Images data array
   *
   * @var array
   */
  protected $_images;

  /**
   * Images number
   *
   * @var int
   */
  protected $_total = null;

  /**
   * Constructor
   *
   * @param   array An optional associative array of configuration settings
   * @return  void
   * @since   2.0
   */
  public function __construct($config = array())
  {
    parent::__construct($config);

    $this->filter_fields = array(
        'id', 'a.id',
        'imgtitle', 'a.imgtitle',
        'alias', 'a.alias',
        'catid', 'a.catid',
        'category_name',
        'published', 'a.published',
        'approved', 'a.approved',
        'access', 'a.access', 'access_level',
        'owner', 'a.owner',
        'imgauthor', 'a.imgauthor',
        'imgdate', 'a.imgdate',
        'hits', 'a.hits',
        'ordering', 'a.ordering'
        );
  }

  /**
   * Retrieves the images data
   *
   * @access  public
   * @return  array   Array of objects containing the images data from the database
   * @since   1.5.5
   */
  function getImages()
  {
    // Let's load the data if it doesn't already exist
    if(empty($this->_images))
    {
      $query = $this->_buildQuery();
      $this->_images = $this->_getList($query, $this->getState('list.start'), $this->getState('list.limit'));
    }

    return $this->_images;
  }

  /**
   * Method to get the pagination object for the list.
   * This method uses 'getTotel', 'getStart' and the current
   * list limit of this view.
   *
   * @return  object  A pagination object
   * @since   2.0
   */
  function getPagination()
  {
    jimport('joomla.html.pagination');
    return new JPagination($this->getTotal(), $this->getStart(), $this->getState('list.limit'));
  }

  /**
   * Method to get the total number of images
   *
   * @access  public
   * @return  int     The total number of images
   * @since   1.5.5
   */
  function getTotal()
  {
    // Let's load the total number of images if it doesn't already exist
    if(empty($this->_total))
    {
      $query = $this->_buildQuery();
      $this->_total = $this->_getListCount($query);
    }

    return $this->_total;
  }

  /**
   * Method to get the starting number of items for the data set.
   *
   * @return  int The starting number of items available in the data set.
   * @since   2.0
   */
  public function getStart()
  {
    $start = $this->getState('list.start');
    $limit = $this->getState('list.limit');
    $total = $this->getTotal();
    if($start > $total - $limit)
    {
      $start = max(0, (int)(ceil($total / $limit) - 1) * $limit);
    }

    return $start;
  }

  /**
   * Method to auto-populate the model state.
   *
   * Note. Calling getState in this method will result in recursion.
   *
   * @param   string  An optional ordering field.
   * @param   string  An optional direction (asc|desc).
   * @return  void
   * @since   2.0
   */
  protected function populateState($ordering = 'a.ordering', $direction = 'ASC')
  {
    $search = $this->getUserStateFromRequest('joom.images.filter.search', 'filter_search');
    $this->setState('filter.search', $search);

    $access = $this->getUserStateFromRequest('joom.images.filter.access', 'filter_access', 0, 'int');
    $this->setState('filter.access', $access);

    $published = $this->getUserStateFromRequest('joom.images.filter.state', 'filter_state', '');
    $this->setState('filter.state', $published);

    $type = $this->getUserStateFromRequest('joom.images.filter.type', 'filter_type', '');
    $this->setState('filter.type', $type);

    $category = $this->getUserStateFromRequest('joom.images.filter.category', 'filter_category', '');
    $this->setState('filter.category', $category);

    $owner = $this->getUserStateFromRequest('joom.images.filter.owner', 'filter_owner', '');
    $this->setState('filter.owner', $owner);

    $value = $this->getUserStateFromRequest('global.list.limit', 'limit', $this->_mainframe->getCfg('list_limit'));
    $limit = $value;
    $this->setState('list.limit', $limit);

    $value = $this->getUserStateFromRequest('joom.images.limitstart', 'limitstart', 0);
    $limitstart = ($limit != 0 ? (floor($value / $limit) * $limit) : 0);
    $this->setState('list.start', $limitstart);

    // Check if the ordering field is in the white list, otherwise use the incoming value
    $value = $this->getUserStateFromRequest('joom.images.ordercol', 'filter_order', $ordering);
    if(!in_array($value, $this->filter_fields))
    {
      $value = $ordering;
      $this->_mainframe->setUserState('joom.images.ordercol', $value);
    }

    $this->setState('list.ordering', $value);

    // Check if the ordering direction is valid, otherwise use the incoming value
    $value = $this->getUserStateFromRequest('joom.images.orderdirn', 'filter_order_Dir', $direction);
    if(!in_array(strtoupper($value), array('ASC', 'DESC', '')))
    {
      $value = $direction;
      $this->_mainframe->setUserState('joom.images.orderdirn', $value);
    }

    $this->setState('list.direction', $value);

    if($search || $access || $published || $type || $category || $owner)
    {
      $this->setState('filter.inuse', 1);
    }
  }

  /**
   * Method to delete one or more images
   *
   * @access  public
   * @return  int     Number of successfully deleted images, boolean false if an error occured
   * @since   1.5.5
   */
  function delete()
  {
    jimport('joomla.filesystem.file');

    $cids = JRequest::getVar('cid', array(), '', 'array');

    $row  = & $this->getTable('joomgalleryimages');

    if(!count($cids))
    {
      $this->setError(JText::_('COM_JOOMGALLERY_COMMON_MSG_NO_IMAGES_SELECTED'));
      return false;
    }

    $count = 0;

    // Loop through selected images
    foreach($cids as $cid)
    {
      if(!$this->_user->authorise('core.delete', _JOOM_OPTION.'.image.'.$cid))
      {
        $this->setError(JText::plural('COM_JOOMGALLERY_IMGMAN_ERROR_DELETE_NOT_PERMITTED', 1));
        continue;
      }

      $row->load($cid);

      // Database query to check if there are other images which this
      // thumbnail is assigned to and how many of them exist
      $this->_db->setQuery("SELECT
                              COUNT(id)
                            FROM
                              "._JOOM_TABLE_IMAGES."
                            WHERE
                                  imgthumbname = '".$row->imgthumbname."'
                              AND id          != ".$row->id."
                              AND catid        = ".$row->catid
                          );
      $thumb_count = $this->_db->loadResult();

      // Database query to check if there are other images which this
      // detail image is assigned to and how many of them exist
      $this->_db->setQuery("SELECT
                              COUNT(id)
                            FROM
                              "._JOOM_TABLE_IMAGES."
                            WHERE
                                  imgfilename = '".$row->imgfilename."'
                              AND id         != ".$row->id."
                              AND catid       = ".$row->catid
                          );
      $img_count = $this->_db->loadResult();

      // Delete the thumbnail if there are no other images
      // in the same category assigned to it
      if(!$thumb_count)
      {
        $thumb = $this->_ambit->getImg('thumb_path', $row);
        if(!JFile::delete($thumb))
        {
          // If thumbnail is not deleteable raise an error message and abort
          JError::raiseWarning(100, JText::sprintf('COM_JOOMGALLERY_IMGMAN_MSG_COULD_NOT_DELETE_THUMB', $thumb));
          return false;
        }
      }

      // Delete the detail image if there are no other detail and
      // original images from the same category assigned to it
      if(!$img_count)
      {
        $img = $this->_ambit->getImg('img_path', $row);
        if(!JFile::delete($img))
        {
          // If detail image is not deleteable raise an error message and abort
          JError::raiseWarning(100, JText::sprintf('COM_JOOMGALLERY_IMGMAN_MSG_COULD_NOT_DELETE_IMG', $img));
          return false;
        }
        // Original exists?
        $orig = $this->_ambit->getImg('orig_path', $row);
        if(JFile::exists($orig))
        {
          // Delete it
          if(!JFile::delete($orig))
          {
            // If original is not deleteable raise an error message and abort
            JError::raiseWarning(100, JText::sprintf('COM_JOOMGALLERY_IMGMAN_MSG_COULD_NOT_DELETE_ORIG', $orig));
            return false;
          }
        }
      }

      // Delete the corresponding database entries of the comments
      $this->_db->setQuery("DELETE
                            FROM
                              "._JOOM_TABLE_COMMENTS."
                            WHERE
                              cmtpic = ".$cid
                          );
      if(!$this->_db->query())
      {
        JError::raiseWarning(100, JText::sprintf('COM_JOOMGALLERY_MAIMAN_MSG_NOT_DELETE_COMMENTS', $cid));
      }

      // Delete the corresponding database entries of the name tags
      $this->_db->setQuery("DELETE
                            FROM
                              "._JOOM_TABLE_NAMESHIELDS."
                            WHERE
                              npicid = ".$cid
                          );
      if(!$this->_db->query())
      {
        JError::raiseWarning(100, JText::sprintf('COM_JOOMGALLERY_MAIMAN_MSG_NOT_DELETE_NAMETAGS', $cid));
      }

      // Delete the database entry of the image
      if(!$row->delete())
      {
        JError::raiseWarning(100, JText::sprintf('COM_JOOMGALLERY_MAIMAN_MSG_NOT_DELETE_IMAGE_DATA', $cid));
        return false;
      }

      // Image successfully deleted
      $count++;
      $row->reorder('catid = '.$row->catid);
    }

    return $count;
  }

  /**
   * Publishes/unpublishes or approves/rejects one or more images
   *
   * @access  public
   * @param   array   $cid      An array of image IDs to work with
   * @param   int     $publish  1 for publishing and approving, 0 otherwise
   * @param   string  $task     'publish' for publishing/unpublishing, anything else otherwise
   * @return  int     The number of successfully edited images, boolean false if an error occured
   * @since   1.5.5
   */
  function publish($cid, $publish = 1, $task = 'publish')
  {
    JArrayHelper::toInteger($cid);
    $publish = intval($publish);
    $count = count($cid);

    $row = &$this->getTable('joomgalleryimages');

    $column = 'approved';
    if($task == 'publish')
    {
      $column = 'published';
    }

    foreach($cid as $id)
    {
      $row->load($id);
      $row->$column = $publish;
      if(!$row->check())
      {
        $count--;
        continue;
      }

      if(!$row->store())
      {
        $count--;
        continue;
      }

      // If publishing or unpublishung wasn't successful, decrease the
      // counter of successfully published or unpublished images
      if($row->$column != $publish)
      {
        $count--;
      }
    }

    return $count;
  }

  /**
   * Recreates thumbnails of the selected images.
   * If original image is existent, detail image will be recreated, too.
   *
   * @access  public
   * @return  array   An array of result information (thumbnail number, detail image number, array with information which image types have been recreated)
   * @since   1.5.5
   */
  function recreate()
  {
    jimport('joomla.filesystem.file');

    $cids         = $this->_mainframe->getUserStateFromRequest('joom.recreate.cids', 'cid', array(), 'array');
    $type         = $this->_mainframe->getUserStateFromRequest('joom.recreate.type', 'type', '', 'cmd');
    $thumb_count  = $this->_mainframe->getUserState('joom.recreate.thumbcount');
    $img_count    = $this->_mainframe->getUserState('joom.recreate.imgcount');
    $recreated    = $this->_mainframe->getUserState('joom.recreate.recreated');

    $row  = & $this->getTable('joomgalleryimages');

    // Before first loop check for selected images
    if(is_null($thumb_count) && !count($cids))
    {
      $this->setError(JText::_('COM_JOOMGALLERY_COMMON_MSG_NO_IMAGES_SELECTED'));
      return array(false);
    }

    if(is_null($recreated))
    {
      $recreated = array();
    }

    require_once JPATH_COMPONENT.DS.'helpers'.DS.'refresher.php';

    $refresher = new JoomRefresher(array('controller' => 'images', 'task' => 'recreate', 'remaining' => count($cids), 'start' => JRequest::getBool('cid')));

    $debugoutput = '';

    // Loop through selected images
    foreach($cids as $key => $cid)
    {
      $row->load($cid);

      $orig   = $this->_ambit->getImg('orig_path', $row);
      $img    = $this->_ambit->getImg('img_path', $row);
      $thumb  = $this->_ambit->getImg('thumb_path', $row);

      // Check if there is an original image
      if(JFile::exists($orig))
      {
        $orig_existent = true;
      }
      else
      {
        // If not, use detail image to create thumbnail
        $orig_existent = false;
        if(JFile::exists($img))
        {
          $orig = $img;
        }
        else
        {
          JError::raiseWarning(100, JText::sprintf('COM_JOOMGALLERY_IMGMAN_MSG_IMAGE_NOT_EXISTENT', $img));
          $this->_mainframe->setUserState('joom.recreate.cids', array());
          $this->_mainframe->setUserState('joom.recreate.imgcount', null);
          $this->_mainframe->setUserState('joom.recreate.thumbcount', null);
          $this->_mainframe->setUserState('joom.recreate.recreated', null);
          return false;
        }
      }

      // Recreate thumbnail
      if(!$type || $type == 'thumb')
      {
        // TODO: Move image into a trash instead of deleting immediately for possible rollback
        if(JFile::exists($thumb))
        {
          JFile::delete($thumb);
        }
        $return = JoomFile::resizeImage($debugoutput,
                                        $orig,
                                        $thumb,
                                        $this->_config->get('jg_useforresizedirection'),
                                        $this->_config->get('jg_thumbwidth'),
                                        $this->_config->get('jg_thumbheight'),
                                        $this->_config->get('jg_thumbcreation'),
                                        $this->_config->get('jg_thumbquality'),
                                        false,
                                        $this->_config->get('jg_cropposition')
                                        );
        if(!$return)
        {
          JError::raiseWarning(100, JText::sprintf('COM_JOOMGALLERY_IMGMAN_MSG_COULD_NOT_CREATE_THUMB', $thumb));
          $this->_mainframe->setUserState('joom.recreate.cids', array());
          $this->_mainframe->setUserState('joom.recreate.thumbcount', null);
          $this->_mainframe->setUserState('joom.recreate.imgcount', null);
          $this->_mainframe->setUserState('joom.recreate.recreated', null);
          return false;
        }

        //$this->_mainframe->enqueueMessage(JText::sprintf('COM_JOOMGALLERY_IMGMAN_MSG_SUCCESSFULLY_CREATED_THUMB', $row->id, $row->imgtitle));
        $recreated[$cid][] = 'thumb';
        $thumb_count++;
      }

      // Recreate detail image if original image is existent
      if($orig_existent && (!$type || $type == 'img'))
      {
        // TODO: Move image into a trash instead of deleting immediately for possible rollback
        if(JFile::exists($img))
        {
          JFile::delete($img);
        }
        $return = JoomFile::resizeImage($debugoutput,
                                        $orig,
                                        $img,
                                        false,
                                        $this->_config->get('jg_maxwidth'),
                                        false,
                                        $this->_config->get('jg_thumbcreation'),
                                        $this->_config->get('jg_picturequality'),
                                        true,
                                        0
                                        );
        if(!$return)
        {
          JError::raiseWarning(100, JText::sprintf('COM_JOOMGALLERY_IMGMAN_MSG_COULD_NOT_CREATE_IMG', $img));
          $this->_mainframe->setUserState('joom.recreate.cids', array());
          $this->_mainframe->setUserState('joom.recreate.thumbcount', null);
          $this->_mainframe->setUserState('joom.recreate.imgcount', null);
          $this->_mainframe->setUserState('joom.recreate.recreated', null);
          return false;
        }

        //$this->_mainframe->enqueueMessage(JText::sprintf('COM_JOOMGALLERY_IMGMAN_MSG_SUCCESSFULLY_CREATED_IMG', $row->id, $row->imgtitle));
        $recreated[$cid][] = 'img';
        $img_count++;
      }

      unset($cids[$key]);

      // Check remaining time
      if(!$refresher->check())
      {
        $this->_mainframe->setUserState('joom.recreate.cids', $cids);
        $this->_mainframe->setUserState('joom.recreate.thumbcount', $thumb_count);
        $this->_mainframe->setUserState('joom.recreate.imgcount', $img_count);
        $this->_mainframe->setUserState('joom.recreate.recreated', $recreated);
        $refresher->refresh(count($cids));
      }
    }

    $this->_mainframe->setUserState('joom.recreate.cids', array());
    $this->_mainframe->setUserState('joom.recreate.type', null);
    $this->_mainframe->setUserState('joom.recreate.thumbcount', null);
    $this->_mainframe->setUserState('joom.recreate.imgcount', null);
    $this->_mainframe->setUserState('joom.recreate.recreated', null);
    return array($thumb_count, $img_count, $recreated);
  }

  /**
   * Returns the query for listing the images
   *
   * @access  protected
   * @return  string    The query to be used to retrieve the images data from the database
   * @since   1.5.5
   */
  function _buildQuery()
  {
    // Create a new query object
    $query = $this->_db->getQuery(true);

    // Select the required fields from the table
    $query->select('a.*')
          ->from(_JOOM_TABLE_IMAGES.' AS a');

    // Join over the categories
    $query->select('c.cid AS category, c.name AS category_name')
          ->leftJoin(_JOOM_TABLE_CATEGORIES.' AS c ON c.cid = a.catid');

    // Join over the access levels
    $query->select('v.title AS access_level')
          ->leftJoin('#__viewlevels AS v ON v.id = a.access');

    // Join over the users
    $query->leftJoin('#__users AS u ON u.id = a.owner');

    // Join over the categories again in order to check access levels
    if(!$this->_user->authorise('core.admin'))
    {
      $query->leftJoin(_JOOM_TABLE_CATEGORIES.' AS p ON c.lft BETWEEN p.lft AND p.rgt')
            ->select('c.level')
            ->where('p.access IN ('.implode(',', $this->_user->getAuthorisedViewLevels()).')')
            ->group('a.id')
            ->having('COUNT(p.cid) > c.level')

      // Access level check for the image and the category the image is in
            ->where('a.access IN ('.implode(',', $this->_user->getAuthorisedViewLevels()).')')
            ->where('c.access IN ('.implode(',', $this->_user->getAuthorisedViewLevels()).')');
    }

    // Filter by access level
    if($access = $this->getState('filter.access'))
    {
      $query->where('a.access = '.(int) $access);
    }

    // Filter by owner
    $owner = $this->getState('filter.owner');
    if($owner !== '')
    {
      $query->where('a.owner = '.(int) $owner);
    }

    // Filter by category
    if($category = $this->getState('filter.category'))
    {
      $query->where('a.catid = '.(int) $category);
    }

    // Filter by state
    $published = $this->getState('filter.state');
    switch($published)
    {
      case 1:
        // Published
        $query->where('a.published = 1');
        break;
      case 2:
        // Not published
        $query->where('a.published = 0');
        break;
      case 3:
        // Approved
        $query->where('a.approved = 1');
        break;
      case 4:
        // Not approved / rejected
        $query->where('a.approved = 0');
        break;
      default:
        // No filter by state
        break;
    }

    // Filter by type
    $type = $this->getState('filter.type');
    switch($type)
    {
      case 1:
        // User images
        $query->where('a.owner != 0');
        break;
      case 2:
        // Administrator images
        $query->where('a.owner = 0');
        break;
      default:
        // No filter by type
        break;
    }

    // Filter by search
    $search = $this->getState('filter.search');
    if(!empty($search))
    {
      if(stripos($search, 'id:') === 0)
      {
        $query->where('a.id = '.(int) substr($search, 3));
      }
      else
      {
        if(stripos($search, 'author:') === 0)
        {
          $search = $this->_db->Quote('%'.$this->_db->getEscaped(substr($search, 7), true).'%');
          $query->where('(u.name LIKE '.$search.' OR u.username LIKE '.$search.')');
        }
        else
        {
          $search = $this->_db->Quote('%'.$this->_db->getEscaped($search, true).'%');
          $query->where('(a.imgtitle LIKE '.$search.' OR a.alias LIKE '.$search.' OR LOWER(a.imgtext) LIKE '.$search.')');
        }
      }
    }

    // Add the order clause
    $orderCol   = $this->state->get('list.ordering');
    $orderDirn  = $this->state->get('list.direction');
    if($orderCol == 'a.ordering' || $orderCol == 'category_name')
    {
      $orderCol = 'category_name '.$orderDirn.', a.ordering';
    }
    $query->order($this->_db->getEscaped($orderCol.' '.$orderDirn));

    return $query;
  }

  /**
   * Gets the value of a user state variable and sets it in the session
   * This is the same as the method in JApplication except that this also can optionally
   *    force you back to the first page when a filter has changed
   *
   * @param   string  The key of the user state variable.
   * @param   string  The name of the variable passed in a request.
   * @param   string  The default value for the variable if not found. Optional.
   * @param   string  Filter for the variable, for valid values see {@link JFilterInput::clean()}. Optional.
   * @param   boolean If true, the limitstart in request is set to zero
   * @return  The requested user state.
   * @since   2.0
   */
  public function getUserStateFromRequest($key, $request, $default = null, $type = 'none', $resetPage = true)
  {
    $app = JFactory::getApplication();
    $old_state = $app->getUserState($key);
    $cur_state = (!is_null($old_state)) ? $old_state : $default;
    $new_state = JRequest::getVar($request, null, 'default', $type);

    if (($cur_state != $new_state) && ($resetPage)){
      JRequest::setVar('limitstart', 0);
    }

    // Save the new value only if it was set in this request.
    if ($new_state !== null) {
      $app->setUserState($key, $new_state);
    }
    else {
      $new_state = $cur_state;
    }

    return $new_state;
  }
}