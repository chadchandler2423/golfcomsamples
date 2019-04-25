$(document).ready(function() {
	/* Open PGA Tour Link in new window */
	$(".menu-path-front-pga-tour a").attr("target", "_blank");
	$(".menu-path-instruction-find-pga-instructor a").attr("target", "_blank");

	/* Find an instructor widget - code moved here from search because the widget is used across the site in multiple places */
	$('#instructor-name-field').focus(function() {
      if(this.value=='Instructor Name'){ this.value = '' }
    });
    $('#edit-zip').focus(function() {
      if(this.value=='Zip'){ this.value = '' }
    });
	$('#instructor-name-field').blur(function() {
      if(this.value==''){ this.value = 'Instructor Name' }
    });
    $('#edit-zip').blur(function() {
      if(this.value==''){ this.value = 'Zip' }
    });
	$('#sitewide-search-pga-form').submit(function() {
	  var name = $('#instructor-name-field').val();
	  var zip = $('#edit-zip').val();
	  //window.location = "http://www.pga.com/golf-instruction/find-instructor?searchbox=" + name + "&searchbox_zip=" + zip;
	  var instructor_url = "http://www.pga.com/golf-instruction/find-instructor?searchbox=" + name + "&searchbox_zip=" + zip;
	  var new_window = window.open(instructor_url, "_blank");
	  new_window.focus();
	  return false;
	});

	// Bind click event on drop down
	$('#see-try-buy ul.selects li.root').each(function(index){
		$(this).find('a.elm').click(function(){
			$drop_down_root_elem = $(this).parent();
			$drop_down_root_elem.trigger('hover');
			// Reset each drop down
			$('#see-try-buy ul.selects li.root').removeClass('selected');
			$drop_down_root_elem.addClass('selected');
			$drop_down_root_elem.find('ul').addClass("child-selected");
		});
	});

	// Bind hover event for top drop down
	$('#see-try-buy ul.selects li.root').hover(
		function(){
			$drop_down_is_hover = true;
			$(this).find('ul').addClass("child-selected");
		},
		function(){
			$drop_down_is_hover = false;
			setTimeout( function() {
				if(!$drop_down_is_hover && typeof $drop_down_root_elem!='undefined') {
					$drop_down_root_elem.find('ul').removeClass("child-selected");
					$drop_down_root_elem.removeClass('selected');
				}
			}, 2000);
		}
	);

    $('#last-mag .golf-mag-bt').click(function() {
	  $(this).parent().toggleClass('open');
	});


 	//Remove comment form default value
	$('#edit-comment').focus(function() {
		if ($(this).text() == Drupal.settings.comment_form_settings.comment_form_default) {
			$(this).text("");
		}
	});
	$('#edit-comment').blur(function() {
		if ($(this).text() == "") {
			$(this).text(Drupal.settings.comment_form_settings.comment_form_default);
		}
	});


	showTopStories('1');

    /* Add the correct style class  for navigation elements- PD */
	var hashes = window.location.href.split('/');
	$("li.menuparent").children("a").children("span").each(function(a){
    $navPath=hashes[3].toUpperCase();
    if($navPath=='AP-NEWS' || $navPath=='TOUR-AND-NEWS' || $navPath=='LEADERBOARD'){
      $navPath="NEWS";
    }
    if($navPath==$(this).html().toUpperCase()){
			$(this).parent().parent().addClass("active-trail");
		}
	});

	/* Set active state on gallery page thumbnails */
	$(".jcarousel-item .field-content a").each(function() {
		if (document.URL.indexOf($(this).attr("href")) !== -1) {
			$(this).addClass("active");
		}
	});

	/* Clear text in search boxex on video and gallery pages */
	$('#edit-keys-wrapper #edit-keys').focus(function() {
      if(this.value=='Search'){ this.value = '' }
    });
	$('#edit-course-name').focus(function() {
      if(this.value=='Course Name'){ this.value = '' }
    });
	$('#edit-zip').focus(function() {
      if(this.value=='Zip'){ this.value = '' }
    });
	$('#edit-commenter-name').focus(function() {
      if(this.value=='Name'){ this.value = '' }
    });
	$('#edit-review').focus(function() {
      if($(this).val()=='Type your review here.'){ $(this).val('') }
    });

	/* Return text to textbox if no value entered */
	$('#edit-keys-wrapper #edit-keys').blur(function() {
      if(this.value==''){ this.value = 'Search' }
    });
	$('#edit-course-name').blur(function() {
      if(this.value==''){ this.value = 'Course Name' }
    });
	$('#edit-zip').blur(function() {
      if(this.value==''){ this.value = 'Zip' }
    });
	$('#edit-commenter-name').blur(function() {
      if(this.value==''){ this.value = 'Name' }
    });
	$('#edit-review').blur(function() {
      if($(this).val()==''){ $(this).val('Type your review here.') }
    });
});

/* ADBP */
// Bind STB click event on STB select
$(document).ready(function(){
	$('#see-try-buy ul.selects li.root ul li a').each(function(index){
		$(this).click(function(){
			var value=$(this).html().toLowerCase();
			var key=$(this).parent().parent().parent().children(':first-child').html().toLowerCase();
			callADBPMetrics("module-click",key+" : "+value);
		});
	});
});

function callADBPMetrics(moduleClick,moduleName){
	try {
		trackMetrics({
			type: moduleClick,
			data: {
				module_name: moduleName
			}
		});
	} catch(e){}
}

function clog(obj){
	if(typeof console!='undefined'){
		console.log(obj);
	}
}

function showTopStories(idx){
	for(var i=1;i<4;i++){
		$('#top-stories-and-social-tabs-content-'+i).attr('style','display:none');
	}
	$('#top-stories-and-social-tabs-content-'+idx).attr('style','display:block');
	$('#top-stories-and-social ul li').each(function(i,data){
		$(this).removeClass('active  ui-tabs-selected');
		if(i==(idx-1)){
			$(this).addClass('active  ui-tabs-selected');
		}
	});
}


function StringBuffer() {
	this.buffer = [];
}

StringBuffer.prototype.append = function append(string) {
    this.buffer.push(string);
    return this;
};

StringBuffer.prototype.toString = function toString() {
    return this.buffer.join("");
};



/* Sometimes the data being passed for some values will be an array and sometimes it won't be - this is a fix for that */
function getArray(object) {
  if(object!=undefined){
  	var new_array = [];
  	if (object.length === undefined) {
  		new_array.push(object);
  		return new_array;
  	} else {
  		return object;
  	}
  }
}

function formatCurrency(num,symbol) {
    num = num.toString().replace(/\$|\,/g,'');
    if(isNaN(num))
        num = "0";
    sign = (num == (num = Math.abs(num)));
    num = Math.floor(num*100+0.50000000001);
    cents = num%100;
    num = Math.floor(num/100).toString();
    if(cents<10)
        cents = "0" + cents;
    for (var i = 0; i < Math.floor((num.length-(1+i))/3); i++)
        num = num.substring(0,num.length-(4*i+3))+','+
        num.substring(num.length-(4*i+3));
    return (((sign)?'':'-') + symbol + num + '.' + cents);
}

/*Lazy load the tweets */
function showLatestTweets(){
	if($.trim($('#top-stories-and-social-tabs-content-3').html())==''){//load it only once to avoid rate limits
		$('#top-stories-and-social-tabs-content-3').jTweetsAnywhere({
			username: 'si_golf',
			list: 'si-golf-group-4',
			count: 10,
			showTweetFeed: {
				autoConformToTwitterStyleguide: true,
				showTimestamp: {
					refreshInterval: 15
				}
			}
		});
	}
}

$(document).ready(function() {
  $("div.insert-image img").each(function (){
    if($(this).css("float")=='right'){
      $(this).css({'float' : 'none'});
      $(this).parent().css({'float' : 'right', 'margin-left' : '8px', 'margin-bottom' : '5px' });
    }else if($(this).css("float")=='left'){
      $(this).css({'float' : 'none'});
      $(this).parent().css({'float' : 'left', 'margin-right' : '8px', 'margin-bottom' : '5px' });
    }
  });
});

