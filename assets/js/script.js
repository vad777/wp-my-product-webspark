jQuery( function( $ ) {
   $(document).ready(function($) {
        $(".quantity-selector").each(function() {
            var input = $(this).find("input");
            var btnMinus = $(this).find(".quantity-minus");
            var btnPlus = $(this).find(".quantity-plus");

            console.log(input);

            btnMinus.click(function() {
                var value = parseInt(input.val(), 10);
                if (value > 1) {
                    input.val(value - 1);
                }
            });

            btnPlus.click(function() {
                var value = parseInt(input.val(), 10);
                input.val(value + 1);
            });
        });
    });
});



