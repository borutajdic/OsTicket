<h3><?php echo Format::htmlchars($form->getTitle()); ?></h3>
<em><?php echo Format::htmlchars($form->getInstructions()); ?></em>

<?php
// Form fields, each with corresponding errors follows. Fields marked
// 'private' are not included in the output for clients
global $thisclient;
foreach ($form->getFields() as $field) {
    if (!$field->isVisibleToUsers())
        continue;
    ?>
    <div class="row">
	    <div class="col-xs-12">
		    <div class="form-group">
		        <?php if ($field->isBlockLevel()) { ?>
		            <label for="<?php echo $field->getFormName(); ?>" class="<?php
		                if ($field->get('required')) echo 'required'; ?>">
		            <?php echo Format::htmlchars($field->get('label')); ?>
		            <?php if ($field->get('required')) { ?>
			            <font class="error">*</font>
			        <?php
			        }?>
		            </label>
		        <?php
		        }
		        else { ?>
		            <label for="<?php echo $field->getFormName(); ?>" class="<?php
		                if ($field->get('required')) echo 'required'; ?>">
		            <?php echo Format::htmlchars($field->get('label')); ?>
		            <?php if ($field->get('required')) { ?>
			            <font class="error">*</font>
			        <?php
			        }
			        ?>
		            </label>
		        <?php
		        }
		        $field->render('client');
		        if ($field->get('hint') && !$field->isBlockLevel()) { ?>
		            <br /><em style="color:gray;display:inline-block"><?php
		                echo Format::htmlchars($field->get('hint')); ?></em>
		        <?php
		        }
		        foreach ($field->errors() as $e) { ?>
		            <br />
		            <font class="error"><?php echo $e; ?></font>
		        <?php }
		        $field->renderExtras('client');
		        ?>
		    </div>
		    <!-- /.form-group -->
	    </div>
	    <!-- /.col -->
    </div>
	<!-- /.row -->
    
<?php } ?>