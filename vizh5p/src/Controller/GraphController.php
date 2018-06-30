<?php
/**
 * @file
 * Contains \Drupal\vizh5p\Controller\GraphController.
 */
namespace Drupal\vizh5p\Controller;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
function findtype( $res ){
  $case = "0";
  foreach($res as $r) {
    if($r->verb == 'attempted') {
      $case = "1";
    } elseif($r->verb == 'interacted'){
      $case = "2";
    } elseif($r->verb == 'answered' && $case == "1"){
      $case = "3";
    } else {
      $case = "4";
    }
  }
  return $case;
}
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
    $c = array();
    $cu = array();
    $cua = array();
    $temp = array();
    $length = count($data);
    for($i = 0 ; $i < $length ; $i++){
      if($data[$i]->content_id == $cid && $data[$i]->verb == "attempted"){
        $temp = array();
        $user = $data[$i]->actor;
        array_push($temp , $data[$i]);
        for($j = $i + 1 ; $j < $length ; $j++){
          if($data[$j]->content_id == $cid && $user == $data[$j]->actor && $data[$j]->verb == "attempted")
            break;
          if($data[$j]->content_id == $cid && $user == $data[$j]->actor)
            array_push($temp , $data[$j]);
        }
        $case = findtype($temp);
        if($case == "4") {
          $len = count($temp);
          $atime = $temp[0]->time;
          $i1time = $temp[1]->time;
          $i2time = $temp[$len - 2]->time;
          $antime = $temp[$len - 1]->time;
          $atime = explode("." , $atime);
          $i1time = explode("." , $i1time);
          $i2time = explode("." , $i2time);
          $antime = explode("." , $antime);
          $t1 = strtotime($atime[0]) . "." . $atime[1];
          $t2 = strtotime($i1time[0]) . "." . $i1time[1];
          $t3 = strtotime($i2time[0]) . "." . $i2time[1];
          $t4 = strtotime($antime[0]) . "." . $antime[1];
          array_push($c , array($t2 - $t1 , $t3 - $t2 , $len - 2, $t4 - $t3));
        }
      }
    }
    $length = count($c);
    $response[0][0] = 0;
    $response[0][1] = 0;
    $response[0][2] = 0;
    $response[0][3] = 0;
    for($i = 0 ; $i < $length ; $i++){
      $response[0][0] += $c[$i][0];
      $response[0][1] += $c[$i][1];
      $response[0][2] += $c[$i][2];
      $response[0][3] += $c[$i][3];
    }
    $response[0][0] /= $length;
    $response[0][1] /= $length;
    $response[0][2] /= $length;
    $response[0][3] /= $length;
    
    $length = count($data);
    for($i = 0 ; $i < $length ; $i++){
      if($data[$i]->content_id == $cid && $data[$i]->actor == $actor && $data[$i]->verb == "attempted"){
        $temp = array();
        array_push($temp , $data[$i]);
        for($j = $i + 1 ; $j < $length ; $j++){
          if($data[$j]->content_id == $cid && $actor == $data[$j]->actor && $data[$j]->verb == "attempted")
            break;
          if($data[$j]->content_id == $cid && $actor == $data[$j]->actor)
            array_push($temp , $data[$j]);
        }
        $case = findtype($temp);
        if($case == "4") {
          $len = count($temp);
          $atime = $temp[0]->time;
          $i1time = $temp[1]->time;
          $i2time = $temp[$len - 2]->time;
          $antime = $temp[$len - 1]->time;
          $atime = explode("." , $atime);
          $i1time = explode("." , $i1time);
          $i2time = explode("." , $i2time);
          $antime = explode("." , $antime);
          $t1 = strtotime($atime[0]) . "." . $atime[1];
          $t2 = strtotime($i1time[0]) . "." . $i1time[1];
          $t3 = strtotime($i2time[0]) . "." . $i2time[1];
          $t4 = strtotime($antime[0]) . "." . $antime[1];
          array_push($cu , array($t2 - $t1 , $t3 - $t2 , $len - 2 , $t4 - $t3));
        }
      }
    }
    $length = count($cu);
    $response[1][0] = 0;
    $response[1][1] = 0;
    $response[1][2] = 0;
    $response[1][3] = 0;
    for($i = 0 ; $i < $length ; $i++){
      $response[1][0] += $cu[$i][0];
      $response[1][1] += $cu[$i][1];
      $response[1][2] += $cu[$i][2];
      $response[1][3] += $cu[$i][3];
    }
    $response[1][0] /= $length;
    $response[1][1] /= $length;
    $response[1][2] /= $length;
    $response[1][3] /= $length;
    $i = 0;
    foreach ($data as $d) {                                 //Checking attempt number
      if($d->content_id == $cid && $d->actor == $actor && $d->verb == 'attempted') 
         $i++;
      if($d->content_id == $cid && $d->actor == $actor && $i == $attempt)
        array_push($cua , $d);
    }
    $case = findtype($cua);
    $response[2][0] = 0;
    $response[2][1] = 0;
    $response[2][2] = 0;
    $response[2][3] = 0;
    if($case == "2") {
      $atime = $temp[0]->time;
      $i1time = $temp[1]->time;
      $atime = explode("." , $atime);
      $i1time = explode("." , $i1time);
      $t1 = strtotime($atime[0]) . "." . $atime[1];
      $t2 = strtotime($i1time[0]) . "." . $i1time[1];
      $cua = array($t2 - $t1);
      $response[2][0] = $cua[0];
      $response[2][2] = 1;
    } elseif($case == "4") {
      $len = count($cua);
      $atime = $cua[0]->time;
      $i1time = $cua[1]->time;
      $i2time = $cua[$len - 2]->time;
      $antime = $cua[$len - 1]->time;
      $atime = explode("." , $atime);
      $i1time = explode("." , $i1time);
      $i2time = explode("." , $i2time);
      $antime = explode("." , $antime);
      $t1 = strtotime($atime[0]) . "." . $atime[1];
      $t2 = strtotime($i1time[0]) . "." . $i1time[1];
      $t3 = strtotime($i2time[0]) . "." . $i2time[1];
      $t4 = strtotime($antime[0]) . "." . $antime[1];
      $cua = array($t2 - $t1 , $t3 - $t2 , $len - 2 , $t4 - $t3);
      $response[2][0] = $cua[0];
      $response[2][1] = $cua[1];
      $response[2][2] = $cua[2];
      $response[2][3] = $cua[3];
    }
    return new JsonResponse( $response );
  }
}