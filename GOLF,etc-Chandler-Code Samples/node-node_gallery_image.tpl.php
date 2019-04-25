<h1 class="title"><?php $gallery = node_load($node->gid);  print $gallery->title; ?></h1>
  <!-- Prev/Next Back to Gallery functionality BEGIN -->
 <?php print theme('node_gallery_image_navigator', node_gallery_get_image_navigator($node->gid, $node->nid), $node); ?>
  <!-- Prev/Next Back to Gallery functionality END -->
<div class="node <?php print $node_classes; ?>">
			<div id="node-inner" class="node-inner">
			<!--  Image BEGIN -->
			<?php
			if($navigator['current'] >= $navigator['total']) {
				$last_image_class = "last-gallery-photo";
			}
			?>
				<div id="golf-gallery-image" class="<?php print $last_image_class ?>">
				<?php print $node->field_node_gallery_image[0]['view'] ?>
				</div>
				<span class="gallery-credit"><?php print ($node->field_gallery_credit[0]['view'] ? '<strong>CREDIT:</strong>  ' . $node->field_gallery_credit[0]['view'] : ''); ?></span>
				<div id="golf-gallery-bottom">
					<div id="golf-gallery-image-details">
					<span class="gallery-caption"> <?php print $node->content['body']['#value'];?></span>
					</div>
						<div id="image-navigator-more">
						<a title="More Photo Galleries" href="/photos"><img src="/dr/golf/www/release/sites/default/themes/basic/images/more-photos.png"></a>
						</div>
					<div class="social-buttons">
						<div class="addthis_toolbox addthis_default_style "
						addthis:url="www.golf.com/<?php print $gallery->path; ?>"
						addthis:title="<?php print $gallery->title; ?>"
						addthis:description="<?php print $gallery->body; ?>">
						<a class="addthis_button_facebook_like" fb:like:layout="button_count"></a>
						<div class="fb-share">
							<div id="social-facebook">
							<ul>
							<li>><a style="font-size:12px;" class="addthis_button_facebook" addthis:url="www.golf.com/<?php print $node->path; ?>" addthis:title="<?php print $gallery->title; ?>">Share this Image</a></li>
							<li>><a style="font-size:12px;" class="addthis_button_facebook" addthis:url="www.golf.com/<?php print $gallery->path; ?>" addthis:title="<?php print $gallery->title; ?>">Share this Gallery</a></li>
							</ul>
							</div>
						</div>
					<div class="twitter-share">
						<div id="social-twitter">
						<ul>
						<li>><a style="font-size:12px;" class="addthis_button_twitter" addthis:url="www.golf.com/<?php print $node->path; ?>" addthis:title="<?php print $node->title; ?>">Share this Image</a></li>
						<li>><a style="font-size:12px;" class="addthis_button_twitter" addthis:url="www.golf.com/<?php print $gallery->path; ?>" addthis:title="<?php print $gallery->title; ?>">Share this Gallery</a></li>
						</ul>
						</div>
					</div>
					<a class="addthis_toolbox_item addthis_button_email at300b"></a>
					<a class="addthis_toolbox_item addthis_button_compact at300m"></a>
				</div>
			</div>
		</div>
		<div id="gallery-blackdiv">
		</div>
	</div>
</div>
<script>
function updateGalleryCount(start,end){
  $('#golf-gal-cnt').html('<em>' + start + '</em>' + ' of <em>' + end +'</em>');
}

function getCurrentSlideNumber(){
  return parseInt($('#golf-gal-cnt em:eq(0)').html());
}

function getTotalSlides(){
  return parseInt($('#golf-gal-cnt em:eq(1)').html());
}

var lastSlideClicked=false;

$(document).ready(function () {
  $('#image-navigator-next-last a, .last-gallery-photo img').click(function () {
    if(!lastSlideClicked){
      lastSlideClicked=true;
      updateGalleryCount(getTotalSlides(),getTotalSlides());
      $('#end-slide').fadeIn('slow');
    }
  });
  if ($('#end-slide').length > 0) {
    $('#endslide_prev_link').click(function (e) {
      if(lastSlideClicked){
        e.preventDefault();
        lastSlideClicked=false;
        $('#end-slide').fadeOut('slow');
        updateGalleryCount(getTotalSlides()-1,getTotalSlides());
     }
    });
  }
});

var t = '';
$(function () {
    $('.fb-share').hover(function () {
        $('div#social-twitter').hide();
        $('div#social-facebook').show();
    }, function () {
        var timerCallback = function () {
                $('div#social-facebook').hide();
            }
        t = setTimeout(timerCallback, 2150);
    });
});

$(function () {
    $('.twitter-share').hover(function () {
        $('div#social-facebook').hide();
        $('div#social-twitter').show();
    }, function () {
        var timerCallback = function () {
                $('div#social-twitter').hide();
            }
        t = setTimeout(timerCallback, 2150);
    });
});
</script>
