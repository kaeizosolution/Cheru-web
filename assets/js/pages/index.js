var postData = {};
postData[csrf.name] = csrf.hash;
$(document).ready(function(){
index_data(postData);

oblc();
});

function index_data(postData)
{
	var request = $.ajax({
      url: "/welcome/index_api",
      type: "POST",
      data: postData,
      dataType: "json"
    });

	request.done(function(resp) {
		if(resp.STATUS)
		{
			if(resp.DATA.banner)
			{
				banner_func(resp.DATA.banner);
			}
		}
		oblc();	
	});

	request.fail(function(jqXHR, textStatus) {
	});	
}

function banner_func(args_banner)
{
	var banner_html = '';
	$.each(args_banner, function (key, val) {
		banner_html += 
			'<div class="item">'+
              '<div class="offer-item">'+
                '<div class="offer-item-img">'+
                 '<img src="'+val.banner_image+'" alt=""> </div>'+
              '</div>'+
            '</div>';	
    });
	$('.js_banner_item').html(banner_html);
	oblc();	
}

function oblc()
{
$('.js_banner_item').owlCarousel({
  loop: true,
  nav: false,
  dots: false,
  autoplay: true,
  responsive: {
    0: {
      items: 1
    }, 
    600: {
      items: 1
    },
    1000: {
      items: 1
    },
    1200: {
      items: 1
    },
    1400: {
      items: 1
    }
  }   
})

}
