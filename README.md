# woocommerce-product-questions
A simple form added to 'woocommerce_after_single_product_summary' hook which allows users to search for, ask and awnser questions about a product.

## TODO
[x] - Moderate questions/answers
[] - Search for users who purchased product 'X', email them the question. Must have unsubscribe feature.
[] - Single product top review count is off, fix filter.
[] - Use PHP/JS to validate questions, is it a question, is it blank? is it short?
[] - Add recaptcha to FORMS ( add hook for add recaptcha from theme! )
[x] - Limit question query on initial page load, LIMIT 5

### Available Hooks
- gerrg_after_question_new
- gerrg_after_new_answer
