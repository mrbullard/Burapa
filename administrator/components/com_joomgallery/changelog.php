<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-2.0/JG/trunk/administrator/components/com_joomgallery/changelog.php $
// $Id: changelog.php 3681 2012-03-04 15:59:38Z erftralle $
/******************************************************************************\
**   JoomGallery 2                                                            **
**   By: JoomGallery::ProjectTeam                                             **
**   Copyright (C) 2008 - 2012  JoomGallery::ProjectTeam                      **
**   Based on: JoomGallery 1.0.0 by JoomGallery::ProjectTeam                  **
**   Released under GNU GPL Public License                                    **
**   License: http://www.gnu.org/copyleft/gpl.html or have a look             **
**   at administrator/components/com_joomgallery/LICENSE.TXT                  **
\******************************************************************************/

defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );
?>

CHANGELOG JOOMGALLERY (since Version 2.0.0 BETA)

Legende / Legend:

* -> Security Fix
# -> Bug Fix
+ -> Addition
^ -> Change
- -> Removed
! -> Note

===============================================================================
                                        2.0.0
                                      (20120304)
===============================================================================
20120227
# Sometimes wrong thumbnail links in gallery and category view if 'Skip category
  view' and manual thumbnail selection was configured

20120216
# With specific server configurations the time out for the refresher was too long

20120211
# In category view the image sorting by user did not work across all image pages

20120207
# in_hidden flag of categories was not always set correctly

20120205
^ CSS definition of different tool tip styling changed for better integration

20120127
^ Prevent bots from voting in case of star rating without AJAX

===============================================================================
                                      2.0.0 BETA5
                                      (20120127)
===============================================================================
20120127
# Delete created database entry of new category if creation of folders fails
+ Decide dynamically whether detail or original image should be linked when
  thumbs are displayed with the interface

20120115
- Last references to 'ordering' column of categories table removed
# Access level of images has not been checked in search query

20120113
# Access level of images has not been checked in top list queries

20120112
# Some erroneous quotation marks were inserted with the code of images when using JoomBu
# Changes for following 'Strict Standards'

===============================================================================
                                      2.0.0 BETA4
                                      (20111224)
===============================================================================
20111224
# Missing access level checks in gallery, category and detail view

20111219
# Missing database commands in delete function of front end edit model, therefore
  image files where not deleted on filesystem

20111215
# wrong information of automatic detection of Image Magick if the path has been
  setted manually before

20111211
# Frontend upload limit check was done in backend
# Redirect in Frontend JAVA-Upload not working

20111209
# Add default charset=utf to all tables in the installation SQL file removing
  ENGINE=MyISAM because it's not necessary anymore
^ If only the correspondent category is hidden (and not also the images in it)
  all images of the category will be displayed in detail view now (they can be
  hidden there by hide the single images in image manager)
^ Colon added to COM_JOOMGALLERY_TOPLIST_TOP instead of hard coded output

20111208
# PHP error if creating favourites zip download file failed,
  404 page, if original images where missing while creating archive file for
  favourites zip download
# Output of obsolete html code in category view if no textelements and icons are
  activated

20111123
# Rating not working, because Mootools library was not loaded for some
  configuration cases

20111122
# Small ACL bugs in fronted fixed
# Report could not be sent by unregistered users

20111119
# (Sub)Category thumbnails in category view did not display always correctly
  if manual setting has been selected

20111118
# Limiting the image preview in image manager (edit mode) to a maximum width

20111117
# Thickbox did not work in IE 8

===============================================================================
                                      2.0.0 BETA3
                                      (20111115)
===============================================================================

20111115
# Nested Set tree was not build correctly when moving a category into a category
  which doesn't already have sub-categories

20111114
# AJAX rating not working if display of rating in detail view (image information)
  not enabled

20111113
+ Changing access level for multiple images in the back end

20111108
# Inserting images from MiniJoom wasn't possible after an Ajax request was done

20111107
# SEF of images shouldn't be enabled by default

20111104
# Bug in MiniJoom due to which upload in frontend wasn't possible if upload
  categories were specified

20111102
# Alphabetical sorting of categories in category view

20111101
+ Usability improvements in category, image, comments and maintenance manager

===============================================================================
                                      2.0.0 BETA2
                                      (20111031)
===============================================================================
20111030
+ Improvement in migration manager so that category ordering isn't lost during migration

20111025
# Wrong language constants corrected and missing constants added

20111022
# Missing language constants added

20111021
# Tooltips were not working if 'Enabled with different styling' in configuration
  manager
# Language output in configuration manager for joom_settings.css corrected
# Hits haven't been counted in default detail view if 'Use real paths' was enabled
# Small fixes in the interface

===============================================================================
                                      2.0.0 BETA
                                      (20111016)
===============================================================================
20110917
^ new JAVA-Applet 5.0.5 Build 1566
20110714
+ Options 'Image title/description in DHTML container' renamed to
  'Image title/description in popup' -> functionality enlarged to all boxes
20110630
+ more options for accordion
+ new option 'skip category view'
20110622
+ Batchupload allows now any archive types defined in Joomla!
20110515
^ new jquery version 1.6.1 for thickbox3 because of problems with IE9 and DOMready()
  in older jquery, small changes in thickbox.js
