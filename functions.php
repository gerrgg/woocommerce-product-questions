<?php
/**
 * Plugin Name:       WooCommerce Product Questions and Answers
 * Description:       A simple form added to 'woocommerce_after_single_product_summary' hook which allows users to search for, ask and answer questions about a product.
 * Version:           0.5
 * Author:            Greg Bastianelli
 * Author URI:        http://gerrg.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       gerrg
 * Domain Path:       /languages
 */


 // grab the classes
require_once plugin_dir_path( __FILE__ ) . 'gerrg-customer-questions-class.php';
require_once plugin_dir_path( __FILE__ ) . 'gerrg-question-class.php';

// add js
add_action( 'wp_enqueue_scripts', 'gerrg_enqueue_scripts');

// add form to single product page
add_action( 'woocommerce_after_single_product_summary', 'gerrg_get_product_qa_form', 20 );

// add ajax hook for question search
add_action( 'wp_ajax_gerrg_search_questions', 'search_questions' );
add_action( 'wp_ajax_nopriv_gerrg_search_questions', 'search_questions' );

// add admin_post hooks for posting questions & answer's
// TODO: Make ajax?
add_action( 'wp_ajax_gerrg_ask_question', 'gerrg_create_question' );
add_action( 'wp_ajax_nopriv_gerrg_ask_question', 'gerrg_create_question' );

add_action( 'wp_ajax_gerrg_answer_question', 'gerrg_create_answer' );
add_action( 'wp_ajax_nopriv_gerrg_answer_question', 'gerrg_create_answer' );

// Fixes the count displayed in # of reviews.
add_filter( 'woocommerce_reviews_title', 'gerrg_fix_product_reviews_title');
add_filter( 'woocommerce_product_review_list_args', 'gerrg_fix_product_reviews_list' );

function gerrg_fix_product_reviews_list( $args ){
    return array( 'type' => 'review' );
}

function gerrg_enqueue_scripts(){
    wp_enqueue_script( 'gerrg-functions', plugin_dir_url( __FILE__ ) . 'functions.js', array('jquery'), '', true );
    wp_localize_script( 'gerrg-functions', 'wp_ajax', array( 'url' => admin_url( 'admin-ajax.php' ) ) );
}

function gerrg_fix_product_reviews_title(){
    // Simply fixes product reviews title. Was counting 'product_question' & 'product_answer'
    $reviews = get_comments( array(
        'post_id' => get_the_ID(),
        'type'    => 'review',
        'status'  => 'approve',
    ) );

    $count = sizeof( $reviews );
    return sprintf( esc_html( _n( '%1$s review for %2$s', '%1$s reviews for %2$s', $count, 'woocommerce' ) ), esc_html( $count ), '<span>' . get_the_title() . '</span>' );
}

function gerrg_get_product_qa_form(){
    /**
     * Displays the HTML template for the Customer Questions Form.
     */
    $product_qa = new Customer_Questions( get_the_ID() );
    set_query_var( 'qa', $product_qa );
    wc_get_template( 'templates/wc-product-qa-form.php', array(), '', plugin_dir_path( __FILE__ ) );
}

function gerrg_look_for_user( $checklist ){
    /**
     * Attempts to get a user where column = key and row = value.
     * @param array [ [key => value], ... ]
     * @return WP_User | WP_Error
     */
    foreach( $checklist as $key => $value ){
        $user = get_user_by( $key, $value );
        if( false !== $user ) return $user;
    }

    // if we get this far... we are making a user!
    return gerrg_create_user_by_email( $checklist['email'] );
}

function gerrg_create_user_by_email( $email ){
    /**
     * Quickly creates a user using the provided email.
     * @param string
     * @return WP_User | False
     */
    $username = explode('@', $email)[0] . rand(100, 999);
    $password = wp_generate_password(8);
    $user_id = wp_create_user( $username, $password, $email );
    return get_user_by( 'id', $user_id );
}

function gerrg_create_question(){
    /**
     * Creates the question, checks for users with a matching ID or email. Creates them if doesn't exist.
     */
    if( empty( $_POST['question'] ) ) wp_redirect( get_permalink( $_POST['post_id'] ) . '#product-qa' );

    $user = gerrg_look_for_user( array(
        'id' => $_POST['user_id'],
        'email' => ( isset( $_POST['email'] ) ) ? $_POST['email'] : '',
    ) );

    $args = array(
        'comment_post_ID'       => $_POST['post_id'],
        'comment_author'        => $user->user_login,
        'comment_author_email'  => $user->user_email,
        'comment_author_url'   => '',
        'comment_content'       => $_POST['question'],
        'comment_type'          => 'product_question',
        'comment_author_IP'     => $_SERVER['REMOTE_ADDR'],
        'comment_agent'         => $_SERVER['HTTP_USER_AGENT'],
        'user_id'               => $user->ID,
    );


    $comment_id = wp_new_comment( $args );
    gerrg_send_questions_to_customers( $comment_id );

    //html
    $success = '<p class="text-center"><i class="fas fa-check text-success fa-2x"></i> Thank you! We are looking around for you.</p>';
    $error = '<p class="text-center"><i class="fas fa-times text-danger fa-2x"></i> An error occured. Try again or call us.</p>';
    echo ( ! empty( $comment_id ) ) ? $success : $error;

    wp_die();
}

function gerrg_create_answer(){
    /**
     * Create the answer, set the parent to the question_id
     */

    if( ! isset( $_POST['post_id'], $_POST['question_id'] ) ) return;
    if( empty( $_POST['answer'] ) ) wp_redirect( get_permalink( $_POST['post_id'] ) . '#product-qa' );
 
    $user = get_user_by( 'id', $_POST['user_id'] );
    $user_id = ( false === $user ) ? '' : $user->user_id;

    $args = array(
        'comment_post_ID'         => $_POST['post_id'],
        'comment_parent'          => $_POST['question_id'],
        'comment_content'         => $_POST['answer'],
        'comment_type'            => 'product_answer',
        'user_id'                 => $user_id,
    );

    // add user info if user
    if( false !== $user){
        $args['comment_author'] = $user->user_login;
        $args['comment_author_email'] = $user->user_email;
        $args['comment_author_IP'] = $_SERVER['REMOTE_ADDR'];
        $args['comment_agent'] = $_SERVER['HTTP_USER_AGENT'];
    }

    $comment_id = wp_new_comment( $args );
    gerrg_email_asker( $comment_id );

    $success = '<p class="text-center"><i class="fas fa-check text-success fa-2x"></i> Thank you! We appreciate your input!</p>';
    $error = '<p class="text-center"><i class="fas fa-times text-danger fa-2x"></i>Sorry, your submission failed, please try again!</p>';

    echo ( ! empty( $comment_id ) ) ? $success : $error;
    wp_die();
}

function gerrg_email_asker( $comment_id ){
    /**
     * Find the comment question, email the poster the new answer.
     * @param int - ID of the answer to the question
     */

     // get Q and A
    $answer = get_comment( $comment_id );
    $question = get_comment( $answer->comment_parent );
    $question_content = stripslashes($question->comment_content);
    $product = wc_get_product($question->comment_post_ID);

    // Link to question on product page
    $comment_link = $product->get_permalink() .'#comment-'. $question->comment_ID;

    // setup email
    $to = $question->comment_author_email;
    $headers = array('Content-Type: text/html; charset=UTF-8');
    $subject = sizeof($question->get_children()) . " answers to your question '" . $question_content . "'.";

    $logo = Customer_Questions::get_site_logo();

    $html = '<img src="'. $logo .'" />';
    $html .= "<h4>Someone has answered your question!</h4>";
    $html .= "<p>You asked: '$question_content'</p>";
    $html .= "<p>Your answer: '$answer->comment_content'</p>";

    $html .= '<a href="'. $comment_link .'">Click here to see the answer</a>';

    wp_mail($to, $subject, $html, $headers);
    
}


function gerrg_send_questions_to_customers( $comment_id )
{
    /**
     * Puts together a list of verified users and store admins, then emails the question to those users
     * @param int
     */

    //data
    $comment = get_comment( $comment_id );
    $product = wc_get_product( $comment->comment_post_ID );

    //settings
    $headers = array('Content-Type: text/html; charset=UTF-8');
    $subject = 'A customer asked "' . stripslashes($comment->comment_content) . '".';

    // gather list
    $email_list = gerrg_generate_email_list( $comment->comment_post_ID );
    
    // create html
    $html = gerrg_generate_email_html( $comment, $product );

    if ( ! empty( $email_list ) ){
        foreach ( $email_list as $email) wp_mail( $email, $subject, $html, $headers );
    }
}

function gerrg_generate_email_list( )
{
    /**
     * Generates a list of people who either purchased the product in the past or is a store admin
     * @param int
     * @return array - [string, ...]
     */
    $email_list = array();
    $user_list = array();

     // get verified customers - returns ID
     // TODO: Just Admins for now.

    // get admins - Returns ID
    $admins = get_users( array( 'fields' => array('user_email'), 'role' => 'administrator' ) );
    foreach( $admins as $admin ){
        array_push( $email_list, $admin->user_email );
    }

    

    return $email_list;
}

function gerrg_generate_email_html( $comment, $product ){
    /**
     * Generates the html for email
     * @param WP_Comment,WP_Product
     * @return string - formatted html
     */
    $comment_link = $product->get_permalink() .'#comment-'. $comment->comment_ID;
    $content = stripslashes($comment->comment_content);


    $custom_logo_id = get_theme_mod( 'custom_logo' );
    $image = wp_get_attachment_image_url( $custom_logo_id , 'medium' );

    $html = '<img src="'. $image[0] .'" />';
    $html .= '<h3>"'. stripslashes($content) .'"</h3>';
    $html .= '<p>Hello, a customer asked the following question "'. $content .'" about the <strong>'. $product->get_name() .'</strong>.</p>';
    $html .= '<p>Can you help us out here? Do you know the answer to this question?</p>';
    $html .= '<a href="'. $comment_link .'">Click here to answer the question</a>';

    return $html;
}

function gerrg_get_customers_who_purchased_product( $product_id ){
    /**
     * A seemingly complex SQL query which gets all user ID's of users who purchased a product.
     */
    global $wpdb;
    $order_item = $wpdb->prefix . 'woocommerce_order_items';
    $order_item_meta = $wpdb->prefix . 'woocommerce_order_itemmeta';

    $sql = "SELECT DISTINCT p_meta.meta_value
            FROM $wpdb->users u, $wpdb->posts p, $wpdb->postmeta p_meta, $order_item i, $order_item_meta meta
            WHERE p.post_type = 'shop_order'
            AND p.post_status = 'wc-completed'
            AND p.ID = i.order_id
            AND p_meta.post_id = P.ID
            AND i.order_item_type = 'line_item'
            AND i.order_item_id = meta.order_item_id
            AND meta.meta_value = $product_id";
            
    return $wpdb->get_results( $sql );
}

function search_questions(){
    /**
     * Is hooked to an ajax call, returns html.
     * @return array WP_Comment's
     * @see wp_ajax_gerrg_search_questions
     */
    $q = $_POST['q'];

    $args = array(
        'post_parent' => $_POST['post_id'],
        'type'    => 'product_question',
        'status'  => 'approve',
    );

    if( ! empty( $q ) ) $args['s'] = $q;
    
    $questions = get_comments( $args );
    $question = new WC_Product_Question( $_POST['post_id'] );

    ob_start();
    empty( $questions ) ? $question->new( $q ) : $question->index( $questions );
    echo ob_get_clean();

    wp_die();
}
