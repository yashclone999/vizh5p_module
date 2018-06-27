<?php	  
namespace Drupal\vizh5p;
use Drupal\Core\Database\Connection;
/**
 * Defines a storage handler class that handles the node grants system.
 * This is used to build node query access.	
 * @ingroup mypage
 */
class savetoDB {
	/**
	 * The database connection.
	 * @var \Drupal\Core\Database\Connection
	 */
	protected $database;  
	/**
	 * Constructs a MyPageDbLogic object.	  
	 * @param \Drupal\Core\Database\Connection $database
	 *   The database connection.
	 */
	// The $database variable came to us from the service argument.
	public function __construct(Connection $database) {
		$this->database = $database;
	}	  
	/**
	 * Add new record in table VizH5P.
	 */
	public function add($content_id , $actor , $verb) {
		if (empty($content_id) || empty($actor) || empty($verb)){
			return FALSE;
		}
		//Getting time in microseconds in year month day hour minute second and microsecond format
		$micro_date = \Drupal::time()->getRequestMicroTime();
		$date_array = explode(".",$micro_date);
		$date = date("Y-m-d H:i:s") . "." . $date_array[1];
		//Select database using drupal inbuit APIs
		$query = $this->database->insert('vizh5p');         
		//Add fields
		$query->fields(array(
			'time' => $date,
			'content_id' => $content_id,
			'actor' => $actor,
			'verb' => $verb
		));
		//Execute query
		return $query->execute();
	}
	
	/**
	 * Get the compelete table from VizH5P in the form of array.
	 */
	public function get() {
		//Select database using drupal inbuit APIs
		$query = db_select('vizh5p' , 'v');
		$data = $query
				->fields('v' , array('time','content_id','actor','verb'))
				->orderBy('time' , 'ASC')
				->execute()->fetchAll();
		return $data;
	}
	
	/**
	 * Selecting the rows of the table which match with our content id and username
	 */
	public function fetch($cid , $actor) {
		$query = db_select('vizh5p' , 'v');
		$data = $query
				->fields('v' , array('time','content_id','actor','verb'))
				->condition('content_id' , $cid)
				->condition('actor', $actor)
				->orderBy('time' , 'ASC')
				->execute()->fetchAll();
		return $data;
	} 
}