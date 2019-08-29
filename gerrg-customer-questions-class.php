<?php
defined( 'ABSPATH' ) || exit;

class WC_Product_QA
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
    {
        $args = array(
            'post_id' => $this->post_id,
            'type'    => 'product_question',
            'number'  => 5
        );
        return get_comments( $args );
        
    }
}

