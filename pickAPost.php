<?php
/**
 * Plugin Name: pickAPost
 * Plugin URI: http://countryfriedcoders.me
 * Description: Allows users to select their favorite posts and save the ID's to their profile
 * Version: 1.0
 * Author: Ben Redden
 * Author URI: http://benjaminredden.we.bs
 * License: GPL2.0
 */

 /**
  * enqueue stuff
  */
  function enqueuePostRatingScripts() {
    wp_enqueue_style( 'font-awesome', '//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.min.css' );
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'pickAPost', plugin_dir_url( __FILE__ ) . 'js/functions.js', array( 'jquery' ), '1.0.0', true );
    // localize var for the admin-ajax url and the nonce
    wp_localize_script( 'ppickAPost', 'ajax_var', array(
      'url' => admin_url( 'admin-ajax.php' ),
      'nonce' => wp_create_nonce( 'ajax-nonce' )
    ) );
  }

 /**
  * add favoritePost meta field to user profile
  */
 function addFavoritePostField( $user ) { ?>

     <h3>My Picks</h3>

     <table class="form-table">

         <tr>
             <th><label for="myPicks">My Picks</label></th>

             <td>
                 <?php
                 $favedPosts = get_the_author_meta( 'myPicks', $user->ID, true );
                 $prettyFavedPosts = implode(', ', $favedPosts);
                 ?>
                 <input type="text" name="myPicks" id="myPicks" value="<?php echo $prettyFavedPosts; ?>" class="regular-text" /><br />
                 <span class="description">List of your "favorited" posts; don't touch!</span>
             </td>
         </tr>

     </table>
 <?php }
 add_action( 'show_user_profile', 'addFavoritePostField' );
 add_action( 'edit_user_profile', 'addFavoritePostField' );

 /*
 * when a post is "picked"
 */
function pickAFavPost()
{
    // Check for nonce security
    $nonce = $_POST['nonce'];

    // if they aint got a nonce, send em away
    if ( ! wp_verify_nonce( $nonce, 'ajax-nonce' ) )
    {
        wp_die( 'Busted!'); // YOU SHALL NOT PASS
    }

    // if a post is rated
    if( isset( $_POST['post_id'] ) ) {
        // Retrieve user/post id/existing favorites/user_name
        $userID = get_current_user_id();
        $user_info = get_userdata($userID);
        $first_name = $user_info->first_name;
        $post_id = $_POST['post_id'];
        $favPosts = get_user_meta( $userID, 'myPicks', true);

        if (!in_array($post_id, $favPosts))
        {
            if( empty( $favPosts ) )
            {   // There was no meta_value, set an array.
                update_user_meta( $userID, 'myPicks', array( $post_id ) );
            }
            else
            {
                $favPosts_arr = ( is_array( $favPosts ) ) ? $favPosts : array( $favPosts );  // Added in case current value is not an array already.
                $favPosts_arr[] = $post_id;
                update_user_meta( $userID, 'myPicks', $favPosts_arr );
            }
        }
        else
        {
            echo 'Already added!';
        }

        // set up cars for bp activity stream
        $action = $first_name . ' added ' . get_the_permalink($post_id) . ' to their favorites.';
        $content = get_the_post_thumbnail($post_id, 'thumb');
        $time = date( 'Y-m-d H:i:s' );

        // add this to BP activity stream on profile
        bp_activity_add( array(
            'user_id' => $userID,
            'item_id' => $post_id,
            'action' => $action,
            'content' => $content,
            'component' => 'favorite',
            'type' => 'favorited_post',
            'recorded_time' => $time,
            'hide_sitewide' => false,
        ));
    }

    wp_die();
}
add_action( 'wp_ajax_nopriv_addFavVideo', 'pickAFavPost' );
add_action( 'wp_ajax_addFavVideo', 'pickAFavPost' );
