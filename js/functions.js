jQuery(document).on('click', '.addFavVideo', function(e){
    if(jQuery(e.target).closest('a').length)
    {
        return;
    }
    else
    {
        // post id
        post_id = jQuery(this).data( "post_id" );

        // Ajax call
        jQuery.ajax( {
            type: "post",
            url: ajax_var.url,
            data: "action=addFavVideo&nonce="+ajax_var.nonce+"&post_id="+post_id,
            success: function( response )
            {
                // if they've already voted, tell em
                if (response === 'Already added!')
                {
                    jQuery( '.addFavVideo span' ).fadeOut( 200 ).html( response ).fadeIn( 200 );
                }
                else
                {
                    jQuery( '.addFavVideo span' ).fadeOut( 200 ).html( 'Video added to <a href="/videos/?myPicks=1">My Picks</a>' ).fadeIn( 200 );
                }
            }
        });
    }
});
