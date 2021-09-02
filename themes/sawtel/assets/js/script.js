(function ($) {
    "use strict";


    
    //=============================
    // MOBILE Nav 
    //=============================

    $('#mobile-menu-toggler').on("click", function () {
        $('ul.main-menu').slideToggle(500);
    });


    
    // DRP-DOWN 

    $(".has-menu-child").append('<i class="menu-dropdown fas fa-angle-down"></i>');
    if ($(window).width() <= 992) {
        
        $(".has-menu-child").on("click", function () {
            $('.sub-menu').slideUp();
            $('.sub-menu', this).slideDown();
            $(".has-menu-child i").toggleClass("fas fa-angle-down fas fa-angle-up");
        });
    }






})(jQuery);