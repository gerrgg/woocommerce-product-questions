<?php 

defined( 'ABSPATH' ) || exit;

class WC_Product_Question
{   
    public $post_id;

    function __construct( $post_id )
    {
        $this->post_id = $post_id;
    }
    public function new()
    {
        set_query_var( 'post_id', $this->post_id );
        wc_get_template( 'new.php', array(), '', plugin_dir_path( __FILE__ ) . 'templates/question/' );
    }

    public function show( $question ){
        $id = $question->comment_ID;
        $answers = $question->get_children();
        $badge = array(
            'color' => ( ! empty( $answers ) ) ? 'badge-success' : 'badge-danger',
            'text' => ( ! empty( $answers ) ) ? sizeof( $answers ) . ' answers' : 'None',
        );

        ?>
        <li class="list-group-item">
            <a class="link-normal" data-toggle="collapse" href="#answers_to_question_<?php echo $id ?>" role="button" aria-expanded="false">
                <span class="font-weight-bold pr-2">Q: </span>
                <?php echo $question->comment_content ?>
                <span class="float-right"><i class="fas fa-chevron-down"></i></span>
            </a>
            <span class="badge badge-pill ml-sm-3 <?php echo $badge['color'] ?>"><?php echo $badge['text'] ?></span>
            <div id="answers_to_question_<?php echo $id ?>" class="collapse">
                <?php ( empty( $answers ) ) ? $this->new_answer( $id ) : $this->index_answers( $answers ); ?>
            </div>
        </li>
        <?php
    }

    public function new_answer( $id ){
        ?>
        <form id="new_answer" method="POST" action="<?php echo admin_url( 'admin-post.php' ) ?>">
            <label class="font-weight-bold m-0">answer this question</label>
            <textarea name="answer" class="form-control" placeholder="Your answers help others learn about this product" required></textarea>
            <input type="hidden" name="post_id" value="<?php echo $this->post_id ?>" />
            <input type="hidden" name="user_id" value="<?php echo get_current_user_id() ?>" />
            <input type="hidden" name="question_id" value="<?php echo $id ?>" />
            <input type="hidden" name="action" value="gerrg_answer_question" />
            <button type="submit" form="new_answer" class="btn btn-success">answer</button>
        </form>
        <?php
    }

    public function index( $questions ){        
        echo '<ul class="list-group m-0">';
        foreach( $questions as $question ){
            $this->show( $question );
        }
        echo '</ul>';
    }

    public function index_answers( $answers ){
        foreach( $answers as $answer ){
            $this->show_answer( $answer );
        }
    }

    public function show_answer( $answer ){
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