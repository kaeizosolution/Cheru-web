
<div class="wrapper">

	<div class="imosysnew-Breadcrumb">
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-12">
				 <?= $breadcrumbs ?>
                    <div class="col-md-12 all-product-grid" id="post-<?php echo (isset($detail->product_id)) ? $detail->product_id : ''; ?>">
			<?php if($detail){
				if(isset($detail->featured_image) && $detail->featured_image){
					$img = (string)$detail->featured_image;
					$imgUrl = base_url('assets/images/'.$img);
					echo '<div class="mb-4 text-center"><img src="'.$imgUrl.'" alt="" style="max-width:520px; width:100%; height:auto; border-radius:12px;"></div>';
				}
				$content = (isset($detail->post_content)) ? (string)$detail->post_content : '';
				if($content !== '' && $content === strip_tags($content)){
					echo nl2br($content);
				}else{
					$sanitized = $content;
					$sanitized = preg_replace('#<(script|style)\b[^>]*>.*?</\1>#is', '', $sanitized);
					$sanitized = preg_replace('#<(head)\b[^>]*>.*?</\1>#is', '', $sanitized);
					$sanitized = preg_replace('#<(html|body)\b[^>]*>#is', '', $sanitized);
					$sanitized = preg_replace('#</(html|body)>#is', '', $sanitized);
					$sanitized = preg_replace('#<\s*(meta|link)\b[^>]*>#is', '', $sanitized);
					echo $sanitized;
				}
			} ?>

	</div>
      				</div>
			</div>
		</div>
	</div>

	


</div>
