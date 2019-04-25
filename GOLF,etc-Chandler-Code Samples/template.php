<?php
// Auto-rebuild the theme registry during theme development.
if (theme_get_setting('basic_rebuild_registry')) {
    drupal_rebuild_theme_registry();
}

// Add Zen Tabs styles
if (theme_get_setting('basic_zen_tabs')) {
    drupal_add_css(drupal_get_path('theme', 'basic') . '/css/tabs.css', 'theme', 'screen');
}

// Add Jquery UI
if (module_exists('jquery_ui')) {
    jquery_ui_add(array('ui.tabs'));
}

/**
 * Convert a string into a SEO friendly string
 *
 * @param type $string
 * @return type
 */
function getSEOFormattedString($string) {
    $map = array(
        '-' => '_',
        ' ' => '-',
        '&' => 'and'
    );
    ##TODO: doing 4 preg_replaces is VERY expensive.
    $string = strtr($string, $map);
    $string = preg_replace("`\[.*\]`U", "", $string);
    $string = preg_replace('`&(amp;)?#?[a-z0-9]+;`i', '-', $string);
    $string = htmlentities($string, ENT_COMPAT, 'utf-8');
    $string = preg_replace("`&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);`i", "\\1", $string);
    $string = preg_replace(array("`[^a-z0-9]`i", "`[-]+`"), "-", $string);
    return strtolower(trim($string, '-'));
}

/*
 * 	 This function creates the body classes that are relative to each page
 *
 * 	@param $vars
 * 	  A sequential array of variables to pass to the theme template.
 * 	@param $hook
 * 	  The name of the theme function being called ("page" in this case.)
 */

function basic_preprocess_page(&$vars, $hook) {
    //Add drupal js setting for CVP player
    drupal_add_js(array('nid' => $vars['node']->nid), 'setting');

		$vars['page_header'] = theme('page_header');
		$vars['page_topads'] = theme('page_topads');
		$vars['page_bottomads'] = theme('page_bottomads');
		$vars['page_bottom_row_ads'] = theme('page_bottom_row_ads');

     // Don't display empty help from node_help().
    if ($vars['help'] == "<div class=\"help\"><p></p>\n</div>") {
        $vars['help'] = '';
    }

    // Classes for body element. Allows advanced theming based on context
    // (home page, node of certain type, etc.)
    $body_classes = array($vars['body_classes']);
    if (user_access('administer blocks')) {
        $body_classes[] = 'admin';
    }
    if (theme_get_setting('basic_wireframe')) {
        $body_classes[] = 'with-wireframes'; // Optionally add the wireframes style.
    }
    if (!empty($vars['primary_links']) or !empty($vars['secondary_links'])) {
        $body_classes[] = 'with-navigation';
    }
    if (!empty($vars['secondary_links'])) {
        $body_classes[] = 'with-secondary';
    }
    if (module_exists('taxonomy') && $vars['node']->nid) {
        foreach (taxonomy_node_get_terms($vars['node']) as $term) {
            $body_classes[] = 'tax-' . eregi_replace('[^a-z0-9]', '-', $term->name);
        }
    }
    if (!$vars['is_front']) {
        // Add unique classes for each page and website section
        $path = drupal_get_path_alias($_GET['q']);
        list($section, ) = explode('/', $path, 2);
        $body_classes[] = basic_id_safe('page-' . $path);
        $body_classes[] = basic_id_safe('section-' . $section);

        if (arg(0) == 'node') {
            if (arg(1) == 'add') {
                if ($section == 'node') {
                    array_pop($body_classes); // Remove 'section-node'
                }
                $body_classes[] = 'section-node-add'; // Add 'section-node-add'
            } elseif (is_numeric(arg(1)) && (arg(2) == 'edit' || arg(2) == 'delete')) {
                if ($section == 'node') {
                    array_pop($body_classes); // Remove 'section-node'
                }
                $body_classes[] = 'section-node-' . arg(2); // Add 'section-node-edit' or 'section-node-delete'
            }
        }
    }

      /* Add template suggestions based on content type
     * You can use a different page template depending on the
     * content type or the node ID
     * For example, if you wish to have a different page template
     * for the story content type, just create a page template called
     * page-type-story.tpl.php
     * For a specific node, use the node ID in the name of the page template
     * like this : page-node-22.tpl.php (if the node ID is 22)
     */

    if ($vars['node']->type != "") {
        $vars['template_files'][] = "page-type-" . $vars['node']->type;
    }
    if ($vars['node']->nid != "") {
        $vars['template_files'][] = "page-node-" . $vars['node']->nid;
    }
    if ($vars['node']->path != "") {
        $vars['template_files'][] = "page-" . $vars['node']->path;
    }
    if (preg_match('#^(leaderboard|video|photos|statistics|statistics-champions|statistics-nationwide)#', $vars['node']->path) ||
            preg_match('#^(leaderboard|video|photos|statistics|statistics-champions|statistics-nationwide)#', $_GET['q'])) {
        $body_classes[] = 'no-leaderboard-tabs';
    }

    if ($vars['node']->type == 'course') {
        $vars['head'] .= '<script type="text/javascript" src="http://i.cdn.turner.com/dr/pga/sites/default/modules/dev/pga_weather/csiManager.js"></script>';
    }

    $vars['body_classes'] = implode(' ', $body_classes); // Concatenate with spaces

    /* General */
    $tpath = drupal_get_path('theme', 'basic');
    $fpath = base_path() . $tpath;

    $vars['theme_img_path'] = $tpath . '/images/';
    $vars['T1_type'] = variable_get('switch_front_page', array('T1'));
    $vars['share'] = theme('page_menu_share');

    // Just for test - need to remove this
    $vars['ad_top_content'] = '';
    $vars['ad_top_content'] .= '';
    $vars['ad_right_content'] = '';

    $vars['page_footer'] = theme('page_footer');

    switch ($vars['node']->type){
        case 'topic_page':
            if (arg(3)) {
                $vars['template_files'][] = 'page';
            }
            include_once('template-topic-page.inc');
            _template_preprocess_page_topic_page($vars);
            break;
        case 'special_topic_page':
            if (arg(3)) {
                $vars['template_files'][] = 'page';
            }
            include_once('template-special-topic-page.inc');
            _template_preprocess_page_special_topic_page($vars);
            break;
        case 'article':
            include_once('template-article.inc');
            _template_preprocess_page_article($vars);
            _basic_generate_footer_html($vars);
			break;
        case 'article_video':
            include_once('template-article-video.inc');
            _template_preprocess_page_article_video($vars);
			break;
	    case 'node_gallery_image':
            include_once('template-node-gallery-image.inc');
            _template_preprocess_page_node_gallery_image($vars);
            _basic_generate_footer_html($vars);
			break;
		case 'course':
			$course_image =  $vars['node']->field_course_photo[0]['filepath'];
			$vars['head'] .= '<meta property="og:title" content="'.$vars['node']->title .'">';
			$vars['head'] .= '<meta property="og:image" content="http://i.cdn.turner.com/dr/golf/www/release/'.$course_image.'"/>';
			$vars['head'] .= '<meta property="og:url" content="'._basic_get_aliasedurl ($vars['node']->nid ).'">';
			break;
		default:break;
	}

}

function basic_preprocess_page_header(&$vars) {
    /* General */
    $vars['theme_img_path'] = drupal_get_path('theme', 'basic') . '/images/';
    $vars['front_page'] = url();
}
function basic_preprocess_page_topads(&$vars) {
    /* General */
    $vars['theme_img_path'] = drupal_get_path('theme', 'basic') . '/images/';
    $vars['front_page'] = url();
}

function basic_preprocess_page_bottomads(&$vars) {
    /* General */
    $vars['theme_img_path'] = drupal_get_path('theme', 'basic') . '/images/';
    $vars['front_page'] = url();
}

function basic_preprocess_page_bottom_row_ads(&$vars) {
    /* General */
    $vars['theme_img_path'] = drupal_get_path('theme', 'basic') . '/images/';
    $vars['front_page'] = url();
}
function basic_preprocess_page_footer(&$vars) {
    /* General */
    $vars['theme_img_path'] = drupal_get_path('theme', 'basic') . '/images/';

    /* Footer
    $vars['footer_logo'] = '<a href="' . url() . '">' . theme_image(drupal_get_path('theme', 'basic') . '/images/footer_logo.png', 'Golf.com', 'Golf.com') . '</a>';
    $vars['copyright'] = 'Golf.com is part of SI Digital Sites, part of the CNN Digital Network';
    $menu_items = menu_navigation_links('primary-footer-links');
    $vars['primary_footer_links'] = '<div id="primary-footer-links">' . ($menu_items ? theme('links', $menu_items) : '') . '</div>';
    $menu_items = menu_navigation_links('secondary-footer-links');
    $vars['footer_menu_sub'] = '<div id="secondary-footer-links">' . ($menu_items ? theme('links', $menu_items) : '') . '</div>';*/

    // Newsletter form
    $block = (object) module_invoke('newsletter_subscription', 'block', 'view', 0);
    $block->module = 'newsletter_subscription';
    $block->delta = 0;
    $vars['newsletter_form'] = theme('block', $block);

    /*
    //Subscription-data block - vignms
    $block = module_invoke('golf_subscription_data', 'block', 'view', 0);
    $vars['subscription_data'] = $block['content'];*/
}

/*
 * 	 This function creates the NODES classes, like 'node-unpublished' for nodes
 * 	 that are not published, or 'node-mine' for node posted by the connected user...
 *
 * 	@param $vars
 * 	  A sequential array of variables to pass to the theme template.
 * 	@param $hook
 * 	  The name of the theme function being called ("node" in this case.)
 */

function basic_preprocess_node(&$vars, $hook) {
    // Special classes for nodes

	if(!empty($vars['node']->field_article_image[0]['status'])){
		foreach($vars['node']->field_article_image as $key => $value){
			list($width,$height,$type, $attr)= getimagesize($value['filepath']);
					//$img_title=$value['data']['title'];

			if(strlen($value['data']['image_credit']['body']) > 1){
		        $img_credit= "<div class=\"article_img_credit\">" . $value['data']['image_credit']['body'] . "</div>";
			}
		    if ($width >='639'){
				if(strlen($value['data']['image_caption']['body']) > 1){
					$img_caption="<div class=\"article_large_img_caption\">" . $value['data']['image_caption']['body'] ."</div>";
				}
		        $img = theme('imagecache', 'article_large_image', $value['filepath']);
				$img_data[]=$img.$img_credit.$img_caption;
		    }else{
                if(strlen($value['data']['image_caption']['body']) > 1){
		            $img_caption="<div class=\"article_img_caption\">" . $value['data']['image_caption']['body'] ."</div>";
				}
		        $img= theme('imagecache', 'article_med_image', $value['filepath']);
				$img_data[] = $img.$img_credit.$img_caption;
			}
		}
		$vars['article_top_img'] =  '<div id="article-topper-image">'.$img_data[0].'</div>';
			$img_count =count($img_data);
		 /* if ($img_count > 1){
			for($i=1; $i < $count;$i++){
				$extra_imgs .= '<div id="article-second-image">' . $img_data[$i] .'</div>';
			}
			$vars['article_extra_imgs'] = $extra_imgs;
		}  */
	}

	if(strlen($vars['node']->field_article_body[0]['value']) > 1 ){
		$article_body = $vars['node']->field_article_body[0]['value'];
		$page_break = '<!-- pagebreak -->';
		$content = explode($page_break, $article_body);
		$no_of_pages = count($content);
		$new_content = array();
		if($no_of_pages > 1){
			//$content_nav = '<div class= "article_pager">';

			if (!isset($_GET['page'])) {
				$current_page = 1;
			}else{
				$current_page = $_GET['page'];
			}
			$next = $current_page + 1;
			$previous = $current_page - 1;
			$content_nav .='<div id="article-pager"><div class="item-list">';
			$content_nav .= '<ul class="pager">';
			if ($current_page > 1 && $current_page <= $no_of_pages ){
			$content_nav .= '<span class="prev-page"><a class ="prev" href="?page='.$previous.'"></a></span>';
			}

			foreach ($content as $key => $value){
				$page = $key + 1;
				$new_content[] = $value;
				if ($current_page == $page) {
					$class_active = "active-page";
				}
				else {
					$class_active = "";
				}
				$content_nav .='<li class="pager-item '.$class_active.'" ><a href="?page='.$page.'">'.$page.'</a></li>';
			}
			if ($current_page >= 1 && $current_page < $no_of_pages ){
			$content_nav .= '<span class="next-page"><a class ="next" href="?page='.$next.'"></a></span>';
			}
			$content_nav .='</ul>';
			$content_nav .='</div></div>';
			$new_key = $current_page - 1;
			if ($current_page <= $img_count) {
				$extra_img = '<div id="article-topper-image">'.$img_data[$new_key].'</div>' ;
			}
			else {
				//$vars['article_image_paged'] = "";
				$extra_img = '';
			}
			$vars['paged_content'] = $extra_img . $new_content[$new_key];
			$vars['page_nav'] = $content_nav;

		}elseif(($no_of_pages < 1) && ($img_count > 1)){
			for($i=1; $i < $count;$i++){
				$extra_imgs .= '<div id="article-second-image">' . $img_data[$i] .'</div>';
			}
			$vars['article_extra_imgs'] = $extra_imgs;

		}

	}

    $classes = array('node');
    if ($vars['sticky']) {
        $classes[] = 'sticky';
    }
    // support for Skinr Module
    if (module_exists('skinr')) {
        $classes[] = $vars['skinr'];
    }
    if (!$vars['status']) {
        $classes[] = 'node-unpublished';
        $vars['unpublished'] = TRUE;
    } else {
        $vars['unpublished'] = FALSE;
    }
    if ($vars['uid'] && $vars['uid'] == $GLOBALS['user']->uid) {
        $classes[] = 'node-mine'; // Node is authored by current user.
    }
    if ($vars['teaser']) {
        $classes[] = 'node-teaser'; // Node is displayed as teaser.
    }
    $classes[] = 'clearfix';

    // Class for node type: "node-type-page", "node-type-story", "node-type-my-custom-type", etc.
    $classes[] = basic_id_safe('node-type-' . $vars['type']);
    $vars['classes'] = implode(' ', $classes); // Concatenate with spaces
    // Special case for search, new template suggestion
    if ($vars['build_mode'] == 3) {
        $vars['template_files'][] = 'node-' . $vars['type'] . '-build-mode-' . $vars['build_mode'];
        $vars['template_files'][] = 'node-build-mode-' . $vars['build_mode'];
    }
}

function basic_preprocess_comment_wrapper(&$vars) {
    $classes = array();
    $classes[] = 'comment-wrapper';

    // Provide skinr support.
    if (module_exists('skinr')) {
        $classes[] = $vars['skinr'];
    }
    $vars['classes'] = implode(' ', $classes);
}

/*
 * 	This function create the EDIT LINKS for blocks and menus blocks.
 * 	When overing a block (except in IE6), some links appear to edit
 * 	or configure the block. You can then edit the block, and once you are
 * 	done, brought back to the first page.
 *
 * @param $vars
 *   A sequential array of variables to pass to the theme template.
 * @param $hook
 *   The name of the theme function being called ("block" in this case.)
 */

function basic_preprocess_block(&$vars, $hook) {
    $block = $vars['block'];

    // special block classes
    $classes = array('block');
    $classes[] = basic_id_safe('block-' . $vars['block']->module);
    $classes[] = basic_id_safe('block-' . $vars['block']->region);
    $classes[] = basic_id_safe('block-id-' . $vars['block']->bid);
    $classes[] = 'clearfix';

    // support for Skinr Module
    if (module_exists('skinr')) {
        $classes[] = $vars['skinr'];
    }

    $vars['block_classes'] = implode(' ', $classes); // Concatenate with spaces

    if (theme_get_setting('basic_block_editing') && user_access('administer blocks')) {
        // Display 'edit block' for custom blocks.
        if ($block->module == 'block') {
            $edit_links[] = l('<span>' . t('edit block') . '</span>', 'admin/build/block/configure/' . $block->module . '/' . $block->delta, array(
                'attributes' => array(
                    'title' => t('edit the content of this block'),
                    'class' => 'block-edit',
                ),
                'query' => drupal_get_destination(),
                'html' => TRUE,
                    )
            );
        }
        // Display 'configure' for other blocks.
        else {
            $edit_links[] = l('<span>' . t('configure') . '</span>', 'admin/build/block/configure/' . $block->module . '/' . $block->delta, array(
                'attributes' => array(
                    'title' => t('configure this block'),
                    'class' => 'block-config',
                ),
                'query' => drupal_get_destination(),
                'html' => TRUE,
                    )
            );
        }
        // Display 'edit menu' for Menu blocks.
        if ( user_access('administer menu') ) {
          if ($block->module == 'menu' || ($block->module == 'user' && $block->delta == 1))  {
            $menu_name = ($block->module == 'user') ? 'navigation' : $block->delta;
            $edit_links[] = l('<span>' . t('edit menu') . '</span>', 'admin/build/menu-customize/' . $menu_name, array(
                'attributes' => array(
                    'title' => t('edit the menu that defines this block'),
                    'class' => 'block-edit-menu',
                ),
                'query' => drupal_get_destination(),
                'html' => TRUE,
                    )
            );
        }
        // Display 'edit menu' for Menu block blocks.
          elseif ($block->module == 'menu_block') {
            list($menu_name, ) = split(':', variable_get("menu_block_{$block->delta}_parent", 'navigation:0'));
            $edit_links[] = l('<span>' . t('edit menu') . '</span>', 'admin/build/menu-customize/' . $menu_name, array(
                'attributes' => array(
                    'title' => t('edit the menu that defines this block'),
                    'class' => 'block-edit-menu',
                ),
                'query' => drupal_get_destination(),
                'html' => TRUE,
                    )
            );
        }
        }
        $vars['edit_links_array'] = $edit_links;
        $vars['edit_links'] = '<div class="edit">' . implode(' ', $edit_links) . '</div>';
    }
}

/*
 * Override or insert PHPTemplate variables into the block templates.
 *
 *  @param $vars
 *    An array of variables to pass to the theme template.
 *  @param $hook
 *    The name of the template being rendered ("comment" in this case.)
 */

function basic_preprocess_comment(&$vars, $hook) {
    // Add an "unpublished" flag.
    $vars['unpublished'] = ($vars['comment']->status == COMMENT_NOT_PUBLISHED);

    // If comment subjects are disabled, don't display them.
    if (variable_get('comment_subject_field_' . $vars['node']->type, 1) == 0) {
        $vars['title'] = '';
    }

    // Special classes for comments.
    $classes = array('comment');
    if ($vars['comment']->new) {
        $classes[] = 'comment-new';
    }
    $classes[] = $vars['status'];
    $classes[] = $vars['zebra'];
    if ($vars['id'] == 1) {
        $classes[] = 'first';
    }
    if ($vars['id'] == $vars['node']->comment_count) {
        $classes[] = 'last';
    }
    if ($vars['comment']->uid == 0) {
        // Comment is by an anonymous user.
        $classes[] = 'comment-by-anon';
    } else {
        if ($vars['comment']->uid == $vars['node']->uid) {
            // Comment is by the node author.
            $classes[] = 'comment-by-author';
        }
        if ($vars['comment']->uid == $GLOBALS['user']->uid) {
            // Comment was posted by current user.
            $classes[] = 'comment-mine';
        }
    }
    $vars['classes'] = implode(' ', $classes);
}

/**
 * Theme a "Submitted by ..." notice.
 *
 * @param $comment
 *   The comment.
 * @ingroup themeable
 */
function basic_comment_submitted($comment) {
    return theme('username', $comment);
}

/*
 * 	Customize the PRIMARY and SECONDARY LINKS, to allow the admin tabs to work on all browsers
 * 	An implementation of theme_menu_item_link()
 *
 * 	@param $link
 * 	  array The menu item to render.
 * 	@return
 * 	  string The rendered menu item.
 */

function basic_menu_item_link($link) {
    if (empty($link['localized_options'])) {
        $link['localized_options'] = array();
    }

    // If an item is a LOCAL TASK, render it as a tab
    if ($link['type'] & MENU_IS_LOCAL_TASK) {
        $link['title'] = '<span class="tab">' . check_plain($link['title']) . '</span>';
        $link['localized_options']['html'] = TRUE;
    }

    // If an item is in primary links
    if ($link['menu_name'] == 'primary-links') {
        $link['title'] = '<span>' . check_plain($link['title']) . '</span>';
        $link['localized_options']['html'] = TRUE;
    }

    return l($link['title'], $link['href'], $link['localized_options']);
}

/*
 * theme_links()
 */

function basic_links($links, $attributes = array('class' => 'links')) {
    $output = '';

    if (count($links) > 0) {
        $output = '<ul' . drupal_attributes($attributes) . '>';

        $num_links = count($links);
        $i = 1;

        foreach ($links as $key => $link) {
            $class = $key;

            // Add first, last and active classes to the list of links to help out themers.
            if ($i == 1) {
                $class .= ' first';
            }
            if ($i == $num_links) {
                $class .= ' last';
            }
            if (isset($link['href']) && ($link['href'] == $_GET['q'] || ($link['href'] == '<front>' && drupal_is_front_page()))) {
                $class .= ' active';
            }
            $output .= '<li' . drupal_attributes(array('class' => $class)) . '>';


            if (isset($link['href'])) {
                $link['title'] = '<span>' . check_plain($link['title']) . '</span>';
                $link['html'] = TRUE;

                // Add target attribute for external links
                if (preg_match('#^[http|https]#', $link['href'])) {
                    $link['attributes'] = array(
                        'target' => '_blank',
                    );
                }

                // Pass in $link as $options, they share the same keys.
                $output .= l($link['title'], $link['href'], $link);
            } else if (!empty($link['title'])) {
                // Some links are actually not links, but we wrap these in <span> for adding title and class attributes
                if (empty($link['html'])) {
                    $link['title'] = check_plain($link['title']);
                }
                $span_attributes = '';
                if (isset($link['attributes'])) {
                    $span_attributes = drupal_attributes($link['attributes']);
                }
                $output .= '<span' . $span_attributes . '>' . $link['title'] . '</span>';
            }

            $i++;
            $output .= "</li>\n";
        }

        $output .= '</ul>';
    }

    return $output;
}

/*
 *  Duplicate of theme_menu_local_tasks() but adds clear-block to tabs.
 */

function basic_menu_local_tasks() {
    $output = '';
    if ($primary = menu_primary_local_tasks()) {
        if (menu_secondary_local_tasks()) {
            $output .= '<ul class="tabs primary with-secondary clearfix">' . $primary . '</ul>';
        } else {
            $output .= '<ul class="tabs primary clearfix">' . $primary . '</ul>';
        }
    }
    if ($secondary = menu_secondary_local_tasks()) {
        $output .= '<ul class="tabs secondary clearfix">' . $secondary . '</ul>';
    }
    return $output;
}

/*
 * 	Remove MS-Word Smart Quotes, double apostrophes, and an (AP)- text change to a string
 */
function convert_smart_quotes($string){

	$search = array(chr(145), chr(146), chr(147), chr(148), '``', '&#39;&#39;', '(AP) -');

	$replace = array("'", "'", '"', '"', '"', '"', '(AP) --');

	return str_replace($search, $replace, $string);
}

/*
 * 	Add custom classes to menu item
 */
function basic_menu_item($link, $has_children, $menu = '', $in_active_trail = FALSE, $extra_class = NULL) {
    $class = ($menu ? 'expanded' : ($has_children ? 'collapsed' : 'leaf'));
    if (!empty($extra_class)) {
        $class .= ' ' . $extra_class;
    }
    if ($in_active_trail) {
        $class .= ' active-trail';
    }
#New line added to get unique classes for each menu item
    $css_class = basic_id_safe(str_replace(' ', '_', strip_tags($link)));
    return '<li class="' . $class . ' ' . $css_class . '">' . $link . $menu . "</li>\n";
}

/*
 * 	Converts a string to a suitable html ID attribute.
 *
 * 	 http://www.w3.org/TR/html4/struct/global.html#h-7.5.2 specifies what makes a
 * 	 valid ID attribute in HTML. This function:
 *
 * 	- Ensure an ID starts with an alpha character by optionally adding an 'n'.
 * 	- Replaces any character except A-Z, numbers, and underscores with dashes.
 * 	- Converts entire string to lowercase.
 *
 * 	@param $string
 * 	  The string
 * 	@return
 * 	  The converted string
 */

function basic_id_safe($string) {
    // Replace with dashes anything that isn't A-Z, numbers, dashes, or underscores.
    $string = strtolower(preg_replace('/[^a-zA-Z0-9_-]+/', '-', $string));
    // If the first character is not a-z, add 'n' in front.
    if (!ctype_lower($string{0})) { // Don't use ctype_alpha since its locale aware.
        $string = 'id' . $string;
    }
    return $string;
}

/**
 * Return a themed breadcrumb trail.
 *
 * @param $breadcrumb
 * An array containing the breadcrumb links.
 * @return
 * A string containing the breadcrumb output.
 */
function basic_breadcrumb($breadcrumb) {
    // Determine if we are to display the breadcrumb.
    $show_breadcrumb = theme_get_setting('basic_breadcrumb');
    if (arg(0) == 'news' || arg(0) == 'leaderboard' || arg(0) == 'video' || arg(0) == 'statistics' ||arg(0) =='statistics-champions' || arg(0)=='statistics-nationwide') {
        unset($breadcrumb);
    }
    if ($show_breadcrumb == 'yes' || $show_breadcrumb == 'admin' && arg(0) == 'admin') {

        // Optionally get rid of the homepage link.
        $show_breadcrumb_home = theme_get_setting('basic_breadcrumb_home');
        if (!$show_breadcrumb_home) {
            array_shift($breadcrumb);
        }

        // Return the breadcrumb with separators.
        if (!empty($breadcrumb)) {
            $breadcrumb_separator = theme_get_setting('basic_breadcrumb_separator');
            $trailing_separator = $title = '';
            if (theme_get_setting('basic_breadcrumb_title')) {
                if ($title = drupal_get_title()) {
                    $trailing_separator = $breadcrumb_separator;
                }
            } elseif (theme_get_setting('basic_breadcrumb_trailing')) {
                $trailing_separator = $breadcrumb_separator;
            }
            return '<div class="breadcrumb">' . implode($breadcrumb_separator, $breadcrumb) . "$trailing_separator$title</div>";
        }
    }
    // Otherwise, return an empty string.
    return '';
}

/**
 * Implementation of hook_theme().
 * @param unknown_type $existing
 * @param unknown_type $type
 * @param unknown_type $theme
 * @param unknown_type $path
 */
function basic_theme($existing, $type, $theme, $path) {
    return array(
        'page_header' => array(
            'arguments' => array(),
            'path' => drupal_get_path('theme', 'basic') . '/templates',
            'template' => 'page_header',
        ),
		'page_topads' => array(
            'arguments' => array(),
            'path' => drupal_get_path('theme', 'basic') . '/templates',
            'template' => 'page_topads',
        ),
		'page_bottomads' => array(
            'arguments' => array(),
            'path' => drupal_get_path('theme', 'basic') . '/templates',
            'template' => 'page_bottomads',
        ),
		'page_bottom_row_ads' => array(
            'arguments' => array(),
            'path' => drupal_get_path('theme', 'basic') . '/templates',
            'template' => 'page_bottom_row_ads',
        ),
        'page_footer' => array(
            'arguments' => array(),
            'path' => drupal_get_path('theme', 'basic') . '/templates',
            'template' => 'page_footer',
        ),
        'page_menu_share' => array(
            'arguments' => array(),
            'path' => drupal_get_path('theme', 'basic') . '/templates',
            'template' => 'menu-share',
        ),
        // Override the form in page news
        'news_search_search_form' => array(
            'arguments' => array('form' => NULL)
        ),
        // Override the search form in video page
        'video_search_search_form' => array(
            'arguments' => array('form' => NULL)
        ),
        // Override the search form in gallery page
        'gallery_search_search_form' => array(
            'arguments' => array('form' => NULL)
        ),
        // Override the form in general search page
        'search_form_1' => array(
            'arguments' => array('form' => NULL)
        ),
    );
}

function basic_preprocess_search_theme_form(&$vars, $hook) {
    $vars['form']['search_theme_form']['#title'] = '';
    $vars['form']['search_theme_form']['#value'] = 'Search';
    $vars['form']['search_theme_form']['#attributes'] = array(
        'onclick' => "javascript:if(this.value=='Search'){ this.value = '' }",
        'onblur' => "javascript:if(this.value==''){ this.value = 'Search' }"
    );

    // Rebuild the input field
    unset($vars['form']['search_theme_form']['#printed']);
    $vars['search']['search_theme_form'] = drupal_render($vars['form']['search_theme_form']);

    // Change the text on the submit button


    // Rebuild the submit field
    unset($vars['form']['submit']['#printed']);
    $vars['search']['submit'] = drupal_render($vars['form']['submit']);

    // Collect all form elements to make it easier to print the whole form.
    $vars['search_form'] = implode($vars['search']);
}

/**
 * Theme function to allow any menu tree to be themed as a Nice menu.
 *
 * @param $id
 *   The Nice menu ID.
 * @param $menu_name
 *   The top parent menu name from which to build the full menu.
 * @param $mlid
 *   The menu ID from which to build the displayed menu.
 * @param $direction
 *   Optional. The direction the menu expands. Default is 'right'.
 * @param $depth
 *   The number of children levels to display. Use -1 to display all children
 *   and use 0 to display no children.
 * @param $menu
 *   Optional. A custom menu array to use for theming --
 *   it should have the same structure as that returned
 *  by menu_tree_all_data(). Default is the standard menu tree.
 * @return
 *   An HTML string of Nice menu links.
 */
function basic_nice_menus($id, $menu_name, $mlid, $direction = 'right', $depth = -1, $menu = NULL) {
    $output = array();

    if ($menu_tree = theme('nice_menus_tree', $menu_name, $mlid, $depth, $menu)) {
        if ($menu_tree['content']) {
            $output['content'] = '<ul class="nice-menu nice-menu-' . $direction . '" id="' . $menu_name . '">' . $menu_tree['content'] . '</ul>' . "\n";
            $output['subject'] = $menu_tree['subject'];
        }
    }
    return $output;
}

/**
 * Helper function that builds the nested lists of a Nice menu.
 *
 * @param $menu
 *   Menu array from which to build the nested lists.
 * @param $depth
 *   The number of children levels to display. Use -1 to display all children
 *   and use 0 to display no children.
 * @param $trail
 *   An array of parent menu items.
 */
function basic_nice_menus_build($menu, $depth = -1, $trail = NULL) {
    $output = '';
    // Prepare to count the links so we can mark first, last, odd and even.
    $index = 0;
    $count = 0;
    foreach ($menu as $menu_count) {
        if ($menu_count['link']['hidden'] == 0) {
            $count++;
        }
    }
    // Get to building the menu.
    foreach ($menu as $menu_item) {
        $mlid = $menu_item['link']['mlid'];
        // Check to see if it is a visible menu item.
        if (!isset($menu_item['link']['hidden']) || $menu_item['link']['hidden'] == 0) {
            // Check our count and build first, last, odd/even classes.
            $index++;
            $first_class = $index == 1 ? ' first ' : '';
            $oddeven_class = $index % 2 == 0 ? ' even ' : ' odd ';
            $last_class = $index == $count ? ' last ' : '';
            // Build class name based on menu path
            // e.g. to give each menu item individual style.
            // Strip funny symbols.
            $clean_path = str_replace(array('http://', 'www', '<', '>', '&', '=', '?', ':', '.'), '', $menu_item['link']['href']);
            // Convert slashes to dashes.
            $clean_path = str_replace('/', '-', $clean_path);
            $class = 'menu-path-' . $clean_path;
            if ($trail && in_array($mlid, $trail)) {
                $class .= ' active-trail';
            }
            // If it has children build a nice little tree under it.
            if ((!empty($menu_item['link']['has_children'])) && (!empty($menu_item['below'])) && $depth != 0) {
                // Keep passing children into the function 'til we get them all.
                $children = theme('nice_menus_build', $menu_item['below'], $depth, $trail);
                // Set the class to parent only of children are displayed.
                $parent_class = ($children && ($menu_item['link']['depth'] <= $depth || $depth == -1)) ? 'menuparent ' : '';
                $output .= '<li class="menu-' . $mlid . ' ' . $parent_class . $class . $first_class . $oddeven_class . $last_class . '">';
                $output .= theme('menu_item_link', $menu_item['link']);
                if ($menu_item['link']['depth'] == 1) {
                    $output .= "<div class='panel clearfix'>\n";
                }
                // Check our depth parameters.
                if ($menu_item['link']['depth'] <= $depth || $depth == -1) {
                    // Build the child UL only if children are displayed for the user.
                    if ($children) {
                        $output .= '<ul>';
                        $output .= $children;
                        $output .= "</ul>\n";
                    }
                }
                if ($menu_item['link']['depth'] == 1) {
                    $output .= "</div>\n";
                }
                $output .= "</li>\n";
            } else {

                $output .= '<li class="menu-' . $mlid . ' ' . $class . $first_class . $oddeven_class . $last_class . '">';
                $output .= theme('menu_item_link', $menu_item['link']);
                $output .= '</li>' . "\n";
            }
        }
    }
    return $output;
}

/**
 * Theme function to modify the sort block for apachesolr search.
 *
 * We need this trick to put back &solrsort=score asc with relevance link.
 * Relevancy is hard coded by default in the sold query lib and suppose that
 * score is the default sort you want.
 *
 */
function basic_apachesolr_sort_list($items) {
    // theme('item_list') expects a numerically indexed array.
    $items = array_values($items);
    for ($i = 0; $i < count($items); $i++) {
        if (preg_match('/Relevance/', $items[$i])) {
            //add solrsort value to the link in the news
            $items[$i] = preg_replace('/news\/(.*)\?(.*)"/U', 'news/$1?$2&solrsort=' . 'score asc"', $items[$i]);
            //remove image
            $items[$i] = preg_replace("/<img[^>]+>/si", "", $items[$i]);
            //add solrsort value to the link in solr search
            $q = $_GET;
            unset($q['q']);
            //check if we have other arguments than q
            if (count($q) > 0) {
                //avoid to add the sort if already present
                if ($q['solrsort'] != 'score asc') {
                    if (preg_match('/href=".*\?.*"/', $items[$i])) {
                        $items[$i] = preg_replace('/search\/apachesolr_search\/(.*)\?(.*)"/U', 'search/apachesolr_search/$1?$2&solrsort=' . 'score asc"', $items[$i]);
                    } else {
                        $items[$i] = preg_replace('/search\/apachesolr_search\/(.*)"/U', 'search/apachesolr_search/$1?solrsort=' . 'score asc"', $items[$i]);
                    }
                }
            } else {
                //or if no ?filters is here
                $items[$i] = preg_replace('/search\/apachesolr_search\/(.*)"/U', 'search/apachesolr_search/$1?solrsort=' . 'score asc"', $items[$i]);
            }
        }
        if (preg_match('/Date/', $items[$i])) {
            $items[$i] = preg_replace("/<img[^>]+>/si", "", $items[$i]);
        }
    }
    return theme('item_list', $items);
}

/**
 * Theme function to modify the facet block.
 *
 */
function basic_apachesolr_facet_list($items, $display_limit = 0) {
    // theme('item_list') expects a numerically indexed array.
    $items = array_values($items);
    // If there is a limit and the facet count is over the limit, hide the rest.
    if (($display_limit > 0) && (count($items) > $display_limit)) {
        // Show/hide extra facets.
        drupal_add_js(drupal_get_path('module', 'apachesolr') . '/apachesolr.js');
        // Add translated strings.
        drupal_add_js(array('apachesolr' => array('showMore' => t('Show more'), 'showFewer' => t('Show fewer'))), 'setting');
        // Split items array into displayed and hidden.
        $hidden_items = array_splice($items, $display_limit);
        foreach ($hidden_items as $hidden_item) {
            if (!is_array($hidden_item)) {
                $hidden_item = array('data' => $hidden_item);
            }
            $items[] = $hidden_item + array('class' => 'apachesolr-hidden-facet');
        }
    }
    $admin_link = '';
    if (user_access('administer search')) {
        $admin_link = l(t('Configure enabled filters'), 'admin/settings/apachesolr/enabled-filters');
    }
    return theme('item_list', $items) . $admin_link;
}

/**
 * This function theme, modify the search form in page news.
 * @param $form
 */
function basic_news_search_search_form($form) {

    $output = '<h3>' . t('Search Results') . '</h3>';
    $form['basic']['inline']['submit']['#value'] = '';
    $form['basic']['inline']['submit']['#type'] = 'image_button';
    $form['basic']['inline']['submit']['#src'] = drupal_get_path('theme', 'basic') . '/images/news-image-button.png';
    $form['basic']['inline']['keys']['#value'] = t('Search News');
    $output .= drupal_render($form);

    return $output;
}

function basic_preprocess_views_view_row_rss(&$vars) {
    $view = &$vars['view'];
    $options = &$vars['options'];
    $item = &$vars['row'];
    $result    = &$vars['view']->result;
    $id     = &$vars['id'];
    ##TODO: Definitely need to turn this into a query, doing a node load on each rss item is expensive
    $node     = node_load( $result[$id-1]->nid );
    $vars['title'] = check_plain($item->title);
    $vars['link'] = check_url($item->link);
    $vars['node'] = $node;
    $vars['item_elements'] = empty($item->elements) ? '' : format_xml_elements($item->elements);
}

function basic_video_search_search_form($form) {
    $output = '';
    $form['basic']['inline']['submit']['#value'] = '';
    $form['basic']['inline']['submit']['#type'] = 'image_button';
    $form['basic']['inline']['submit']['#src'] = drupal_get_path('theme', 'basic') . '/images/video-image-button.png';
    $form['basic']['inline']['keys']['#value'] = t('Search');
    $output .= drupal_render($form);

    return $output;
}

function basic_gallery_search_search_form($form) {

    $output = '';
    $form['basic']['inline']['submit']['#value'] = '';
    $form['basic']['inline']['submit']['#type'] = 'image_button';
    $form['basic']['inline']['submit']['#src'] = drupal_get_path('theme', 'basic') . '/images/video-image-button.png';
    $form['basic']['inline']['keys']['#value'] = t('Search');
    $output .= drupal_render($form);

    return $output;
}

function basic_preprocess_search_result(&$variables) {
    if ($variables['result']['node']->type == "node_gallery_gallery") {
        $variables['template_files'][] = 'search-photos-result-node_gallery_gallery';
    } else if ($variables['result']['node']->type == "node_gallery_image") {
        $variables['template_files'][] = 'search-photos-result-node_gallery_image';
    } else if ($variables['result']['node']->type == "article_video") {
        $variables['template_files'][] = 'search-video-result-article_video';
    }
}

function basic_preprocess_search_results(&$variables) {
	$path = isset($_GET['q']) ? $_GET['q'] : '<front>';
	$link = url($path, array('absolute' => TRUE));
	if (strstr($link, "/video/")) {
		$variables['template_files'][] = 'search-results-page-type-video';
	}
	if (strstr($link, "/photos/")) {
		$variables['template_files'][] = 'search-results-page-type-gallery';
	}
}

/**
 * Preprocess for mobile RSS feed image caption
 */
function basic_preprocess_views_view_field__mobile_rss_feeds__field_article_image_fid_1(&$vars) {
    $nid = $vars['row']->nid;

    $data = db_result(db_query("SELECT field_article_image_data FROM {content_field_article_image} WHERE nid = '%s'", $nid));
    $data = unserialize ($data);

    $vars['output'] = ($data['image_caption']['body']);
}

/**
 * Preprocess for mobile RSS feed image credit
 */
function basic_preprocess_views_view_field__mobile_rss_feeds__field_article_image_fid_2(&$vars) {
    $nid = $vars['row']->nid;

    $data = db_result(db_query("SELECT field_article_image_data FROM {content_field_article_image} WHERE nid = '%s'", $nid));
    $data = unserialize ($data);

    $vars['output'] = ($data['image_credit']['body']);
}


function _basic_generate_footer_html(&$vars) {

 /* Footer */
    /*
  $vars['footer_logo'] = '<a href="' . url() . '">' . theme_image($path . '/images/footer_logo.png', 'Golf.com', 'Golf.com') . '</a>';
  $vars['copyright'] = 'Golf.com is part of SI Digital Sites, part of the CNN Digital Network';

  $cache = cache_get('theme-basic-primary-footer-links');
  if ( $cache && !empty($cache->data) ) {
    $links = $cache->data;
  } else {
    $menu_items = menu_navigation_links('primary-footer-links');
    $links = '<div id="primary-footer-links">' . ($menu_items ? theme('links', $menu_items) : '') . '</div>';
    cache_set('theme-basic-primary-footer-links', $links, 'cache', time() + 63);
  }
  $vars['primary_footer_links'] = $links;

  $cache = cache_get('theme-basic-footer-menu-sub');
  if ( $cache && !empty($cache->data) ) {
    $links = $cache->data;
  } else {
    $menu_items = menu_navigation_links('secondary-footer-links');
    $links = '<div id="secondary-footer-links">' . ($menu_items ? theme('links', $menu_items) : '') . '</div>';
    cache_set('theme-basic-footer-menu-sub', $links, 'cache', time() + 63);
  }
  $vars['footer_menu_sub'] = $links; */

  // Newsletter form
  $cache = cache_get('theme-basic-block-newsletter');
  if ($cache && !empty($cache->data)) {
    $newsletter = $cache->data;
  } else {
    $block = (object) module_invoke('newsletter_subscription', 'block', 'view', 0);
    $block->module = 'newsletter_subscription';
    $block->delta = 0;
    $newsletter = theme('block', $block);
    cache_set('theme-basic-block-newsletter', $newsletter, 'cache', time() + 133);
  }

  $vars['newsletter_form'] = $newsletter;

/*
  //Subscription-data block - vignms
  $block = module_invoke('golf_subscription_data', 'block', 'view', 0);
  $vars['subscription_data'] = $block['content']; */
  //end footer
}

function _basic_get_aliasedurl($nid,$domain=''){
	return ($domain!=''?$domain:$base_url) .'/'. drupal_get_path_alias('node/' .$nid);
}

function _basic_get_video_block($nid, $use_title_always=false){
    $vdo_node = _basic_get_video_node_data($nid);
    $vdo_tout_image = theme('imagecache', 'black_bg_video_129x95', $vdo_node->imgpath);
	$vdo_url=_basic_get_aliasedurl($vdo_node->nid);
    if($use_title_always==false){
        $vdo_tout_title = !empty($vdo_node->tout_title) ? $vdo_node->tout_title : $vdo_node->title  ;
    }else{
        $vdo_tout_title =$vdo_node->title;
    }
    //return '<li><a href="' . $vdo_url. '">' . $vdo_tout_image . '</a><h5><a href="' . $vdo_url . '">' . $vdo_tout_title . '</a></h5><p>' . substr($vdo_node->tout_body, 0, 80) . '<a href="' .$vdo_url. '">  Go to Video</a></p></li>';
    return '<li><a href="' . $vdo_url. '">' . $vdo_tout_image . '</a><h5><a href="' . $vdo_url . '">' . $vdo_tout_title . '</a></h5></li>';
}

function _basic_get_video_node_data($nid){
    $sql = <<<SQLS
        SELECT
            a.nid nid,
            a.field_video_tout_title_value tout_title,
            a.field_video_tout_body_value tout_body,
            c.title title,
            b.filepath imgpath
        FROM
            {content_type_article_video} a, {files} b, {node} c
        WHERE
            a.field_video_tout_image_fid=b.fid and a.nid= %d and a.nid=c.nid
SQLS;
    return db_fetch_object(db_query($sql,$nid));
}

