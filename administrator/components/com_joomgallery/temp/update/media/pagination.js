// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-1.5/Module/JoomImages/branches/JGMVC/helper.php $
// $Id: helper.php 2058 2010-04-10 09:40:15Z aha $
/**
* Module JoomImages 1.5
* by JoomGallery::Project Team
* @package JoomGallery
* @Copyright JoomGallery team
* @ All rights reserved
* @ Joomla Open Source is Free Stuff
* @ Released under GnuGPL License.
**/
var JoomImgPagination = new Class({
  options:
  {
      moduleid: 1,
      pagpersite: 2,
      csstag: ''
  },
  initialize: function(options)
  {
    this.setOptions(options);
    this.pagination = $(this.options.csstag+'pagnavi');

    // Container of including visible images
    this.images = $$('div.'+this.options.csstag+'imgct');
    this.imgct = this.images.length;

    // Container including hidden images
    this.hiddenimages = $$('div.'+this.options.csstag+'pagelem');
    this.hiddenimgct = this.hiddenimages.length;

    // *** Events ***
    // Active pagination link(s)
    this.paglinks= $$('span.'+this.options.csstag+'paglink','span.'+this.options.csstag+'paglinkactive');
    this.paglinks.each(function(link)
    {
      // Mouse hover, not for active link
      link.addEvent('mouseover',function(event){
        if (link.className.indexOf('paglinkactive')==-1)
        {
          this.setStyle('cursor','pointer');
        }
      });
      // Click
      link.addEvent('click',function(event){
        if (link.className.indexOf('paglinkactive')==-1)
        {
          // Get the target element, different in IE
          var target = (event.target) ? event.target.textContent : event.srcElement.innerText;
          destpage = parseInt(target);

          // Get current page
          activepaglink = $$('span.'+this.options.csstag+'paglinkactive')[0];
          activepage    = parseInt(activepaglink.innerHTML);

          // Calculate the starting hidden image
          starthdimage = (destpage -1) * this.options.pagpersite + 1;
          endhdimage   = starthdimage + this.options.pagpersite;
          if (endhdimage > this.hiddenimgct)
          {
            endhdimage = this.hiddenimgct;
          }
          // Change the elements
          tgtimg = 0;
          for (srcimg = starthdimage, tgtimg=0; srcimg <= endhdimage; srcimg++, tgtimg++)
          {
            // Check first if target container exists
            if ($chk(this.images[tgtimg]))
            {
              // Calculate the container for saveback
              saveback = this.hiddenimages[((activepage-1) * this.options.pagpersite) + tgtimg];
              // call save back container, copy from container, copy to container
              this.changeelemes(saveback, this.hiddenimages[srcimg-1], this.images[tgtimg]);
              this.images[tgtimg].setStyle('display','inline');
            }
          }

          // Hide non used containers and calculate again the saveback
          for (x=tgtimg; x <= this.options.pagpersite-1; x++)
          {
            this.images[x].setStyle('display','none');
            saveback = this.hiddenimages[((activepage-1) * this.options.pagpersite) + x];
            // Move the source back to hidden elements
            this.savebackelemes(this.images[x], saveback);
          }
          // Change class for new active link
          activepaglink.className=this.options.csstag+'paglink';

          // IE
          if (typeof event.target =='undefined')
          {
            event.srcElement.className = this.options.csstag+'paglinkactive';
          }
          else
          {
            event.target.className = this.options.csstag+'paglinkactive';
          }
        }
      }.bind(this));
    }.bind(this));
    // Build elements at first site
    starthdimage = 1;
    endhdimage = this.options.pagpersite;
    for (srcimg = starthdimage, tgtimg=0; srcimg <= endhdimage; srcimg++,tgtimg++)
    {
      // Check first if target container exists
      if ($chk(this.images[tgtimg]))
      {
        source = this.hiddenimages[srcimg-1];
        target = this.images[tgtimg];
        childrensource = source.getChildren();
        childrentarget = target.getChildren();

        // Move link with image
        if($chk(childrentarget[0]) && $chk(childrensource[0]))
        {
          childrentarget[0].replaceWith(childrensource[0]);
        }

        // Move text tags
        if($chk(childrentarget[1]) && $chk(childrensource[1]))
        {
          childrentarget[1].replaceWith(childrensource[1]);
        }
        this.images[tgtimg].setStyle('display','inline');
      }
    }
  },
  changeelemes: function(saveback, source, target)
  {
    // Get all children of source and target
    // returns array with element [0] = div for image
    // and [1] = div for text, maybe not existent
    childrensource   = source.getChildren();
    childrentarget   = target.getChildren();

    if($chk(saveback))
    {
      this.savebackelemes(target, saveback);
    }
    // Replace the new elements in target
    if($chk(childrensource[0]))
    {
      childrensource[0].inject(target);
    }
    if($chk(childrensource[1]))
    {
      childrensource[1].inject(target);
    }
  },
  savebackelemes: function(savefrom, saveto)
  {
    childrenfrom   = savefrom.getChildren();
    // Move the source back to hidden elements
    if($chk(childrenfrom[0]))
    {
      childrenfrom[0].inject(saveto);
    }
    if($chk(childrenfrom[1]))
    {
      childrenfrom[1].inject(saveto);
    }
  }
});
JoomImgPagination.implement(new Options);
