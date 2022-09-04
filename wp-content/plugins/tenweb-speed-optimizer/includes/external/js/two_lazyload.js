jQuery("document").ready(function () {
  jQuery("*:not('br, hr, iframe, pre')").each(function () {
    var bg_img = jQuery(this).css('background-image');
    if(bg_img !== "none" && bg_img.indexOf("data:image/svg") >= 0){
      var el_bg_ob = bg_img.split("#}");
      if(typeof el_bg_ob === "object" && el_bg_ob.length === 2){
        var el_bg =el_bg_ob[1].replace('")', "");
        el_bg = el_bg.replace("')", "");
        jQuery(this).addClass("two_bg");
        jQuery(this).attr("data-src", el_bg);
        jQuery(this).on('visibility', function () {
          var $element = jQuery(this);
          setInterval(function () {
            jQuery('.two_bg').Lazy({
              visibleOnly:true,
            });
          }, 300);
        }).trigger('visibility');

      }
    }

  });
  jQuery('.two_bg').Lazy({
    visibleOnly:true,
  });
});

