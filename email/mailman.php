<?php
defined( '_JEXEC' ) or die;

/**
 * A helper class for using a Mailman listserv
 *
 * @package default
 * @author Ted Lowe
 */
class LibcsEmailMailman
{
	private $listname;
	private $host;
	private $domain;
	private $pw;
	private $last_error;
	
	function __construct($listname,$host,$domain,$component_name)
	{
		$this->listname = $listname;
		$this->host = $host;
		$this->domain = $domain;
		$this->pw = JComponentHelper::getParams($component_name)->get("mailman_password","");
	}
	private function set_values($url)
	{
		$values = array("listname","host","domain","pw");
		foreach( $values as $value )
			$url = str_replace("%$value%", $this->$value, $url);
		return $url;
	}
	public function sub($email,$fname=null,$lname=null)
	{
		if ( (!empty($fname)) && (!empty($lname)))
			$email = "$email%20($fname%20$lname)";

		$url = "https://%host%/mailman/admin/%listname%_%domain%/%listname%/add?subscribe_or_invite=0&send_welcome_msg_to_this_batch=1&notification_to_list_owner=1&subscribees_upload=$email&adminpw=%pw%";

		$body = $this->do_curl_return_result($url);
		
		/*
		 possible responses:
		
		 Authorization failed.
		 (wrong or no password specified in component parameters)
		
		 Successfully subscribed:
		 Fname Lname <fnamelname@example.com>
		 Members mailing list administration
		
		 or
		
		 Error subscribing:
		 fnamelname@example.com (Fname Lname) -- Already a member
		 Members mailing list administration
		 */
		
		return strpos($body, "Successfully subscribed:") !== FALSE;
	}
	public function unsub($email)
	{
		$url = "https://%host%/mailman/admin/%listname%_%domain%/%listname%/remove?send_unsub_ack_to_this_batch=1&send_unsub_notifications_to_list_owner=1&unsubscribees=$email&adminpw=%pw%";
		
		$body = $this->do_curl_return_result($url);
		
		/*
		 possible responses:
		
		 Authorization failed.
		 Members Administrator Authentication
		 (no or wrong password specfied in component plugin parameters)
		
		 or
		
		 Successfully Unsubscribed:
		 fnamelname@example.com
		 Members mailing list administration
		
		 or
		
		 Cannot unsubscribe non-members:
		 fnamelname@example.com
		 Members mailing list administration
		
		 */
		
		return strpos($body, "Successfully Unsubscribed") !== FALSE;
	}
	public function who($email=null)
	{
		// if email is specified, return true or false (already on list)
		// else return whole list of email addresses
		// todo: implement email param

		$url = "https://%host%/mailman/roster/%listname%_%domain%/?roster-pw=%pw%";

		return $this->do_curl_return_result($url);
	}
	private function do_curl_return_result($url)
	{
		$url = $this->set_values($url);

		// todo: check returns and set last_error and return false if necessary
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$body = curl_exec($ch);
		curl_close($ch);
		
		return $body;
	}
	public function get_last_error()
	{
		return $this->last_error;
	}
}