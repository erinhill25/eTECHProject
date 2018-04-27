<div class='conversationArea' style='float:none;margin:15px auto;'>
	<div class='conversationHeader'><a href='<?php echo SITE_PATH; ?>/home/courses/'><< Return to Courses</a></div>
	<div class='page'>
		<h1>Course Materials for <?php echo $course->Name; ?></h1>
		<?php
		if(count($resources) == 0) { ?>
		<h2>No materials found for this course</h2>
		<?php 
		} 
		?>
		<div class='resource-list'>
	    <?php
		foreach($resources AS $resource) {
		?>
			<div class='resource-single'>
			<?php if(!empty($resource->Image)) { ?><img src="<?php echo BUCKET; ?>/resources/<?php echo $resource->Image; ?>" alt="<?php echo $resource->Name; ?>" class='resourceImage' / ><?php } else { ?>
			<div class='resourceImage' style='height:200px;'></div>
			<?php } ?>
			<div class='resource-data'>
			<h2><?php echo $resource->Name; ?><?php if(!empty($resource->Edition)) { ?> - <?php echo $resource->Edition; ?> Edition<?php } ?></h2>

				<div class='resource-details'>
					<?php if(!empty($resource->Author)) { 
					echo $resource->Author . "<br />";
					} ?>
					<strong>ISBN-10</strong>: <?php echo $resource->ISBN10; ?><br />
					<strong>ISBN-13</strong>: <?php echo $resource->ISBN13; ?>
				</div>
			</div>
		</div>
		<?php
		}
		?>
		</div>

		<h1>Campus Bookstores</h1>
		<ul class='campusBooks'>
			<li><a href="http://clcbookstore.com/brainerd/home.aspx" target="_blank">Central Lakes College</a></li>
			<li><a href="https://hennepintech.edu/" target="_blank">Hennepin Technical College</a></li>
			<li><a href="http://store.lsc.edu/SiteText.aspx?id=8151" target="_blank">Lake Superior College</a></li>
			<li><a href="http://distanceminnesota.bkstr.com/" target="_blank">Northland Community and Technical College</a></li>
			<li><a href="http://distanceminnesota.bkstr.com/" target="_blank">Pine Technical and Community College</a></li>
			<li><a href="http://www.riverland.edu/bookstore/" target="_blank">Riverland Community College</a></li>
			<li><a href="http://www.saintpaulcollegebookstore.com/" target="_blank">Saint Paul College</a></li>
	    <ul>
		<br />
		<p>For more information, please go to the <a href="https://360etech.org/app/answers/detail/a_id/5199">Official 360 eTECH Books & Course Materials Page</a></p>
	</div>
</div>