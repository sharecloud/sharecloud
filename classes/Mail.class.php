<?php
final class Mail {
	/**
	 * Subject
	 * @var string
	 */
	private $subject = '';
	
	/**
	 * Content (HTML or plain text)
	 * @var string
	 */
	private $content = '';
	
	/**
	 * Recipient
	 * @var object
	 */
	private $recipient = NULL;
	
	/**
	 * Constructor
	 * @param string Subject
	 * @param string Content
	 * @param object Recipient
	 */
	public function __construct($subject, $content, User $recipient) {
		$this->subject = $subject;
		$this->content = $content;
		
		$this->recipient = $recipient;
	}
	
	/**
	 * Sends the mail
	 * @throws MailFailureException
	 */
	public function send() {
		// Create headers
		$headers = array();
		$headers[] = 'From: One-Click Filehost <noreply@' . HOST_NAME . '>';
		$headers[] = 'MIME-Version: 1.0';
		$headers[] = 'Content-Type: text/html; Charset=utf-8';
		
		$recipient = sprintf('%s <%s>', $this->recipient->getFullname(), $this->recipient->email);
		
		if(mail($recipient, $this->subject, $this->content, implode("\n", $headers)) !== true) {
			throw new MailFailureException();	
		}
	}
}
?>