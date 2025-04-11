<?php
$receiving_email_address = 'info@smartcodembs.com';


// Load Composer's autoloader
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;


require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';


class PHP_Email_Form {
    public $to;
    public $from_name;
    public $from_email;
    public $subject;
    public $message = '';
    public $ajax = false;
    public $smtp = array();
    public $errors = array();

    /**
     * Add a message to the email body
     *
     * @param string $content
     * @param string $label
     * @param int $max_length
     * @return void
     */
    public function add_message($content, $label = '', $max_length = 0) {
        $content = $this->sanitize_input($content);
        $label = $this->sanitize_input($label);

        if ($max_length > 0 && strlen($content) > $max_length) {
            $content = substr($content, 0, $max_length);
        }

        if (!empty($label)) {
            $this->message .= "<p><strong>{$label}:</strong><br>{$content}</p>";
        } else {
            $this->message .= "<p>{$content}</p>";
        }
    }

    /**
     * Send the email
     *
     * @return string JSON response if AJAX, otherwise boolean
     */
    public function send() {
        try {
            // Validate required fields
            if (empty($this->to)) {
                throw new Exception('Recipient email address is required');
            }

            if (empty($this->from_email) || !filter_var($this->from_email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Valid sender email address is required');
            }

            if (empty($this->subject)) {
                $this->subject = 'New message from your website';
            }

            // Prepare headers
            $headers = "From: {$this->from_name} <{$this->from_email}>\r\n";
            $headers .= "Reply-To: {$this->from_email}\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

            // Send via SMTP or mail()
            if (!empty($this->smtp)) {
                $sent = $this->send_via_smtp();
            } else {
                $sent = mail($this->to, $this->subject, $this->message, $headers);
            }

            if (!$sent) {
                throw new Exception('Failed to send email');
            }

               // Always return JSON structure
        $response = [
            'success' => true,
            'message' => 'Email sent successfully',
            'data' => [
                'to' => $this->to,
                'subject' => $this->subject
            ]
        ];
        
        return json_encode($response);
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            return $this->ajax ? json_encode(array('success' => false, 'error' => $e->getMessage())) : false;
        }
    }

    /**
     * Send email via SMTP
     *
     * @return bool
     */
    private function send_via_smtp() {
        $mail = new PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->SMTPOptions = array(
                'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
                )
                );
            $mail->Host = $this->smtp['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $this->smtp['username'];
            $mail->Password = $this->smtp['password'];
            $mail->Port = $this->smtp['port'] ?? 587;
            $mail->SMTPSecure = $this->smtp['encryption'] ?? 'tls';

            // Recipients
            $mail->setFrom($this->from_email, $this->from_name);
            $mail->addAddress($this->to);
            $mail->addReplyTo($this->from_email, $this->from_name);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $this->subject;
            $mail->Body = $this->message;
            $mail->AltBody = strip_tags($this->message);

            $mail->send();
            return true;
        } catch (Exception $e) {
            throw new Exception("SMTP Error: {$mail->ErrorInfo}");
        }
    }

    /**
     * Sanitize input data
     *
     * @param string $data
     * @return string
     */
    private function sanitize_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        return $data;
    }
}

$contact = new PHP_Email_Form;
$contact->ajax = true;

$contact->to = $receiving_email_address;
$contact->from_name = $_POST['name'];
$contact->from_email = $_POST['email'];
$contact->subject = $_POST['subject'];

// Optional: add phone number
$contact->add_message($_POST['name'], 'From');
$contact->add_message($_POST['email'], 'Email');
$contact->add_message($_POST['phonenumber'], 'Phone');
$contact->add_message($_POST['message'], 'Message', 10);

// SMTP Setup (optional - comment in and configure if needed)

$contact->smtp = array(
  'host' => 'mail.smartcodembs.com',
  'username' => 'info@smartcodembs.com',
  'password' => 'AllenTx202@',
  'port' => 1025
);


echo $contact->send();
?>

