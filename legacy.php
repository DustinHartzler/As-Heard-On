<?php
// update version in options table
  delete_option("ppg_version");
  add_option("ppg_version", "0.5");

  $aho_old = get_option('ppg_version');
  if ($aho_old == '0.5') {
      add_option('aho_version',  "2.0" );
      //delete_option("ppg_version");

      $aho_showlink     = get_option('showlink');
      $aho_linktext     = get_option('linktext');
	  $aho_linkurl		= get_option('linkurl');
      $aho_imgwidth     = get_option('image_width');
      $aho_imgheight    = get_option('image_height');
      $aho_opacity      = get_option('opacity');
      $aho_setlimit     = get_option('setlimit');
      $aho_imgalign     = get_option('aho_imgalign');
      $aho_imgdisplay   = get_option('imgdisplay');
      $aho_imgmaxheight = get_option('imgmax');

	  //Changed variable names in 2.0
	  $aho_sorder       = get_option('sorder');
	  if ($aho_sorder == 'testid ASC') {
		  $aho_sorder = 'asc';
	  } elseif ($aho_sorder == 'testid DESC') {
		  $aho_sorder = 'desc';
	  } else { $aho_sorder = 'user';}

	  $aho_deldata      = get_option('deldata');
	  if ($aho_deldata == 'yes'){
		  $aho_deldata = 1;
	  }

      $aho_widget = array('showlink'       => $aho_showlink,
                          'linktext'       => $aho_linktext,
						  'linkurl'		   => $aho_linkurl,
                          'image_width'    => $aho_imgwidth,
                          'image_height'   => $aho_imgheight,
						  'opacity'        => $aho_opacity,
						  'setlimit'       => $aho_setlimit
                    );
	   $aho_page  = array('sorder'         => $aho_sorder,
						  'imgalign'       => $aho_imgalign,
						  'imgdisplay'     => $aho_imgdisplay,
						  'imgmax'         => $aho_imgmaxheight,
					  	  'deldata'        => $aho_deldata
					  );

	  add_option('aho_widget',  $aho_widget );
	  add_option('aho_page',  $aho_page );

	  // Remove old unnecessary options
      delete_option('sfs_admng');
	  delete_option('admng');
      delete_option('sfs_deldata');
      delete_option('sfs_setlimit');
      delete_option('sfs_linktext');
      delete_option('sfs_linkurl');
      delete_option('sfs_showlink');
      delete_option('showlink');
      delete_option('sfs_imgalign');
      delete_option('sfs_sorder');
	  delete_option('aho_imgalign');
      delete_option('imgdisplay');
      delete_option('imgmax');
      delete_option('sorder');
      delete_option('deldata');
      delete_option('adming');
      delete_option('showlink');
      delete_option('linktext');
      delete_option('image_width');
      delete_option('image_height');
      delete_option('opacity');
      delete_option('setlimit');
      delete_option('linkurl');
	  delete_option('ppg_version');
    }
