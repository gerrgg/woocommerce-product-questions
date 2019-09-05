# woocommerce-product-questions
A simple form added to 'woocommerce_after_single_product_summary' hook which allows users to search for, ask and awnser questions about a product.

## TODO
- [x] Moderate questions/answers
- [x] Provide the user feedback when a question / answer is successfully submitted. (AJAX)
- [x] Email the admin and team ( role specific ) the question.
- [ ] Search for users who purchased product 'X', email them the question. Must have unsubscribe feature.
- [x] Single product top review count is off, fix filter.
- [ ] Email the asker when a question is answered
- [ ] Include a JS event that will open the question the user wants to answer. ( or seperate form in case of not approved ).
- [ ] We cannot let any post questions to our users. Must create seperate answer form. (CRUD?, Seperate Page)
- [x] Use PHP/JS to validate questions, is it a question, is it blank? is it short?
- [x] Add recaptcha to FORMS ( add hook for add recaptcha from theme! )
- [x] Limit question query on initial page load, LIMIT 5

### Available Hooks
- gerrg_after_question_new
- gerrg_after_new_answer
