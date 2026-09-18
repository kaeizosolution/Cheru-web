<script src="/assets/plugins/float-label/jquery.placeholder.label.js"></script>
<style>
.daterangepicker.single.ltr .ranges { display : block !important; }
</style>
<div class="page-wrapper">
    <div class="container-fluid">
        <div class="authentication-main">
            <div class="row">
                <div class="col-md-8 offset-md-4 p-0">
                    <div class="auth-innerright">
                        <div class="authentication-box">
                            <h4>LOGIN</h4>
                            <div class="card mt-4 p-4 mb-0">
                                <div class="alert alert-success" role="alert">
                                    This is a success alert—check it out!
                                </div>
                                <div class="alert alert-danger" role="alert">
                                    This is a danger alert—check it out!
                                </div>

                                <div data-toolbar="user-options" class="btn-toolbar" style="margin:auto"><i class="fa fa-cog"></i></div>
                                <div id="neeraj" class="toolbar-icons hidden">
                                    <a href="#"><i class="fa fa-user"></i></a>
                                    <a href="#"><i class="fa fa-star"></i></a>
                                    <a href="#"><i class="fa fa-edit"></i></a>
                                    <a href="#"><i class="fa fa-trash"></i></a>
                                    <a href="#"><i class="fa fa-circle"></i></a>
                                </div>

                                <div class="form-group">
                                    <button type="button" class="btn btn-primary" data-toggle="tooltip" title="" data-html="true" data-original-title="Tooltip <b>with</b> <code>HTML</code>">Tooltip</button>
                                </div>
                                <div class="form-group">
                                    <button type="button" class="btn btn-primary" data-toggle="popover" data-placement="top" data-html="true" data-content="Just add <code>data-html='true'</code> attribute and you can purse <b>html</b> code" data-original-title="Popover title">Popover</button>
                                </div>
                                <div class="form-group">
                                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModalfat" data-whatever="@mdo">Open Model</button>
                                </div>
                                <div class="form-group">
                                    <button type="button" class="btn btn-success sweet-8" onclick="salert();">Sweat Alert</button>
                                </div>
                                <div class="form-group">
                                    <nav aria-label="Page navigation example">
                                        <ul class="pagination pagination-primary">
                                            <li class="page-item">
                                                <a class="page-link" href="#" aria-label="Previous">
                                                <span aria-hidden="true">«</span>
                                                <span class="sr-only">Previous</span>
                                                </a>
                                            </li>
                                            <li class="page-item"><a class="page-link" href="#">1</a></li>
                                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                                            <li class="page-item">
                                                <a class="page-link" href="#" aria-label="Next">
                                                <span aria-hidden="true">»</span>
                                                <span class="sr-only">Next</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                                <div class="form-group">
                                    <ol class="breadcrumb bg-white m-b-0 p-b-0 p-l-0">
                                        <li class="breadcrumb-item"><a href="javascript:void(0)"><i class="fa fa-home"></i></a></li>
                                        <li class="breadcrumb-item"><a href="javascript:void(0)">Library</a></li>
                                        <li class="breadcrumb-item active">Data</li>
                                    </ol>
                                </div>


                                <?php echo form_open("/admin/auth/login", array('id' => 'loginForm', 'autocomplete' => 'off', 'method' => 'POST', 'class' => 'theme-form')) ?>
                                    <div class="form-group">
                                        <input type="text" class="form-control form-control-lg" placeholder="Email" id="email" required>
                                    </div>
                                    <div class="form-group">
                                        <input type="password" class="form-control form-control-lg" placeholder="Password"  required>
                                    </div>
                                    <div class="form-group">
                                        <textarea rows="5" class="form-control" placeholder="Enter About your description" required></textarea>
                                    </div>
                                    <div class="form-group">
                                        <input type="text" name="activation_date" id="activation_date" class="form-control fancy_date" placeholder="Date" readonly required />
                                    </div>
                                    <div class="checkbox p-0">
                                        <input id="checkbox1" type="checkbox" required>
                                        <label for="checkbox1">Remember me</label>
                                    </div>
                                    <div class="form-group">
                                        <div class="form-group m-t-15 m-checkbox-inline mb-0 custom-radio-ml">
                                            <div class="radio radio-primary">
                                                <input type="radio" name="radio1" id="radioinline1" value="option1">
                                                <label for="radioinline1" class="mb-0">Option<span class="digits"> 1</span></label>
                                            </div>
                                            <div class="radio radio-primary">
                                                <input type="radio" name="radio1" id="radioinline2" value="option1">
                                                <label for="radioinline2" class="mb-0">Option<span class="digits"> 2</span></label>
                                            </div>
                                            <div class="radio radio-primary">
                                                <input type="radio" name="radio1" id="radioinline3" value="option1">
                                                <label for="radioinline3" class="mb-0">Option<span class="digits"> 3</span></label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="form-group m-t-15 m-checkbox-inline mb-0">
                                            <div class="checkbox">
                                                <input id="inline-1" type="checkbox">
                                                <label for="inline-1">Option<span class="digits"> 1</span></label>
                                            </div>
                                            <div class="checkbox">
                                                <input id="inline-2" type="checkbox">
                                                <label for="inline-2">Option<span class="digits"> 2</span></label>
                                            </div>
                                            <div class="checkbox">
                                                <input id="inline-3" type="checkbox">
                                                <label for="inline-3">Option<span class="digits"> 3</span></label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <select class="col-sm-12" id="street" name="street" data-parsley-trigger="change" data-parsley-errors-container="#street_error" placeholder="neeraj" required>
                                            <option></option>
                                            <option value="AL">Alabama</option>
                                            <option value="WY">Wyoming</option>
                                            <option value="WY">Peter</option>
                                            <option value="WY">Hanry Die</option>
                                            <option value="WY">John Doe</option>
                                        </select>
                                        <div id="street_error"></div>
                                    </div>
                                    <div class="form-group">
                                        <select class="col-sm-12" id="floor" name="floor" data-parsley-trigger="change" data-parsley-errors-container="#floor_error" placeholder="diwakar" multiple="multiple" required>
                                            <option value="AL">Alabama</option>
                                            <option value="WY">Wyoming</option>
                                            <option value="WY">Coming</option>
                                            <option value="WY">Hanry Die</option>
                                            <option value="WY">John Doe</option>
                                        </select>
                                        <div id="floor_error"></div>
                                    </div>
                                    <div class="form-group form-row mt-3 mb-0">
                                        <div class="col-md-3">
                                            <button type="submit" class="btn btn-secondary">LOGIN</button>
                                        </div>
                                    </div>
                                <?php echo form_close() ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="exampleModalfat" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel2">New message</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label for="recipient-name" class="col-form-label" >Recipient:</label>
                        <input type="text" class="form-control"  Value="@fat">
                    </div>
                    <div class="form-group">
                        <label for="message-text" class="col-form-label">Message:</label>
                        <textarea class="form-control" id="message-text"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Send message</button>
            </div>
        </div>
    </div>
</div>
<script>
$('.fancy_date').daterangepicker({
    singleDatePicker: true,
    showDropdowns: true,
    autoUpdateInput: false,
    timePicker: true,
    timePicker24Hour: true,
    autoUpdateInput: false,
    drops: 'down',
    locale: { cancelLabel: 'Clear', format: 'YYYY-MM-DD' }
});

$('.fancy_date').on('apply.daterangepicker', function (ev, picker) {
    $(this).val(picker.startDate.format('YYYY-MM-DD'));
    setTimeout(function(){ $(this).blur();console.log('hiiiiiiii'); }, 300);
//    $(this).focus();
    //alert('apply');
    //$(this).parsley().reset();
});

$('.fancy_date').on('cancel.daterangepicker', function(ev, picker) {
    $(this).val('');
    //alert('clear');
    //$(this).parsley().validate()
});

function salert(){
    swal("Good job!", "You clicked the button!", "success");
}

$(document).ready(function($) {
    $('.toolbar-icons a').on('click', function( event ) {
        event.preventDefault();
    });

    $('div[data-toolbar="user-options"]').toolbar({
        content: '#neeraj',
        position: 'top',
    });
});
</script>
