<!--Header Area End Here-->
<div class="wrapper">
  <div class="shop-by-categories bx-shadow">
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-12">
          <div class="owl-carousel cate-slider owl-theme">
          <?php
              $cat_html_first = '';
              if(isset($cate_all_strip['all_cat']) && $cate_all_strip['all_cat'])
              {
                foreach($cate_all_strip['all_cat'] as $row)
                {
                  
                  $img = $row->thumbnail;
                  $name = $row->name; $url = '/category/'.$row->slug;
                  $cat_html_first .=<<<HTML
                  <div class="item"> 
                    <a href="$url" class="category-item">
                            <div class="cate-img"><img src="$img" alt=""></div>
                              <h4>$name</h4>
                            </a> 
                  </div>
HTML;
                }
                echo $cat_html_first;
              }
          ?> 
          </div>
        </div>
      </div>
    </div>
  </div>
