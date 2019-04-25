<?php
// $Id:

/**
 * @file node.tpl.php
 *
 * Theme implementation to display a node.
 *
 * Available variables:
 * - $title: the (sanitized) title of the node.
 * - $content: Node body or teaser depending on $teaser flag.
 * - $picture: The authors picture of the node output from
 *   theme_user_picture().
 * - $date: Formatted creation date (use $created to reformat with
 *   format_date()).
 * - $links: Themed links like "Read more", "Add new comment", etc. output
 *   from theme_links().
 * - $name: Themed username of node author output from theme_username().
 * - $node_url: Direct url of the current node.
 * - $terms: the themed list of taxonomy term links output from theme_links().
 * - $submitted: themed submission information output from
 *   theme_node_submitted().
 *
 * Other variables:
 * - $node: Full node object. Contains data that may not be safe.
 * - $type: Node type, i.e. story, page, blog, etc.
 * - $comment_count: Number of comments attached to the node.
 * - $uid: User ID of the node author.
 * - $created: Time the node was published formatted in Unix timestamp.
 * - $zebra: Outputs either "even" or "odd". Useful for zebra striping in
 *   teaser listings.
 * - $id: Position of the node. Increments each time it's output.
 *
 * Node status variables:
 * - $teaser: Flag for the teaser state.
 * - $page: Flag for the full page state.
 * - $promote: Flag for front page promotion state.
 * - $sticky: Flags for sticky post setting.
 * - $status: Flag for published status.
 * - $comment: State of comment settings for the node.
 * - $readmore: Flags true if the teaser content of the node cannot hold the
 *   main body content.
 * - $is_front: Flags true when presented in the front page.
 * - $logged_in: Flags true when the current user is a logged-in member.
 * - $is_admin: Flags true when the current user is an administrator.
 *
 * @see template_preprocess()
 * @see template_preprocess_node()
 */
?>
<?php
  function _node_article_video_tax_term($node,$vocab_id){
    $vocab = taxonomy_node_get_terms_by_vocabulary($node, $vocab_id);
    rsort($vocab);
    $terms = taxonomy_get_term($vocab[0]->tid);
    if (!empty($terms->name)) {
      $term = $terms->name . " | ";
    }
    return $term;
  }
?>

<div class="node <?php print $classes; ?>" id="node-<?php print $node->nid; ?>">
  <div class="node-inner">

    <?php if (!$page): ?>
      <h2 class="title"><a href="<?php print $node_url; ?>"><?php print $title; ?></a></h2>
    <?php endif; ?>

    <?php print $picture; ?>

    <?php if ($submitted): ?>
      <span class="submitted"><?php print $submitted; ?></span>
    <?php endif; ?>

    <div class="content">
      <?php print $messages; ?>
      <?php //print $content; ?>
  	  <?php
  	  /*
      foreach ($node->taxonomy as $key => $term) {
  		if ($term->vid == 52) {
  		  $instruction_term = "<strong>" . $term->name . "</strong> | ";
  		}
  		else {
  		  $instruction_term = "";
  		}
  	  }

	    $instruction_vocab_id = 52;
	    $instruction_vocab = taxonomy_node_get_terms_by_vocabulary($node, $instruction_vocab_id);
  		rsort($instruction_vocab);
  		$instruction_terms = taxonomy_get_term($instruction_vocab[0]->tid);
  		if (!empty($instruction_terms->name)) {
  			$instruction_term = $instruction_terms->name . " | ";
  		}

      $equipment_vocab_id = 27;
      $equipment_vocab = taxonomy_node_get_terms_by_vocabulary($node, $equipment_vocab_id);
  		rsort($equipment_vocab);
  		$equipment_terms = taxonomy_get_term($equipment_vocab[0]->tid);
  		if (!empty($equipment_terms->name)) {
  			$equipment_term = $equipment_terms->name . " | ";
  		}
      */
      $instruction_vocab_id = 52;
      $equipment_vocab_id = 27;

  	  ?>
  	  <div class="video-date"><?php print "<strong>"._node_article_video_tax_term($node,$instruction_vocab_id)._node_article_video_tax_term($node,$equipment_vocab_id)."</strong> Posted: " . date("F j, Y", $node->created) ; ?></div>
  	  <h2 class="title"><?php print $title; ?></h1>
  	  <div class="video-tout-content">
  	    <?php
          if (strlen($node->field_video_body[0]['value']) > 0) {
            print $node->field_video_body[0]['value'];
           } else {
            print $node->field_video_abstract[0]['value'];
          }
        ?>
      </div>
    </div>

    <?php if ($terms): ?>
      <div class="taxonomy"><?php //print $terms; ?></div>
    <?php endif;?>

    <?php if ($links): ?>
      <div class="links"> <?php //print $links; ?></div>
    <?php endif; ?>

  </div> <!-- /node-inner -->
</div> <!-- /node-->
<?php
	if (strlen($_GET['keys']) < 1 && strlen($_GET['filters']) < 1){
		adbp_outputJSON($node);
	}
?>
