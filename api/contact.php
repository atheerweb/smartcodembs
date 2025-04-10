<?php
$receiving_email_address = 'info@smartcodembs.com';

if (file_exists($php_email_form =  __DIR__ . '/php-email-form/php-email-form.php')) {
  include($php_email_form);
} else {
  die(__DIR__ . './php-email-form/php-email-form.php');
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

