<!-- Image upload box start -->
<div data-id="" data-input-ref="" data-target="1" data-target-ref="" class="modal fade full-screen-popup"
     id="imageLibraryModalBox" tabindex="-1" role="dialog"
     aria-labelledby="myLargeModalLabel"
     aria-hidden="true">
    <input type="hidden" name="image_type" id="image_type">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-area">
                <!-- modal-header -->
                <div class="modal-header">
                    <h5 class="modal-title" id="">Select Images</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <!-- modal-body -->
                <div class="modal-body">

                    <div class="container">
                        <div class="row">
                            <div class="col-md-4 offset-md-2 form-group">
                                {{ Form::text('image_search', null, ['class' => 'form-control', 'id' => 'imageSearch','autocomplete'=>'off','placeholder'=>'Image search']) }}
                                {{--                                <input class="form-control" id="imageSearch" autocomplete="off" placeholder="Image search" name="image_search" type="text">--}}
                            </div>
                            <div class="col-md-4 form-group">
                                <div class="custom-file">
                                    {{ Form::file('image', [ 'id' => 'profileImage', 'autocomplete' => 'off','class'=>'custom-file-input'])}}
                                    <label class="custom-file-label" for="profileImage">Choose file</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="image-select-modal-list m-l--15 m-r--15"></div>
                    <div class="page-inner-loader">
                        <div class="loader">
                            <div class="">
                                <div class="lds-ripple">
                                    <div></div>
                                    <div></div>
                                </div>
                                <p>Please wait...</p></div>
                        </div>
                    </div>
                    <div id="imageLibraryModalBoxHtmlMore"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Image upload box end -->