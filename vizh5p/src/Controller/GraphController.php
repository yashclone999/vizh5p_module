<?php
/**
 * @file
 * Contains \Drupal\vizh5p\Controller\GraphController.
 */
namespace Drupal\vizh5p\Controller;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
class GraphController extends ControllerBase {
  /**
   * Callback for `vizh5p/getjson` API method.
   */
  public function getjson( Request $request ) {
    // This condition checks the `Content-type` and makes sure to 
    // decode JSON string from the request body into array.
    if ( 0 === strpos( $request->headers->get( 'Content-Type' ), 'application/json' ) ) {
      $params = json_decode( $request->getContent(), TRUE );
      $request->request->replace( is_array( $params ) ? $params : [] );
    }
    //Using our service to get all the data from the table vizh5p
    $db_logic = \Drupal::service('vizh5p.db_logic');
    $data = $db_logic->get();
    $datauserarray = array();
    $datacidarray = array();
    $dataattemptarray = array();
    //selecting distinct content id
    foreach ($data as $d) {
        if(!in_array($d->content_id , $datacidarray))
            array_push($datacidarray , $d->content_id);
    }
    sort($datacidarray);
    $cid = $datacidarray[$params['content_id']];            //Finding the content id
    foreach ($data as $d) {                                 //Selecting username based on content id
        if($d->content_id == $cid)
        if(!in_array($d->actor , $datauserarray))
            array_push($datauserarray , $d->actor);
    }
    sort($datauserarray);
    $actor = $datauserarray[$params['username']];           //Finding the username
	$i = 0;
    foreach ($data as $d) {
      if($cid == $d->content_id && $actor == $d->actor && $d->verb == 'attempted') {
        $i++;
        array_push($dataattemptarray , $i);
      }
    }
    $attempt = $dataattemptarray[$params['attempt']];
    $response = array();
    
    $i = 0;
    foreach ($data as $d) {                                 //Checking attempt number
      if($d->content_id == $cid && $d->actor == $actor && $d->verb == 'attempted') 
         $i++;
      if($d->content_id == $cid && $d->actor == $actor && $i == $attempt)
        array_push($response , $d);
     }
    return new JsonResponse( $response );
  }
}