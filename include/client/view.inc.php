<?php
if(!defined('OSTCLIENTINC') || !$thisclient || !$ticket || !$ticket->checkUserAccess($thisclient)) die('Access Denied!');

/* <<code>> */
if(isset($_GET['action'])){
    if(($_GET['action'] == 'close')){
        $ticket->setStatus(7); // equivalent to status = closed
		$ticket->setAnsweredState(1);
    }
	else {
		echo __('Ni mogoče');
	}    
}
/* <<code>> */

$info=($_POST && $errors)?Format::htmlchars($_POST):array();

$dept = $ticket->getDept();

if ($ticket->isClosed() && !$ticket->isReopenable())
    $warn = __('This ticket is marked as closed and cannot be reopened.');

//Making sure we don't leak out internal dept names
if(!$dept || !$dept->isPublic())
    $dept = $cfg->getDefaultDept();

if ($thisclient && $thisclient->isGuest()
    && $cfg->isClientRegistrationEnabled()) { ?>


    <div id="alert alert-info">
        <i class="fa fa-compass fa-2x"></i>
        <strong><?php echo __('Looking for your other tickets?'); ?></strong></br>
        <a href="<?php echo ROOT_PATH; ?>login.php?e=<?php
            echo urlencode($thisclient->getEmail());
        ?>"><?php echo __('Sign In'); ?></a>
        <?php echo sprintf(__('or %s register for an account %s for the best experience on our help desk.'),
            '<a href="account.php?do=create">','</a>'); ?>
    </div>
    <!-- /.alert -->


<?php } ?>

<div class="row"> 

    <div class="col-xs-12"> 
        <header class="page-title text-center">   
            <h1>
                <?php echo sprintf(__('Ticket #%s'), $ticket->getNumber()); ?> &nbsp;
                        <a class="tkt-refresh" href="tickets.php?id=<?php echo $ticket->getId(); ?>" title="Reload"><i class="icon-refresh"></i></a>
                <?php if ($cfg->allowClientUpdates()
                // Only ticket owners can edit the ticket details (and other forms)
                && $thisclient->getId() == $ticket->getUserId()) { ?>
                        <a class="action-button pull-right tkt-edit" href="tickets.php?a=edit&id=<?php
                             echo $ticket->getId(); ?>"><i class="icon-edit"></i> Edit</a>
                <?php } ?>
            </h1>
        </header>
    </div>
    <!-- /.col -->
    
</div>
<!-- /.row -->

<div class="row"> 
    <div class="col-xs-6">
	    <div class="table-responsive">
		    <table class="table table-bordered table-striped">
		        <tr>
		            <th width="160"><?php echo __('Ticket Status');?>:</th>
		            <td><?php echo $ticket->getStatus(); ?></td>
		        </tr>
		        <tr>
		            <th><?php echo __('Department');?>:</th>
		            <td><?php echo Format::htmlchars($dept instanceof Dept ? $dept->getName() : ''); ?></td>
		        </tr>
		        <tr>
		            <th><?php echo __('Create Date');?>:</th>
		            <td><?php echo Format::db_datetime($ticket->getCreateDate()); ?></td>
		        </tr>
		   </table>
	    </div>
	    <!-- /.table-responsive -->
    </div>
	<!-- /.col -->
	<div class="col-xs-6">
		<div class="table-responsive">
	       	<table class="table table-bordered table-striped">
	           <tr>
	               <th width="160"><?php echo __('Name');?>:</th>
	               <td><?php echo mb_convert_case(Format::htmlchars($ticket->getName()), MB_CASE_TITLE); ?></td>
	           </tr>
	           <tr>
	               <th width="160"><?php echo __('Email');?>:</th>
	               <td><?php echo Format::htmlchars($ticket->getEmail()); ?></td>
	           </tr>
	           <tr>
	               <th><?php echo __('Phone');?>:</th>
	               <td><?php echo $ticket->getPhoneNumber(); ?></td>
	           </tr>
	        </table>
		</div>
		<!-- /.table-responsive -->
    </div>
	<!-- /.col -->
</div>
<!-- /.row -->

<!-- <<code>> -->
<?php
if($ticket->isOpen()){?>
	<a class="btn btn-success action-button" href="tickets.php?id=<?php echo $ticket->getId(); ?>&action=close"><?php echo __('Close Ticket') ?></a>	
<?php
}
?>
<!-- <<code>> -->

<div class="row"> 
	<?php
    foreach (DynamicFormEntry::forTicket($ticket->getId()) as $idx=>$form) {
        $answers = $form->getAnswers();
        if ($idx > 0 and $idx % 2 == 0) { ?>
        <?php } ?>
	    <div class="col-xs-12">
		    <div class="table-responsive">
		        <table class="table table-bordered table-striped">
		            <?php foreach ($answers as $answer) {
	                    if (in_array($answer->getField()->get('name'), array('name', 'email', 'subject')))
	                        continue;
	                    elseif ($answer->getField()->get('private'))
	                        continue;
	                    ?>
	                    <tr>
	                    <th width="160"><?php echo $answer->getField()->get('label');
	                        ?>:</th>
	                    <td><?php echo $answer->display(); ?></td>
	                    </tr>
	                <?php } ?>
		       </table>
		    </div>
		    <!-- /.table-responsive -->
	    </div>
		<!-- /.col -->
	<?php } ?>
</div>
<!-- /.row -->

<div class="row">
	<div class="col-xs-12">
		<h5 class="blue"><span class="weak"><?php echo __('Subject'); ?>:</span><?php echo Format::htmlchars($ticket->getSubject()); ?></h5>
	</div>
	<!-- /.col -->
</div>
<!-- /.row -->

<div class="row">
	<div class="col-xs-12">
	<?php
        if($ticket->getThreadCount() && ($thread=$ticket->getClientThread())) {
            $threadType=array('M' => 'message', 'R' => 'response');
            foreach($thread as $entry) {

                //Making sure internal notes are not displayed due to backend MISTAKES!
                if(!$threadType[$entry['thread_type']]) continue;
                $poster = $entry['poster'];
                if($entry['thread_type']=='R' && ($cfg->hideStaffName() || !$entry['staff_id']))
                    $poster = ' ';
                ?>
                <div class="table-responsive">
                    <table class="thread-entry <?php echo $threadType[$entry['thread_type']]; ?> table table-bordered table-striped">
                        <tr>
	                        <th>
		                        <div><?php echo Format::db_datetime($entry['created']); ?>&nbsp;&nbsp;<span class="textra"></span><span><?php echo $poster; ?></span>
								</div>
                        	</th>
                        </tr>
                        <tr>
	                        <td class="threadbody">
		                        <div><?php echo Format::clickableurls($entry['body']->toHtml()); ?></div>
		                    </td>
		                </tr>
                        <?php
                        if($entry['attachments']
                                && ($tentry=$ticket->getThreadEntry($entry['id']))
                                && ($urls = $tentry->getAttachmentUrls())
                                && ($links=$tentry->getAttachmentsLinks())) { ?>
                        <tr>
	                        <td class="info">
		                        <?php echo $links; ?>
		                    </td>
		                </tr>
						<?php   }
						if ($urls) { ?>
	                        <script type="text/javascript">
	                            $(function() { showImagesInline(<?php echo
	                                JsonDataEncoder::encode($urls); ?>); });
	                        </script>
						<?php   } ?>
                	</table>
                </div>
                <!-- /.table-responsive -->
            <?php
            } // End foreach
        } // End if
        ?>
	</div>
	<!-- /.col -->
</div>
<!-- /.row -->

    <div class="clear" style="padding-bottom:10px;"></div>
    <?php if($errors['err']) { ?>
        <div class="alert alert-danger" id="msg_error"><?php echo $errors['err']; ?></div>
    <?php }elseif($msg) { ?>
        <div class="alert alert-info" id="msg_notice"><?php echo $msg; ?></div>
    <?php }elseif($warn) { ?>
        <div class="alert alert-warning" id="msg_warning"><?php echo $warn; ?></div>
    <?php } ?>
    
<?php
if (!$ticket->isClosed() || $ticket->isReopenable()) { ?>
<form id="reply" action="tickets.php?id=<?php echo $ticket->getId(); ?>#reply" name="reply" method="post" enctype="multipart/form-data">
	
	<?php csrf_token(); ?>
	<input type="hidden" name="id" value="<?php echo $ticket->getId(); ?>">
    <input type="hidden" name="a" value="reply">
	
	<div class="row">
		<div class="col-xs-12">
			<h2><?php echo __('Post a Reply');?></h2>
		</div>
		<!-- /.col -->
	</div>
	<!-- /.row -->
	
	<div class="row">
		<div class="col-xs-12">
			<div class="form-group">>
	            <?php
                if($ticket->isClosed()) {
                    $msg='<label>'.__('Ticket will be reopened on message post').'</label>';
                } else {
                    $msg='<label>'.__('To best assist you, we request that you be specific and detailed').'</label>';
                }
                ?>
	            <span id="msg"><em><?php echo $msg; ?> </em></span><font class="error">*&nbsp;<?php echo $errors['message']; ?></font>
		            <textarea name="message" id="message" cols="50" rows="9" wrap="soft"
                    data-draft-namespace="ticket.client"
                    data-draft-object-id="<?php echo $ticket->getId(); ?>"
                    class="richtext ifhtml draft form-control"><?php echo $info['message']; ?></textarea>
                    <?php
                    if ($messageField->isAttachmentsEnabled()) { ?>
            <?php
                        print $attachments->render(true);
                        print $attachments->getForm()->getMedia();
            ?>
                    <?php
                    } ?>
            </div>
            <!-- /.form-group -->
		    
		</div>
		<!-- /.col -->
	</div>
	<!-- /.row -->
	
	<div class="row">
		<div class="col-xs-12">
            <button class="btn btn-success pull-right" type="submit"><i class="fa fa-check"></i> <?php echo __('Post Reply');?></button>
		    <button class="btn btn-default" type="reset"><i class="fa fa-refresh"></i> <?php echo __('Reset');?></button>
			<button class="btn btn-default" type="button" onClick="history.go(-1)"><i class="fa fa-times"></i> <?php echo __('Cancel'); ?></button>
			
		</div>
		<!-- /.col -->
	</div>
	<!-- /.row -->

</form>

<?php } ?>
