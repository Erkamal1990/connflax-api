<div class="row ">
  <div class="col-lg-12">
    <a href="<?php echo base_url();?>index.php?admin/category_create/" class="btn btn-primary" style="float:right; margin-top: -45px; margin-bottom: 20px;">
	<i class="fa fa-plus"></i>
		Create category
	</a>
  </div><!-- end col-->
</div>

<div class="panel panel-primary">
	<div class="panel-heading">
		<div class="panel-title">
			<?php echo get_phrase('categories_list'); ?>
		</div>
	</div>
	<div class="panel-body">
		<?php 
	    	$success_category = $this->session->flashdata("success_category");
	    	if(isset($success_category)){
	    		echo '<p class="alert alert-success">'.$this->session->flashdata("success_category").'</p>';
	    	}
	    ?>
		<table class="table table-bordered datatable" id="table_export">
			<thead>
				<tr>
					<th>
						#
					</th>
					<th></th>
					<th>Category Name</th>
					<th>Operation</th>
				</tr>
			</thead>
			<tbody>
				<?php
					$categories = $this->db->get('categories')->result_array();
					$counter = 1;
					foreach ($categories as $row):
					  ?>
				<tr>
					<td style="vertical-align: middle;"><?php echo $counter++;?></td>
					<td><img src="<?php echo $this->crud_model->get_category_image_url($row['category_id']);?>" style="height: 60px;" /></td>
					<td style="vertical-align: middle;"><?php echo $row['name'];?></td>
					<td style="vertical-align: middle;">
						<a href="<?php echo base_url();?>index.php?admin/category_edit/<?php echo $row['category_id'];?>" class="btn btn-info">
						edit</a>
						<a href="<?php echo base_url();?>index.php?admin/category_delete/<?php echo $row['category_id'];?>" class="btn btn-danger" onclick="return confirm('Want to delete?')">
						delete</a>
					</td>
				</tr>
				<?php endforeach;?>
			</tbody>
		</table>
	</div>
</div>