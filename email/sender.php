<?php
defined( '_JEXEC' ) or die;

/**
 * A helper class for sending email
 *
 * @package default
 * @author Ted Lowe
 */
class LibcsEmailSender
{
	private $from;
	private $to;
	private $bcc;
	private $cc;
	private $body;
	private $subject;
	private $BccSender;
	/**
	 * Expects the from email address and optionally the name.
	 *
	 * @param string $from_email_address
	 * @param string $from_email_name
	 * @author Ted Lowe
	 */
	function __construct($template_prefix, $component_name)
	{
		// todo: allow caller to pass in templates instead of relying component_parms
		$from_name = JComponentHelper::getParams($component_name)->get("{$template_prefix}_from_name","");
		$from_addr = JComponentHelper::getParams($component_name)->get("{$template_prefix}_from_addr","");
		if (empty($from_addr))
			throw new Exception("Missing $template_prefix From Address");
		if (empty($from_name))
			$this->from = "<$from_addr>";
		else
			$this->from = "\"$from_name\" <$from_addr>";
		
		$this->subject = JComponentHelper::getParams($component_name)->get("{$template_prefix}_subject","No Subject");
		$this->body = JComponentHelper::getParams($component_name)->get("{$template_prefix}_body","No Body");
		
/* todo: check email addresses are legitimate
		if (strpos($from_email_address,"@") === FALSE)
		{
			throw new Exception("From email address is not formatted properly");
		}
*/		
		// if the optional to_addr parameter exists and is non-blank, populate to address
		$to_addr = JComponentHelper::getParams($component_name)->get("{$template_prefix}_to_addr","");
		if (!empty($to_addr))
			$this->to = $to_addr;

		$this->BccSender = true;	// email is Bcc'ed to sender by default
	}
	private function fill_template($template,&$data)
	{
		foreach( $data as $k => $v )
			$template = str_replace("%$k%",$v,$template);
		return $template;
	}
	/*
	public function setSubject($template,$data)
	{
		$this->subject = $this->fill_template($template,$data);
	}
	public function setBody($template,$data)
	{
		$this->body = $this->fill_template($template,$data);
	}
	*/
	public function addTo($addr,$name=null)
	{
		if ($this->to === null)
			$this->to = "";
		else
			$this->to .= ",";
		if ($name)
			$this->to .= "$name <$addr>";
		else
			$this->to .= "$addr";
	}
	
	/**
	 * Bcc the email sender too
	 *
	 * @author Ted Lowe
	 */
	public function NoBccSender()
	{
		$this->BccSender = false;
	}
	
	/**
	 * send the email
	 *
	 * @return boolean (TRUE or FALSE)
	 * @author Ted Lowe
	 */
	public function send($data, $html=false)
	{
		if ( empty($this->to) || empty($this->subject) || empty($this->body) )
			return false; 	// todo: throw exception?
		
		$this->subject = $this->fill_template($this->subject,$data);
		$this->body = $this->fill_template($this->body,$data);

		$addhdrs = "From: " . $this->from  . "\r\n";
		
		if ( $this->BccSender )
			$addhdrs .= "Bcc: " . $this->from  . "\r\n";
		
		if ( $html )
		{	
			$addhdrs .= "Content-Type: text/html\r\n";
			$addhdrs .= "MIME-Version: 1.0\r\n";
		}
		
		return mail( $this->to, $this->subject, $this->body, $addhdrs );
	}
	private function eol()
	{
		echo "<br />";
	}
	public function dump()
	{
		echo "From: " . htmlentities($this->from);
		$this->eol();
		echo "To: " . htmlentities($this->to);
		$this->eol();
		echo "Subject: " . $this->subject;
		$this->eol();
		echo "Body: " . $this->body;
		$this->eol();
	}
	/**
	 * PHP magic method: if this object is converted to a string,
	 * @return string
	 * @author Ted Lowe
	 */
	public function __tostring()
	{
		ob_start();
		$this->dump();
		return ob_get_clean();
		//return json_encode($this);
	}
}
