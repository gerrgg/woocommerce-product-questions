<?php
defined( 'ABSPATH' ) || exit;

class Customer_Questions
/**
 * This is the Customer Question form. 
 */
{
    public $form = null;
    public $customer_questions = array();
    public $site_logo;
    public $post_id;

    function __construct( $post_id )
    {
        $this->post_id = $post_id;
        $this->customer_questions = $this->get_customer_questions();
        $this->form = new WC_Product_Question( $this->post_id );
        $this->site_logo = $this->get_site_logo();
    }

    public static function get_site_logo(){
        $custom_logo_id = get_theme_mod( 'custom_logo' );
        $image = wp_get_attachment_image_url( $custom_logo_id , 'medium' );
        return $image[0];
    }

    public function get_customer_questions()
    /**
     * this is the initial query into product questions. Gets most recent.
     */
    {
        $args = array(
            'post_id' => $this->post_id,
            'status'  => 'approve',
            'type'    => 'product_question',
            'number'  => 5,
            'order'   => 'DESC',
        );
        return get_comments( $args );
        
    }
}

