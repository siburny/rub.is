<link rel='stylesheet' href='/wp-content/plugins/instagram-gallery/css/jquery.bxslider.css' type='text/css'> 
<link rel='stylesheet' href='/wp-content/plugins/instagram-gallery/css/vendor/css/font-awesome.min.css' type='text/css'> 

<script src="/wp-content/plugins/instagram-gallery/js/jquery.bxslider.js"></script>

<style>
.slider > div {
	min-height: 600px;
	display: flex;
	justify-content: center;
	align-items: center;
	position:relative;
}
.slider.plus > div {
	min-height: 640px;
}
.slider img {
	max-height: 600px;
	margin: 0px auto;
}
.bx-wrapper {
	margin: 0px 0px 50px 0px;
	border: none;
	background: #efefef url(/wp-content/plugins/instagram-gallery/css/images/background.png) repeat 0 0;
    -moz-box-shadow: none;
    -webkit-box-shadow: none;
    box-shadow: none;
}
.hidden {
	display: none !important;
}
.bx-wrapper .bx-pager {
	padding-top: 0px;
}
.bx-wrapper .bx-pager a {
	background: transparent;
	width: 20px;
	height: 20px;
	text-indent: 0px;
	font-size: 12px;
	margin: 0 5px;
}
.bx-wrapper .bx-pager a:not(.active) {
    color: #666;
}
.bx-wrapper a {
	cursor: pointer;
}
.bx-wrapper .bx-controls-direction a.bx-prev, .bx-wrapper .bx-controls-direction a.bx-next {
	text-indent: 0;
	font-size: 4em;
	background: #fff;
	text-align: center;
	width: 0.75em;
	height: 1.5em;
	line-height: 1.33em;
}
.bx-wrapper .bx-controls-direction a.bx-prev:hover, .bx-wrapper .bx-controls-direction a.bx-next:hover {
	/*color: #666;*/
}
.bx-wrapper .bx-prev {
	left: 0;
}
.bx-wrapper .bx-next {
	right: 0;
}
</style>

<div style="text-align:center; color:#aaa;font-size:0.8em;padding: 10px 0;background:#fff;"><i class="fa fa-long-arrow-left" aria-hidden="true"></i> USE ARROW KEYS <i class="fa fa-long-arrow-right" aria-hidden="true"></i></div>
<div class="slider">
<?php $i = 1; ?>
<?php foreach ($images as $image): ?>
<?php if(!empty($data) && $i++ % $vendor_status == 0): ?>
  <div>
	<?php echo $data; ?>
  </div>
<?php endif; ?>

<div>
	<img src="<?php echo $image['url']; ?>" />
</div>

	<!--<div style="position: absolute;width: 100%;top: 50%;left: 50%;transform: translate(-50%, -50%);">
		<div style="display:inline-block;">
		<div style="position:relative;">
			<a style="position:absolute;left:5px;bottom:5px;width:20px;height:20px;background:#fff;color:#444;" class="open-captions">
				<i class="fa fa-info" aria-hidden="true"></i>
			</a>
		</div>
		<div style="background:#fff;padding: 5px 30px; display:inline-block; position: relative;" class="captions hidden">
			<?php echo $image['caption']; ?>
			<?php if(!empty($image['instagram'])): ?>
				<?php if(!empty($image['name'])): ?>
				<br /><span style="color:#aaa;font-size:0.75em;">via <a href="https://www.instagram.com/" target="_blank">Instagram</a>, <a href="https://www.instagram.com/<?php echo $image['name'];?>/" target="_blank"><?php echo $image['name']; ?></a></span>
				<?php else: ?>
				<br /><span style="color:#aaa;font-size:0.75em;">via <a href="https://www.instagram.com/" target="_blank">Instagram</a>, <a href="<?php echo $image['link'];?>" target="_blank"><?php echo $image['link']; ?></a></span>
				<?php endif; ?>
			<?php endif; ?>
			<div style="position:absolute;top:0;right:0;"><a href="#" class="close-captions" style="display:block;padding:5px;color:#444;"><i class="fa fa-times" aria-hidden="true"></i></a></div>
		</div>
		</div>
	</div>-->

<?php endforeach; ?>
</div>

<script>
  jQuery(document).ready(function(){
    var slider = jQuery('.slider').bxSlider({
		mode: 'fade',
		speed: 300,
		adaptiveHeight: true,
		buildPager: function(slideIndex){
			return '<i class="fa fa-circle" aria-hidden="true"></i>';
		},
		pagerType: 'full',
		nextText: '<i class="fa fa-angle-right" aria-hidden="true"></i>',
		prevText: '<i class="fa fa-angle-left" aria-hidden="true"></i>',
		keyboardEnabled: true
	});
	
	jQuery('.close-captions, .open-captions').on('click', function() {
		jQuery('.captions').toggleClass('hidden');
		jQuery('.slider').toggleClass('plus');
		slider.redrawSlider();
		return false;
	});
  });
</script>