		</div>
		<!-- /.container -->
	</section>
	<!-- /#main -->
	
	<footer>
		<div class="container text-center">
			<p>Copyright &copy; <?php echo date('Y'); ?> <?php echo (string) $ost->company ?: 'osTicket.com'; ?> - All rights reserved.</p>
	        <a href="http://osticket.com" target="_blank"><?php echo __('Helpdesk software - powered by osTicket'); ?></a> &nbsp;&nbsp;&nbsp; <a href="http://osticketthemes.com" target="_blank">Theme by osTicket Themes</a>
		</div>
		<!-- /.container -->
	</footer>


<div id="overlay"></div>
<div id="loading">
    <div class="modal" tabindex="-1" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">Please wait...</h4>
      			</div>
	  			<div class="modal-body">
		  			<div class="progress">
		  				<div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="45" aria-valuemin="0" aria-valuemax="100" style="width: 100%">
		  					<span class="sr-only">100% Complete</span>
  						</div>
					</div>
      			</div>

    		</div><!-- /.modal-content -->
  		</div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
</div>
<!-- /.loading -->

<!-- Scripts -->

<script type="text/javascript">
	$(document).ready(function() {
	    
	});
</script>

<!-- /Scripts -->

<?php
if (($lang = Internationalization::getCurrentLanguage()) && $lang != 'en_US') { ?>
    <script type="text/javascript" src="ajax.php/i18n/<?php
        echo $lang; ?>/js"></script>
<?php } ?>
</body>
</html>
