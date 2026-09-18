<?php //echo "<pre>"; print_r($detail); echo "</pre>"; 
//echo $page->post_content;
?>
<section>
    <img src="/assets/frontend/images/p_banner.jpg" class="img-fluid w-100" alt="">
</section>
<section>
    <div class="container">
          <?= $breadcrumbs ?>
        <hr>
        <div class="row mb-4" >
            <div class="col-md-12">
                <!-- main slider carousel -->
                <div class="row">	
					<div class="pro-detail" id="post-<?php echo (isset($detail->product_id)) ? $detail->product_id : ''; ?>">
						<h1><?php echo (isset($detail->post_title)) ? $detail->post_title : ''; ?></h1>
						<?php 
						if($detail){ ?>
						<?php echo (isset($detail->post_content)) ? $detail->post_content : ''; ?>
						<?php } ?>	                         
					</div>
                </div>
                
            </div>
           
            <!--/main slider carousel-->
            <!-- thumb navigation carousel -->
        </div>
    </div>
</section>
