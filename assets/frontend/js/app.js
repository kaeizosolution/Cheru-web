function getSliderSettings(){
  return {
    dots: true,
       infinite: true,
       slidesToShow: 5,
       slidesToScroll: 1,
       autoplay: true,

        autoplaySpeed: 2000,
          margin:5,

          pauseOnHover: true,

        responsive: [
        {
            breakpoint: 1040,
            settings: {
                slidesToShow: 3,
                slidesToScroll: 1,
            }
        },
        {
            breakpoint: 600,
            settings: {
                slidesToShow: 2,
                slidesToScroll: 1
            }
        },
        {
            breakpoint: 480,
            settings: {
                slidesToShow: 1,
                slidesToScroll: 1
            }
        }
    ]
    
    }
}

$('.quantity_update').on('click', function (e) {
    var id = $(this).attr('data-id');
    var txtBox = $(this).closest('.numbering').find('input[type=text]');
    var quantity = txtBox.val().trim();

    if(id == 'plus'){
        quantity++;
    }else if(id == 'minus'){
        if(quantity > 1){
            quantity--;
        }
    }
    txtBox.val(quantity);
});

$('.qty_update').on('keyup', function (e) {
    if($(this).val() == '' || $(this).val() == 0){
        $(this).val(1);
    }
});

$(".nav a").on("click", function(){
   $(".nav").find(".active").removeClass("active");
   $(this).parent().addClass("active");
});


		  $(document).on('hover', '.dropdown-menu', function (e) {
	  e.stopPropagation();
	});
			 
			 $(function() {
    $('.mob-menu').click(function() {
      $('#sidebar').toggleClass('visible');
    });
  });
			 
			 $(document).ready(function () {
	$("#accordion li > h4").click(function () {

		if ($(this).next().is(':visible')) {
			$(this).next().slideUp(300);
			$(this).children(".plusminus").text('+');
		} else {
			$(this).next("#accordion ul").slideDown(300);
			$(this).children(".plusminus").text('-');
		}
	});
});




//producr page

		
			 $('#myCarousel').carousel({
    interval: 4000
});

// handles the carousel thumbnails
$('[id^=carousel-selector-]').click( function(){
  var id_selector = $(this).attr("id");
  var id = id_selector.substr(id_selector.length -1);
  id = parseInt(id);
  $('#bannerCarousel').carousel(id);
  $('[id^=carousel-selector-]').removeClass('selected');
  $(this).addClass('selected');
});

// when the carousel slides, auto update
$('#myCarousel').on('slid', function (e) {
  var id = $('.item.active').data('slide-number');
  id = parseInt(id);
  $('[id^=carousel-selector-]').removeClass('selected');
  $('[id=carousel-selector-'+id+']').addClass('selected');
});
	
