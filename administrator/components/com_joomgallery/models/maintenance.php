<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-2.0/JG/trunk/administrator/components/com_joomgallery/models/maintenance.php $
// $Id: maintenance.php 3651 2012-02-19 14:36:46Z mab $
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
 * Maintenance model
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryModelMaintenance extends JoomGalleryModel
{
  /**
   * Images data array
   *
   * @access  protected
   * @var     array
   */
  var $_images;

  /**
   * Categories data array
   *
   * @access  protected
   * @var     array
   */
  var $_categories;

  /**
   * Orphans data array
   *
   * @access  protected
   * @var     array
   */
  var $_orphans;

  /**
   * Orphaned folders data array
   *
   * @access  protected
   * @var     array
   */
  var $_orphanedfolders;

  /**
   * Images number
   *
   * @access  protected
   * @var     int
   */
  var $_totalimages;

  /**
   * Categories number
   *
   * @access  protected
   * @var     int
   */
  var $_totalcategories;

  /**
   * Orphans number
   *
   * @access  protected
   * @var     int
   */
  var $_totalorphans;

  /**
   * Orphaned folders number
   *
   * @access  protected
   * @var     int
   */
  var $_totalorphanedfolders;

  /**
   * Holds information in which tab something will be listed
   *
   * @access  protected
   * @var     array
   */
  var $_information;

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
        'title', 'a.title',
        'alias', 'a.alias',
        'thumb', 'a.thumb',
        'img', 'a.img',
        'orig', 'a.orig',
        'fullpath', 'a.fullpath',
        'type', 'a.type',
        'refid', 'a.refid',
        'user',
        'category',
        );
  }

  /**
   * Returns the images data
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
      if(!$this->_loadImages())
      {
        return array();
      }
    }

    return $this->_images;
  }

  /**
   * Returns the categories data
   *
   * @access  public
   * @return  array   Array of objects containing the categories data from the database
   * @since   1.5.5
   */
  function getCategories()
  {
    // Let's load the data if it doesn't already exist
    if(empty($this->_categories))
    {
      if(!$this->_loadCategories())
      {
        return array();
      }
    }

    return $this->_categories;
  }

  /**
   * Returns the data of orphand files
   *
   * @access  public
   * @return  array   Array of objects containing the data of orphaned files from the database
   * @since   1.5.5
   */
  function getOrphans()
  {
    // Let's load the data if it doesn't already exist
    if(empty($this->_orphans))
    {
      if(!$this->_loadOrphans())
      {
        return array();
      }
    }

    return $this->_orphans;
  }

  /**
   * Returns the data of orphand folders
   *
   * @access  public
   * @return  array   Array of objects containing the data of orphaned folders from the database
   * @since   1.5.5
   */
  function getOrphanedFolders()
  {
    // Let's load the data if it doesn't already exist
    if(empty($this->_orphanedfolders))
    {
      if(!$this->_loadOrphanedFolders())
      {
        return array();
      }
    }

    return $this->_orphanedfolders;
  }

  /**
   * Returns information from the database about found inconsitencies
   *
   * @access  public
   * @return  array   Array of objects containing the information from the database
   * @since   1.5.5
   */
  function getInformation()
  {
    // If the images and categories haven't been checked afore
    // we don't have to request the information
    if(!$this->_mainframe->getUserState('joom.maintenance.checked'))
    {
      return array( 'images'      => 0,
                    'categories'  => 0,
                    'orphans'     => 0,
                    'folders'     => 0
                  );
    }

    // Let's load the data if it doesn't already exist
    if(empty($this->_information))
    {
      if(!$this->_loadInformation())
      {
        return array();
      }
    }

    return $this->_information;
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
   * Method to get the total number of items
   *
   * @access  public
   * @return  int     The total number of items
   * @since   1.5.5
   */
  function getTotal()
  {
    $total = 0;

    switch(JRequest::getCmd('tab'))
    {
      case 'categories':
        // Let's load the categories if they don't already exist
        if(empty($this->_totalcategories))
        {
          $query = $this->_buildCategoriesQuery();
          $this->_totalcategories = $this->_getListCount($query);
        }

        $total = $this->_totalcategories;
        break;
      case 'orphans':
        // Let's load the data of the orphaned files if it doesn't already exist
        if(empty($this->_totalorphans))
        {
          $query = $this->_buildOrphansQuery();
          $this->_totalorphans = $this->_getListCount($query);
        }

        $total = $this->_totalorphans;
        break;
      case 'folders':
        // Let's load the data of the orphaned folders if it don't already exist
        if(empty($this->_totalorphanedfolders))
        {
          $query = $this->_buildOrphanedFoldersQuery();
          $this->_totalorphanedfolders = $this->_getListCount($query);
        }

        $total = $this->_totalorphanedfolders;
        break;
      default:
        // Let's load the images if they don't already exist
        if(empty($this->_totalimages))
        {
          $query = $this->_buildImagesQuery();
          $this->_totalimages = $this->_getListCount($query);
        }

        $total = $this->_totalimages;
        break;
    }

    return $total;
  }

  /**
   * Method to get the starting number of items for the data set.
   *
   * @return  int   The starting number of items available in the data set.
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
  protected function populateState($default_ordering = 'a.id', $default_direction = 'ASC')
  {
    switch(JRequest::getCmd('tab'))
    {
      case 'categories':
        $search     = $this->getUserStateFromRequest('joom.maintenance.categories.filter.search', 'filter_search');
        $category   = $this->getUserStateFromRequest('joom.maintenance.categories.filter.category', 'filter_category', '');
        $type       = $this->getUserStateFromRequest('joom.maintenance.categories.filter.type', 'filter_type', '');
        $proposal   = null;
        $limit      = $this->getUserStateFromRequest('global.list.limit', 'limit', $this->_mainframe->getCfg('list_limit'));
        $limitstart = $this->getUserStateFromRequest('joom.maintenance.categories.limitstart', 'limitstart', 0);

        // Check if the ordering field is in the white list, otherwise use the incoming value
        $ordering   = $this->getUserStateFromRequest('joom.maintenance.categories.ordercol', 'filter_order', $default_ordering);
        if(!in_array($ordering, $this->filter_fields))
        {
          $ordering = $default_ordering;
          $this->_mainframe->setUserState('joom.maintenance.categories.ordercol', $ordering);
        }

        // Check if the ordering direction is valid, otherwise use the incoming value
        $direction = $this->getUserStateFromRequest('joom.maintenance.categories.orderdirn', 'filter_order_Dir', $default_direction);
        if(!in_array(strtoupper($direction), array('ASC', 'DESC', '')))
        {
          $direction = $default_direction;
          $this->_mainframe->setUserState('joom.maintenance.categories.orderdirn', $direction);
        }
        break;
      case 'orphans':
        $search     = $this->getUserStateFromRequest('joom.maintenance.orphans.filter.search', 'filter_search');
        $category   = $this->getUserStateFromRequest('joom.maintenance.orphans.filter.category', 'filter_category', '');
        $type       = $this->getUserStateFromRequest('joom.maintenance.orphans.filter.type', 'filter_type', '');
        $proposal   = $this->getUserStateFromRequest('joom.maintenance.images.filter.proposal', 'filter_proposal', '');
        $limit      = $this->getUserStateFromRequest('global.list.limit', 'limit', $this->_mainframe->getCfg('list_limit'));
        $limitstart = $this->getUserStateFromRequest('joom.maintenance.orphans.limitstart', 'limitstart', 0);

        // Check if the ordering field is in the white list, otherwise use the incoming value
        $ordering   = $this->getUserStateFromRequest('joom.maintenance.orphans.ordercol', 'filter_order', $default_ordering);
        if(!in_array($ordering, $this->filter_fields))
        {
          $ordering = $default_ordering;
          $this->_mainframe->setUserState('joom.maintenance.orphans.ordercol', $ordering);
        }

        // Check if the ordering direction is valid, otherwise use the incoming value
        $direction = $this->getUserStateFromRequest('joom.maintenance.orphans.orderdirn', 'filter_order_Dir', $default_direction);
        if(!in_array(strtoupper($direction), array('ASC', 'DESC', '')))
        {
          $direction = $default_direction;
          $this->_mainframe->setUserState('joom.maintenance.orphans.orderdirn', $direction);
        }
        break;
      case 'folders':
        $search     = $this->getUserStateFromRequest('joom.maintenance.folders.filter.search', 'filter_search');
        $category   = $this->getUserStateFromRequest('joom.maintenance.folders.filter.category', 'filter_category', '');
        $type       = $this->getUserStateFromRequest('joom.maintenance.folders.filter.type', 'filter_type', '');
        $proposal   = $this->getUserStateFromRequest('joom.maintenance.images.filter.proposal', 'filter_proposal', '');
        $limit      = $this->getUserStateFromRequest('global.list.limit', 'limit', $this->_mainframe->getCfg('list_limit'));
        $limitstart = $this->getUserStateFromRequest('joom.maintenance.folders.limitstart', 'limitstart', 0);

        // Check if the ordering field is in the white list, otherwise use the incoming value
        $ordering   = $this->getUserStateFromRequest('joom.maintenance.folders.ordercol', 'filter_order', $default_ordering);
        if(!in_array($ordering, $this->filter_fields))
        {
          $ordering = $default_ordering;
          $this->_mainframe->setUserState('joom.maintenance.folders.ordercol', $ordering);
        }

        // Check if the ordering direction is valid, otherwise use the incoming value
        $direction = $this->getUserStateFromRequest('joom.maintenance.folders.orderdirn', 'filter_order_Dir', $default_direction);
        if(!in_array(strtoupper($direction), array('ASC', 'DESC', '')))
        {
          $direction = $default_direction;
          $this->_mainframe->setUserState('joom.maintenance.folders.orderdirn', $direction);
        }
        break;
      default:
        $search     = $this->getUserStateFromRequest('joom.maintenance.images.filter.search', 'filter_search');
        $category   = $this->getUserStateFromRequest('joom.maintenance.images.filter.category', 'filter_category', '');
        $type       = $this->getUserStateFromRequest('joom.maintenance.images.filter.type', 'filter_type', '');
        $proposal   = null;
        $limit      = $this->getUserStateFromRequest('global.list.limit', 'limit', $this->_mainframe->getCfg('list_limit'));
        $limitstart = $this->getUserStateFromRequest('joom.maintenance.images.limitstart', 'limitstart', 0);

        // Check if the ordering field is in the white list, otherwise use the incoming value
        $ordering   = $this->getUserStateFromRequest('joom.maintenance.images.ordercol', 'filter_order', $default_ordering);
        if(!in_array($ordering, $this->filter_fields))
        {
          $ordering = $default_ordering;
          $this->_mainframe->setUserState('joom.maintenance.images.ordercol', $ordering);
        }

        // Check if the ordering direction is valid, otherwise use the incoming value
        $direction = $this->getUserStateFromRequest('joom.maintenance.images.orderdirn', 'filter_order_Dir', $default_direction);
        if(!in_array(strtoupper($direction), array('ASC', 'DESC', '')))
        {
          $direction = $default_direction;
          $this->_mainframe->setUserState('joom.maintenance.images.orderdirn', $direction);
        }
        break;
    }

    if($search || $category || $type || $proposal)
    {
      $this->setState('filter.inuse', 1);
    }

    $this->setState('filter.search',    $search);
    $this->setState('filter.category',  $category);
    $this->setState('filter.type',      $type);
    $this->setState('filter.proposal',  $proposal);
    $this->setState('list.limit',       $limit);
    $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
    $this->setState('list.start',       $limitstart);
    $this->setState('list.ordering',    $ordering);
    $this->setState('list.direction',   $direction);

    $check_originals = $this->getUserStateFromRequest('joom.maintenance.checkoriginals', 'check_originals', true);
    $this->setState('check_originals', $check_originals);
  }

  /**
   * Method to delete one or more images
   *
   * Images will be deleted even though there are inconsistencies
   *
   * @access  public
   * @param   boolean $refids True, if refids are given, false if image IDs are given, defaults to true
   * @return  int     The number of deleted images
   * @since   1.5.5
   */
  function delete($refids = true)
  {
    jimport('joomla.filesystem.file');

    $cids = JRequest::getVar('cid', array(), 'post', 'array');

    JArrayHelper::toInteger($cids);

    if($refids)
    {
      $cid_string = implode(',', $cids);

      // Get selected image IDs
      $query = 'SELECT
                  refid
                FROM
                  '._JOOM_TABLE_MAINTENANCE.'
                WHERE
                      id IN ('.$cid_string.')
                  AND type = 0';
      $this->_db->setQuery($query);
      if(!$cids = $this->_db->loadResultArray())
      {
        $this->setError($this->_db->getErrorMsg());
        return false;
      }
    }

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
      $error = false;

      if(!$row->load($cid))
      {
        continue;
      }

      // Database query to check if there are other images with this thumbnail
      // assigned and how many
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

      // Database query to check if there are other images with this detail
      // or original assigned and how many
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
      // in same category assigned to it
      if(!$thumb_count)
      {
        $thumb = $this->_ambit->getImg('thumb_path', $row);
        if(JFile::exists($thumb))
        {
          if(!JFile::delete($thumb))
          {
            $error = true;
            JError::raiseWarning(100, JText::sprintf('COM_JOOMGALLERY_MAIMAN_MSG_DELETE_FILE_VIA_FTP', $thumb));
          }
        }
      }

      // Delete the detail if there are no other detail and
      // originals from same category assigned to it
      if(!$img_count)
      {
        $img = $this->_ambit->getImg('img_path', $row);
        if(JFile::exists($img))
        {
          if(!JFile::delete($img))
          {
            $error = true;
            JError::raiseWarning(100, JText::sprintf('COM_JOOMGALLERY_MAIMAN_MSG_DELETE_FILE_VIA_FTP', $img));
          }
        }

        $orig = $this->_ambit->getImg('orig_path', $row);
        if(JFile::exists($orig))
        {
          if(!JFile::delete($orig))
          {
            $error = true;
            JError::raiseWarning(100, JText::sprintf('COM_JOOMGALLERY_MAIMAN_MSG_DELETE_FILE_VIA_FTP', $orig));
          }
        }
      }

      // Delete the corresponding database entries in comments
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

      // Delete the corresponding database entries in nameshields
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
        $error = true;
        JError::raiseWarning(100, JText::sprintf('COM_JOOMGALLERY_MAIMAN_MSG_NOT_DELETE_IMAGE_DATA', $cid));
      }

      if(!$error)
      {
        // Image deleted
        $count++;
        $row->reorder('catid = '.$row->catid);

        // Delete the image in the maintenance table
        $query = "DELETE
                  FROM
                    "._JOOM_TABLE_MAINTENANCE."
                  WHERE
                        refid = ".$cid."
                    AND type  = 0";
        $this->_db->setQuery($query);
        if(!$this->_db->query())
        {
          JError::raiseWarning(100, JText::sprintf('COM_JOOMGALLERY_MAIMAN_MSG_NOT_DELETE_IMAGE_TABLE_ENTRY', $cid));
        }
      }
    }

    return $count;
  }

  /**
   * Method to delete one or more categories
   *
   * Categories will be deleted even though there are inconsistencies or sub-categories
   *
   * @access  public
   * @param   int     $recursion_level  Level of recursion depth
   * @param   boolean $refids           True, if refids are given, false if category IDs are given, defaults to true
   * @return  int     The number of deleted categories
   * @since   1.5.5
   */
  function deletecategory($recursion_level = 0, $refids = true)
  {
    jimport('joomla.filesystem.file');

    $cids = JRequest::getVar('cid', array(), 'post', 'array');

    JArrayHelper::toInteger($cids);

    if(!$recursion_level)
    {
      $this->_mainframe->setUserState('joom.maintenance.delete.categories', null);
      $this->_mainframe->setUserState('joom.maintenance.delete.images', null);

      if($refids)
      {
        $cid_string = implode(',', $cids);

        // Get selected category IDs
        $query = 'SELECT
                    refid
                  FROM
                    '._JOOM_TABLE_MAINTENANCE.'
                  WHERE
                        id IN ('.$cid_string.')
                    AND type != 0';
        $this->_db->setQuery($query);
        if(!$cids = $this->_db->loadResultArray())
        {
          $this->setError($this->_db->getErrorMsg());
          return false;
        }
      }
    }

    $row  = & $this->getTable('joomgallerycategories');

    if(!count($cids))
    {
      $this->setError(JText::_('COM_JOOMGALLERY_COMMON_MSG_NO_CATEGORIES_SELECTED'));
      return false;
    }

    $count = 0;
    $extant_images  = false;
    $extant_subcats = false;

    // Loop through selected categories
    foreach($cids as $cid)
    {
      // Database query to check assigned images to category
      $this->_db->setQuery("SELECT
                              id
                            FROM
                              "._JOOM_TABLE_IMAGES."
                            WHERE
                              catid = ".$cid);
      $images = $this->_db->loadResultArray();

      $continue = false;
      if(count($images))
      {
        $extant_images = true;

        $msg = JText::sprintf('COM_JOOMGALLERY_CATMAN_MSG_CATEGORY_NOT_EMPTY_IMAGES', $cid);
        $this->_mainframe->enqueueMessage($msg, 'notice');
        $continue = true;

        $images_to_delete     = $this->_mainframe->getUserState('joom.maintenance.delete.images');
        $categories_to_delete = $this->_mainframe->getUserState('joom.maintenance.delete.categories');

        if($images_to_delete)
        {
          $this->_mainframe->setUserState('joom.maintenance.delete.images', array_unique(array_merge($images_to_delete, $images)));
        }
        else
        {
          $this->_mainframe->setUserState('joom.maintenance.delete.images', $images);
        }

        if(!$categories_to_delete)
        {
          $categories_to_delete = array(0 => array($cid));
        }
        $this->_mainframe->setUserState('joom.maintenance.delete.categories', $categories_to_delete);
      }

      // Are there any sub-category assigned?
      $this->_db->setQuery("SELECT
                              cid
                            FROM
                              "._JOOM_TABLE_CATEGORIES."
                            WHERE
                              parent_id = ".$cid);
      $categories = $this->_db->loadResultArray();
      if(count($categories))
      {
        $extant_subcats = true;

        $msg = JText::sprintf('COM_JOOMGALLERY_CATMAN_MSG_CATEGORY_NOT_EMPTY_CATEGORIES', $cid);
        $this->_mainframe->enqueueMessage($msg, 'notice');
        $continue = true;

        $categories_to_delete = $this->_mainframe->getUserState('joom.maintenance.delete.categories');

        if($categories_to_delete)
        {
          if(isset($categories_to_delete[$recursion_level]))
          {
            $categories_to_delete[$recursion_level] = array_unique(array_merge($categories_to_delete[$recursion_level], array($cid)));
          }
          else
          {
            $categories_to_delete[$recursion_level] = array($cid);
          }

          if(isset($categories_to_delete[$recursion_level + 1]))
          {
            $categories_to_delete[$recursion_level + 1] = array_unique(array_merge($categories_to_delete[$recursion_level + 1], $categories));
          }
          else
          {
            $categories_to_delete[$recursion_level + 1] = $categories;
          }
        }
        else
        {
          $categories_to_delete = array(0 => array($cid), 1 => $categories);
        }
        $this->_mainframe->setUserState('joom.maintenance.delete.categories', $categories_to_delete);

        // Next level
        JRequest::setVar('cid', $categories);
        $this->deletecategory($recursion_level + 1, false);
      }

      if($continue || $recursion_level)
      {
        continue;
      }

      $error = false;

      $catpath = JoomHelper::getCatPath($cid);
      if(!$this->_deleteFolders($catpath))
      {
        $error = true;
      }

      $row->load($cid);
      if(!$row->delete())
      {
        $error = true;
        JError::raiseWarning(500, $row->getError());
      }

      if(!$error)
      {
        // Category deleted
        $count++;
        $row->reorder('parent_id = '.$row->parent_id);

        // Delete the image in the maintenance table
        $query = "DELETE
                  FROM
                    "._JOOM_TABLE_MAINTENANCE."
                  WHERE
                        refid = ".$cid."
                    AND type != 0";
        $this->_db->setQuery($query);
        if(!$this->_db->query())
        {
          JError::raiseWarning(100, JText::sprintf('COM_JOOMGALLERY_MAIMAN_MSG_NOT_DELETE_CATEGORY_TABLE_ENTRY', $cid));
        }
      }
    }

    if(!$recursion_level && ($extant_images || $extant_subcats))
    {
      $images     = array();
      $img_count  = 0;
      $categories = array();
      $cat_count  = 0;

      $images = $this->_mainframe->getUserState('joom.maintenance.delete.images');
      if($images)
      {
        $img_count  = count($images);
      }

      if($extant_subcats)
      {
        $categories = $this->_mainframe->getUserState('joom.maintenance.delete.categories');
        foreach($categories as $level)
        {
          $cat_count += count($level);
        }
      }

      $msg  = '<br />'.JText::_('COM_JOOMGALLERY_MAIMAN_MSG_DELETECOMPLETELY');
      if($img_count)
      {
        if($img_count == 1)
        {
          $msg .= '<br />'.JText::_('COM_JOOMGALLERY_MAIMAN_MSG_DELETECOMPLETELY_IMAGES_NUMBER_1');
        }
        else
        {
          $msg .= '<br />'.JText::sprintf('COM_JOOMGALLERY_MAIMAN_MSG_DELETECOMPLETELY_IMAGES_NUMBER', $img_count);
        }
      }
      if($cat_count)
      {
        if($cat_count == 1)
        {
          $msg .= '<br />'.JText::_('COM_JOOMGALLERY_MAIMAN_MSG_DELETECOMPLETELY_CATEGORIES_NUMBER_1');
        }
        else
        {
          $msg .= '<br />'.JText::sprintf('COM_JOOMGALLERY_MAIMAN_MSG_DELETECOMPLETELY_CATEGORIES_NUMBER', $cat_count);
        }
      }
      $msg .= '<br /><p>
      <form action="index.php?option='._JOOM_OPTION.'&amp;controller=maintenance&amp;task=deletecompletely" method="post" onsubmit="if(!this.security_check.checked){return false;}">
        <span><input type="checkbox" name="security_check" value="1" /> <input type="submit" value="'.JText::_('COM_JOOMGALLERY_MAIMAN_MSG_DELETECOMPLETELY_BUTTON_LABEL').'" /></span>
      </form>';
      $this->_mainframe->enqueueMessage($msg, 'notice');
    }

    return $count;
  }

  /**
   * Resets aliases of all images and categories
   *
   * @access  public
   * @return  array   An array of result information (image number, category number, result information about migrated dates)
   * @since   1.5.5
   */
  function setAlias()
  {
    $images     = $this->_mainframe->getUserStateFromRequest('joom.setalias.images', 'images', array(), 'array');
    $categories = $this->_mainframe->getUserStateFromRequest('joom.setalias.categories', 'categories', array(), 'array');
    $img_count  = $this->_mainframe->getUserState('joom.setalias.imgcount');
    $cat_count  = $this->_mainframe->getUserState('joom.setalias.catcount');

    $dates      = $this->_mainframe->getUserStateFromRequest('joom.setalias.dates', 'migrate_date_colums', 0, 'int');

    $start      = (JRequest::getBool('images') || JRequest::getBool('categories'));

    // Before first loop check for selected images and categories
    if(isset($images[0]) && strpos(',', $images[0]) !== false)
    {
      $images     = explode(',', $images[0]);
    }
    if(isset($categories[0]) && strpos(',', $categories[0]) !== false)
    {
      $categories = explode(',', $categories[0]);
    }

    if(is_null($img_count) && !count($images))
    {
      $this->_db->setQuery("SELECT
                              id
                            FROM
                              "._JOOM_TABLE_IMAGES
                          );

      if($images = $this->_db->loadResultArray())
      {
        $start = true;
        $this->_mainframe->setUserState('joom.setalias.images', $images);
      }
    }

    if(is_null($cat_count) && !count($categories))
    {
      $this->_db->setQuery("SELECT
                              cid
                            FROM
                              "._JOOM_TABLE_CATEGORIES
                          );

      if($categories = $this->_db->loadResultArray())
      {
        $start = true;
        $this->_mainframe->setUserState('joom.setalias.categories', $categories);
      }
    }

    if(!$images && !$categories)
    {
      $this->setError(JText::_('COM_JOOMGALLERY_COMMON_MSG_NO_IMAGES_NO_CATEGORIES_SELECTED'));
      return array(false);
    }

    // Load refresher
    require_once JPATH_COMPONENT.DS.'helpers'.DS.'refresher.php';

    $refresher = new JoomRefresher(array('remaining' => (count($images) + count($categories)), 'start' => $start));

    $row  = & $this->getTable('joomgalleryimages');

    // Loop through selected images
    foreach($images as $key => $id)
    {
      $row->load($id);

      if($dates && is_numeric($row->imgdate))
      {
        $row->imgdate = date('Y-m-d H:i:s', $row->imgdate);
      }

      $row->check();

      if(!$row->store())
      {
        JError::raiseWarning(100, JText::sprintf('COM_JOOMGALLERY_MAIMAN_MSG_NOT_STORE_IMAGE_DATA', $id));
        $this->_mainframe->setUserState('joom.setalias.images', array());
        $this->_mainframe->setUserState('joom.setalias.categories', array());
        $this->_mainframe->setUserState('joom.setalias.imgcount', null);
        $this->_mainframe->setUserState('joom.setalias.catcount', null);
        return array(false);
      }
      $img_count++;

      unset($images[$key]);

      // Check remaining time
      if(!$refresher->check() && count($images))
      {
        $this->_mainframe->setUserState('joom.setalias.images', $images);
        #$this->_mainframe->setUserState('joom.setalias.categories', $categories);
        $this->_mainframe->setUserState('joom.setalias.imgcount', $img_count);
        #$this->_mainframe->setUserState('joom.setalias.catcount', $cat_count);
        $refresher->refresh(count($images) + count($categories));
      }
    }

    $row  = & $this->getTable('joomgallerycategories');

    //loop through selected categories
    foreach($categories as $key => $id)
    {
      $row->load($id);

      // Trim slashes
      $row->catpath = trim($row->catpath, '/');
      $row->alias = trim($row->alias, '/');
      $row->check();

      if(!$row->store())
      {
        JError::raiseWarning(100, JText::sprintf('COM_JOOMGALLERY_MAIMAN_MSG_NOT_STORE_CATEGORY_DATA', $id));
        $this->_mainframe->setUserState('joom.setalias.images', array());
        $this->_mainframe->setUserState('joom.setalias.categories', array());
        $this->_mainframe->setUserState('joom.setalias.imgcount', null);
        $this->_mainframe->setUserState('joom.setalias.catcount', null);
        return array(false);
      }
      $cat_count++;

      unset($categories[$key]);

      // Check remaining time
      if(!$refresher->check() && count($categories))
      {
        #$this->_mainframe->setUserState('joom.setalias.images', $images);
        $this->_mainframe->setUserState('joom.setalias.categories', $categories);
        #$this->_mainframe->setUserState('joom.setalias.imgcount', $img_count);
        $this->_mainframe->setUserState('joom.setalias.catcount', $cat_count);
        $refresher->refresh(count($categories));
      }
    }

    $this->_mainframe->setUserState('joom.setalias.images', array());
    $this->_mainframe->setUserState('joom.setalias.categories', array());
    $this->_mainframe->setUserState('joom.setalias.imgcount', null);
    $this->_mainframe->setUserState('joom.setalias.catcount', null);

    if($dates)
    {
      JRequest::setVar('images', array());
      $dates = $this->migrateDates();
    }

    return array($img_count, $cat_count, $dates);
  }

  /**
   * Migrates all date columns in the images, comments and name tag tables
   *
   * @access  public
   * @return  boolean True on success, false otherwise
   * @since   1.5.5
   */
  function migrateDates()
  {
    $images   = $this->_mainframe->getUserStateFromRequest('joom.migratedates.images',   'images',   null, 'array');
    $comments = $this->_mainframe->getUserStateFromRequest('joom.migratedates.comments', 'comments', null, 'array');
    $nametags = $this->_mainframe->getUserStateFromRequest('joom.migratedates.nametags', 'nametags', null, 'array');

    // Before first loop check for selected images, comments and nametags
    if(strpos(',', $images[0]) !== false)
    {
      $images = explode(',', $images[0]);
    }
    if(strpos(',', $comments[0]) !== false)
    {
      $comments = explode(',', $comments[0]);
    }
    if(strpos(',', $nametags[0]) !== false)
    {
      $nametags = explode(',', $nametags[0]);
    }

    if(is_null($images))
    {
      $this->_db->setQuery("SELECT
                              id
                            FROM
                              "._JOOM_TABLE_IMAGES
                          );

      if($images = $this->_db->loadResultArray())
      {
        $this->_mainframe->setUserState('joom.migratedates.images', $images);
      }
      else
      {
        JError::raiseError(500, $this->_db->getErrorMsg());
      }
    }

    if(is_null($comments))
    {
      $this->_db->setQuery("SELECT
                              cmtid
                            FROM
                              "._JOOM_TABLE_COMMENTS
                          );

      if($comments = $this->_db->loadResultArray())
      {
        $this->_mainframe->setUserState('joom.migratedates.comments', $comments);
      }
      else
      {
        JError::raiseError(500, $this->_db->getErrorMsg());
      }
    }

    if(is_null($nametags))
    {
      $this->_db->setQuery("SELECT
                              nid
                            FROM
                              "._JOOM_TABLE_NAMESHIELDS
                          );

      if($nametags = $this->_db->loadResultArray())
      {
        $this->_mainframe->setUserState('joom.migratedates.nametags', $nametags);
      }
      else
      {
        JError::raiseError(500, $this->_db->getErrorMsg());
      }
    }

    if(!$images && !$comments && !$nametags)
    {
      $this->setError(JText::_('COM_JOOMGALLERY_MAIMAN_MSG_NO_IMAGES_COMMENTS_NAMETAGS'));
      return false;
    }

    // Load the refresher
    require_once JPATH_COMPONENT.DS.'helpers'.DS.'refresher.php';

    $refresher = new JoomRefresher(array('task' => 'migratedates'));

    $row  = & $this->getTable('joomgalleryimages');

    // Loop through selected images
    foreach($images as $key => $id)
    {
      $row->load($id);

      if(is_numeric($row->imgdate))
      {
        $row->imgdate = date('Y-m-d H:i:s', $row->imgdate);
      }

      $row->check();

      if(!$row->store())
      {
        JError::raiseWarning(100, JText::sprintf('COM_JOOMGALLERY_MAIMAN_MSG_NOT_STORE_IMAGE_DATA', $id));
        $this->_mainframe->setUserState('joom.migratedates.images', array());
        $this->_mainframe->setUserState('joom.migratedates.comments', array());
        $this->_mainframe->setUserState('joom.migratedates.nametags', array());
        return false;
      }

      unset($images[$key]);

      // Check remaining time
      if(!$refresher->check() && count($images))
      {
        $this->_mainframe->setUserState('joom.migratedates.images', $images);
        $refresher->refresh();
      }
    }

    $row  = & $this->getTable('joomgallerycomments');

    // Loop through selected comments
    foreach($comments as $key => $id)
    {
      $row->load($id);

      if(is_numeric($row->cmtdate))
      {
        $row->cmtdate = date('Y-m-d H:i:s', $row->cmtdate);
      }

      $row->check();

      if(!$row->store())
      {
        JError::raiseWarning(100, JText::sprintf('COM_JOOMGALLERY_MAIMAN_MSG_NOT_STORE_COMMENT_DATA', $id));
        $this->_mainframe->setUserState('joom.migratedates.images', array());
        $this->_mainframe->setUserState('joom.migratedates.comments', array());
        $this->_mainframe->setUserState('joom.migratedates.nametags', array());
        return false;
      }

      unset($comments[$key]);

      // Check remaining time
      if(!$refresher->check() && count($comments))
      {
        $this->_mainframe->setUserState('joom.migratedates.comments', $comments);
        $refresher->refresh();
      }
    }

    $row  = & $this->getTable('joomgallerynameshields');

    // Loop through selected nametags
    foreach($nameshields as $key => $id)
    {
      $row->load($id);

      if(is_numeric($row->ndate))
      {
        $row->ndate = date('Y-m-d H:i:s', $row->ndate);
      }

      $row->check();

      if(!$row->store())
      {
        JError::raiseWarning(100, JText::sprintf('COM_JOOMGALLERY_MAIMAN_MSG_NOT_STORE_NAMETAG_DATA', $id));
        $this->_mainframe->setUserState('joom.migratedates.images', array());
        $this->_mainframe->setUserState('joom.migratedates.comments', array());
        $this->_mainframe->setUserState('joom.migratedates.nametags', array());
        return false;
      }

      unset($nametags[$key]);

      // Check remaining time
      if(!$refresher->check() && count($nametags))
      {
        $this->_mainframe->setUserState('joom.migratedates.nametags', $nametags);
        $refresher->refresh();
      }
    }

    $this->_mainframe->setUserState('joom.migratedates.images', null);
    $this->_mainframe->setUserState('joom.migratedates.comments', null);
    $this->_mainframe->setUserState('joom.migratedates.nametags', null);

    // Alter the table colums
    $this->_db->setQuery("ALTER TABLE `"._JOOM_TABLE_IMAGES."` CHANGE `imgdate` `imgdate` DATETIME NOT NULL ");
    if(!$this->_db->query())
    {
      $this->setError($this->_db->getErrorMsg());
      return false;
    }

    $this->_db->setQuery("ALTER TABLE `"._JOOM_TABLE_COMMENTS."` CHANGE `cmtdate` `cmtdate` DATETIME NOT NULL");
    if(!$this->_db->query())
    {
      $this->setError($this->_db->getErrorMsg());
      return false;
    }

    $this->_db->setQuery("ALTER TABLE `"._JOOM_TABLE_NAMESHIELDS."` CHANGE `ndate` `ndate` DATETIME NOT NULL ");
    if(!$this->_db->query())
    {
      $this->setError($this->_db->getErrorMsg());
      return false;
    }

    return true;
  }

  /**
   * Sets a new user as the owner of the selected images
   *
   * @access  public
   * @return  int     Number of successfully edited images, boolean false if an error occured
   * @since   1.5.5
   */
  function setUser()
  {
    $user = JRequest::getInt('newuser', 0);
    $cids = JRequest::getVar('cid', array(), 'post', 'array');

    if(!count($cids))
    {
      $this->setError(JText::_('COM_JOOMGALLERY_COMMON_MSG_NO_IMAGES_SELECTED'));
      return false;
    }

    JArrayHelper::toInteger($cids);
    $cid_string = implode(',', $cids);

    // Get selected image IDs
    $query = 'SELECT
                refid
              FROM
                '._JOOM_TABLE_MAINTENANCE.'
              WHERE
                    id IN ('.$cid_string.')
                AND type = 0';
    $this->_db->setQuery($query);
    if(!$ids = $this->_db->loadResultArray())
    {
      $this->setError($this->_db->getErrorMsg());
      return false;
    }

    $id_string = implode(',', $ids);

    // Set the new user
    $query = 'UPDATE
                '._JOOM_TABLE_IMAGES.'
              SET
                owner = '.$user.'
              WHERE
                id IN ('.$id_string.')';
    $this->_db->setQuery($query);
    if(!$this->_db->query())
    {
      $this->setError($this->_db->getErrorMsg());
      return false;
    }

    // Update maintenance table
    $query = 'UPDATE
                '._JOOM_TABLE_MAINTENANCE.'
              SET
                owner = '.$user.'
              WHERE
                    id IN ('.$cid_string.')
                AND type = 0';
    $this->_db->setQuery($query);
    if(!$this->_db->query())
    {
      $this->setError($this->_db->getErrorMsg());
      return false;
    }

    return count($cids);
  }

  /**
   * Sets a new user as the owner of the selected categories
   *
   * @access  public
   * @return  int     Number of successfully edited categories, boolean false if an error occured
   * @since   1.5.5
   */
  function setCategoryUser()
  {
    $user = JRequest::getInt('newuser', 0);
    $cids = JRequest::getVar('cid', array(), 'post', 'array');

    if(!count($cids))
    {
      $this->setError(JText::_('COM_JOOMGALLERY_COMMON_MSG_NO_CATEGORIES_SELECTED'));
      return false;
    }

    JArrayHelper::toInteger($cids);
    $cid_string = implode(',', $cids);

    // Get selected category IDs
    $query = 'SELECT
                refid
              FROM
                '._JOOM_TABLE_MAINTENANCE.'
              WHERE
                    id IN ('.$cid_string.')
                AND type != 0';
    $this->_db->setQuery($query);
    if(!$ids = $this->_db->loadResultArray())
    {
      $this->setError($this->_db->getErrorMsg());
      return false;
    }

    $id_string = implode(',', $ids);

    // Set the new user
    $query = 'UPDATE
                '._JOOM_TABLE_CATEGORIES.'
              SET
                owner = '.$user.'
              WHERE
                cid IN ('.$id_string.')';
    $this->_db->setQuery($query);
    if(!$this->_db->query())
    {
      $this->setError($this->_db->getErrorMsg());
      return false;
    }

    // Update maintenance table
    $query = 'UPDATE
                '._JOOM_TABLE_MAINTENANCE.'
              SET
                owner = '.$user.'
              WHERE
                    id IN ('.$cid_string.')
                AND type != 0';
    $this->_db->setQuery($query);
    if(!$this->_db->query())
    {
      $this->setError($this->_db->getErrorMsg());
      return false;
    }

    return count($cids);
  }

  /**
   * Deletes one or more orphaned files
   *
   * @access  public
   * @return  int     Number of successfully deleted files, boolean false if an error occured
   * @since   1.5.5
   */
  function deleteOrphan()
  {
    jimport('joomla.filesystem.file');

    $orphans = JRequest::getVar('cid', array(), 'post', 'array');

    if(!count($orphans))
    {
      $this->setError(JText::_('COM_JOOMGALLERY_MAIMAN_MSG_NO_FILES_SELECTED'));
      return false;
    }

    $count = 0;

    $row = &$this->getTable('joomgalleryorphans');

    foreach($orphans as $orphan)
    {
      if(!$row->load($orphan))
      {
        continue;
      }

      if(!JFile::delete($row->fullpath))
      {
        JError::raiseWarning(100, JText::sprintf('COM_JOOMGALLERY_MAIMAN_MSG_DELETE_FILE_VIA_FTP', $row->fullpath));
      }
      else
      {
        if($row->refid)
        {
          $query = "UPDATE
                      "._JOOM_TABLE_MAINTENANCE."
                    SET
                      ".$row->type."orphan = ''
                    WHERE
                          refid = ".$row->refid."
                      AND type = 0";
          $this->_db->setQuery($query);
          if(!$this->_db->query())
          {
            $this->setError($this->_db->getErrorMsg());
            return false;
          }
        }

        if(!$row->delete())
        {
          $this->setError($this->_db->getErrorMsg());
          return false;
        }

        $count++;
      }
    }

    return $count;
  }

  /**
   * Deletes one or more orphaned folders
   *
   * @access  public
   * @return  int     Number of successfully deleted folders, boolean false if an error occured
   * @since   1.5.5
   */
  function deleteOrphanedFolder()
  {
    $folders = JRequest::getVar('cid', array(), 'post', 'array');

    if(!count($folders))
    {
      $this->setError(JText::_('COM_JOOMGALLERY_MAIMAN_OF_MSG_NO_FOLDERS_SELECTED'));
      return false;
    }

    $count = 0;

    $row = &$this->getTable('joomgalleryorphans');

    foreach($folders as $folder)
    {
      if(!$row->load($folder))
      {
        continue;
      }

      if(!JFolder::delete($row->fullpath))
      {
        JError::raiseWarning(100, JText::sprintf('COM_JOOMGALLERY_MAIMAN_OF_MSG_DELETE_FOLDER_VIA_FTP', $row->fullpath));
      }
      else
      {
        if($row->refid)
        {
          $query = "UPDATE
                      "._JOOM_TABLE_MAINTENANCE."
                    SET
                      ".$row->type."orphan = ''
                    WHERE
                          refid = ".$row->refid."
                      AND type != 0";
          $this->_db->setQuery($query);
          if(!$this->_db->query())
          {
            $this->setError($this->_db->getErrorMsg());
            return false;
          }
        }

        if(!$row->delete())
        {
          $this->setError($this->_db->getErrorMsg());
          return false;
        }

        $count++;
      }
    }

    return $count;
  }

  /**
   * Filters all orphaned files with a valid suggestion out of the selected files
   * and calls 'addOrphan()' with these files selected
   *
   * @access  public
   * @return  int     Number of successfully moved files, boolean false if an error occured
   * @since   1.5.5
   */
  function addOrphans()
  {
    $cids = JRequest::getVar('cid', array(), '', 'array');

    if(!count($cids))
    {
      $this->setError(JText::_('COM_JOOMGALLERY_MAIMAN_MSG_NO_FILES_SELECTED'));
      return false;
    }

    $image = &$this->getTable('joomgallerymaintenance');

    $orphans = array();

    foreach($cids as $key => $id)
    {
      if(!$image->load($id))
      {
        JError::raiseNotice(500, JText::sprintf('COM_JOOMGALLERY_MAIMAN_MSG_IMAGE_WITH_ID_NOT_FOUND', $id));
        unset($cids[$key]);
        continue;
      }

      if($image->thumborphan)
      {
        $orphans[]  = $image->thumborphan;
      }
      if($image->imgorphan)
      {
        $orphans[]  = $image->imgorphan;
      }
      if($image->origorphan)
      {
        $orphans[]  = $image->origorphan;
      }
    }

    if(!count($orphans))
    {
      return false;
    }

    JRequest::setVar('cid', $orphans);

    return $this->addOrphan();
  }

  /**
   * Moves all orphaned files to their suggested folder
   *
   * @access  public
   * @return  int     Number of successfully moved files, boolean false if an error occured
   * @since   1.5.5
   */
  function addOrphan()
  {
    $cids = JRequest::getVar('cid', array(), '', 'array');

    if(!count($cids))
    {
      $this->setError(JText::_('COM_JOOMGALLERY_MAIMAN_MSG_NO_FILES_SELECTED'));
      return false;
    }

    $count = 0;

    $orphan = &$this->getTable('joomgalleryorphans');

    foreach($cids as $id)
    {
      if(!$orphan->load($id))
      {
        JError::raiseNotice(500, JText::sprintf('COM_JOOMGALLERY_MAIMAN_MSG_ORPHAN_WITH_ID_NOT_FOUND', $id));
        continue;
      }

      // Check whether an appropriate image was found
      if(!$orphan->refid)
      {
        continue;
      }

      $query = "SELECT
                  refid,
                  thumborphan,
                  imgorphan,
                  origorphan
                FROM
                  "._JOOM_TABLE_MAINTENANCE."
                WHERE
                      refid = ".$orphan->refid."
                  AND type = 0";

      $this->_db->setQuery($query);
      if(!$image = $this->_db->loadObject())
      {
        JError::raiseNotice(500, JText::sprintf('COM_JOOMGALLERY_MAIMAN_MSG_CORRUPT_IMAGE_WITH_ID_NOT_FOUND', $id, $orphan->refid));
        continue;
      }

      if($image->thumborphan == $id)
      {
        $type = 'thumb';
      }
      else
      {
        if($image->imgorphan == $id)
        {
          $type = 'img';
        }
        else
        {
          if($image->origorphan == $id)
          {
            $type = 'orig';
          }
          else
          {
            JError::raiseNotice(500, JText::sprintf('COM_JOOMGALLERY_MAIMAN_MSG_SUGGESTED_IMAGE_NOT_CORRUPT', $id, $orphan->refid));
            continue;
          }
        }
      }

      // Move orphaned file
      jimport('joomla.filesystem.file');

      $src  = $orphan->fullpath;
      $dest = $this->_ambit->getImg($type.'_path', $image->refid);

      if(!JFile::move($src, $dest))
      {
        JError::raiseWarning(500, JText::sprintf('COM_JOOMGALLERY_MAIMAN_MSG_NOT_MOVE_FILE', $src, $dest));
        continue;
      }

      // Update maintenance database tables
      $query = "UPDATE
                  "._JOOM_TABLE_MAINTENANCE."
                SET
                  ".$type." = '".$this->_ambit->getImg($type.'_url', $image->refid)."',
                  ".$type."orphan = 0
                WHERE
                      refid = ".$orphan->refid."
                  AND type = 0";

      $this->_db->setQuery($query);
      if(!$this->_db->query())
      {
        $this->setError($this->_db->getErrorMsg());
        return false;
      }

      if(!$orphan->delete())
      {
        $this->setError($this->_db->getErrorMsg());
        return false;
      }

      $count++;
    }

    return $count;
  }

  /**
   * Filters all orphaned folders with a valid suggestion out of the selected folders
   * and calls 'addOrphanedFolder()' with these folders selected
   *
   * @access  public
   * @return  int     Number of successfully moved folders, boolean false if an error occured
   * @since   1.5.5
   */
  function addOrphanedFolders()
  {
    $cids = JRequest::getVar('cid', array(), '', 'array');

    if(!count($cids))
    {
      $this->setError(JText::_('COM_JOOMGALLERY_MAIMAN_OF_MSG_NO_FOLDERS_SELECTED'));
      return false;
    }

    $category = &$this->getTable('joomgallerymaintenance');

    $orphans = array();

    foreach($cids as $key => $id)
    {
      if(!$category->load($id))
      {
        JError::raiseNotice(500, JText::sprintf('COM_JOOMGALLERY_MAIMAN_OF_MSG_CATEGORY_WITH_ID_NOT_FOUND', $id));
        unset($cids[$key]);
        continue;
      }

      if($category->thumborphan)
      {
        $orphans[]  = $category->thumborphan;
      }
      if($category->imgorphan)
      {
        $orphans[]  = $category->imgorphan;
      }
      if($category->origorphan)
      {
        $orphans[]  = $category->origorphan;
      }
    }

    if(!count($orphans))
    {
      return false;
    }

    JRequest::setVar('cid', $orphans);

    return $this->addOrphanedFolder();
  }

  /**
   * Moves all orphaned folders to their suggested folder if there is a suggestion
   *
   * @access  public
   * @return  int     Number of successfully moved folders, boolean false if an error occured
   * @since   1.5.5
   */
  function addOrphanedFolder()
  {
    $cids = JRequest::getVar('cid', array(), '', 'array');

    if(!count($cids))
    {
      $this->setError(JText::_('COM_JOOMGALLERY_MAIMAN_OF_MSG_NO_FOLDERS_SELECTED'));
      return false;
    }

    $count = 0;

    $orphan = &$this->getTable('joomgalleryorphans');

    foreach($cids as $id)
    {
      if(!$orphan->load($id))
      {
        JError::raiseNotice(500, JText::sprintf('COM_JOOMGALLERY_MAIMAN_OF_MSG_FOLDER_WITH_ID_NOT_FOUND', $id));
        continue;
      }

      // Check whether an appropriate image was found
      if(!$orphan->refid)
      {
        continue;
      }

      $query = "SELECT
                  refid,
                  thumborphan,
                  imgorphan,
                  origorphan
                FROM
                  "._JOOM_TABLE_MAINTENANCE."
                WHERE
                      refid = ".$orphan->refid."
                  AND type != 0";

      $this->_db->setQuery($query);
      if(!$category = $this->_db->loadObject())
      {
        JError::raiseNotice(500, JText::sprintf('COM_JOOMGALLERY_MAIMAN_OF_MSG_CORRUPT_CATEGORY_WITH_ID_NOT_FOUND', $id, $orphan->refid));
        continue;
      }

      if($category->thumborphan == $id)
      {
        $type = 'thumb';
      }
      else
      {
        if($category->imgorphan == $id)
        {
          $type = 'img';
        }
        else
        {
          if($category->origorphan == $id)
          {
            $type = 'orig';
          }
          else
          {
            JError::raiseNotice(500, JText::sprintf('COM_JOOMGALLERY_MAIMAN_OF_MSG_SUGGESTED_CATEGORY_NOT_CORRUPT', $id, $orphan->refid));
            continue;
          }
        }
      }

      // Move orphaned file
      $src  = $orphan->fullpath;
      $dest = $this->_ambit->get($type.'_path').JoomHelper::getCatPath($category->refid);

      if(JFolder::move(JPath::clean($src), JPath::clean($dest)) !== true)
      {
        JError::raiseWarning(500, JText::sprintf('COM_JOOMGALLERY_MAIMAN_OF_MSG_COULD_NOT_MOVE_FOLDER', $src, $dest));
        continue;
      }

      // Update maintenance database tables
      $query = "UPDATE
                  "._JOOM_TABLE_MAINTENANCE."
                SET
                  ".$type."       = '".$dest."',
                  ".$type."orphan = 0
                WHERE
                      refid = ".$orphan->refid."
                  AND type != 0";

      $this->_db->setQuery($query);
      if(!$this->_db->query())
      {
        $this->setError($this->_db->getErrorMsg());
        return false;
      }

      if(!$orphan->delete())
      {
        $this->setError($this->_db->getErrorMsg());
        return false;
      }

      $count++;
    }

    return $count;
  }

  /**
   * Applies all suggestions on the selected orphaned files,
   * either moving to their suggested folder or deleting them.
   *
   * @access  public
   * @return  array   An array of result information (number of moved files, number of deleted files)
   * @since   1.5.5
   */
  function applySuggestions()
  {
    $cids = JRequest::getVar('cid', array(), 'post', 'array');

    if(!count($cids))
    {
      $this->setError(JText::_('COM_JOOMGALLERY_MAIMAN_MSG_NO_FILES_SELECTED'));
      return false;
    }

    $delete = array();
    $move   = array();

    foreach($cids as $id)
    {
      $query = "SELECT
                  refid
                FROM
                  "._JOOM_TABLE_ORPHANS."
                WHERE
                      id = ".intval($id)."
                  AND type != 'folder'";
      $this->_db->setQuery($query);
      $imgid = $this->_db->loadResult();
      if(!is_null($imgid))
      {
        if($imgid)
        {
          $move[]   = $id;
        }
        else
        {
          $delete[] = $id;
        }
      }
    }

    $moved = 0;
    if(count($move))
    {
      JRequest::setVar('cid', $move);
      $moved= $this->addOrphan();
      if($moved === false)
      {
        return false;
      }
    }

    $deleted = 0;
    if(count($delete))
    {
      JRequest::setVar('cid', $delete);
      $deleted = $this->deleteOrphan();
      if($deleted === false)
      {
        return false;
      }
    }

    return array($moved, $deleted);
  }

  /**
   * Applies all suggestions on the selected orphaned folders,
   * either moving to their suggested folder or deleting them.
   *
   * @access  public
   * @return  array   An array of result information (number of moved folders, number of deleted folders)
   * @since   1.5.5
   */
  function applyFolderSuggestions()
  {
    $cids = JRequest::getVar('cid', array(), 'post', 'array');

    if(!count($cids))
    {
      $this->setError(JText::_('COM_JOOMGALLERY_MAIMAN_OF_MSG_NO_FOLDERS_SELECTED'));
      return false;
    }

    $delete = array();
    $move   = array();

    foreach($cids as $id)
    {
      $query = "SELECT
                  refid
                FROM
                  "._JOOM_TABLE_ORPHANS."
                WHERE
                      id = ".intval($id)."
                  AND type = 'folder'";
      $this->_db->setQuery($query);
      $imgid = $this->_db->loadResult();
      if(!is_null($imgid))
      {
        if($imgid)
        {
          $move[]   = $id;
        }
        else
        {
          $delete[] = $id;
        }
      }
    }

    $moved = 0;
    if(count($move))
    {
      JRequest::setVar('cid', $move);
      $moved= $this->addOrphanedFolder();
      if($moved === false)
      {
        return false;
      }
    }

    $deleted = 0;
    if(count($delete))
    {
      JRequest::setVar('cid', $delete);
      $deleted = $this->deleteOrphanedFolder();
      if($deleted === false)
      {
        return false;
      }
    }

    return array($moved, $deleted);
  }

  /**
   * Creates new folders for categories.
   *
   * @access  public
   * @return  int/boolean Number of created folders on success, false otherwise
   * @since   1.5.5
   */
  function create()
  {
    $cids   = JRequest::getVar('cid', array(), '', 'array');
    $types  = JRequest::getVar('type', array('thumb', 'img', 'orig'), '', 'array');

    if(!count($cids))
    {
      $this->setError(JText::_('COM_JOOMGALLERY_COMMON_MSG_NO_CATEGORIES_SELECTED'));
      return false;
    }

    $cid_string = implode(',', $cids);

    // Get selected category IDs
    $query = 'SELECT
                refid
              FROM
                '._JOOM_TABLE_MAINTENANCE.'
              WHERE
                    id IN ('.$cid_string.')
                AND type != 0';
    $this->_db->setQuery($query);
    if(!$ids = $this->_db->loadResultArray())
    {
      $this->setError($this->_db->getErrorMsg());
      return false;
    }

    $count = 0;

    foreach($types as $type)
    {
      foreach($ids as $cid)
      {
        // Create the folder
        $folder = $this->_ambit->get($type.'_path').JoomHelper::getCatPath($cid);

        if(!JFolder::create($folder))
        {
          continue;
        }

        JoomFile::copyIndexHtml($folder);

        // Update maintenance table
        $query = "UPDATE
                    "._JOOM_TABLE_MAINTENANCE."
                  SET
                    ".$type." = '".$this->_db->escape($folder)."'
                  WHERE
                        refid = ".$cid."
                    AND type != 0";
        $this->_db->setQuery($query);
        if(!$this->_db->query())
        {
          $this->setError($this->_db->getErrorMsg());
          return false;
        }

        $count++;
      }
    }

    return $count;
  }

  /**
   * Optimizes all database tables of JoomGallery
   *
   * @access  public
   * @return  boolean True on success, false otherwise
   * @since   1.5.5
   */
  function optimize()
  {
    $query = "OPTIMIZE TABLE
                "._JOOM_TABLE_IMAGES.",
                "._JOOM_TABLE_CATEGORIES.",
                "._JOOM_TABLE_COMMENTS.",
                "._JOOM_TABLE_CONFIG.",
                "._JOOM_TABLE_COUNTSTOP.",
                "._JOOM_TABLE_MAINTENANCE.",
                "._JOOM_TABLE_NAMESHIELDS.",
                "._JOOM_TABLE_ORPHANS.",
                "._JOOM_TABLE_USERS.",
                "._JOOM_TABLE_VOTES;

    $this->_db->setQuery($query);
    if(!$this->_db->query())
    {
      JError::raiseWarning(500, $this->_db->getErrorMsg());
      return false;
    }

    return true;
  }

  /**
   * Loads the images data
   *
   * @access  public
   * @return  array   An array of objects containing the images data from the database
   * @since   1.5.5
   */
  function _loadImages()
  {
    $query = $this->_buildImagesQuery();

    if(!$this->_images = $this->_getList($query, $this->getState('list.start'), $this->getState('list.limit')))
    {
      return false;
    }

    return true;
  }

  /**
   * Loads the categories data
   *
   * @access  public
   * @return  array   An array of objects containing the categories data from the database
   * @since   1.5.5
   */
  function _loadCategories()
  {
    $query = $this->_buildCategoriesQuery();

    if(!$this->_categories = $this->_getList($query, $this->getState('list.start'), $this->getState('list.limit')))
    {
      return false;
    }

    return true;
  }

  /**
   * Loads the data of orphaned files
   *
   * @access  public
   * @return  array   An array of objects containing the data of orphaned files from the database
   * @since   1.5.5
   */
  function _loadOrphans()
  {
    $query = $this->_buildOrphansQuery();

    if(!$this->_orphans = $this->_getList($query, $this->getState('list.start'), $this->getState('list.limit')))
    {
      return false;
    }

    return true;
  }

  /**
   * Loads the data of orphaned folders
   *
   * @access  public
   * @return  array   An array of objects containing the data of orphaned folders from the database
   * @since   1.5.5
   */
  function _loadOrphanedFolders()
  {
    $query = $this->_buildOrphanedFoldersQuery();

    if(!$this->_orphanedfolders = $this->_getList($query, $this->getState('list.start'), $this->getState('list.limit')))
    {
      return false;
    }

    return true;
  }

  /**
   * Loads the information about  inconsitencies
   *
   * @access  public
   * @return  array   An array of information about inconsitencies
   * @since   1.5.5
   */
  function _loadInformation()
  {
    $query = $this->_db->getQuery(true)
          ->select('COUNT(id)')
          ->from(_JOOM_TABLE_MAINTENANCE)
          ->where('type = 0')
          ->where("(thumb = '' OR img = ''".($this->getState('check_originals') ? " OR orig = ''" : '')." OR owner = -1 OR catid = -1)");
    $this->_db->setQuery($query, 0, 1);
    $this->_information['images'] = $this->_db->loadResult();

    $query->clear('where')
          ->where('type != 0')
          ->where("(thumb = '' OR img = '' OR orig = '' OR owner = -1 OR catid = -1)");
    $this->_db->setQuery($query, 0, 1);
    $this->_information['categories'] = $this->_db->loadResult();

    $query->clear('from')
          ->clear('where')
          ->from(_JOOM_TABLE_ORPHANS)
          ->where("type != 'folder'");
    $this->_db->setQuery($query, 0, 1);
    $this->_information['orphans']  = $this->_db->loadResult();

    $query->clear('where')
          ->where("type = 'folder'");
    $this->_db->setQuery($query, 0, 1);
    $this->_information['folders']  = $this->_db->loadResult();

    return true;
  }

  /**
   * Returns the query for listing the images
   *
   * @access  protected
   * @return  string    The query to be used to retrieve the images data from the database
   * @since   1.5.5
   */
  function _buildImagesQuery()
  {
    $query = $this->_db->getQuery(true)
          ->select('a.*, c.name AS category, u.username AS user')
          ->from(_JOOM_TABLE_MAINTENANCE.' AS a')
          ->leftJoin(_JOOM_TABLE_CATEGORIES.' AS c ON a.catid = c.cid')
          ->leftJoin('#__users AS u ON a.owner = u.id')
          ->where('a.type = 0')
          ->where("(a.thumb = '' OR a.img = ''".($this->getState('check_originals') ? " OR a.orig = ''" : '')." OR a.owner = -1 OR a.catid = -1)");

    // Filter by category
    if($category = $this->getState('filter.category'))
    {
      $query->where('a.catid = '.(int) $category);
    }

    // Filter by type
    $type = $this->getState('filter.type');
    switch($type)
    {
      case 1:
        // Only missing thumbnails
        $query->where("a.thumb = ''");
        break;
      case 2:
        // Only missing detail images
        $query->where("a.img = ''");
        break;
      case 3:
        // Only missing original images
        $query->where("a.orig = ''");
        break;
      case 4:
        // Only missing owner
        $query->where('a.owner = -1');
        break;
      case 5:
        // Only missing category
        $query->where('a.catid = -1');
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
        $search = $this->_db->Quote('%'.$this->_db->getEscaped($search, true).'%');
        $query->where('a.title LIKE '.$search);
      }
    }

    // Add the order clause
    $query->order($this->_db->getEscaped($this->getState('list.ordering', 'a.id')).' '.$this->_db->getEscaped($this->getState('list.direction', 'ASC')));

    return $query;
  }

  /**
   * Returns the query for listing the categories
   *
   * @access  protected
   * @return  string    The query to be used to retrieve the categories data from the database
   * @since   1.5.5
   */
  function _buildCategoriesQuery()
  {
    $query = $this->_db->getQuery(true)
          ->select('a.*, c.name AS category, u.username AS user')
          ->from(_JOOM_TABLE_MAINTENANCE.' AS a')
          ->leftJoin(_JOOM_TABLE_CATEGORIES.' AS c ON a.catid = c.cid')
          ->leftJoin('#__users AS u ON a.owner = u.id')
          ->where('type != 0')
          ->where("(a.thumb = '' OR a.img = '' OR a.orig = '' OR a.owner = -1 OR a.catid = -1)");

    // Filter by category
    if($category = $this->getState('filter.category'))
    {
      $query->where('a.catid = '.(int) $category);
    }

    // Filter by type
    $type = $this->getState('filter.type');
    switch($type)
    {
      case 1:
        // Only missing thumbnail folders
        $query->where("a.thumb = ''");
        break;
      case 2:
        // Only missing detail images folders
        $query->where("a.img = ''");
        break;
      case 3:
        // Only missing original images folders
        $query->where("a.orig = ''");
        break;
      case 4:
        // Only missing owner
        $query->where('a.owner = -1');
        break;
      case 5:
        // Only missing parent_category
        $query->where('a.catid = -1');
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
        $search = $this->_db->Quote('%'.$this->_db->getEscaped($search, true).'%');
        $query->where('a.title LIKE '.$search);
      }
    }

    // Add the order clause
    $query->order($this->_db->getEscaped($this->getState('list.ordering', 'a.id')).' '.$this->_db->getEscaped($this->getState('list.direction', 'ASC')));

    return $query;
  }

  /**
   * Returns the query for listing the orphaned files
   *
   * @access  protected
   * @return  string    The query to be used to retrieve the data of the orphaned files from the database
   * @since   1.5.5
   */
  function _buildOrphansQuery()
  {
    $query = $this->_db->getQuery(true)
          ->select('a.*')
          ->from(_JOOM_TABLE_ORPHANS.' AS a')
          ->where("a.type != 'folder'");

    // Filter by proposal
    $proposal = $this->getState('filter.proposal');
    switch($proposal)
    {
      case 1:
        // Only orphans with a proposal
        $query->where('a.refid != 0');
        break;
      case 2:
        // Only orphans without a proposal
        $query->where('a.refid = 0');
        break;
      default:
        // No filter by proposal
        break;
    }

    // Filter by type
    $type = $this->getState('filter.type');
    switch($type)
    {
      case 1:
        // Only thumbnails
        $query->where("a.type = 'thumb'");
        break;
      case 2:
        // Only detail images
        $query->where("a.type = 'img'");
        break;
      case 2:
        // Only original images
        $query->where("a.type = 'orig'");
        break;
      case 2:
        // Only unknown file types
        $query->where("a.type = 'unknown'");
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
        $search = $this->_db->Quote('%'.$this->_db->getEscaped($search, true).'%');
        $query->where('a.fullpath LIKE '.$search);
      }
    }

    // Add the order clause
    $query->order($this->_db->getEscaped($this->getState('list.ordering', 'a.id')).' '.$this->_db->getEscaped($this->getState('list.direction', 'ASC')));

    return $query;
  }

  /**
   * Returns the query for listing the orphaned folders
   *
   * @access  protected
   * @return  string    The query to be used to retrieve the data of the orphaned folders from the database
   * @since   1.5.5
   */
  function _buildOrphanedFoldersQuery()
  {
    $query = $this->_db->getQuery(true)
          ->select('a.*')
          ->from(_JOOM_TABLE_ORPHANS.' AS a')
          ->where("a.type = 'folder'");

    // Filter by proposal
    $proposal = $this->getState('filter.proposal');
    switch($proposal)
    {
      case 1:
        // Only orphans with a proposal
        $query->where('a.refid != 0');
        break;
      case 2:
        // Only orphans without a proposal
        $query->where('a.refid = 0');
        break;
      default:
        // No filter by proposal
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
        $search = $this->_db->Quote('%'.$this->_db->getEscaped($search, true).'%');
        $query->where('a.fullpath LIKE '.$search);
      }
    }

    // Add the order clause
    $query->order($this->_db->getEscaped($this->getState('list.ordering', 'a.id')).' '.$this->_db->getEscaped($this->getState('list.direction', 'ASC')));

    return $query;
  }

  /**
   * Deletes folders of an existing category
   *
   * @access  protected
   * @param   string    $catpath  The catpath of the category
   * @return  boolean   True on success, false otherwise
   * @since   1.5.5
   */
  function _deleteFolders($catpath)
  {
    if(!$catpath)
    {
      return true;
    }

    $orig_path  = JPath::clean($this->_ambit->get('orig_path').$catpath);
    $img_path   = JPath::clean($this->_ambit->get('img_path').$catpath);
    $thumb_path = JPath::clean($this->_ambit->get('thumb_path').$catpath);

    $error = false;

    // Delete the folder of the category for the original images
    if(JFolder::exists($orig_path) && !JFolder::delete($orig_path))
    {
      $error = true;
      JError::raiseWarning(500, JText::sprintf('COM_JOOMGALLERY_MAIMAN_MSG_ERROR_DELETING_DIRECTORY', $orig_path));
    }

    // Delete the folder of the category for the detail images
    if(JFolder::exists($img_path) && !JFolder::delete($img_path))
    {
      $error = true;
      JError::raiseWarning(500, JText::sprintf('COM_JOOMGALLERY_MAIMAN_MSG_ERROR_DELETING_DIRECTORY', $img_path));
    }

    // Delete the folder of the category for the thumbnails
    if(JFolder::exists($thumb_path) && !JFolder::delete($thumb_path))
    {
      $error = true;
      JError::raiseWarning(500, JText::sprintf('COM_JOOMGALLERY_MAIMAN_MSG_ERROR_DELETING_DIRECTORY', $thumb_path));
    }

    if($error)
    {
      return false;
    }

    return true;
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