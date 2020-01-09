<?php
defined( '_JEXEC' ) or die;

/**
 * A helper class for using SMF forums
 *
 * @package default
 * @author Ted Lowe
 */
class LibcsForumsSmf
{
	private $userdata;
	private $last_error;
	private $component_name;
	private $db = null;
	private $caller;

	public static function getDBConnectionInfo()
	{
		// find and return forums connection information
		 
		// todo: assumes forums are in subdir off HTML_ROOT
		@include_once 'forums/Settings.php';
		
/* from Settings.php:
 
########## Database Info ##########
$db_server = 'localhost';
$db_name = 'dbname';
$db_user = 'dbuser';
$db_passwd = 'dbpass';
$db_prefix = 'smf_';
$db_persist = 0;		// not needed to connect to db
$db_error_send = 1;		// not needed to connect to db

returning connection info:

$options['driver']   = 'mysql';      // todo assumed db driver name
$options['host']     = 'localhost';  // db host name
$options['user']     = 'dbuser';     // User for db authentication
$options['password'] = 'dbpass';  // Password for db authentication
$options['database'] = 'dbname';  // db name
$options['prefix']   = 'smf_';             // db prefix (may be empty)

*/
		$settings_vars = array( "db_server" => "host",
				"db_name" => "database",
				"db_user" => "user",
				"db_passwd" => "password",
				"db_prefix" => "prefix" );

		$options = array(); 	// db connection data
		
		// parse and convert settings into connection array
		
		foreach( $settings_vars as $k => $v )
			if ( isset( ${$k} ) && ! empty( ${$k} ) )
				$options[$v] = ${$k};
			
		if ( count( $options ) != count( $settings_vars ) )
			return null; // todo: or array() or throw exception?
		
		$options['driver']   = 'mysql';        // todo assumed db driver name
		
		return $options;
	}
	function __construct($caller,$data,$component_name)
	{
		$this->caller = $caller;
		$this->userdata = $data;
		$this->component_name = $component_name;
		
	}
	private function getDB()
	{
		if ( $this->db === null )
		{
			// instantiate the DB connection the first time called

			$this->db = JDatabaseDriver::getInstance( self::getDBConnectionInfo() );
		}
		
		return $this->db;
	}
	public function add($verbose=false)
	{
		// todo: check for proper username and email
		// todo: tmp xxxx check if userdata already contains non-zero forumsid

		$username = $this->getUsername();
		
		if ( $this->isUser() )
		{
			if ( $verbose )
				JFactory::getApplication()->enqueueMessage($this->caller . ": username \"$username\" already exists.", 'error');
			else 
				; // todo: addNote(
			return false;
		}
		
		$db = $this->getDB();

		$obj = new stdClass();
		$obj->ID_MEMBER = 0;
		$obj->memberName = $username;
		$obj->realName = $username;
		$obj->passwd = md5($username);	// todo: how does this work?
		$obj->emailAddress = $this->userdata["email"];
		$obj->dateRegistered = time();
		
		$result = $db->insertObject( "smf_members", $obj, 'ID_MEMBER' );

		if ($verbose)
 			JFactory::getApplication()->enqueueMessage($this->caller . ": username \"$username\" added with ID #" . $obj->ID_MEMBER, 'success');

 		return $obj->ID_MEMBER;
	}
	private function isUser()
	{
		// todo: tmp xxxx check if userdata already contains non-zero forumsid
		
		$db = $this->getDB();

		$sql = "SELECT * FROM smf_members WHERE memberName='" . addslashes($this->getUsername()) ."'";
		$db->setQuery($sql);
		$user = $db->loadAssoc();
		
		return is_array($user);
	}
	private function getUsername()
	{
		// todo: should check for non-empty fname and lname

		return strtolower( $this->userdata["fname"] . "." . $this->userdata["lname"]);
	}
	private function getForumsId()
	{
		// todo: should check for zero

		return $this->userdata["forumsid"];
	}
	public function check()
	{
		$db = $this->getDB();

		$found = $this->isUser();
		
		$note = "Forums user \"" . $this->getUsername() . "\" ";
		if ($found)
			$note .= "found!";
		else
			$note .= "NOT found!";
	
		JFactory::getApplication()->enqueueMessage($note, $found ? 'success':'error');
	}
	public function get_last_error()
	{
		return $this->last_error;
	}
	public function checkban()
	{
		// todo: tmp xxx
	}
	public function ban($verbose=true)
	{
		// todo: should have previously check if there are ban records in place?
		
		// records in two tables (smf_ban_groups & smf_ban_items) are necessary to enforce an SMF ban
		
		// first insert smf_ban_groups record
		
		$db = $this->getDB();
		
		$username = $this->getUsername();
		
		$obj = new stdClass();
		$obj->ID_BAN_GROUP = 0;
		$obj->name = $username;
		$obj->ban_time = time();
		$obj->cannot_access = 1;
		$obj->reason = $obj->notes = "Membership Not Renewed";
		
		$result = $db->insertObject( "smf_ban_groups", $obj, 'ID_BAN_GROUP' );	// todo: check result
			
		$group_id = $obj->ID_BAN_GROUP;
		$forumsid = $this->getForumsId();
		// second insert smf_ban_items record
		
		$obj = new stdClass();
		$obj->ID_BAN = 0;
		$obj->ID_BAN_GROUP = $group_id;
		$obj->ID_MEMBER = $forumsid;

		$result = $db->insertObject( "smf_ban_items", $obj, 'ID_BAN' );	// todo: check result

		if ($verbose)
		 	JFactory::getApplication()->enqueueMessage($this->caller . ": Banned forums username \"$username\" with ID # $forumsid", 'success');
		
		return true;
	}
	public function unban()
	{
		// todo: tmp xxx
	}
}