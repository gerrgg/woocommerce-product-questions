<?php defined( 'ABSPATH' ) || exit; ?>

<form id="product-qa" class="mt-2" method="POST" action="<?php echo admin_url( 'admin-post.php' ) ?>">
    <h2>Customer Questions</h2>
    <div class="input-group mb-3">
        <div class="input-group-prepend">
            <span class="input-group-text"><i class="fas fa-search"></i></span>
        </div>
        <input type="text" name="s_questions" placeholder="Have a question? Search for answers" class="form-control" />
    </div>
    <input type="hidden" name="post_id" value="<?php echo get_the_ID() ?>" />
    <input type="hidden" name="user_id" value="<?php echo get_current_user_id() ?>" />
    <input type="hidden" name="action" value="gerrg_ask_question" />
</form>

<div id="product-qa-results" class="mb-5 border-bottom">
        <?php
            $product_qa = get_query_var( 'qa' );
            if( isset( $product_qa->customer_questions ) && ! empty( $product_qa->customer_questions ) ){
                $product_qa->form->index( $product_qa->customer_questions );
            }
        ?>
</div>