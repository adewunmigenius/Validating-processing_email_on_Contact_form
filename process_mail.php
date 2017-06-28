<?php
$mailSent = false;
$suspect = false;
$pattern = '/Content-Type:|Bcc:|Cc:/i';

function isSuspect($value, $pattern, &$suspect){
	if(is_array($value)) {
		foreach($value as $item){
			isSuspect($item, $value, $suspect);
		}
	}else {
		if (preg_match($pattern, $value)){
				$suspect = true;
		}
	}
}

isSuspect($_POST, $pattern, $suspect);

if (!$suspect):
	foreach($_POST as $key => $value){
		$value = is_array($value) ? $value : trim($value);
		if (empty($value) && in_array($key, $required)){
			$missing[] = $key;
			$$key = '';	
		}elseif (in_array($key, $expected)){
			$$key = $value;
		}
	}
	if (!$missing && !empty($email)):
		$validemail = filter_input(INPUT_POST,'email', FILTER_VALIDATE_EMAIL);
		if($validemail){
			$header[] = "Reply-to: $validemail";
		} else{
			$error['email'] = true;
		}
	endif;
	// if no errors, create header and message body
	if(!$missing && !$error):
		$header[] = implode("\r\n", $header);
		// Initialize message
		$message = '';
		foreach ($expected as $field):
			if (isset($$field) && !empty($$field)){
			$val = $$field;
			}else{
				$val = 'Not selected';
			}
			// if an array, expand to a comma-separated string
			if(is_array($val)){
				$val = implode(', ', $val);
			}
			// Replace underscore in the field names with spaces
			$field = str_replace('_', ' ', $field);
			$message .= ucfirst ($field) .":$val\r\n\r\n";
		endforeach;
		$message = wordwrap($message, 70);
		$mailSent = mail($to, $subject, $message, $header, $authorized);
	endif;
endif;
?>