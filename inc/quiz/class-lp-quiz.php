<?php

/**
 * Class LP_Quiz
 *
 * @author  ThimPress
 * @package LearnPress/Classes
 * @version 3.0.0
 */

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'LP_Quiz' ) ) {

	/**
	 * Class LP_Quiz
	 */
	class LP_Quiz extends LP_Course_Item implements ArrayAccess {

		/**
		 * @var string
		 */
		protected $_post_type = LP_QUIZ_CPT;

		/**
		 * @var array
		 */
		protected $_data = array(
			'retake_count'       => 0,
			'show_result'        => 'no',
			'passing_grade_type' => '',
			'passing_grade'      => 0,
			'show_check_answer'  => 'no',
			'count_check_answer' => 0,
			'show_hint'          => 'no',
			'count_hint'         => 0,
			'archive_history'    => 'no'
		);

		/**
		 * @var int
		 */
		protected static $_loaded = 0;

		/**
		 * LP_Quiz constructor.
		 *
		 * @since 3.0.0
		 *
		 * @param mixed $the_quiz
		 * @param null $args
		 */
		public function __construct( $the_quiz, $args = null ) {
			parent::__construct( $the_quiz, $args );

			$this->_curd = new LP_Quiz_CURD();

			if ( is_numeric( $the_quiz ) && $the_quiz ) {
				$this->set_id( $the_quiz );
			} elseif ( $the_quiz instanceof self ) {
				$this->set_id( absint( $the_quiz->get_id() ) );
			} elseif ( ! empty( $the_quiz->ID ) ) {
				$this->set_id( absint( $the_quiz->ID ) );
			}

			if ( $this->get_id() > 0 ) {
				$this->load();
			}
			self::$_loaded ++;
			if ( self::$_loaded == 1 ) {
				add_filter( 'debug_data', array( __CLASS__, 'log' ) );
			}
		}

		/**
		 *
		 * Log debug data.
		 *
		 * @since 3.0.0
		 *
		 * @param $data
		 *
		 * @return array
		 */
		public static function log( $data ) {
			$data[] = __CLASS__ . '( ' . self::$_loaded . ' )';

			return $data;
		}

		/**
		 * Load the quiz data.
		 * Check if the id is not zero but it's post type does not exists.
		 *
		 * @since 3.0.0
		 *
		 * @throws Exception
		 */
		public function load() {
			$this->_curd->load( $this );
		}

		/**
		 * Save quiz data.
		 *
		 * @return mixed
		 *
		 * @throws Exception
		 */
		public function save() {
			if ( $this->get_id() ) {
				$return = $this->_curd->update( $this );
			} else {
				$return = $this->_curd->create( $this );
			}

			return $return;
		}

		/**
		 * Set quiz retake count.
		 *
		 * @since 3.0.0
		 *
		 * @param $count
		 */
		public function set_retake_count( $count ) {
			$this->_set_data( 'retake_count', $count );
		}

		/**
		 * @return array|mixed
		 */
		public function get_retake_count() {
			return $this->get_data( 'retake_count' );
		}

		/**
		 * @param $show_result
		 */
		public function set_show_result( $show_result ) {
			$this->_set_data( 'show_result', $show_result );
		}

		/**
		 * @return array|mixed
		 */
		public function get_show_result() {
			return $this->get_data( 'show_result' );
		}

		/**
		 * @param $type
		 */
		public function set_passing_grade_type( $type ) {
			$this->_set_data( 'passing_grade_type', $type );
		}

		/**
		 * @return array|mixed
		 */
		public function get_passing_grade_type() {
			return $this->get_data( 'passing_grade_type' );
		}

		/**
		 * @param $value
		 */
		public function set_passing_grade( $value ) {
			$this->_set_data( 'passing_grade', $value );
		}

		/**
		 * @return array|mixed
		 */
		public function get_passing_grade() {
			return $this->get_data( 'passing_grade' );
		}

		/**
		 * @param $value
		 */
		public function set_show_check_answer( $value ) {
			$this->_set_data( 'show_check_answer', $value );
		}

		/**
		 * @return array|mixed
		 */
		public function get_show_check_answer() {
			return $this->get_data( 'show_check_answer' );
		}

		/**
		 * @param $count
		 */
		public function set_count_check_answer( $count ) {
			$this->_set_data( 'count_check_answer', $count );
		}

		/**
		 * @return int
		 */
		public function get_check_answer_count() {
			return intval( $this->get_data( 'check_answer_count' ) );
		}

		/**
		 * @param $value
		 */
		public function set_show_hint( $value ) {
			$this->_set_data( 'show_hint', $value );
		}

		/**
		 * @return int
		 */
		public function get_show_hint() {
			return intval( $this->get_data( 'show_hint' ) );
		}

		/**
		 * @param $count
		 */
		public function set_count_hint( $count ) {
			$this->_set_data( 'count_hint', $count );
		}

		/**
		 * Return true if hint answer is enabled.
		 *
		 * @return bool
		 */
		public function enable_show_hint() {
			return apply_filters( 'learn-press/quiz/enable-show-hint', $this->get_data( 'show_hint' ) == 'yes', $this->get_id() );
		}

		/**
		 * @param $value
		 */
		public function set_archive_history( $value ) {
			$this->_set_data( 'archive_history', $value );
		}

		/**
		 * Return true if archive history is enabled.
		 *
		 * @return bool
		 */
		public function enable_archive_history() {
			return apply_filters( 'learn-press/quiz/enable-archive-history', $this->get_data( 'archive_history' ) == 'yes', $this->get_id() );
		}

		/**
		 * Get quiz duration.
		 *
		 * @since 3.0.0
		 *
		 * @return mixed
		 */
		public function get_duration() {
			$duration = $this->get_data( 'duration' );
			if ( false === $duration || '' === $duration ) {
				if ( $duration = get_post_meta( $this->get_id(), '_lp_duration', true ) ) {
					$duration = new LP_Duration( $duration );
				}

				$this->_set_data( 'duration', $duration );
			}

			return apply_filters( 'learn-press/quiz-duration', $duration, $this->get_id() );
		}

		/**
		 * Get quiz mark.
		 *
		 * @since 3.0.0
		 *
		 * @return mixed
		 */
		public function get_mark() {
			$mark = $this->get_data( 'mark' );
			if ( false === $mark || '' === $mark ) {
				$questions = $this->get_questions();
				$mark      = 0;
				foreach ( $questions as $question_id ) {
					$question = LP_Question::get_question( $question_id );
					$mark     += $question->get_mark();
				}
				$this->_set_data( 'mark', $mark );
			}

			return apply_filters( 'learn-press/quiz-mark', $mark, $this->get_id() );
		}

		/**
		 * Get quiz questions.
		 *
		 * since 3.0.0
		 *
		 * @return mixed
		 */
		public function get_questions() {
			$questions = $this->_curd->get_questions( $this );

			return apply_filters( 'learn-press/quiz/questions', $questions, $this->get_id() );
		}

		/**
		 * @return mixed|string
		 */
		public function get_heading_title() {
			global $lp_quiz_question;
			$title = $this->get_title();
			if ( $lp_quiz_question instanceof LP_Question ) {
				$titles = apply_filters( 'learn-press/quiz/title-parts', array(
					$title,
					sprintf( '<small>%s</small>', $lp_quiz_question->get_title() )
				) );
				$title  = apply_filters( 'learn-press/quiz/heading-title', join( ' ', $titles ) );
			}

			return $title;
		}

		/**
		 * Quiz editor get questions.
		 *
		 * @return mixed
		 */
		public function quiz_editor_get_questions() {
			// list questions
			$questions = $this->get_questions();
			// order questions in quiz
			$order = learn_press_quiz_get_questions_order( $questions );

			$result = array();
			if ( is_array( $questions ) ) {
				foreach ( $questions as $index => $id ) {
					$question = LP_Question::get_question( $id );


					$answers = array();
					foreach ( $question->get_answer_options() as $key => $answer ) {
						$answers[ $key ] = array_values( $answer );
					}

					$post     = get_post( $id );
					$result[] = array(
						'id'       => $id,
						'open'     => false,
						'title'    => get_the_title( $id ),
						'type'     => array(
							'key'   => $question->get_type(),
							'label' => $question->get_type_label()
						),
						'answers'  => $question->get_answer_options(),
						'settings' => array(
							'content'     => $post->post_content,
							'mark'        => get_post_meta( $id, '_lp_mark', true ),
							'explanation' => get_post_meta( $id, '_lp_explanation', true ),
							'hint'        => get_post_meta( $id, '_lp_hint', true )
						),
						'order'    => $order[ $index ]
					);
				}
			}

			return apply_filters( 'learn-press/quiz/quiz_editor_questions', $result, $this->get_id() );
		}

		/**
		 * Get quiz duration html.
		 *
		 * @return mixed
		 */
		public function get_duration_html() {
			$duration = $this->get_duration();
			if ( $duration ) {
				$duration = learn_press_seconds_to_time( $duration->get_seconds() );
			} else {
				$duration = __( 'Unlimited', 'learnpress' );
			}

			return apply_filters( 'learn_press_quiz_duration_html', $duration, $this );
		}

		/**
		 * Get number quiz question.
		 *
		 * @return int
		 */
		public function get_total_questions() {
			$questions = $this->get_questions();
			$count     = 0;
			if ( $questions ) {
				$count = count( $questions );
			}

			return $count;
		}

		/**
		 * Get quiz's settings for json
		 *
		 * @param int $user_id
		 * @param int $course_id
		 * @param bool $force
		 *
		 * @return mixed|void
		 */
		public function get_settings( $user_id = 0, $course_id = 0, $force = false ) {
			if ( ! $course_id ) {
				$course_id = get_the_ID();
			}
			$user        = learn_press_get_current_user( $user_id );
			$course      = learn_press_get_course( $course_id );
			$quiz_params = LP_Cache::get_quiz_params( false, array() );
			$key         = sprintf( '%d-%d-%d', $user_id, $course_id, $this->get_id() );
			if ( ! array_key_exists( $key, $quiz_params ) || $force ) {

				if ( $results = $user->get_quiz_results( $this->get_id(), $course_id, $force ) ) {
					$questions = $results->questions;
				} else {
					$questions = learn_press_get_quiz_questions();
					$questions = array_keys( $questions );
				}

				$current_question_id = $user->get_current_quiz_question( $this->get_id(), $course->get_id() );
				$question            = LP_Question::get_question( $current_question_id );
				$duration            = $this->get_duration();
				$remaining           = $user->get_quiz_time_remaining( $this->get_id(), $course_id );
				if ( $remaining === false ) {
					$remaining = $this->get_duration();
				} elseif ( $remaining < 0 ) {
					$remaining = 0;
				}
				//$r_time              = ( $remaining > 0 ) && !in_array( $user->get_quiz_status( $this->get_id(), $course_id, $force ), array( '', 'completed' ) ) ? $remaining : $this->duration;

				$js = array(
					'id'              => $this->get_id(),
					'questions'       => array_values( $this->get_question_params( $questions, $current_question_id ) ),
					//$questions,
					'status'          => $user->get_quiz_status( $this->get_id(), $course_id, $force ),
					'permalink'       => get_the_permalink(),
					'ajaxurl'         => admin_url( 'admin-ajax.php' ),
					'question'        => $question ? array( 'check_answer' => $question->can_check_answer() ) : false,
					'totalTime'       => $this->get_duration(),
					'userTime'        => $duration - $remaining,
					'currentQuestion' => get_post_field( 'post_name', $current_question_id ),
					'usePermalink'    => get_option( 'permalink' ),
					'courseId'        => $course_id
				);
				if ( $js['status'] == 'completed' ) {
					$js['result'] = $user->get_quiz_results( $this->get_id(), $course_id, $force );
				}
				if ( $js['status'] == 'started' ) {
					if ( $history = $user->get_quiz_results( $this->get_id(), $course_id ) ) {
						$js['startTime']  = strtotime( $history->start, current_time( 'timestamp' ) );
						$js['serverTime'] = date( 'Z' ) / 3600;//date_timezone_get( date_default_timezone_get() );// get_option('gmt_offset');
					}
				}

				$quiz_params[ $key ] = $js;
				LP_Cache::set_quiz_params( $quiz_params );
			}

			return apply_filters( 'learn_press_single_quiz_params', $quiz_params[ $key ], $this );
		}

		public function get_question_params( $ids, $current = 0 ) {
			global $wpdb;
			if ( ! $ids ) {
				$ids = array( 0 );
			}

			$results = array();
			if ( $questions = $this->get_questions() ) {
				$user              = learn_press_get_current_user();
				$show_check_answer = $this->show_check_answer;
				$show_hint         = $this->show_hint;
				$checked_answers   = array();
				//$show_explanation  = $this->show_explanation;
				if ( $show_check_answer == 'yes' ) {
					if ( $history = $user->get_quiz_results( $this->get_id() ) ) {
						$checked_answers = ! empty( $history->checked ) ? (array) $history->checked : array();
					}
				}
				foreach ( $questions as $question_id => $question ) {
					$_question = (object) array(
						'id'    => absint( $question->ID ),
						'type'  => $question->type,
						'title' => get_the_title( $question->ID ),
						'name'  => get_post_field( 'post_name', $question->ID ),
						'url'   => trailingslashit( $this->get_question_link( $question->ID ) )
					);
					if ( $show_check_answer == 'yes' ) {
						//$_question->check_answer = learn_press_question_type_support( $question->type, 'check-answer' );
						$_question->hasCheckAnswer = learn_press_question_type_support( $question->type, 'check-answer' ) ? 'yes' : 'no';
						$_question->checked        = array();
					}
					if ( $show_hint == 'yes' && empty( $question->hasHint ) ) {
						$_question->hasHint = get_post_meta( $question->ID, '_lp_hint', true ) ? 'yes' : 'no';
					}
					/*if ( $show_explanation == 'yes' && empty( $question->hasExplanation ) ) {
						$_question->hasExplanation = get_post_meta( $question->ID, '_lp_explanation', true ) ? 'yes' : 'no';
					}*/
					/*if ( empty( $results[$row->id] ) ) {
						$results[$row->id] = (object) array(
							'id'   => absint( $row->id ),
							'type' => $row->type
						);
						if ( $show_check_answer == 'yes' ) {
							$results[$row->id]->check_answer = learn_press_question_type_support( $row->type, 'check-answer' );
							$results[$row->id]->checked      = array();
						}

						if ( $show_hint == 'yes' && empty( $results[$row->id]->hint ) ) {
							$results[$row->id]->hint = get_post_meta( $row->id, '_lp_hint', true ) ? true : false;
						}
						if ( $show_explanation == 'yes' && empty( $results[$row->id]->explanation ) ) {
							$results[$row->id]->explanation = get_post_meta( $row->id, '_lp_explanation', true ) ? true : false;
						}
					//}
					*/
					if ( $show_check_answer == 'yes' ) {
						if ( in_array( $question->ID, $checked_answers ) ) {
							if ( ! empty( $question->answers ) ) {
								foreach ( $question->answers as $answer ) {
									$checked = maybe_unserialize( $answer );
									unset( $checked['text'] );
									$_question->checked[ $answer['id'] ] = $checked;
								}
							}
						} else {
							$_question->checked = false;
						}
					}

					if ( $current == $question->ID ) {
						$_question->current = 'yes';
					}
					$results[ $question->ID ] = $_question;
				}
			}

			return apply_filters( 'learn_press_quiz_param_questions', $results, $this->get_id() );
		}

		/**
		 * Localize frontend script.
		 *
		 * @return mixed
		 */
		public function get_localize() {
			$localize = array(
				'confirm_finish_quiz' => array(
					'title'   => __( 'Finish quiz', 'learnpress' ),
					'message' => __( 'Are you sure you want to finish this quiz?', 'learnpress' )
				),
				'confirm_retake_quiz' => array(
					'title'   => __( 'Retake quiz', 'learnpress' ),
					'message' => __( 'Are you sure you want to retake this quiz?', 'learnpress' )
				),
				'quiz_time_is_over'   => array(
					'title'   => __( 'Time out!', 'learnpress' ),
					'message' => __( 'The time is over! Your quiz will automate come to finish', 'learnpress' )
				),
				'finished_quiz'       => __( 'Congrats! You have finished this quiz', 'learnpress' ),
				'retaken_quiz'        => __( 'Congrats! You have re-taken this quiz. Please wait a moment and the page will reload', 'learnpress' )
			);

			return apply_filters( 'learn_press_single_quiz_localize', $localize, $this );
		}

		/**
		 * __isset function.
		 *
		 * @param mixed $key
		 *
		 * @return bool
		 */
		public function __isset( $key ) {
			return metadata_exists( 'post', $this->get_id(), '_' . $key );
		}

		/**
		 * __get function.
		 *
		 * @param string $key
		 *
		 * @return mixed
		 */
		public function __get( $key ) {
			echo '@deprecated[' . $key . ']';
			learn_press_debug( debug_backtrace() );

			return false;
		}

		/**
		 * This function is no longer support. Check directly from course.
		 *
		 * @deprecated
		 *
		 * @param int $the_course
		 *
		 * @return bool
		 */
		public function is_require_enrollment( $the_course = 0 ) {
			if ( ! $the_course ) {
				$the_course = get_the_ID();
			}

			$return = false;
			if ( $course = learn_press_get_course( $the_course ) ) {
				$return = $course->is_require_enrollment();
			}

			return $return;
		}

		/**
		 * Get the course that contains this quiz
		 *
		 * @param string
		 *
		 * @return bool|null
		 */
		public function get_course( $args = null ) {
			if ( empty( $this->course ) ) {
				global $wpdb;
				$query = $wpdb->prepare( "
				SELECT c.*
				FROM {$wpdb->posts} c
				INNER JOIN {$wpdb->learnpress_sections} s on c.ID = s.section_course_id
				INNER JOIN {$wpdb->learnpress_section_items} si on si.section_id = s.section_id AND si.item_id = %d
				", $this->get_id() );
				if ( $course_id = $wpdb->get_var( $query ) ) {
					$this->course = LP_Course::get_course( $course_id );
				}
			}
			$return = $this->course;
			if ( $this->course && $args ) {
				$args = wp_parse_args( $args, array( 'field' => null ) );
				if ( $args['field'] ) {
					$return = $this->course->{$args['field']};
				}
			}

			return $return;
		}

		/**
		 * @param $feature
		 *
		 * @return mixed
		 * @throws Exception
		 */
		public function has( $feature ) {
			$args = func_get_args();
			unset( $args[0] );
			$method   = 'has_' . preg_replace( '!-!', '_', $feature );
			$callback = array( $this, $method );
			if ( is_callable( $callback ) ) {
				return call_user_func_array( $callback, $args );
			} else {
				throw new Exception( sprintf( __( 'The function %s doesn\'t exists', 'learnpress' ), $feature ) );
			}
		}

		/**
		 * This quiz has any question?
		 *
		 * @return bool
		 */
		public function has_questions() {
			return $this->count_questions() > 0;
		}

		/**
		 * Count number questions in quiz.
		 *
		 * @return int
		 */
		public function count_questions() {
			$size = 0;
			if ( ( $questions = $this->get_questions() ) ) {
				$size = sizeof( $questions );
			}

			return apply_filters( 'learn-press/quiz/count-questions', $size, $this->get_id() );
		}

		/**
		 * Return TRUE if quiz contain a question.
		 *
		 * @param int $question_id
		 *
		 * @return bool
		 */
		public function has_question( $question_id ) {
			$questions = $this->get_questions();

			return apply_filters( 'learn-press/quiz/has-question', is_array( $questions ) && ( false !== array_search( $question_id, $questions ) ), $question_id, $this->get_id() );
		}

		/**
		 * Get question permalink from it's ID.
		 * If permalink option is turn on, add name of question
		 * into quiz permalink. Otherwise, add it's ID into
		 * query var.
		 *
		 * @param int $question_id
		 *
		 * @return string
		 */
		public function get_question_link( $question_id = null ) {
			$course = LP_Global::course();

			$permalink = $course->get_item_link( $this->get_id() );
			if ( '' != get_option( 'permalink_structure' ) && get_post_status( $this->get_id() ) != 'draft' ) {
				$question_name = get_post_field( 'post_name', $question_id );
				$permalink     = $permalink . $question_name;
			} else {
				$permalink = add_query_arg( array( 'question', $question_id ), $permalink );
			}

			// @deprecated
			$permalink = apply_filters( 'learn_press_quiz_question_permalink', $permalink, $question_id, $this );

			return apply_filters( 'learn-press/quiz/question-permalink', $permalink, $question_id, $this->get_id() );
		}

		public function get_question_at( $at = 0 ) {
			if ( $questions = $this->get_questions() ) {
				$questions = array_values( $questions );

				return @$questions[ $at ];
			}

			return false;
		}

		/**
		 * Get prev question from a question.
		 *
		 * @param int $id
		 *
		 * @return bool
		 */
		public function get_prev_question( $id ) {
			$prev = false;
			if ( ( $questions = $this->get_questions() ) ) {
				$questions = array_values( $questions );
				if ( 0 < ( $at = array_search( $id, $questions ) ) ) {
					$prev = $questions[ $at - 1 ];
				}
			}

			return apply_filters( 'learn-press/quiz/prev-question-id', $prev, $this->get_id() );
		}

		/**
		 * Get next question from a question.
		 *
		 * @param int $id
		 *
		 * @return bool
		 */
		public function get_next_question( $id ) {
			$next = false;
			if ( ( $questions = $this->get_questions() ) ) {
				$questions = array_values( $questions );
				if ( sizeof( $questions ) - 1 > ( $at = array_search( $id, $questions ) ) ) {
					$next = $questions[ $at + 1 ];
				}
			}

			return apply_filters( 'learn-press/quiz/next-question-id', $next, $this->get_id() );
		}

		/**
		 * Get index number of a question.
		 *
		 * @param int $id
		 * @param int $start
		 *
		 * @return bool|mixed
		 */
		public function get_question_index( $id, $start = 0 ) {
			$index = false;
			if ( ( $questions = $this->get_questions() ) ) {
				$questions = array_values( $questions );
				$index     = array_search( $id, $questions );
			}

			return apply_filters( 'learn-press/quiz/question-index', intval( $start ) + $index, $this->get_id() );
		}

		/**
		 * @param $name
		 * @param $id
		 *
		 * @return bool|null
		 */
		public function get_question_param( $name, $id ) {
			if ( $this->get_questions() ) {
				if ( ! empty( $this->questions[ $id ] ) ) {
					return ! empty( $this->questions[ $id ]->params[ $name ] ) ? $this->questions[ $id ]->params[ $name ] : null;
				}
			}

			return false;
		}

		/**
		 * @param $question_id
		 * @param $user_id
		 *
		 * @return bool
		 */
		public function check_question( $question_id, $user_id ) {

			if ( ! $question = LP_Question::get_question( $question_id ) ) {
				return false;
			}

			$user = learn_press_get_user( $user_id );

			$history = $user->get_quiz_results( $this->get_id() );
			if ( ! $history ) {
				return false;
			}
			$checked = (array) learn_press_get_user_quiz_meta( $history->history_id, 'checked' );
			$checked = array_filter( $checked );
			if ( ! in_array( $question_id, $checked ) ) {
				$checked[] = $question_id;
			}

			learn_press_update_user_quiz_meta( $history->history_id, 'checked', $checked );
		}

		/**
		 * @param $question
		 * @param int $user_id
		 *
		 * @return false|int|string
		 */
		public function get_question_position( $question, $user_id = 0 ) {
			if ( ! $user_id ) {
				$user_id = learn_press_get_current_user_id();
			}
			$user = learn_press_get_user( $user_id );
			if ( $user && $results = $user->get_quiz_results( $this->get_id() ) ) {
				$questions = (array) $results->questions;
			} else {
				$questions = (array) $this->get_questions();
				$questions = array_keys( $questions );
			}
			$position = array_search( $question, $questions );

			return $position;
		}

		/**
		 * @param int $user_id
		 * @param int $course_id
		 *
		 * @return bool|LP_Question
		 */
		public function get_current_question( $user_id = 0, $course_id = 0 ) {
//			$user = learn_press_get_user( $user_id );
//
//			return LP_Question::get_question( $id );
		}

		/**
		 * @return LP_Question
		 */
		public function get_viewing_question() {
			global $lp_quiz_question;

			return $lp_quiz_question;
		}

		/**
		 * Implement ArrayAccess functions.
		 *
		 * @param mixed $offset
		 * @param mixed $value
		 */
		public function offsetSet( $offset, $value ) {
			// Do not allow to set value directly!
		}

		public function offsetUnset( $offset ) {
			// Do not allow to unset value directly!
		}

		public function offsetGet( $offset ) {
			return $this->offsetExists( $offset ) ? $this->_questions[ $offset ] : false;
		}

		public function offsetExists( $offset ) {
			return array_key_exists( $offset, $this->_questions );
		}

		/**
		 * @param bool $the_quiz
		 * @param array $args
		 *
		 * @return LP_Quiz|bool
		 */
		public static function get_quiz( $the_quiz = false, $args = array() ) {
			$the_quiz = self::get_quiz_object( $the_quiz );
			if ( ! $the_quiz ) {
				return false;
			}

			if ( ! empty( $args['force'] ) ) {
				$force = ! ! $args['force'];
				unset( $args['force'] );
			} else {
				$force = false;
			}
			$key_args = wp_parse_args( $args, array( 'id' => $the_quiz->ID, 'type' => $the_quiz->post_type ) );

			$key = LP_Helper::array_to_md5( $key_args );

			if ( $force ) {
				LP_Global::$quizzes[ $key ] = false;
			}

			if ( empty( LP_Global::$quizzes[ $key ] ) ) {
				$class_name = self::get_quiz_class( $the_quiz, $args );
				if ( is_string( $class_name ) && class_exists( $class_name ) ) {
					$lesson = new $class_name( $the_quiz->ID, $args );
				} elseif ( $class_name instanceof LP_Course_Item ) {
					$lesson = $class_name;
				} else {
					$lesson = new self( $the_quiz->ID, $args );
				}
				LP_Global::$quizzes[ $key ] = $lesson;
			}

			return LP_Global::$quizzes[ $key ];
		}

		/**
		 * @param  string $quiz_type
		 *
		 * @return string|false
		 */
		private static function get_class_name_from_quiz_type( $quiz_type ) {
			return LP_QUIZ_CPT === $quiz_type ? __CLASS__ : 'LP_Quiz_' . implode( '_', array_map( 'ucfirst', explode( '-', $quiz_type ) ) );
		}

		/**
		 * Get the lesson class name
		 *
		 * @param  WP_Post $the_quiz
		 * @param  array $args (default: array())
		 *
		 * @return string
		 */
		private static function get_quiz_class( $the_quiz, $args = array() ) {
			$lesson_id = absint( $the_quiz->ID );
			$type      = $the_quiz->post_type;

			$class_name = self::get_class_name_from_quiz_type( $type );

			// Filter class name so that the class can be overridden if extended.
			return apply_filters( 'learn-press/quiz/object-class', $class_name, $type, $lesson_id );
		}

		/**
		 * Get the lesson object
		 *
		 * @param  mixed $the_quiz
		 *
		 * @uses   WP_Post
		 * @return WP_Post|bool false on failure
		 */
		private static function get_quiz_object( $the_quiz ) {
			if ( false === $the_quiz ) {
				$the_quiz = get_post_type() === LP_LESSON_CPT ? $GLOBALS['post'] : false;
			} elseif ( is_numeric( $the_quiz ) ) {
				$the_quiz = get_post( $the_quiz );
			} elseif ( $the_quiz instanceof LP_Course_Item ) {
				$the_quiz = get_post( $the_quiz->get_id() );
			} elseif ( ! ( $the_quiz instanceof WP_Post ) ) {
				$the_quiz = false;
			}

			return apply_filters( 'learn-press/quiz/post-object', $the_quiz );
		}
	}

}