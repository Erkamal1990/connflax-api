<form method="post" action="<?php echo base_url();?>index.php?admin/settings" enctype="multipart/form-data">
	<div class="row">
		<div class="col-md-6">
	        <div class="panel panel-primary">
				<div class="panel-heading">
					<div class="panel-title" style="height: 20px;">
						
					</div>
				</div>
				<div class="panel-body">
					<div class="form-group mb-3">
						<label for="simpleinput1">Website Name</label>
						<input type="text" class="form-control" id = "simpleinput1" name="site_name" value="<?php echo $site_name;?>">
					</div>
					<div class="form-group mb-3">
						<label for="simpleinput2">Website Email</label>
						<input type="text" class="form-control" id = "simpleinput2" name="site_email" value="<?php echo $site_email;?>">
					</div>

					<div class="form-group mb-3">
                        <label for="example-select">Trial Period Functionality</label>
                        <select class="form-control" id="example-select" name="trial_period">
							<option value="on" <?php if ($trial_period == 'on')echo 'selected';?>>On</option>
							<option value="off" <?php if ($trial_period == 'off')echo 'selected';?>>Off</option>
                        </select>
                    </div>

					<div class="form-group mb-3">
						<label for="simpleinput3">Trial Period Number of days</label>
						<input type="number" min="0" class="form-control" id = "simpleinput3" name="trial_period_days" value="<?php echo $trial_period_days;?>">
					</div>

					<!-- WEBSITE LANGUAGE SETTINGS -->
					<div class="form-group mb-3">
                        <label for="example-select2">Website Language</label>
                        <select class="form-control" id="example-select2" name="language">
							<?php foreach ($languages as $language): ?>
				                <option value="<?php echo $language; ?>" <?php if(get_settings('language') == $language) echo 'selected'; ?>><?php echo ucfirst($language); ?></option>
				             <?php endforeach; ?>
                        </select>
                    </div>

					<!-- WEBSITE TEMPLATE SETTINGS -->
					<div class="form-group mb-3">
                        <label for="example-select3">Website Theme</label>
                        <select class="form-control" id="example-select3" name="theme">
							<?php
								$themes = directory_map('./application/views/frontend/', 1);
								//print_r($themes);
								for($i = 0; $i < sizeof($themes) ; $i++) {
									if ($themes[$i] == 'index.php')
										continue;
									$themes[$i] = substr($themes[$i], 0, -1);
									?>
									<option value="<?php echo $themes[$i];?>" <?php if ($theme == $themes[$i])echo 'selected';?>>
										<?php echo $themes[$i];?></option>
									<?php
								}
							?>
                        </select>
                    </div>

					<div class="form-group mb-3">
                        <label for="simpleinput8">Invoice address</label>
                        <input type="text" id="simpleinput8" class="form-control" name="invoice_address" value="<?php echo $invoice_address;?>">
                    </div>

					<div class="form-group mb-3">
                        <label for="simpleinput9">Envato purchase code</label>
                        <input type="text" id="simpleinput9" class="form-control" name="purchase_code" value="<?php echo $purchase_code;?>">
                    </div>
                    <hr>
                    <div class="form-group">
                        <label for="cookie_status"><?php echo get_phrase('cookie_status'); ?></label><br>
                        <input type="radio" value="active" name="cookie_status" <?php if(get_settings('cookie_status') == 'active') echo 'checked'; ?>> <?php echo get_phrase('active'); ?>
                        &nbsp;&nbsp;
                        <input type="radio" value="inactive" name="cookie_status" <?php if(get_settings('cookie_status') == 'inactive') echo 'checked'; ?>> <?php echo get_phrase('inactive'); ?>
                    </div>
                    <div class="form-group">
                        <label for="cookie_note"><?php echo get_phrase('cookie_note'); ?></label>
	                    <textarea class="form-control" rows="4" name="cookie_note" id="cookie_note" placeholder="<?php echo get_phrase('cookie_note'); ?>"><?php echo get_settings('cookie_note'); ?></textarea>
                    </div>
	            </div>
	        </div>
	    </div>

		<div class="col-md-6">
	        <div class="panel panel-primary">
				<div class="panel-heading">
					<div class="panel-title" style="height: 20px;">
						
					</div>
				</div>
				<div class="panel-body">
	            	<div class="form-group mb-3">
		            	<div class="row">
		            		<div class="col-md-6">
								<label class="form-label">Website logo</label>
								<span class="help"></span>
								<div class="controls">
									<input type="file" class="form-control" name="logo" />
								</div>
							</div>
							<div class="col-md-6 text-center" style="padding-top: 20px;">
								<img src="<?php echo base_url();?>assets/global/logo.png" height="20" />
							</div>
						</div>
					</div>

					<div class="form-group mb-3">
                        <label for="example-textarea">Website privacy policy</label>
                        <textarea class="form-control" id="ckeditor1" name="privacy_policy" rows="3"><?php echo $privacy_policy;?></textarea>
                    </div>

					<div class="form-group mb-3">
                        <label for="example-textarea">Website refund policy</label>
                        <textarea class="form-control" id="ckeditor2" name="refund_policy" rows="3"><?php echo $refund_policy;?></textarea>
                    </div>
                    <div class="form-group mb-3">
                        <label for="example-textarea"><?php echo get_phrase('cookie_policy'); ?></label>
                        <textarea class="form-control" id="ckeditor3" name="cookie_policy" rows="3"><?php echo get_settings('cookie_policy'); ?></textarea>
                    </div>
	            </div>
	        </div>
	    </div>
		<div class="col-md-12 text-left">
				<input type="submit" class="btn btn-primary" value="Update Website Settings">
		</div>
	</div>
</form>
	
<hr>
<div class="row" style="margin-top: 50px;">
	<div class="col-md-6">
		<?php echo form_open(site_url('index.php?updater/update') , array('class' => 'form-horizontal form-groups-bordered', 'enctype' => 'multipart/form-data'));?>
			<div class="panel panel-primary">
				<div class="panel-heading">
					<div class="panel-title">
						Update Product
					</div>
				</div>
				<div class="panel-body" style="margin: 20px;">
					<div class="form-group">
                        <label><?php echo get_phrase('file'); ?></label>
                    	<input type="file" class="form-control" name="file_name" data-label="<i class='fa fa-file'></i> Browse" required/>
                    </div>

                    <div class="form-group">
                        <input type="submit" class="btn btn-info" value="<?php echo get_phrase('install_update'); ?>" />
                    </div>
					
				</div>
			</div>
		<?php echo form_close(); ?>
	</div>
</div>

<script>
	$('document').ready(function(){
		CKEDITOR.replace( 'ckeditor1' );
		CKEDITOR.replace( 'ckeditor2' );
		CKEDITOR.replace( 'ckeditor3' );
	});
</script>
