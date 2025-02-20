<?php
	$director_detail = $this->db->get_where('director',array('director_id'=>$director_id))->row();
?>
<div class="row">
    <div class="col-md-2"></div>
    <div class="col-md-8">
        <div class="panel panel-primary">
        	<div class="panel-heading">
				<div class="panel-title">
					<?php echo get_phrase('director'); ?>
				</div>
			</div>
            <div class="panel-body">
				<form method="post" action="<?php echo base_url();?>index.php?admin/director_edit/<?php echo $director_id;?>" enctype="multipart/form-data">
					<div class="form-group mb-3">
	                    <label for="name">Actor Name</label>
	                    <input type="text" class="form-control" id = "name" name="name" value="<?php echo $director_detail->name;?>" required="">
	                </div>
					<div class="form-group mb-3">
	                    <label for="thumb">Image</label>
	                    <input type="file" class="form-control" name="thumb">
	                </div>
					
					<div class="form-group">
						<input type="submit" class="btn btn-success" value="Update">
						<a href="<?php echo base_url();?>index.php?admin/director_list" class="btn btn-black">Go back</a>
					</div>
				</form>
            </div>
        </div>
    </div>
</div>
