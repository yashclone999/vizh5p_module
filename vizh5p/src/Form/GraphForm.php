<?php
/**
 * @file
 * Contains \Drupal\vizh5p\Form\GraphForm.
 */
namespace Drupal\vizh5p\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Url;
use Drupal\Component\Utility\SafeMarkup;	  
class GraphForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'vizh5p_form';
  }
  /**
   * Function to build form and display the user the related table and graph  
   */  
  public function buildForm(array $form, FormStateInterface $form_state) {
   	//Attaching js to the form
    $form['#attached']['library'][] = 'vizh5p/plotlylib';
    $form['#attached']['library'][] = 'vizh5p/Graph';
    
    //Select the database Service and get the table
    $db_logic = \Drupal::service('vizh5p.db_logic');
    $data = $db_logic->get();
    $datauserarray = array();
    $datacidarray = array();
    $dataattemptarray = array();
    //Assign unique content ids
    foreach ($data as $d) {
        if(!in_array($d->content_id , $datacidarray))
          array_push($datacidarray , $d->content_id);  
    }
    sort($datacidarray);        //Sorting in ascending order
   
    //Creating form
    $form['content_id'] = array (     
      '#title' => t('Content-ID'),
      '#type' => 'select',
      '#description' => 'Select the content ID.',
      '#options' => $datacidarray,
      '#required' => TRUE,
      '#ajax' => array(                 //Ajax to dynamically load respective users using this content id
        'event' => 'change',
        'callback' => '::changeOptionsForUser',
        'wrapper' => 'user',
        'progress' => array(
          'type' => 'throbber',
          'message' => 'Finding Corresponding Users',
        ),  
      )
    );
    
    //If the content id is selected then find the coresponding users
    if($form_state->hasValue('content_id')){      
      $contentid = $datacidarray[$form_state->getValue('content_id')];
      $datauserarray = array();
      foreach ($data as $d) 
        if($contentid == $d->content_id)
          if(!in_array($d->actor , $datauserarray))
           array_push($datauserarray , $d->actor);
      sort($datauserarray);
    }
    $form['username'] = array (
      '#prefix' => '<div id="user">', 
      '#suffix' => '</div>',
      '#title' => t('Username'),
      '#type' => 'select',
      '#description' => 'Select the username.',
      '#options' => $datauserarray,
      '#required' => TRUE,
      '#ajax' => array(                //Ajax call to change the options of number of attempts
        'event' => 'change',
        'callback' => '::changeOptionsForAttempt',
        'wrapper' => 'attempt',
        'progress' => array(
          'type' => 'throbber',
          'message' => 'Checking number of attempts',
        ),  
      )
    );
    //If content id and username are selected then update the table
    if($form_state->hasValue('content_id') && $form_state->hasValue('username')) {
      $contentid = $datacidarray[$form_state->getValue('content_id')];
      $userid = $datauserarray[$form_state->getValue('username')];
      $i = 0;
      foreach ($data as $item) {
        if($contentid == $item->content_id && $userid == $item->actor && $item->verb == 'attempted') {
          $i++;
          array_push($dataattemptarray , $i);
        }
      }
    }    
    else
      $dataattemptarray = array();
    $form['attempt'] = array (
      '#prefix' => '<div id="attempt">', 
      '#suffix' => '</div>',
      '#title' => t('Attempt Number'),
      '#type' => 'select',
      '#description' => 'Select the Attempt number.',
      '#options' => $dataattemptarray,
      '#required' => TRUE,
      '#ajax' => array(                //Ajax call to change the table according to the content_id and username
        'event' => 'change',
        'callback' => '::changeTable',
        'wrapper' => 'table',
        'progress' => array(
          'type' => 'throbber',
          'message' => 'Fetching Table',
        ),  
      )
    );
    $form['table'] = array(
      '#prefix' => '<div id="table">', 
      '#suffix' => '</div>',
      '#type' => 'table',
      '#caption' => $this->t('Table Data'),
      '#header' => array($this->t('Time'), $this->t('Content ID') ,$this->t('Actor'), $this->t('Verb')),
    );
    
    //If content id , username and attempt number are selected then update the table accordingly
    if($form_state->hasValue('content_id') && $form_state->hasValue('username') && $form_state->hasValue('attempt')){
      $contentid = $datacidarray[$form_state->getValue('content_id')];
      $userid = $datauserarray[$form_state->getValue('username')];
      $attempt = $dataattemptarray[$form_state->getValue('attempt')];
      $i = 0;
      foreach ($data as $item) {
        if($contentid == $item->content_id && $userid == $item->actor && $item->verb == 'attempted')
          $i++;
        if($contentid == $item->content_id && $userid == $item->actor && $i == $attempt) {
          $form['table'][] = array(
            'time' => array(
            '#type' => 'markup',
            '#markup' => $item->time,
            ),
 
            'content_id' => array(
            '#type' => 'markup',
            '#markup' => $item->content_id,
            ),
                  
            'actor' => array(
            '#type' => 'markup',
            '#markup' => $item->actor,
            ),
     
            'verb' => array(
            '#type' => 'markup',
            '#markup' => $item->verb,
            ));
        }
      }
    }    
    else
      $form['table'][] = array();
  	//Container to display the graph
    $form['graph'] = array(
    	'#markup' => '<div id="graph"></div?>',
    );
    return $form;
  }
  /**
   * Ajax callback for content id to change username
   */
  public function changeOptionsForUser(array $form, FormStateInterface $form_state){
    return $form['username'];
  }
  /**
   * Ajax callback for username to change the attempts field
   */
  public function changeOptionsForAttempt(array $form, FormStateInterface $form_state){
    return $form['attempt'];
  }
  
  /**
   * Ajax callback for username to update the table
   */
  public function changeTable(array $form, FormStateInterface $form_state){
    return $form['table'];
  }
  
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }
    
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }
}