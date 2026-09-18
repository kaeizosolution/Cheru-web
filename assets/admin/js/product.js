let productData = {};

try {
  productData = JSON.parse(rawData);
  ['modifiers', 'extras', 'prices'].forEach(key => {
    if (typeof productData[key] === 'string') {
      productData[key] = JSON.parse(productData[key]);
    }
  });
} catch (e) {
  productData = {};
}

$(document).ready(function(){
  $('.select2').select2({ allowClear: true, placeholder: 'Select option' });

  $('#category_id').change(function(){
    const val = $(this).val();
    sub_cat_combo(val);
  });

  $('.sales_duration').daterangepicker({
    timePicker: true,
    timePicker24Hour: true,
    autoUpdateInput: false,
    locale: { format: 'YYYY-MM-DD HH:mm', cancelLabel: 'Clear' },
    opens: 'left'
  });

$('.sales_duration').on('apply.daterangepicker', function(ev, picker) {
  $(this).val(
    picker.startDate.format('YYYY-MM-DD HH:mm') + ' - ' +
    picker.endDate.format('YYYY-MM-DD HH:mm')
  );
});

$('.sales_duration').on('cancel.daterangepicker', function(ev, picker) {
  $(this).val('');
});	

  $('input[name="product_type"]').change(function () {
    if ($(this).val() === 'attribute') {
      $('#simple-fields').hide();
      $('#attribute-section').slideDown();
    } else {
      $('#attribute-section').hide();
      $('#simple-fields').slideDown();
    }
  });

  $('#add-attribute').click(function(){
    const index = $('#attribute-wrapper .attribute-group').length;
    const $html = $('.attribute-group').first().clone();
    $html.find('input').val('');
    $html.find('input[type="file"]').attr('name', `attribute_image[${index}][]`);
    $html.find('.preview-images').html('');
    $('#attribute-wrapper').append($html);
  });

  $(document).on('click', '.remove-attribute', function(){
    if ($('.attribute-group').length > 1) {
      $(this).closest('.attribute-group').remove();
    }
  });

  $('#modifiers').change(function () {
    const selectedIds = $(this).val() || [];
    const currentInputs = {};

    $('#modifier_prices .form-group').each(function () {
      const id = $(this).find('input').attr('name').match(/\[(\d+)\]/)[1];
      currentInputs[id] = $(this);
    });

    for (let id in currentInputs) {
      if (!selectedIds.includes(id)) {
        currentInputs[id].remove();
      }
    }

    $('#modifiers option:selected').each(function () {
      const id = $(this).val();
      const text = $(this).text();
      if (!currentInputs[id]) {
        $('#modifier_prices').append(`
          <div class="form-group" data-mod-id="${id}">
            <label>${text} Price</label>
            <input type="text" name="modifier_price[${id}]" class="form-control">
          </div>
        `);
      }
    });
  });

  $('#extras').change(function () {
    const selectedIds = $(this).val() || [];
    const currentInputs = {};

    $('#extra_prices .form-group').each(function () {
      const id = $(this).find('input').attr('name').match(/\[(\d+)\]/)[1];
      currentInputs[id] = $(this);
    });

    for (let id in currentInputs) {
      if (!selectedIds.includes(id)) {
        currentInputs[id].remove();
      }
    }

    $('#extras option:selected').each(function () {
      const id = $(this).val();
      const text = $(this).text();
      if (!currentInputs[id]) {
        $('#extra_prices').append(`
          <div class="form-group" data-extra-id="${id}">
            <label>${text} Price</label>
            <input type="text" name="extra_price[${id}]" class="form-control">
          </div>
        `);
      }
    });
  });

  if (Object.keys(productData).length > 0) {
    prefilled_product_data();
  }
});

function prefilled_product_data() {
  $('#name').val(productData.name);
  $('#category_id').val(productData.category_id).trigger('change');
  sub_cat_combo(productData.category_id);

  $('#category_id').on('subcat_loaded', function () {
    $('#sub_cat').val(productData.sub_cat_id).trigger('change');
  });

	$('#product_category').val(productData.product_category).trigger('change');
	$('#food_type').val(productData.food_type).trigger('change');

  	$('#short_description').val(productData.short_description);
	$('input[name="product_type"][value="' + productData.product_type + '"]').prop('checked', true).trigger('change');
  $('#tax').val(productData.tax);
  $('#sales_duration').val(productData.sales_duration);

  // Modifiers
  $('#modifiers').val(productData.modifiers.map(m => m.id)).trigger('change');
  setTimeout(() => {
    productData.modifiers.forEach(m => {
      $(`input[name='modifier_price[${m.id}]']`).val(m.price);
    });
  }, 300);

  // Extras
  $('#extras').val(productData.extras.map(e => e.id)).trigger('change');
  setTimeout(() => {
    productData.extras.forEach(e => {
      $(`input[name='extra_price[${e.id}]']`).val(e.price);
    });
  }, 300);

	if (productData.product_type === 'attribute') {
    $('#attribute-wrapper').html('');
    productData.prices.forEach((attr, i) => {
      let html = `
        <div class="row attribute-group">
          <div class="form-group col-md-3">
            <label>Attribute Name</label>
            <input type="text" name="attribute_name[]" value="${attr.name}" class="form-control">
          </div>
          <div class="form-group col-md-2">
            <label>Regular Price</label>
            <input type="text" name="attribute_regular_price[]" value="${attr.regular_price}" class="form-control">
          </div>
          <div class="form-group col-md-2">
            <label>Sales Price</label>
            <input type="text" name="attribute_sales_price[]" value="${attr.sales_price}" class="form-control">
          </div>
          <div class="form-group col-md-3">
            <label>Upload Image</label>
            <input type="file" name="attribute_image[${i}][]" class="form-control">
            <div class="preview-images" id="preview-${i}"></div>
          </div>
          <div class="form-group col-md-2 d-flex align-items-end">
            <button type="button" class="btn btn-danger remove-attribute">Remove</button>
          </div>
        </div>`;

      $('#attribute-wrapper').append(html);
      attr.images.forEach(img => {
        $(`#preview-${i}`).append(`<img src="/uploads/attributes/${img}" height="50" class="mr-1">`);
      });
    });
  } else {
    $('#regular_price').val(productData.prices?.[0]?.regular_price || '');
    $('#sales_price').val(productData.prices?.[0]?.sales_price || '');
    if (productData.prices?.[0]?.images?.length) {
      productData.prices[0].images.forEach(img => {
        $('#simplePreview').append(`<img src="/uploads/products/${img}" height="50" class="mr-1">`);
      });
    } 
	}
 
}

function sub_cat_combo(current_val) {
    var post_data = { category_id: current_val };
    var url = '/admin/product/ajax_sub_cat';
    ajaxFileFunc(url, post_data, sucs_sub_cat, err_sub_cat);
}

function sucs_sub_cat(resp) {
    if (resp.STATUS == 1) {
        $('#sub_cat').html('<option value="">Select Sub Category</option>');
        var combo = '';
        $.each(resp.DATA, function(index, value) {
            combo += '<option value="'+value.id+'">'+value.name+'</option>';
        });
        $('#sub_cat').append(combo);
	if(combo)
	$('#category_id').trigger('subcat_loaded');

    }
}

function err_sub_cat(xhr, status, error) {
    console.error("Subcategory load failed");
}


$('#productForm').on('submit', function (e) {
    e.preventDefault();

    let form = $(this)[0];
    let formData = new FormData(form);
	
	formData.append("id", productData?.id);

    ajax_with_upload_func(
        '/admin/product/post_save',
        formData,
        function (response) {
		var resp = response;
            if (resp.STATUS == 1) {
                alert('Product saved successfully!');
                //window.location.reload();
                window.location.href="/admin/product";
            } else {
                alert('Failed: ' + resp.MSG);
            }
        },
        function (xhr, status, error) {
            console.error('AJAX error:', error);
            alert('Something went wrong during upload.');
        },
        function () {
            console.log('Upload complete.');
        }
    );
});

