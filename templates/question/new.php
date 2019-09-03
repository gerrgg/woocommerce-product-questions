<?php defined( 'ABSPATH' ) || exit; ?>

<form method="POST" action="<?php echo admin_url( 'admin-post.php' ) ?>" >
    <h5 class="font-weight-bold text-center"> Don't see the answer you are looking for?</h5>
    <div class="d-flex d-md-block text-center ">

        <?php if( ! is_user_logged_in() ) :  // add email input if not logged in. ?>
            <input type="email" name="email" placeholder="user@example.com" /> <br>
        <?php else : ?>
            <input type="hidden" name="user_id" value="<?php echo get_current_user_id() ?>" />
        <?php endif; ?>
        <button id="ask-question" type="submit" class="btn btn-dark">Ask</button>
    </div>
    <input type="hidden" name="question" value="<?php echo get_query_var('question') ?>" />
    <input type="hidden" name="post_id" value="<?php echo get_query_var('post_id') ?>" />
    <input type="hidden" name="user_id" value="<?php echo get_current_user_id() ?>" />
    <input type="hidden" name="action" value="gerrg_ask_question" />
    <?php do_action( 'gerrg_after_question_new' ) ?>
</form>

