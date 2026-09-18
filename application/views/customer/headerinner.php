<?php
$TYPE = $_SESSION['type'];
$logged_in = FALSE;
if($this->session->userdata($TYPE)){
    $session_obj = $this->session->userdata($TYPE);
    //echo "<pre>"; print_r($session_obj); echo "</pre>";
    $logged_in = isset($session_obj['logged_in']) ? $session_obj['logged_in'] : false;
    $name  = isset($session_obj['fname']) ? $session_obj['fname'] : (isset($session_obj['name']) ? $session_obj['name'] : 'User');
    $image = isset($session_obj['image']) ? $session_obj['image'] : '';
}
$page_lang  = get_page_language_data('header_page_lang');
$controller = $this->router->fetch_class();
$model = $this->router->fetch_method();

$cat_obj =  $this->Product_model->get_categories_new();
$cat_obj_more = $this->Product_model->get_categories_new_more();

//echo $session_id = $_SESSION['__ci_last_regenerate']; 
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Klentano Shop</title>
        <!-- Bootstrap -->
        <meta name="google-signin-client_id" content="788245722667-ap3h72ft6n8tp299hc0q8mmg1q9a3o2r.apps.googleusercontent.com">
        <link rel="stylesheet" href="/assets/frontend/css/bootstrap-4.3.1.css">
        <link rel="stylesheet" href="/assets/frontend/css/font-awesome.min.css">
        <link rel="stylesheet" href="/assets/frontend/css/slick.css">
        <link rel="stylesheet" href="/assets/frontend/css/slick-theme.css">
        <link rel="stylesheet" href="/assets/frontend/css/custom_style.css">
        <link rel="stylesheet" href="/assets/frontend/css/style.css">
        <link rel="stylesheet" href="/assets/plugins/parsley/parsley.css" />
        <script src="https://apis.google.com/js/platform.js" async defer></script>
        <script src="/assets/frontend/js/jquery.min.js"></script>

        <script src="/assets/frontend/js/popper.min.js"></script>
        <script src="/assets/frontend/js/bootstrap-4.3.1.js"></script>
        <script src="/assets/plugins/sweetalert/sweetalert.min.js"></script>
        <script src="/assets/plugins/parsley/parsley.min.js"></script>
        <script src="/assets/frontend/js/slick.js"></script>
		<script src="/assets/frontend/js/app.js"></script>
        <style>
        .loadingTrack {position: fixed;top:0px;right:0px;bottom:0px;left:0px;z-index: 1000000;background:rgba(0,0,0,.2);}
        .loadingTrack img {width:100%;max-width:150px;height:auto;position: absolute;top:50%;margin-top:-75px;left:50%;margin-left:-75px;background:rgba(255, 255, 255, .9);box-sizing: border-box;border:1px solid #bfbfbf;border-radius:5px;}
        </style>
    </head>
    <body>
        <header>
            <div id="sidebar">
            <div class="widget-title">
             <h4>categories<a href="#" class="mob-menu mob-menu-icon "><i class="fa fa-times"></i></a></h4>
        </div>
          <div class="widget-content">
             <ul id="accordion" class="CategoryList">
                
             </ul>
          </div>
        </div>
            <div class="container">
                <div class="row">
                    <div class="col-md-3 logo">
                      <a class="mob-menu" href="#"><i class="fa fa-bars fa-2x"></i></a>
                      <a href="<?php echo base_url(); ?>" alt="logo"><img src="/assets/frontend/images/logo.png" alt="" class="img-fluid l1"></a>
                      <a href="<?php echo base_url(); ?>" alt="logo"><img src="/assets/frontend/images/logo-2.png" alt="" class="img-fluid l2"></a>
                    </div>
                    <div class="col-md-9">
                        <div class="top-menu">
                            <a href="<?=base_url();?>page/about"><span><?= $page_lang->about_us; ?></span></a>
                            <a href="<?=base_url();?>page/contact-us"><span><?= $page_lang->contact_us; ?></span></a>
                            <a href="<?=base_url();?>page/help--faqs"><span><?= $page_lang->help_faqs; ?></span></a>
              <?php 
                            $default_currency = default_currency();
                            $currency =get_currency();
              ?>
              <select name="currencies" id="currencies_combo">
                <?php foreach($currency as $k=>$v){ 
                                    $selected = ($v->currency_id == $default_currency) ? 'selected' : '';
                                ?>
                  <option value="<?php echo $v->currency_id; ?>" <?= $selected ?>><?php echo $v->iso_code; ?></option>  
                <?php } ?>
                            </select>
              <?php 
                            $default_language = default_language();
              $language =get_language();
              ?>
                           <select name="language" id="language_combo">
                               <?php foreach($language as $k=>$v){
                                    $selected = ($v->language_id == $default_language) ? 'selected' : '';
                                 ?>
                  <option value="<?php echo $v->language_id; ?>" <?= $selected ?>><?php echo $v->name; ?></option>  
                <?php } ?>
                            </select>
                        </div>
                        <div class="row">
							
								<div class="col-md-10 search">
								<form action="/" name="seach-form" id="sform">
									<input type="text" name="fts" id="fts" placeholder="<?= $page_lang->search_product; ?> ...">
									<img src="/assets/frontend/images/search.png" class="searchButton" alt="">
								</form>
								</div>
							
                            <div class="col-md-2 help-menu">
                                <!--<a href="#" class="phone"><img src="/assets/frontend/images/phone.png"><span>CALL US NOW<br><strong>+123 5678 890</strong></span></a>-->

                                <?php if($logged_in){ ?>
                                <a class="dropdown-toggle acc" data-toggle="dropdown" href="#"><img src="/assets/frontend/images/account.png"><br>
                                    <span class="nm" id="login_out">Hi, <?= $name; ?></span>
                                    <ul class="dropdown-menu">
                                        <li><a href="/<?= $TYPE?>/profile"><i class="fa fa-user"></i> <?= $page_lang->my_account; ?></a></li>
                                        <li><a href="/<?= $TYPE?>/auth/logout"><i class="fa fa-sign-out"></i> <?= $page_lang->logout; ?></a></li>
                                    </ul>
                                </a>
                                <?php }else{ ?>
                                <a class="dropdown-toggle acc" href="/<?= $TYPE?>/auth/login"><img src="/assets/frontend/images/account.png"><br>
                                    <span class="nm" id="login"><?= $page_lang->login; ?></span>
                                   <?php /* ?> <ul class="dropdown-menu">
                                        <li><a href="/<?= $TYPE?>/auth/register"><i class="fa fa-user"></i> <?= $page_lang->register; ?></a></li>
                                        <li><a href="/<?= $TYPE?>/auth/login"><i class="fa fa-user"></i> <?= $page_lang->login; ?></a></li>
                                    </ul><?php */ ?>
                                </a>
                                <?php }
                
                ?>
                                <!--<a href="#"><img src="/assets/frontend/images/short.png"><br><span>Wishlist</spsn></a>-->
                                <a href="/cart"><img src="/assets/frontend/images/cart.png"><br><span><sup id="cart_count"><?= cart_item_count(); ?></sup><?= $page_lang->cart; ?></span></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="container">
      <hr class="sn">
        <div class="row in-me">
          <div class="col-md-2 dropdown">
            <a class="cato dropdown-toggle " data-toggle="dropdown" href="#">
              <i class="fa fa-bars"></i> <?= $page_lang->browse_categories; ?>
              </a>
              <div class="dropdown-menu my-drop">
            <ul class="side-bar side-bar2" id="GetCategory1">
            <?php 

                    function fetch_menu($cat_obj){

    foreach($cat_obj as $menu){
        $icon = base_url().'assets/categories/'.$menu->icon;
                    echo '<li class="nav-menu"><a href="/category/'.$menu->slug.'"><img src="'.$icon.'" title="'.$menu->name.'" width="22"><span>'.$menu->name.'</span>';

                    if(!empty($menu->sub)){
                          echo '<i class="fa fa-chevron-right" aria-hidden="true"></i></a>';
                        }else{
                          echo '</a>';
                        }

        if(!empty($menu->sub)){

                        echo '<div class="megadrop"><div class="col"><ul class="nav-menu child">';

            fetch_sub_menu($menu->sub);

                        echo '</ul></div></div>';
        }

    }

}
function fetch_sub_menu($sub_menu){

    foreach($sub_menu as $menu){

        echo '<li id="menu-item-'.$menu->id.'" class="menu-item menu-item-has-children menu-item-'.$menu->id.'"><h4 class="sub-menu_title"><a href="/category/'.$menu->slug.'"><span>'.$menu->name.'</span></a></h4>';

        if(!empty($menu->sub)){

            echo "<ul class='sub-menu'>";

            fetch_sub_menu($menu->sub);

            echo "</ul>";
        }
	echo '</li>';

    }

}

fetch_menu($cat_obj);
?>

            <li class="nav-menu more">
                  <a href="javascript:void(0);"><img src="<?= base_url(); ?>assets/frontend/images/more.png" width="22px"><span>More Products</span><i class="fa fa-chevron-right" aria-hidden="true"></i></a>
                  <?php
                      function fetch_menu_more($cat_obj_more){
                        echo '<div class="megadrop">';
                        foreach($cat_obj_more as $menu){
                            $icon = base_url().'assets/categories/'.$menu->icon;
                                   echo '<div class="col sub-menu">
                                      <h4><a href="/category/'.$menu->slug.'">'.$menu->name.'</a></h4>';
                                    if(!empty($menu->sub)){
                                        echo '<ul class="sub-menu"><li id="menu-item-'.$menu->id.'" class="menu-item menu-item-has-children menu-item-'.$menu->id.'">';
                                        fetch_sub_menu_more($menu->sub);
                                        echo '</li></ul>';
                                    }
                                echo '</div>';
                              }
                          echo '</div>';
                    }

                function fetch_sub_menu_more($sub_menu){

                    foreach($sub_menu as $menu){
                        echo '<li><a href="/category/'.$menu->slug.'"><span>'.$menu->name.'</span></a></li>';
                        if(!empty($menu->sub)){
                            echo "<ul>";
                            fetch_sub_menu($menu->sub);
                            echo '</ul>';
                        }
                    }
                }


                fetch_menu_more($cat_obj_more);   

                  ?>
                </li>

            
            </ul>
          </div>
              
            
          </div>
          <div class="col-md-10 text-right">
              <!--<a class="offer-bold" href="#">-->
            <!--</a>-->
          </div>
          </div>
      </div>
        </header>
<script>
$('.searchButton').click(function(){	
   document.location.href = make_url();
});

$(document).ready(function() {
  $('#fts').keypress(function(event) {	  
    if (event.which === 13) {		
      event.preventDefault();
      document.location.href = make_url();
    }
  });
});
function make_url()
{
    var url = ftsStr = ftsStr = lmyStr = '';
    ftsStr = $('#fts').val();
    $('#fts_hidden').val(ftsStr);
    localStorage.setItem('fts',ftsStr);
    ftsStr = ftsStr.replace(/\bin\b/g, '');
    ftsStr  = ftsStr.replace(/\W/g, '-');
    ftsStr = ftsStr.replace(/(-)+/g, '-');
    ftsStr = ftsStr.replace(/^-|-$/g, '');
    ftsStr = ftsStr.toLowerCase();
    if(ftsStr)
    {
        url = '/shop/'+ftsStr+"";
    }
    return url;
} 
</script>
