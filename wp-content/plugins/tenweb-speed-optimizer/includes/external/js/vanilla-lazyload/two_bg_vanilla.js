window.onload = function () {
    two_replace_backgrounds();
};


function two_replace_backgrounds(){
    var two_elements_list = document.querySelectorAll("*:not(br):not(hr):not(iframe):not(pre)");
    two_elements_list.forEach((elem) => {
        var style = elem.currentStyle || window.getComputedStyle(elem, false);
        var bi = style.backgroundImage.slice(4, -1).replace(/"/g, "");
        if(bi.length>0 && bi.indexOf("data:image/svg") >= 0){
            var el_bg_ob = bi.split("#}");
            elem.classList.add("two_bg");
            elem.classList.add("lazy");
            elem.setAttribute("data-bg", el_bg_ob[1]);
        }
    });
    if(typeof two_lazyLoadInstance === "undefined"){
        var two_lazyLoadInstance = new LazyLoad({});
    }else{
        two_lazyLoadInstance.update();
    }
}
