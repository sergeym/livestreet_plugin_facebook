function lsToggleFacebookBlock(active) {
    if (typeof(MooTools)=='object') {
        $('block_facebook_menu').getElements('li').removeClass('active');
        $('menu_'+active).getParent('li').addClass('active');
        $(active).getParent().getElements('div').hide();
        $(active).show();
    } else if (typeof(jQuery)=='function') {
        $('#block_facebook_menu li').removeClass('active');
        $('#menu_'+active).parent().addClass('active');
        $('#'+active).parent().find('div').hide();
        $('#'+active).show();
    }
}