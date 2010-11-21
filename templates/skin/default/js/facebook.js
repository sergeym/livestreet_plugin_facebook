function lsToggleFacebookBlock(active) {
    $('block_facebook_menu').getElements('li').removeClass('active');
    $('menu_'+active).getParent('li').addClass('active');

    $(active).getParent().getElements('div').hide();
    $(active).show();

}