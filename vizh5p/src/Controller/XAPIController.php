<?php
/**
 * @file
 * Contains \Drupal\vizh5p\Controller\XAPIController.
 */
namespace Drupal\vizh5p\Controller;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
class XAPIController extends ControllerBase {
  /**
   * Callback for `node/vizh5p/postjson` API method.
   */
  public function passtodb( Request $request ) {
    // This condition checks the `Content-type` and makes sure to 
    // decode JSON string from the request body into array.
    if ( 0 === strpos( $request->headers->get( 'Content-Type' ), 'application/json' ) ) {
      $data = json_decode( $request->getContent(), TRUE );
      $request->request->replace( is_array( $data ) ? $data : [] );
    }
    //Using our service to save the parameters in database
    $db_logic = \Drupal::service('vizh5p.db_logic');
    //Calling the add function to add data
    $db_logic->add($data['object']['definition']['extensions']['http://h5p.org/x-api/h5p-local-content-id'],$data['actor']['name'],$data['verb']['display']['en-US']);
    //Return message to the page
    $response['data'] = 'Fetch successful';
    $response['method'] = 'POST';
    return new JsonResponse( $response );
  }
}