<?php
/*********************************************************************
    index.php

    Helpdesk landing page. Please customize it to fit your needs.

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('client.inc.php');
$section = 'home';
require(CLIENTINC_DIR.'header.inc.php');
?>
<div class="landing-page">
	
	<div class="row">
		<div class="col-xs-12 col-md-10 col-md-offset-1 text-center">
		    <?php
		    if($cfg && ($page = $cfg->getLandingPage()))
		        echo $page->getBodyWithImages();
		    else
		        echo  '<h1>'.__('Welcome to the Support Centre').'</h1>';
		    ?>
		</div>
		<!-- /.col -->
	</div>
	<!-- /.row -->

    <div class="row text-center">		    
	    <div class="col-xs-12">
		<!-- <<code>> -->
		<?php if( $cfg->isCreateTicketOnline()) { ?>
		    <a href="open.php" class="block-box">
			    <i data-icon=">" class="icon"></i>
			    <?php echo __('Open a New Ticket');?>
		    </a>
		<?php } ?>
		<!-- <<code>> -->
		
			<a href="<?php if(is_object($thisclient)){ echo 'tickets.php';} else {echo 'view.php';}?>" class="block-box">
			    <i data-icon="~" class="icon"></i>
			    <?php echo __('Check Ticket Status');?>
		    </a>
	    </div>
	    <!-- /.col -->		    
    </div>
    <!-- /.row -->

	<?php
	if($cfg && $cfg->isKnowledgebaseEnabled()){
	    //FIXME: provide ability to feature or select random FAQs ??
	?>
	<section class="kb well space-top-2x padding-bottom-2x">
		<div class="row text-center">
			<div class="col-md-8 col-md-offset-2">
				<h5><?php echo sprintf(__('Be sure to browse our %s before opening a ticket'), sprintf('<a href="kb/index.php">%s</a>', __('Frequently Asked Questions (FAQs)'))); ?></h5>
			</div>
			<!-- /.col -->
		</div>
		<!-- /.row -->
		<div class="row text-center">
			<div class="col-md-8 col-md-offset-2">
				<form action="kb/index.php" method="get" id="kb-search">
	                <div class="row">
		                <div class="col-md-8">
			                <input type="text" class="form-control space-bottom" placeholder="<?php echo sprintf(__('Search %s'),__('Frequently Asked Questions (FAQs)')); ?>" name="q">
		                </div>
		                <!-- /.col -->
		                <div class="col-md-4">
			                <button class="btn btn-success btn-block" type="submit" id="searchSubmit"><?php echo __('Search');?></button>
		                </div>
		                <!-- /.col -->	                
	                </div>
	                <!-- /.row -->
	            </form>
			</div>
			<!-- /.col -->
		</div>
		<!-- /.row -->
	</section>
    <?php
	} ?>
</div>
<!-- /.landing-page -->
	
<?php require(CLIENTINC_DIR.'footer.inc.php'); ?>
