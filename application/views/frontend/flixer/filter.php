<?php include 'header_browse.php';?>

<div class="row" style="margin:20px 60px;">
	<h4 style="text-transform: capitalize;"><?php echo get_phrase('filter_by_cast'); ?></h4>
	<div class="content">
		<div class="grid">
			<div class="select">
				<select name="actor_id" id="actor_id" class="custom-select">
					<option value="all"><?php echo get_phrase('all_actors'); ?></option>
					<?php $actors = $this->db->get('actor')->result_array(); ?>
					<?php foreach ($actors as $key => $actor): ?>
						<option value="<?php echo $actor['actor_id']; ?>" <?php if ($actor['actor_id'] == $actor_id): ?>selected<?php endif; ?>><?php echo $actor['name']; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<button type="submit" class="btn btn-danger" style="float: left; margin-top: 15px;" onclick="submit('<?php echo $genre_id; ?>', '<?php echo $type; ?>')"><?php echo get_phrase('filter'); ?></button>
		</div>
	</div>
</div>

<!-- ITEM LIST, GENRE WISE LISTING -->
<div class="row" style="margin:20px 60px;">
	<h4 style="text-transform: capitalize;">
		<?php echo $this->db->get_where('genre', array('genre_id' => $genre_id))->row()->name;?>
		<?php
            if ($type == 'movie') {
                echo get_phrase('movies');
            }elseif ($type == 'series') {
                echo get_phrase('Tv_series');
            }
         ?>
		(<?php echo $total_result;?>)
	</h4>
	<div class="content">
		<div class="grid">
			<?php
			foreach ($items as $row)
			{
				$title	=	$row['title'];
                if ($type == 'movie') {
                    $link	=	base_url().'index.php?browse/playmovie/'.$row['movie_id'];
    				$thumb	=	$this->crud_model->get_thumb_url('movie' , $row['movie_id']);
                }elseif ($type == 'series') {
                    $link	=	base_url().'index.php?browse/playseries/'.$row['series_id'];
					$thumb	=	$this->crud_model->get_thumb_url('series' , $row['series_id']);
                }

				include 'thumb.php';
			}
			?>
		</div>
	</div>
	<div style="clear: both;"></div>
</div>
<div class="container" style="margin-top: 90px;">
	<hr style="border-top:1px solid #333;">
	<?php include 'footer.php';?>
</div>

<script>
    function submit(genre_id, type)
    {
        actor_id  = document.getElementById("actor_id").value;
        window.location = "<?php echo base_url();?>index.php?browse/filter/"+type+"/"+genre_id+"/"+actor_id;
    }
</script>
