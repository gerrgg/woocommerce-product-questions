<?php 

defined( 'ABSPATH' ) || exit;

class WC_Product_Question
/**
 * A simply class for formatting comments a certain way.
 */
{   
    public $post_id;

    function __construct( $post_id )
    {
        $this->post_id = $post_id;
    }
    public function new( $question )
    // The form for posting a 'new' question
    {
        set_query_var( 'post_id', $this->post_id );
        set_query_var( 'question', $question );
        wc_get_template( 'new.php', array(), '', plugin_dir_path( __FILE__ ) . 'templates/question/' );
    }

    public function show( $question ){
        /**
         * The html markup of a question including any approved answers.
         * @param WC_Comment
         */  
        $id = $question->comment_ID;
        $answers = $question->get_children( array( 'status' => 1 ) );
        $badge = array(
            'color' => ( ! empty( $answers ) ) ? 'badge-success' : 'badge-danger',
            'text' => ( ! empty( $answers ) ) ? sizeof( $answers ) . ' answers' : 'None',
        );

        ?>
        <li id="comment-<?php echo $id ?>" class="list-group-item">
            <a class="link-normal" data-toggle="collapse" href="#answers_to_question_<?php echo $id ?>" role="button" aria-expanded="false">
                <span class="font-weight-bold pr-2">Q: </span>
                <?php echo $question->comment_content ?>
                <span class="float-right"><i class="fas fa-chevron-down"></i></span>
            </a>
            <span class="badge badge-pill ml-sm-3 <?php echo $badge['color'] ?>"><?php echo $badge['text'] ?></span>
            <div id="answers_to_question_<?php echo $id ?>" class="collapse">
                <?php 
                if ( empty( $answers ) ) {
                    $this->new_answer( $id );
                } else { 
                    $this->index_answers( $answers );
                    $this->new_answer( $id );
                }
                ?>
            </div>
        </li>
        <?php
    }

    public function new_answer( $id ){
        /**
         * The form for answering a question
         */
        ?>
        <form method="POST">
            <label class="font-weight-bold m-0">answer this question</label>
            <textarea name="answer" class="form-control" placeholder="Your answers help others learn about this product" required></textarea>
            <input type="hidden" name="post_id" value="<?php echo $this->post_id ?>" />
            <input type="hidden" name="user_id" value="<?php echo get_current_user_id() ?>" />
            <input type="hidden" name="question_id" value="<?php echo $id ?>" />
            <input type="hidden" name="action" value="gerrg_answer_question" />
            <button type="button" role="button" class="btn btn-success submit-answer">answer</button>
            <?php do_action( 'gerrg_after_new_answer' ) ?>
        </form>
        <?php
    }

    public function index( $questions ){
        /**
         * @param array [ WC_Comment, ... ]
         * A list of questions 
         */        
        echo '<ul class="list-group m-0">';
        foreach( $questions as $question ){
            $this->show( $question );
        }
        echo '</ul>';
    }

    public function index_answers( $answers ){
        /**
         * @param array [ WC_Comment, ... ]
         * A list of answers 
         */     
        foreach( $answers as $answer ){
            $this->show_answer( $answer );
        }
    }

    public function show_answer( $answer ){
        /**
         * @param WC_Comment
         * HTML markup for answer's.
         */     
        $author = empty( $answer->comment_author ) ? 'Anonymous' : $answer->comment_author;
        $date = date( 'm-d-y', strtotime( $answer->comment_date ) );
        ?>
        <p>
            <span class="font-weight-bold pr-2">A: </span>
            <?php echo $answer->comment_content ?> 
            <br>
            <small><?php echo $author . ' | ' . $date ?></small>
        </p>
        <?php
    }
}