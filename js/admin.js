//TWT admin scripts
jQuery(document).ready( function($) {
    $('.twt-tab-bar a').click(function(event){
		event.preventDefault();
		var context = $(this).closest('.twt-tab-bar').parent();
		$('.twt-tab-bar li', context).removeClass('twt-tab-active');
		$(this).closest('li').addClass('twt-tab-active');
		$('.twt-tab-panel', context).hide();
		$( $(this).attr('href'), context ).show();
	});
	$('.twt-tab-bar').each(function(){
		if ( $('.twt-tab-active', this).length )
			$('.twt-tab-active', this).click();
		else
			$('a', this).first().click();
	});
});