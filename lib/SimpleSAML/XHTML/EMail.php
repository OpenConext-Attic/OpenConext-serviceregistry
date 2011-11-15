<?php

/**
 * A minimalistic Emailer class. Creates and sends HTML emails.
 *
 * @author Andreas kre Solberg, UNINETT AS. <andreas.solberg@uninett.no>
 * @package simpleSAMLphp
 * @version $Id$
 */
class SimpleSAML_XHTML_EMail {

    const BOUNDARY_OPEN = '--';
    const BOUNDARY_CLOSE = '--';

	private $to = NULL;
	private $cc = NULL;
	private $body = NULL;
	private $from = NULL;
	private $replyto = NULL;
	private $subject = NULL;
	private $headers = array();
    private $_altBoundary;
    private $_mixedBoundary;
    private $_attachmentBody;

	/**
	 * Constructor
	 */
	function __construct($to, $subject, $from = NULL, $cc = NULL, $replyto = NULL) {
		$this->to = $to;
		$this->cc = $cc;
		$this->from = $from;
		$this->replyto = $replyto;
		$this->subject = $subject;
        $this->_createBoundary();
	}

    /**
     * Creates boundary string
     *
     * @return void
     */
    protected function _createBoundary() {
        $random_hash = SimpleSAML_Utilities::stringToHex(SimpleSAML_Utilities::generateRandomBytes(16));
        $this->_altBoundary = 'alt-' . $random_hash;
        $this->_mixedBoundary = 'mixed-' . $random_hash;
    }

	function setBody($body) {
		$this->body = $body;
	}
	
	private function getHTML($body) {
		return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title>simpleSAMLphp Email report</title>
	<style type="text/css">
pre, div.box {
	margin: .4em 2em .4em 1em;
	padding: 4px;

}
pre {
	background: #eee;
	border: 1px solid #aaa;
}
	</style>
</head>
<body>
<div class="container" style="background: #fafafa; border: 1px solid #eee; margin: 2em; padding: .6em;">
' . $body . '
</div>
</body>
</html>';
	}

	function send() {
		if ($this->to == NULL) throw new Exception('EMail field [to] is required and not set.');
		if ($this->subject == NULL) throw new Exception('EMail field [subject] is required and not set.');
		if ($this->body == NULL) throw new Exception('EMail field [body] is required and not set.');
		

		if (isset($this->from))
			$this->headers[]= 'From: ' . $this->from;
		if (isset($this->replyto))
			$this->headers[]= 'Reply-To: ' . $this->replyto;

        $this->headers[] = 'Content-Type: multipart/mixed; boundary="' . $this->_mixedBoundary . '"';

		$message = '
' . self::BOUNDARY_OPEN . $this->_mixedBoundary . '
Content-Type: multipart/alternative; boundary="' . $this->_altBoundary . '"

' . self::BOUNDARY_OPEN . $this->_altBoundary . '
Content-Type: text/plain; charset="utf-8" 
Content-Transfer-Encoding: 8bit

' . strip_tags(html_entity_decode($this->body)) . '

' . self::BOUNDARY_OPEN . $this->_altBoundary . '
Content-Type: text/html; charset="utf-8" 
Content-Transfer-Encoding: 8bit

' . $this->getHTML($this->body) . '

' . self::BOUNDARY_OPEN . $this->_altBoundary . self::BOUNDARY_CLOSE . '
';

$message .= $this->_attachmentBody;

$message .= self::BOUNDARY_CLOSE . $this->_mixedBoundary .self::BOUNDARY_CLOSE;

		$headers = join("\r\n", $this->headers);

		$mail_sent = @mail($this->to, $this->subject, $message, $headers);
		SimpleSAML_Logger::debug('Email: Sending e-mail to [' . $this->to . '] : ' . ($mail_sent ? 'OK' : 'Failed'));
		if (!$mail_sent) throw new Exception('Error when sending e-mail');
	}

    /**
     * Add attachment from string
     *
     * @param   string $content
     * @param   string    $name
     * @return  void
     *
     * NOTE: This was added to solve Surfconext Backlog 287
     * This might not be the best or safest way to add attachments
     * It would be a very good idea to use something like Zend_Mail to replace this whole class
     */
    public function addAttachment($content, $name, $mimeType = 'application/octet-stream') {
        // encode attachment content
        $attachment = chunk_split(base64_encode($content));

        $attachmentString = '
--' . $this->_mixedBoundary . '
Content-Type: ' . $mimeType . '; name="' . $name . '"
Content-Transfer-Encoding: base64
Content-Disposition: attachment; filename="' . $name . '"

' . $attachment . '
';

        $this->_attachmentBody .= $attachmentString;
    }
}
?>