function getCookie(name) {
    var matches = document.cookie.match(new RegExp(
        "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
    ));
    return matches ? decodeURIComponent(matches[1]) : undefined;
}
jQuery(document).ready(function($) {
    var show_modal = getCookie("wcd_show_modal");
    if (show_modal != "false") {
        $(document).mouseleave(function(e){
            if (e.clientY < 10) {
                $(".exitblock").fadeIn("fast");
                var date = new Date;
                date.setDate(date.getDate() + 1);
                document.cookie = "wcd_show_modal=false; path=/; expires=" + date.toUTCString();
            }
        });
        $(document).click(function(e) {
            if (($(".exitblock").is(':visible')) && (!$(e.target).closest(".exitblock .modaltext").length)) {
                $(".exitblock").remove();
            }
        });
        $('.closeblock').click(function(e) {
            $(".exitblock").remove();
        });
        $('.modalbutton').click(function(e) {
            var date = new Date;
            date.setDate(date.getDate() + 3);
            document.cookie = "wcd_get_discount=true; path=/; expires=" + date.toUTCString();
            location.reload();
        });


    }
});