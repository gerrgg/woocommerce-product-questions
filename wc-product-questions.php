<?php
/**
 * Plugin Name:       WooCommerce Product Questions and Answers
 * Description:       A simple form added to 'woocommerce_after_single_product_summary' hook which allows users to search for, ask and awnser questions about a product.
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
add_action( 'admin_post_gerrg_ask_question', 'gerrg_create_question' );
add_action( 'admin_post_nopriv_gerrg_ask_question', 'gerrg_create_question' );

add_action( 'admin_post_gerrg_answer_question', 'gerrg_create_answer' );
add_action( 'admin_post_nopriv_gerrg_answer_question', 'gerrg_create_answer' );

// Fixes the count displayed in # of reviews.
add_filter( 'woocommerce_reviews_title', 'gerrg_fix_product_reviews_title');

function gerrg_enqueue_scripts(){
    wp_enqueue_script( 'gerrg-functions', plugin_dir_url( __FILE__ ) . 'functions.js', array('jquery'), '', true );
    wp_localize_script( 'gerrg-functions', 'wp_ajax', array( 'url' => admin_url( 'admin-ajax.php' ) ) );
}

function gerrg_fix_product_reviews_title(){
    // Simply fixes product reviews title. Was counting 'product_question' & 'product_answer'
    $reviews = get_comments( array(
        'post_id' => get_the_ID(),
        'type'    => 'review'
    ) );

    $count = sizeof( $reviews );
    return sprintf( esc_html( _n( '%1$s review for %2$s', '%1$s reviews for %2$s', $count, 'woocommerce' ) ), esc_html( $count ), '<span>' . get_the_title() . '</span>' );
}

function gerrg_get_product_qa_form(){
    $product_qa = new Customer_Questions( get_the_ID() );
    set_query_var( 'qa', $product_qa );
    wc_get_template( 'templates/wc-product-qa-form.php', array(), '', plugin_dir_path( __FILE__ ) );
}

function gerrg_create_question(){
    /**
     * Creates the question
     */

     // TODO: Plug in more author information.
     // TODO: Automatically create an account for the email
    $user_email = ( isset( $_POST['user_id'] ) ) ? get_user_meta( $_POST['user_id'], 'email', true ) : $_POST['email'];
    
    $args = array(
        'comment_post_ID' => $_POST['post_id'],
        'comment_author_email' => $user_email,
        'comment_content' => $_POST['s_questions'],
        'comment_type'    => 'product_question',
        'user_id'         => ( isset( $_POST['user_id'] ) ) ? $_POST['user_id'] : ''
    );

    $comment_id = wp_insert_comment( $args );
    wp_redirect( get_permalink( $_POST['post_id'] ) . '#product-qa' );
}

function gerrg_create_answer(){
    /**
     * Simply create the awnser, set the parent to the question_id
     */

     // TODO: More author data.
    if( ! isset( $_POST['post_id'], $_POST['question_id'] ) ) return;

    $args = array(
        'comment_post_ID'         => $_POST['post_id'],
        'comment_parent'          => $_POST['question_id'],
        'comment_type'    => 'product_answer',
        'comment_content' => $_POST['answer'],
        'user_id'         => ! empty( $_POST['user_id'] ) ? $_POST['user_id'] : ''
    );
    $comment_id = wp_insert_comment( $args );
    wp_redirect( get_permalink( $_POST['post_id'] ) . '#product-qa' );
}

function search_questions(){
    /**
     * Is hooked to an ajax call, returns html.
     * @return array WP_Comment's
     * @see wp_ajax_gerrg_search_questions
     */
    $q = $_POST['q'];
    
    $questions = get_comments( array(
        'post_parent' => $_POST['post_id'],
        'type'    => 'product_question',
        'search'  => $q
    ) );
    
    $question = new WC_Product_Question( $_POST['post_id'] );

    ob_start();
    empty( $questions ) ? $question->new() : $question->index( $questions );
    echo ob_get_clean();

    wp_die();
}
