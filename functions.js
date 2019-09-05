jQuery( function( $ ) {
    var product_qa = {
        $search: $('#product-qa-search'),
        $results: $('#product-qa-results'),

        init: function(){
            this.$search.keyup( this.debounce( this.search_questions, 200 ) )
            this.$results.on('click', "button#ask-question", this.submit)
            this.$results.on('click', "button.submit-answer", this.submit)
        },

        submit( e ){
            $form = $(e.currentTarget.form);
            $.post( wp_ajax.url, $form.serialize(), function( data ){
                $form.html( data );
            } );
        },

        search_questions( e ){
            // check if the question has a '?' or matches other questions.
            var q = e.target.value
            var post_id = $('.product.type-product').attr('id').split('-');
            post_id = post_id[post_id.length - 1];
            
            if( q.length > 3 ){
                $.post( wp_ajax.url, { action: 'gerrg_search_questions', q: q, post_id: post_id }, function( data ){
                    product_qa.$results.html( data );
                } );
            }
        },

        debounce(fn, bufferInterval) {
            var timeout;
          
            return function () {
              clearTimeout(timeout);
              timeout = setTimeout(fn.apply.bind(fn, this, arguments), bufferInterval);
            };
          
          }
    }
    product_qa.init();
});
