<div id="side" <?php bzb_layout_side(); ?> role="complementary" itemscope="itemscope" itemtype="http://schema.org/WPSideBar">
	<div class="side-inner">
    	<div class="side-widget-area">

<?php 
global $lecturer_id;
$pages = retrieve_recommended_pages(true, $lecturer_id ?? 0);
?>

			<ul>

<?php
for($i = 0; $i < 20 && !empty($pages); $i++) :
	$page = array_splice($pages, mt_rand(0, count($pages) - 1), 1)[0];
?>

				<li class="search-result">
	                <?php echo_whiteboard($page); ?>
				</li>

<?php
endfor;
?>

			</ul>

      	</div><!-- //side-widget-area -->
    </div>
</div><!-- /side -->
