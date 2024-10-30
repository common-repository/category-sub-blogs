<?php
/*
Plugin Name: Category Sub-Blogs
Plugin URI: 
Description: Categories behave as independant sub-blogs. Provides closure for search, tag filtration etc.
             This allows for a full sub-blog look & feel, as you can already customize category themes.
	     Please see full plugin description at http://wordpress.org/extend/plugins/category-sub-blogs/ .
Version: 0.1.1
Author: Michael Shynar
Author URI: http://shmichael.com
*/	

	Class csb
	{
	  var $logging = false;
	  
	  function csb() {
	    $this->csb_log('CLASS CREATED');

	    // when searching, filtering by tag or viewing an individual post, filter out posts not in the active
	    // category.
	    add_filter('posts_join', array(&$this, 'csb_join'));
	    add_filter('posts_where', array(&$this, 'csb_where'));
	    
	    // Replace the title of the blog to the category name
	    add_filter('wp_title',array(&$this, 'csb_title'));
	    
	    // Alter various links to reflect the change.
	    add_filter('bloginfo',array(&$this, 'csb_bloginfo'), 2, 2);
	    add_filter('feed_link', array(&$this, 'csb_custom_feed_link'),2,2);
	    add_filter('post_link', array(&$this, 'csb_post_link'),2,2);
	    add_filter('option_home', array(&$this, 'csb_option_home'));
	    
	    // Fix page links, as our altering breaks them.
	    add_filter('_get_page_link',array(&$this, 'csb_page_link'),2,2);
	  }
	  
	  // This bit of code was copied from Search Everything. http://wordpress.org/extend/plugins/search-everything/
	  function csb_log($msg)
	  {

		  if ($this->logging)
		  {
			  $fp = fopen("logfile.log","a+");
			  if ( !$fp )
			  {
				  echo 'Unable to write to log file!';
			  }
			  $date = date("Y-m-d H:i:s ");
			  $source = "CSB: ";
			  fwrite($fp, $date.": ".$source.": ".$msg."\n");
			  fclose($fp);
		  }
		  return true;
	  }
	  
	  function csb_parse_request()
	  {
	    $this->csb_log('CSB_REQUEST: Current session category is: '. $_SESSION['csb_category']);
	    
	    // There is no "clean" way of getting the "right" category for the currently displayed post.
	    // The only approach is to deduce it from the URL, then store it for future use.
	    // This piece of code I took from the GYS-Themed-Categories plugin. http://get-your-stuff.com/gys-themed-categories-20.html
	    $cid=0;
	    $perms=get_option('permalink_structure');
	    
	    if($perms){
		    // get current URL if permalinks are set
		    $s=empty($_SERVER['HTTPS'])?'':$_SERVER['HTTPS']=='on'?'s':'';
		    $protocol='http'.$s;
		    $port=$_SERVER['SERVER_PORT']=='80'?'':':'.$_SERVER['SERVER_PORT'];
		    $url=$protocol.'://'.$_SERVER['SERVER_NAME'].$port.$_SERVER['REQUEST_URI'];
		    $this->csb_log('CSB_REQUEST: Pretty permalink URL :'.$url);
		    list($url)=explode('?',$url);

		    // get Category ID from URL
		    list($url)=explode('/page/',$url); // <- added for paging compatibility
		    $cid=get_category_by_path($url,false);
		    $cid=$cid->cat_ID;
	    }else{
		    // no permalinks so we simply check GET vars
		    $cid=$_GET['cat']+0;
	    }
	    
	    $this->csb_log('CSB_REQUEST: URL parse cat id: '.$cid);
	    
	    if (isset($cid))
	    {
	      $_SESSION['csb_category'] = $cid;
	      
	      $cat = get_category($_SESSION['csb_category']);
	      $_SESSION['csb_category_slug'] = $cat->slug;
	      
	      $this->csb_log("CSB_REQUEST: Set category id to " .$_SESSION['csb_category']. "and slug to ".$_SESSION['csb_category_slug']);
	      
	      $child_categories = get_categories(array('child_of' => $_SESSION['csb_category']));
	      $child_ids = array($_SESSION['csb_category']);
	      
	      foreach ($child_categories as $child)
	      {
		$child_ids[] = $child->cat_ID;
	      }
	      
	      $_SESSION['csb_child_ids'] = implode($child_ids,",");
	      $this->csb_log("CSB_REQUEST: Child IDs: ".$_SESSION['csb_child_ids']);	    
	    } 
	    elseif (is_front_page()) 
	    { 
	      $this->csb_log("CSB_REQUEST: Front page. Unsetting session variables.");
	      unset($_SESSION['csb_category']);
	      unset($_SESSION['csb_category_slug']);
	      unset($_SESSION['csb_child_ids']);
	    } 
	    else // Just for debugging purposes
	    { 
	      $this->csb_log("CSB_REQUEST: No category deduced. Changing nothing!");
	    }
	  }
	  
	  function csb_join($join)
	  {
	    global $wp_query, $wpdb;
	    
	    $this->csb_parse_request();
	    
	    if (isset($_SESSION['csb_category']))
	    {
	      $join .= " LEFT JOIN $wpdb->term_relationships AS crel ON ($wpdb->posts.ID = crel.object_id) LEFT JOIN $wpdb->term_taxonomy AS ctax ON (ctax.taxonomy = 'category' AND crel.term_taxonomy_id = ctax.term_taxonomy_id) ";
	      // Add this to get all the extra category data: "LEFT JOIN $wpdb->terms AS cter ON (ctax.term_id = cter.term_id) "; 
	    }
	  
	    $this->csb_log('CSB_JOIN: '.$join);
	    return $join;
	  }
	  
	  function csb_where($where)
	  {
	    global $wpdb;
	    
	    $this->csb_parse_request();
	    
	    if (isset($_SESSION['csb_category']))
	    {
	      $where .= " AND ctax.term_id in (".$_SESSION['csb_child_ids'].")";
	      $this->csb_log('CSB_WHERE: cat_id is set!');
	    }
	    $this->csb_log('CSB_WHERE: '.$where);
	    return $where;
	  }
	  
	  function csb_custom_feed_link($output, $feed_type) 
	  {
	    
	    $this->csb_log("CSB_FEED_LINK: output: ".$output);
	    $this->csb_log("CSB_FEED_LINK: feed_type: ".$feed_type);
	    
	    if (isset($_SESSION['csb_category']))
	    {
	      $output .= "/?cat=" . $_SESSION['csb_category'];
	    }
	    
	    //$feed_url = 'http://feeds.feedburner.com/justintadlock';

	    //$feed_array = array('rss' => $feed_url, 'rss2' => $feed_url, 'atom' => $feed_url, 'rdf' => $feed_url, 'comments_rss2' => '');
	    //$feed_array[$feed] = $feed_url;
	    //$output = $feed_array[$feed];

	    $this->csb_log("CSB_FEED_LINK: final: ".$output);

	    return $output;
	  }
	  
	  function csb_title($title)
	  {
	    $this->csb_parse_request();
	    
	    if (isset($_SESSION['csb_category_slug']))
	    {
	      $title = $_SESSION['csb_category_slug'] . ": ". $title;
	    }
	   
	    $this->csb_log('CSB_TITLE: Returned title is: '. $title);
	   
	    return $title;
	  }
	  
	  function csb_bloginfo($value, $show)
	  {
	    $this->csb_log('CSB_BLOGINFO: show: '. $show .'; value: '. $value);
	    
	    if (isset($_SESSION['csb_category_slug']))
	    {
	      if ($show == 'name')
	      {
		$value = $_SESSION['csb_category_slug'];
	      }
	      
	      if ($show == 'description')
	      {
		$value = "A part of ". get_bloginfo('name');
	      }
	      
	      $this->csb_log('CSB_BLOGINFO: new value is '. $value);
	    }
	    return $value;
	  }
	  
	  function csb_post_link($post_url, $post_options)
	  {
	    $this->csb_log('CSB_POST_LINK: url: '. $post_url);
	    $perms=get_option('permalink_structure');
	    $this->csb_log($perms);
	    return $post_url;
	  }
	  
	  // WP's get_category_link relies on get_option('home'). This is unfortunate, as we need it when
	  // filtering get_option('home'). So, this is a copy-paste-hack implementation.
	  function csb_get_category_link($category_id, $home)
	  {
	    global $wp_rewrite;
	    $catlink = $wp_rewrite->get_category_permastruct();
	    
	    if ( empty( $catlink ) ) {
	      $file = $home . '/';
	      $catlink = $file . '?cat=' . $category_id;
	    } else {
	      $category = &get_category( $category_id );
	      if ( is_wp_error( $category ) )
		return $category;
	      $category_nicename = $category->slug;

	      if ( $category->parent == $category_id ) // recursive recursion
		$category->parent = 0;
	      elseif ($category->parent != 0 )
		$category_nicename = get_category_parents( $category->parent, false, '/', true ) . $category_nicename;
    
	      $catlink = str_replace( '%category%', $category_nicename, $catlink );
	      $catlink = $home . user_trailingslashit( $catlink, 'category' );
	    }
	    return $catlink;
	  }
	  
	  function csb_option_home($home)
	  {
	    $_SESSION['csb_home'] = $home;
	    $home = isset($_SESSION['csb_category']) ? $this->csb_get_category_link($_SESSION['csb_category'], $home) : $home;	    
	    $this->csb_log("CSB_OPTION_HOME: url is ". $home);
	    
	    return $home;
	  }
	  
	  function csb_page_link($page_link, $page_id)
	  {
	    $page_link = str_replace(get_option('home'), $_SESSION['csb_home'], $page_link);
	    return $page_link;
	  }
	}
	
	$CSB = new csb();
?>