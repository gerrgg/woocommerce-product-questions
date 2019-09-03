<?php
defined( 'ABSPATH' ) || exit;

class Customer_Questions
/**
 * This is the Customer Question form. 
 */
{
    public $form = null;
    public $customer_questions = array();
    public $post_id;

    function __construct( $post_id )
    {
        $this->post_id = $post_id;
        $this->customer_questions = $this->get_customer_questions();
        $this->form = new WC_Product_Question( $this->post_id );
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

