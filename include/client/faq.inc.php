<?php
if(!defined('OSTCLIENTINC') || !$faq  || !$faq->isPublished()) die('Access Denied');

$category=$faq->getCategory();

?>

<div class="row"> 
	<div class="col-xs-12"> 
    	
    	<div class="page-title">  
			<h1><?php echo __('Frequently Asked Questions');?></h1>
		</div>

		<ol class="breadcrumb bordered">
		  <li><a href="index.php"><?php echo __('All Categories');?></a></li>
		  <li><a href="faq.php?cid=<?php echo $category->getId(); ?>"><?php echo $category->getName(); ?></a></li>
		</ol>
 
		<div class="faq-title text-center">
			<h2><?php echo $faq->getQuestion() ?></h2>
		</div>
		
		<div class="well well-lg">
			<?php echo Format::safe_html($faq->getAnswerWithImages()); ?>
		</div>
		<hr>

		<?php
		if($faq->getNumAttachments()) { ?>
			<p><b><?php echo __('Attachments');?>:</b> <?php echo $faq->getAttachmentsLinks(); ?></p>
			<hr>
		<?php
		} ?>

		<p><b><?php echo __('Help Topics');?>:</b> <?php echo ($topics=$faq->getHelpTopics())?implode(', ',$topics):' '; ?></p>

		<hr>
		<p><small><?php echo __('Last updated').' '.Format::db_daydatetime($category->getUpdateDate()); ?></small></p>
	</div>
	<!-- /.col -->
</div>
<!-- /.row -->
