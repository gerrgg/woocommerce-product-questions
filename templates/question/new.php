<?php defined( 'ABSPATH' ) || exit; ?>

<?php if( is_user_logged_in() ) : ?>
    <div class="d-flex">
        <h5 class="text-center font-weight-bold">Don't see the answer you are looking for?</h5>
        <button type="submit" class="btn btn-dark" value="Ask" form="product-qa">Ask</button>
    </div>
<?php else : ?>
    <h5 class="text-center font-weight-bold"> Don't see the answer you are looking for?</h5>
    <div class="d-flex">
        <p>
            <input type="email" name="email" placeholder="susan@example.com" />
            <small>Automatically creates an account.</small>
        </p>
        <button type="submit" class="btn btn-dark" value="Ask" form="product-qa">Ask</button>
    </div>
<?php endif; ?>

