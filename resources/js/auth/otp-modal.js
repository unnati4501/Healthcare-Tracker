var $inputs = $(".def-txt-input");
var intRegex = /^\d+$/;

// Prevents user from manually entering non-digits.
$inputs.on("input.fromManual", function(){
    if(!intRegex.test($(this).val())){
        $(this).val("");
    }
});


// Prevents pasting non-digits and if value is 6 characters long will parse each character into an individual box.
$inputs.on("paste", function() {
    var $this = $(this);
    var originalValue = $this.val();
   
    $this.val("");

    $this.one("input.fromPaste", function(){
        $currentInputBox = $(this);
        
        var pastedValue = $currentInputBox.val();
        
        if (pastedValue.length == 6 && intRegex.test(pastedValue)) {
            pasteValues(pastedValue);
            $('.verify-btn').attr("disabled",false);
        }
        else {
            $this.val(originalValue);
        }

        $inputs.attr("maxlength", 1);
    });
    
    $inputs.attr("maxlength", 6);
});


// Parses the individual digits into the individual boxes.
function pasteValues(element) {
    var values = element.split("");

    $(values).each(function(index) {
        var $inputBox = $('#digit-' + (index + 1));
        $inputBox.val(values[index])
    });
};