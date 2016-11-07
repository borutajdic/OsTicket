<?php
$info = $_POST;
if (!isset($info['timezone_id']))
    $info += array(
        'timezone_id' => $cfg->getDefaultTimezoneId(),
        'dst' => $cfg->observeDaylightSaving(),
        'backend' => null,
    );
if (isset($user) && $user instanceof ClientCreateRequest) {
    $bk = $user->getBackend();
    $info = array_merge($info, array(
        'backend' => $bk::$id,
        'username' => $user->getUsername(),
    ));
}
$info = Format::htmlchars(($errors && $_POST)?$_POST:$info);

?>

<div class="row">
	<div class="col-xs-12">
		<h1><?php echo __('Account Registration'); ?></h1>
		<p><?php echo __(
		'Use the forms below to create or update the information we have on file for your account'
		); ?>
		</p>
	</div>
	<!-- /.col -->
</div>
<!-- /.row -->

<form action="account.php" method="post">
	<?php csrf_token(); ?>
	<input type="hidden" name="do" value="<?php echo Format::htmlchars($_REQUEST['do']
	?: ($info['backend'] ? 'import' :'create')); ?>" />

	<div class="row">
		<div class="col-sm-6">
			<?php
			    $cf = $user_form ?: UserForm::getInstance();
			    $cf->render(false);
			?>
		</div>
		<!-- /.col -->
		<div class="col-sm-6">
			<h3><?php echo __('Preferences'); ?></h3>
			<div class="form-group">
				<label><?php echo __('Time Zone'); ?></label>
				<select name="timezone_id" id="timezone_id" class="form-control">
		            <?php
		            $sql='SELECT id, offset,timezone FROM '.TIMEZONE_TABLE.' ORDER BY id';
		            if(($res=db_query($sql)) && db_num_rows($res)){
		                while(list($id,$offset, $tz)=db_fetch_row($res)){
		                    $sel=($info['timezone_id']==$id)?'selected="selected"':'';
		                    echo sprintf('<option value="%d" %s>GMT %s - %s</option>',$id,$sel,$offset,$tz);
		                }
		            }
		            ?>
		        </select>
		        <span class="error"><?php echo $errors['timezone_id']; ?></span>
			</div>
			<!-- /.form-group -->
			<div class="form-group">
				<label class="checkbox-inline">
					<input type="checkbox" name="dst" value="1" <?php echo $info['dst']?'checked="checked"':''; ?>>
					<?php echo __('Observe daylight saving'); ?>
					<em>(<?php echo __('Current Time'); ?>: <?php echo Format::date($cfg->getDateTimeFormat(),Misc::gmtime(),$info['tz_offset'],$info['dst']); ?>)</em>
				</label>
			</div>
			<!-- /.form-group -->
		</div>
		<!-- /.col -->
	</div>
	<!-- /.row -->
	
	<div class="row">
		<div class="col-xs-12">
			<h3><?php echo __('Access Credentials'); ?></h3>
		</div>
		<!-- /.col -->
		<?php if ($info['backend']) { ?>
			<div class="col-sm-6">
				<?php echo __('Login With'); ?>
			</div>
			<!-- /.col -->
			<div class="col-sm-6">
				<input type="hidden" name="backend" value="<?php echo $info['backend']; ?>"/>
		        <input type="hidden" name="username" value="<?php echo $info['username']; ?>"/>
				<?php foreach (UserAuthenticationBackend::allRegistered() as $bk) {
				    if ($bk::$id == $info['backend']) {
				        echo $bk->getName();
				        break;
				    }
				} ?>
			</div>
			<!-- /.col -->
		<?php } else { ?>
			<div class="col-sm-6">
				<label><?php echo __('Create a Password'); ?></label>
				<input type="password" size="18" name="passwd1" value="<?php echo $info['passwd1']; ?>" class="form-control">
				<span class="error">&nbsp;<?php echo $errors['passwd1']; ?></span>
			</div>
			<!-- /.col -->
			<div class="col-sm-6">
				<label><?php echo __('Confirm New Password'); ?></label>
				<input type="password" size="18" name="passwd2" value="<?php echo $info['passwd2']; ?>" class="form-control">
				<span class="error">&nbsp;<?php echo $errors['passwd2']; ?></span>
			</div>
			<!-- /.col -->
		<?php } ?>
	</div>
	<!-- /.row -->
	
	<div class="row">
		<div class="col-xs-12">
			<div class="btn-group pull-right">
				<button type="button" class="btn btn-default" onclick="javascript:window.location.href='index.php';"><i class="fa fa-times"></i> Cancel</button>
				<button type="submit" class="btn btn-success"><i class="fa fa-check"></i> Register</button>
			</div>
		</div>
		<!-- /.col -->
	</div>
	<!-- /.row -->

</form>

