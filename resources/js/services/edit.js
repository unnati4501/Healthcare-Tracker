$(document).ready(function() {
    
    function readURL(input, selector) {
        if (input != null && input.files.length > 0) {
            var reader = new FileReader();
            reader.onload = function(e) {
                // Validation for image max height / width and Aspected Ratio
                var image = new Image();
                image.src = e.target.result;
                image.onload = function () {
                    var imageWidth = $(input).data('width');
                    var imageHeight = $(input).data('height');
                    var ratio = $(input).data('ratio');
                    var aspectedRatio = ratio;
                    var ratioSplit = ratio.split(':');
                    var newWidth = ratioSplit[0];
                    var newHeight = ratioSplit[1];
                    var ratioGcd = gcd(this.width, this.height, newHeight, newWidth);
                    if((this.width < imageWidth && this.height < imageHeight) || ratioGcd != aspectedRatio){
                        $(input).empty().val('');
                        $(input).parent('div').find('.custom-file-label').html('Choose File');
                        $(selector).removeAttr('src');
                        toastr.error(upload_image_dimension);
                        readURL(null, selector);
                    }
                }
                $(selector).attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
        } else {
            $(selector).removeAttr('src');
        }
    }

    $(document).on('change', '#logo, #icon, #sub_category_logo', function(e) {
        var previewElement = $(this).data('previewelement');
        if (e.target.files.length > 0) {
            var fileName = e.target.files[0].name,
                allowedMimeTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/svg+xml'];
            if (!allowedMimeTypes.includes(e.target.files[0].type)) {
                toastr.error(messages.image_valid_error);
                $(e.currentTarget).empty().val('');
                $(this).parent('div').find('.custom-file-label').html(messages.choose_file);
                readURL(null, previewElement);
            } else if (e.target.files[0].size > 2097152) {
                toastr.error(messages.image_size_2M_error);
                $(e.currentTarget).empty().val('');
                $(this).parent('div').find('.custom-file-label').html(messages.choose_file);
                readURL(null, previewElement);
            } else {
                readURL(e.target, previewElement);
                $(this).parent('div').find('.custom-file-label').html(fileName);
            }
        } else {
            $(this).parent('div').find('.custom-file-label').html(messages.choose_file);
            readURL(null, previewElement);
        }
    });

    $(document).on('click', '#addSubcategory', function(t) {
        $('#sub_category_logo-error,#sub_cateory_name-error').remove();
        var deleteConfirmModalBox = '#subcategory-model-box';
        $("#modal_title").html(labels.add_subcategory);
        $(deleteConfirmModalBox).attr("data-id", $(this).data('id'));
        $(deleteConfirmModalBox).modal('show');
        $("#sub_category_name").val("");
        $("#sub_category_logo_preview").attr('src','');
        $("#subcategorySave").show();
        $("#subcategoryUpdate").hide();
        $("label[for='sub_category_logo'].custom-file-label").html("Choose File");
    });

    $(document).on('keyup', '#subCategoryTbl tbody tr:last input', function(e) {
        if($('#subCategoryTbl tbody tr').length > 1) {
            $(this).parent().parent().next().toggleClass("show_del", $(this).val().length == 0);
        }
    });
    $("#subcategory_remove_0").hide();
    $('#subcategorySave').on('click', function(e) {
        e.preventDefault();
        $('#sub_category_logo-error, #sub_cateory_name-error').remove();
        var res = [];
        if ($("label[for='sub_category_logo'].custom-file-label").html() == 'Choose File') {
            $('#sub_category_logo').after('<div id="sub_category_logo-error" class="error text-danger">'+messages.field_required+'</div>');
            res.push("logo");
        } else {
            removeFrmArr(res, 'logo');
        }
        var regEx = /^[A-Za-z0-9 \/.,\\<>&()+\'\-]*$/;
        var subCategoriesName = $('#sub_category_name').val().trim();
        if (subCategoriesName == "") {
            $('#sub_category_name').after('<div id="sub_cateory_name-error" class="error text-danger">'+messages.field_required+'</div>');
            res.push("name");
        }else if((regEx.test(subCategoriesName)) == false) {
            $('#sub_category_name').after('<div id="sub_cateory_name-error" class="error text-danger">'+messages.enter_valid_subcategory+'</div>');
            res.push("name");
        }else{
            removeFrmArr(res, 'name');
        }
        
        if(res.length <= 0){

            // Get previous form value
            var data = {};
            
            data['name'] = $("#sub_category_name").val();
            data['image'] = $("#sub_category_logo_preview").attr('src');
            data['imageName']   = $("label#subcategory_logo_name").html();
            var currentFormId = $('#total-form-subcategories').val();
            // Increase form value for next iteration.
            currentFormId++;
            data['id'] = "id"+currentFormId;
            // var previousFormId = currentFormId - 1;
            // Get last html source
            var $lastItem = $('.custom-subcategy-wrap').last();
            var previousFormId = $lastItem.attr('data-order');
            // Create new clone from lastItem
            var $newItem = $lastItem.clone(true);
            // Insert clone html after last html
            $newItem.insertAfter($lastItem);

            // subcategory id increment logic
            $newItem.find('span').each(function() {
                var lebelClass = $(this).attr('class');
                if (lebelClass) {
                    var lebelClass = $(this).attr('class').replace('[' + (previousFormId) + ']', '[' + currentFormId + ']');
                    $(this).attr({
                        'class': lebelClass,
                    });
                }

                var lebelId = $(this).attr('id');
                if (lebelId) {
                    var lebelId = $(this).attr('id').replace(previousFormId, currentFormId);
                    $(this).attr({
                        'id': lebelId,
                    });
                }
            });

            $newItem.find('span img').each(function() {
                var imgid = $(this).attr('id');
                if (imgid) {
                    var imgid = $(this).attr('id').replace(previousFormId, currentFormId);
                    $(this).attr({
                        'id': imgid,
                    });
                }
            });

            $newItem.find('a').each(function() {
                var aId = $(this).attr('id');
                if (aId) {
                    var aId = $(this).attr('id').replace(previousFormId, currentFormId);
                    $(this).attr({
                        'id': aId,
                    });
                }
            });

            $newItem.find(':input').each(function() {
                var name = $(this).attr('name');
                if (name) {
                    var name = $(this).attr('name').replace('[' + (previousFormId) + ']', '[' + currentFormId + ']');
                    var id = $(this).attr('id').replace(previousFormId, currentFormId);
                    
                    // Clean name and id attribute of previous element. DO NOT REMOVE BELLOW LINE, Otherwise it remain old data of previous input.
                    $(this).attr({
                        'name': name,
                        'id': id,
                    });

                    $("#subcategory_name_"+currentFormId).val(data['name']);
                    $("#subcategory_logo_"+currentFormId).val(data['image']);
                    $("#subcategory_src_"+currentFormId).attr('src', data['image']);
                    $("#span_name_"+currentFormId).text(data['name']);
                    $("#subcategory_logo_name_"+currentFormId).val(data['imageName']);
                    $("#subcategory_id_"+currentFormId).val(data['id']);
                }
            });
            $("#subcategory_remove_"+currentFormId).show();
            // This is used for identify current raw of subcategory.
            $newItem.closest('.custom-subcategy-wrap').attr('data-order', currentFormId);
            $newItem.closest('.custom-subcategy-wrap').attr('data-id', 'id'+currentFormId);
            $newItem.closest('.custom-subcategy-wrap').attr('data-wbsassigned', 0);
            $('#total-form-subcategories').val(currentFormId);
            $('#subcategory-model-box').modal('hide');
        }
    });
    document.getElementById("sub_category_logo").addEventListener("change", readFile);

    $(document).on('click', '.subcategory-remove', function(t) {
        var deleteConfirmModalBox = '#delete-model-box';
        $("h5#modal_title").html(labels.delete_subcategory);
        $("#confirm_delete_message").html(labels.delete_subcategory_message);
        $(deleteConfirmModalBox).attr("data-id", $(this).closest("tr").attr('data-id'));
        $(deleteConfirmModalBox).attr("data-wbscount", $(this).closest("tr").attr('data-wbsassigned'));
        $(deleteConfirmModalBox).modal('show');
    });

    $('#delete-model-box-confirm').on('click', function(e) {
        var id       = $("#delete-model-box").attr("data-id");
        var wbscount = $("#delete-model-box").attr("data-wbscount");
        if (id && wbscount <= 0) {
            $("tr").filter("[data-id='" + id + "']").remove();
        } else {
            toastr.error(messages.in_use);
        }
        $('#delete-model-box').modal('hide');
    });

    $(document).on('click', '.subcategory-edit', function(t) {
        $('#sub_category_logo-error,#sub_cateory_name-error').remove();
        var index                 = $(this).closest("tr").attr('data-order');
        var deleteConfirmModalBox = '#subcategory-model-box';
        $("#modal_title").html(labels.edit_subcategory);
        $(deleteConfirmModalBox).attr("data-order", index);
        $(deleteConfirmModalBox).modal('show');
        $("#sub_category_name").val($("#span_name_"+index).text());
        $("#sub_category_logo_preview").attr('src', $("#subcategory_src_"+index).attr('src'));
        $("#subcategory_logo_name").html($("#subcategory_logo_name_"+index).val());
        $("#subcategorySave").hide();
        $("#subcategoryUpdate").show();
        
    });

    $(document).on('click', '#subcategory-model-box #subcategoryUpdate', function(e) {
        $('#sub_category_logo-error, #sub_cateory_name-error').remove();
        var res = [];
        if($("label[for='sub_category_logo'].custom-file-label").html() == 'Choose File'){
            $('#sub_category_logo').after('<div id="sub_category_logo-error" class="error text-danger">'+messages.field_required+'</div>');
            res.push("logo");
        } else {
            removeFrmArr(res, 'logo');
        }
        var regEx = /^[A-Za-z0-9 \/.,\\<>&()+\'\-]*$/;
        var subCategoriesName = $('#sub_category_name').val().trim();
        if(subCategoriesName == "") {
            $('#sub_category_name').after('<div id="sub_cateory_name-error" class="error text-danger">'+messages.field_required+'</div>');
            res.push("name");
        }else if((regEx.test(subCategoriesName)) == false) {
            $('#sub_category_name').after('<div id="sub_cateory_name-error" class="error text-danger">'+messages.enter_valid_subcategory+'</div>');
            res.push("name");
        }else{
            removeFrmArr(res, 'name');
        }
        if(res.length <= 0){
            e.preventDefault();
            var name      = $("#sub_category_name").val();
            var imageName = $("label#subcategory_logo_name").html();
            var image     = $("#sub_category_logo_preview").attr('src');
            var index     = $("#subcategory-model-box").attr('data-order');
            $("#span_name_"+index).html(name);
            $("#subcategory_name_"+index).html(name);
            $("#subcategory_name_"+index).val(name);
            $("#subcategory_logo_"+index).val(image);
            $("#subcategory_src_"+index).attr('src', image);
            $("#subcategory_logo_name_"+index).val(imageName);
            $('#subcategory-model-box').modal('hide');
        }
     });
});

function readFile() {
    if (this.files && this.files[0]) {
      var FR= new FileReader();
      FR.addEventListener("load", function(e) {
        document.getElementById("sub_category_logo_preview").src = e.target.result;
        document.getElementById("sub_category_logo").val = e.target.result;
      });
      FR.readAsDataURL( this.files[0] );
    }
}

function removeFrmArr(array, element) {
    return array.filter(e => e !== element);
}