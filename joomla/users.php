<?php
defined( '_JEXEC' ) or die;

/**
 * A helper class for joomla users
 *
 * @package default
 * @author Ted Lowe
 */
class LibcsJoomlaUsers
{
	private $userdata;
	private $last_error;

	function __construct($data)
	{
		$this->userdata = $data;
	}
	/*
	 * Found Joomla User
{"id":"63","name":"Tad Rowe","username":"tad.rowe","email":"tad.rowe@example.com","password":"xxxxxxxxxxxxxxxxxxxxxx:yyyyyyyyyyyyy","usertype":"Super Administrator","block":"0","sendEmail":"0","gid":"25","registerDate":"2006-12-15 01:23:45","lastvisitDate":"2018-12-06 15:08:00","activation":"","params":"editor=tinymce\nexpired=\nexpired_time="}
	 */
	public function getUserByUsername($username = null)
	{
		if ( $username === null )
		{
			$username = $this->getUsername();
//echo "username=$username<br />";
//jexit("bla");
			if ( $username === null )
				return null;
		}
		
		$sql = "SELECT * FROM #__users WHERE username='". addslashes($username)."'";
//echo "sql=$sql<br />";
//jexit("bla");
		$db = JFactory::getDBO();
		$db->setQuery($sql);
		$row = $db->loadAssoc();
		if (is_array($row))
			return $row;
		return null;
	}
	public function getUsername()
	{
		if (empty($this->userdata["fname"]) || empty($this->userdata["lname"]))
			return null;

		return strtolower( $this->userdata["fname"] . "." . $this->userdata["lname"]);
	}
	public function getId()
	{
		return $this->userdata["cmsid"];
	}
	public function hasId()
	{
		return $this->getId() != "0";
	}
	public function saveUserById($id,$arr)
	{
		$obj = new stdClass();
		$obj->id = $id;
		foreach( $arr as $k => $v)
			$obj->$k = $v;
		$result = JFactory::getDbo()->updateObject( "#__users",  $obj, 'id');
	}
	public function getUserById()
	{
		if ( ! $this->hasId() )
			return null;
		
		$id = $this->getId();
		
		$sql = "SELECT * FROM #__users WHERE id=$id";
		$db = JFactory::getDBO();
		$db->setQuery($sql);
		$row = $db->loadAssoc();
		if (is_array($row))
			return $row;
		return null;
	}
	public function getUsernameFromName($name)
	{
		return str_replace(" ", ".", strtolower($name));
	}
	public function addJoomlaUser($email, $name, $username = null)
	{
		// todo: check the username does not already exist - if it does, a exception is thrown below under save()
		// Could not save user. Error: Username in use.
		// todo: also check that cmsid is not set yet
		// todo: also can't duplicate email
		// Could not save user. Error: The email address you entered is already in use. Please enter another email address.
		// .../components/com_cs_crm/cs_crm.php:371
	
		// joomla enforces each user has a unique email address and unique username
		// this function uses the name in format "Fname Lname" to create the username "fname.lname"
		// this function also sets the initial password to "username" and requires an initial password reset upon first login

	
		if ($this->userdata["cmsid"] != 0)
		{
			$this->last_error = "$name already has CMSID set as " . $this->userdata["cmsid"];
			return false;	
		}
		
		// import Joomla! user library utilities
		
		jimport('joomla.user.helper');

		// if the username is specified, use it, else use the default naming convention
		
		if ( $username === null )
			$username = $this->getUsernameFromName($name);

		$password = $username;

		$data = array(
				"name"=>$name,
				"username"=>$username,
				"password"=>$password,
				"password2"=>$password,
				"email"=>$email,
				"block"=>0,
				"requireReset"=>1,	// new in joomla3 version of memdb - for security purposes, they must change their initial password upon first login
				//"groups"=>array("1","2")	// public=1, registered=2 - todo: only need registered
				"groups"=>array("2")	// registered=2
		);
	
		$user = new JUser;
		/*
		 new user:{"id":0,
		 "name":null,
		 "username":null,
		 "email":null,
		 "password":null,
		 "password_clear":"",
		 "block":null,
		 "sendEmail":0,
		 "registerDate":null,
		 "lastvisitDate":null,
		 "activation":null,
		 "params":null,
		 "groups":[],
		 "guest":1,
		 "lastResetTime":null,
		 "resetCount":null,
		 "requireReset":null,
		 "aid":0}
		 */
		//Write to database
		if(!$user->bind($data)) {
			$this->last_error = "Could not bind data. Error: " . $user->getError();
			return false;
		}
		
		if (!$user->save()) {
			$this->last_error = "Could not save user: username=$username, email=$email Error: " . $user->getError();
			return false;
		}
	
		$this->userdata["cmsid"] = $user->id;
	
		return true;
	}
	public function get_last_error()
	{
		return $this->last_error;
	}
	public function getUserId()
	{
		return $this->userdata["cmsid"];
	}
}