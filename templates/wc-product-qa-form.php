<?php defined( 'ABSPATH' ) || exit; ?>

<div id="product-qa" class="mt-2">
    <h2>Customer Questions</h2>
    <div class="input-group mb-3">
        <div class="input-group-prepend">
            <span class="input-group-text"><i class="fas fa-search"></i></span>
        </div>
        <input id="product-qa-search" type="text" name="s_questions" placeholder="Have a question? Search for answers" class="form-control" />
    </div>
    <ul id="errors"></ul>
</div>

<div id="product-qa-results" class="mb-5 border-bottom">
    <?php
        $product_qa = get_query_var( 'qa' );
        if( isset( $product_qa->customer_questions ) && ! empty( $product_qa->customer_questions ) ){
            $product_qa->form->index( $product_qa->customer_questions );
        }
    ?>
</div>