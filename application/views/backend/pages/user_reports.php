<div class="panel">
	<div class="panel-body">
		<div class="row">
			<div class="col-md-1"></div>
			<div class="col-md-4">
				<select class="select2 form-control select2-multiple" data-toggle="select2" name="month" id="month" data-placeholder="Choose ...">
					<option value="January" 	<?php if($month == 'January')echo 'selected';?>>January</option>
					<option value="February" 	<?php if($month == 'February')echo 'selected';?>>February</option>
					<option value="March" 		<?php if($month == 'March')echo 'selected';?>>March</option>
					<option value="April" 		<?php if($month == 'April')echo 'selected';?>>April</option>
					<option value="May" 		<?php if($month == 'May')echo 'selected';?>>May</option>
					<option value="June" 		<?php if($month == 'June')echo 'selected';?>>June</option>
					<option value="July" 		<?php if($month == 'July')echo 'selected';?>>July</option>
					<option value="August" 		<?php if($month == 'August')echo 'selected';?>>August</option>
					<option value="September" 	<?php if($month == 'September')echo 'selected';?>>September</option>
					<option value="October" 	<?php if($month == 'October')echo 'selected';?>>October</option>
					<option value="November" 	<?php if($month == 'November')echo 'selected';?>>November</option>
					<option value="December" 	<?php if($month == 'December')echo 'selected';?>>December</option>
				</select>
			</div>
			<div class="col-md-4">
				<select class="select2 form-control select2-multiple" data-toggle="select2" name="month" id="year" data-placeholder="Choose ...">
					<option value="2019" <?php if($year == '2019')echo 'selected';?>>2019</option>
					<option value="2018" <?php if($year == '2018')echo 'selected';?>>2018</option>
					<option value="2017" <?php if($year == '2017')echo 'selected';?>>2017</option>
				</select>
			</div>
			<div class="col-md-2 text-center">
				<button type="button" onClick="submit()" class="btn btn-success btn-cons" style="margin-top: 8px;">Filter</button>
			</div>
		</div>
	</div>
</div>
<div class="panel panel-primary">
	<div class="panel-heading">
		<div class="panel-title">
			<?php echo get_phrase('purchased_movie_/_Series_by_customer'); ?>
		</div>
	</div>
	<div class="panel-body">
		<table class="table table-bordered datatable" id="table_export">
			<thead>
				<tr>
					<th>
						#
					</th>
					<th>Date</th>
					<th>Purchased Movie/Series</th>
					<th>Paid Amount</th>
					<th>Method</th>
					<th>User</th>
					<th>Option</th>
				</tr>
			</thead>
			<tbody>
				<?php
					$subscriptions	=	$this->crud_model->get_user_purchase_report($month, $year);
					$counter 		=	1;
					$total_sale		=	0;

					foreach ($subscriptions as $row):
					  $total_sale	+=	$row['paid_amount'];
				?>
				<tr>
					<td><?php echo $counter++;?></td>
					<td><?php echo date("d M, Y" , $row['timestamp']);?></td>
					<?php
						if($row['movie_id'] != ""){
					?>
						<td><?php echo $this->db->get_where('movie', array('movie_id'=>$row['movie_id']))->row()->title; ?></td>
					<?php } ?>

					<?php
						if($row['series_id'] != ""){
					?>
						<td><?php echo $this->db->get_where('series', array('series_id'=>$row['series_id']))->row()->title;?></td>
					<?php } ?>

					<td><?php echo $row['currency'] . ' ' . $row['paid_amount'];?></td>
					<td><?php echo $row['payment_method'];?></td>
					<td><?php echo $this->db->get_where('user', array('user_id'=>$row['user_id']))->row()->email;?></td>
					<td><a href="<?php echo base_url();?>index.php?admin/report_invoice/<?php echo $row['purchase_id'].'/'.$row['user_id']; ?>" class="btn btn-primary" target="blank"><i class="fa fa-print"></i></a></td>
				</tr>
				<?php endforeach;?>
			</tbody>
		</table>
		<!-- <hr> -->
		<!-- <div style="text-align: center;">
			Total sale : <?php echo '$' . $total_sale;?>
		</div> -->
	</div>
</div>
            

<script>
	function submit()
	{
		year  = document.getElementById("year").value;
		month = document.getElementById("month").value;
		window.location = "<?php echo base_url();?>index.php?admin/report/" + month + "/" + year;
	}
</script>
