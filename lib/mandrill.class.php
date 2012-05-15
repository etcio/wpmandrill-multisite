<?php

class Mandrill_Exception extends Exception {
}

class Mandrill {
    const API_VERSION = '1.0';    
    const END_POINT = 'https://mandrillapp.com/api/';
    
    var $api;
    
    // PHP 4.0
    function Mandrill() {$this->__construct();}
    
    // PHP 5.0
    function __construct($api) {
        if ( empty($api) ) throw new Mandrill_Exception('Invalid API key');
        try {
        
            $response = $this->request('users/ping', array( 'key' => $api ) );        
            if ( $response != 'PONG!' ) throw new Mandrill_Exception('Invalid API key');
            
            $this->api = $api;
            
        } catch ( Exception $e ) {
            throw new Mandrill_Exception($e->getMessage());
        }
    }
    
	/**
	 * Work horse. Every API call use this function to actually make the request to Mandrill's servers.
	 *
	 * @link https://mandrillapp.com/api/docs/
	 *
	 * @param string $method API method name
	 * @param array $args query arguments
	 * @param string $http GET or POST request type
	 * @param string $output API response format (json,php,xml,yaml). json and xml are decoded into arrays automatically.
	 * @return array|string|Mandrill_Exception
	 */
	function request($method, $args = array(), $http = 'POST', $output = 'json') {
		if( !isset($args['key']) )
			$args['key'] = $this->api;

		$api_version = self::API_VERSION;
		$dot_output = ('json' == $output) ? '' : ".{$output}";

		$url = self::END_POINT . "{$api_version}/{$method}{$dot_output}";

		switch ($http) {

			case 'GET':

				$url .= '?' . http_build_query($args);
				$response = $this->http_request($url, array(),'GET');
				break;

			case 'POST':
				$response = $this->http_request($url, $args, 'POST');
				break;

			default:
				throw new Mandrill_Exception('Unknown request type');
		}

		$response_code  = $response['header']['http_code'];
		$body           = $response['body'];

		switch ($output) {
			
			case 'json':

				$body = json_decode($body, true);
				break;

			case 'php':

				$body = unserialize($body);
				break;
		}		

		if( 200 == $response_code ) {

			return $body;
		}
		else {

			$message = isset( $body['message'] ) ? $body['message'] : '' ;

			throw new Mandrill_Exception($message . ' - ' . $body, $response_code);
		}
	}

	/**
	 * @link https://mandrillapp.com/api/docs/users.html#method=ping
	 *
	 * @return array|Mandrill_Exception
	 */
	function users_ping() {

		return $this->request('users/ping');
	}
	
	/**
	 * @link https://mandrillapp.com/api/docs/users.html#method=info
	 *
	 * @return array|Mandrill_Exception
	 */
	function users_info() {

		return $this->request('users/info');
	}
	
	/**
	 * @link https://mandrillapp.com/api/docs/users.html#method=senders
	 *
	 * @return array|Mandrill_Exception
	 */
	function users_senders() {

		return $this->request('users/senders');
	}
	
	/**
	 * @link https://mandrillapp.com/api/docs/users.html#method=disable-sender
	 *
	 * @return array|Mandrill_Exception
	 */
	function users_disable_sender($domain) {

		return $this->request('users/disable-senders', array('domain' => $domain) );
	}
	
	/**
	 * @link https://mandrillapp.com/api/docs/users.html#method=verify-sender
	 *
	 * @return array|Mandrill_Exception
	 */
	function users_verify_sender($email) {

		return $this->request('users/verify-senders', array('domain' => $email) );
	}
	
	/**
	 * @link https://mandrillapp.com/api/docs/senders.html#method=domains
	 *
	 * @return array|Mandrill_Exception
	 */
	function senders_domains() {

		return $this->request('senders/domains');
	}
	
	/**
	 * @link https://mandrillapp.com/api/docs/senders.html#method=list
	 *
	 * @return array|Mandrill_Exception
	 */
	function senders_list() {

		return $this->request('senders/list');
	}
	
	/**
	 * @link https://mandrillapp.com/api/docs/senders.html#method=info
	 *
	 * @return array|Mandrill_Exception
	 */
	function senders_info($email) {

		return $this->request('senders/info', array( 'address' => $email) );
	}
	
	/**
	 * @link https://mandrillapp.com/api/docs/senders.html#method=time-series
	 *
	 * @return array|Mandrill_Exception
	 */
	function senders_time_series($email) {

		return $this->request('senders/time-series', array( 'address' => $email) );
	}
	
	/**
	 * @link https://mandrillapp.com/api/docs/tags.html#method=list
	 *
	 * @return array|Mandrill_Exception
	 */
	function tags_list() {

		return $this->request('tags/list');
	}
	
	/**
	 * @link https://mandrillapp.com/api/docs/tags.html#method=info
	 *
	 * @return array|Mandrill_Exception
	 */
	function tags_info($tag) {

		return $this->request('tags/info', array( 'tag' => $tag) );
	}
	
	/**
	 * @link https://mandrillapp.com/api/docs/tags.html#method=time-series
	 *
	 * @return array|Mandrill_Exception
	 */
	function tags_time_series($tag) {

		return $this->request('tags/time-series', array( 'tag' => $tag) );
	}
	
	/**
	 * @link https://mandrillapp.com/api/docs/tags.html#method=all-time-series
	 *
	 * @return array|Mandrill_Exception
	 */
	function tags_all_time_series() {

		return $this->request('tags/all-time-series');
	}
	
	/**
	 * @link https://mandrillapp.com/api/docs/templates.html#method=add
	 *
	 * @return array|Mandrill_Exception
	 */
	function templates_add($name, $code) {

		return $this->request('templates/add', array('name' => $name, 'code' => $code) );
	}

	/**
	 * @link https://mandrillapp.com/api/docs/templates.html#method=update
	 *
	 * @return array|Mandrill_Exception
	 */
	function templates_update($name, $code) {

		return $this->request('templates/update', array('name' => $name, 'code' => $code) );
	}

	/**
	 * @link https://mandrillapp.com/api/docs/templates.html#method=delete
	 *
	 * @return array|Mandrill_Exception
	 */
	function templates_delete($name) {

		return $this->request('templates/delete', array('name' => $name) );
	}

	/**
	 * @link https://mandrillapp.com/api/docs/templates.html#method=info
	 *
	 * @return array|Mandrill_Exception
	 */
	function templates_info($name) {

		return $this->request('templates/info', array('name' => $name) );
	}

	/**
	 * @link https://mandrillapp.com/api/docs/templates.html#method=list
	 *
	 * @return array|Mandrill_Exception
	 */
	function templates_list() {

		return $this->request('templates/list');
	}

	/**
	 * @link https://mandrillapp.com/api/docs/templates.html#method=time-series
	 *
	 * @return array|Mandrill_Exception
	 */
	function templates_time_series($name) {

		return $this->request('templates/time-series', array('name' => $name) );
	}

	/**
	 * @link https://mandrillapp.com/api/docs/urls.html#method=list
	 *
	 * @return array|Mandrill_Exception
	 */
	function urls_list() {

		return $this->request('urls/list');
	}

	/**
	 * @link https://mandrillapp.com/api/docs/urls.html#method=time-series
	 *
	 * @return array|Mandrill_Exception
	 */
	function urls_time_series($name) {

		return $this->request('urls/time-series', array('name' => $name) );
	}

	/**
	 * @link https://mandrillapp.com/api/docs/urls.html#method=search
	 *
	 * @return array|Mandrill_Exception
	 */
	function urls_search($q) {

		return $this->request('urls/search', array('q' => $q) );
	}

	/**
	 * @link https://mandrillapp.com/api/docs/webhooks.html#method=add
	 *
	 * @return array|Mandrill_Exception
	 */
	function webhooks_add($url, $events) {

		return $this->request('webhooks/add', array('url' => $url, 'events' => $events) );
	}

	/**
	 * @link https://mandrillapp.com/api/docs/webhooks.html#method=update
	 *
	 * @return array|Mandrill_Exception
	 */
	function webhooks_update($url, $events) {

		return $this->request('webhooks/update', array('url' => $url, 'events' => $events) );
	}

	/**
	 * @link https://mandrillapp.com/api/docs/webhooks.html#method=delete
	 *
	 * @return array|Mandrill_Exception
	 */
	function webhooks_delete($id) {

		return $this->request('webhooks/delete', array('id' => $id) );
	}

	/**
	 * @link https://mandrillapp.com/api/docs/webhooks.html#method=info
	 *
	 * @return array|Mandrill_Exception
	 */
	function webhooks_info($id) {

		return $this->request('webhooks/info', array('id' => $id) );
	}

	/**
	 * @link https://mandrillapp.com/api/docs/webhooks.html#method=list
	 *
	 * @return array|Mandrill_Exception
	 */
	function webhooks_list() {
		return $this->request('webhooks/list');
	}

	/**
	 * @link https://mandrillapp.com/api/docs/messages.html#method=search
	 *
	 * @return array|Mandrill_Exception
	 */
	function messages_search($query, $date_from = '', $date_to = '', $tags = array(), $senders = array(), $limit = 100) {
		return $this->request('messages/search', compact('query', 'date_from', 'date_to', 'tags', 'senders', 'limit'));
	}

	/**
	 * @link https://mandrillapp.com/api/docs/messages.html#method=send
	 *
	 * @return array|Mandrill_Exception
	 */
	function messages_send($message) {
		return $this->request('messages/send', array('message' => $message) );
	}

	/**
	 * @link https://mandrillapp.com/api/docs/messages.html#method=send-template
	 *
	 * @return array|Mandrill_Exception
	 */
	function messages_send_template($template_name, $template_content, $message) {
		return $this->request('messages/send-template', compact('template_name', 'template_content','message') );
	}

    function http_request($url, $fields = array(), $method = 'POST') {
        if( !ini_get('safe_mode') ){
            set_time_limit(2 * 60);
        }

        if ( !in_array( $method, array('POST','GET') ) ) $method = 'POST';
        if ( !isset( $fields['key']) ) $fields['key'] = $this->api;

        $fields = is_array($fields) ? http_build_query($fields) : $fields;
		if ( defined('WP_DEBUG') && WP_DEBUG !== false ) {
			error_log( "\nMandrill::http_request: URL: $url - Fields: $fields\n" );
		}

        $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            
            curl_setopt($ch, CURLOPT_POST, $method == 'POST');
            
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
            
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2 * 60 * 1000);
            
            $response   = curl_exec($ch);
            $info       = curl_getinfo($ch);
            $error      = curl_error($ch);
            
        curl_close($ch);
        
      return array('header' => $info, 'body' => $response, 'error' => $error);
    }
    
    static function getAttachmentStruct($path) {
        
        $struct = array();
        
        try {
            
            if ( !@is_file($path) ) throw new Exception($path.' is not a valid file.');

            $filename = basename($path);
            
            if ( !function_exists('get_magic_quotes') ) {
                function get_magic_quotes() { return false; }
            }
            if ( !function_exists('st_magic_quotes') ) {
                function set_magic_quotes($value) { return true;}
            }
            
            if (strnatcmp(phpversion(),'6') >= 0) {
                $magic_quotes = get_magic_quotes_runtime();
                set_magic_quotes_runtime(0);
            }
            
            $file_buffer  = file_get_contents($path);
            $file_buffer  = chunk_split(base64_encode($file_buffer), 76, "\n");
            
            if (strnatcmp(phpversion(),'6') >= 0) set_magic_quotes_runtime($magic_quotes);
            
            if (strnatcmp(phpversion(),'5.3') >= 0) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime_type = finfo_file($finfo, $path);
            } else {
                $mime_type = mime_content_type($path);
            }
            
            if ( !Mandrill::isValidContentType($mime_type) ) 
                throw new Exception($mime_type.' is not a valid content type (it should be '.implode('*,', self::getValidContentTypes() ).').');

            $struct['type']     = $mime_type;
            $struct['name']     = $filename;
            $struct['content']  = $file_buffer;

        } catch (Exception $e) {
            throw new Mandrill_Exception('Error creating the attachment structure: '.$e->getMessage());
        }
        
        return $struct;
    }
    
    static function isValidContentType($ct) {
        $valids = self::getValidContentTypes();
        
        foreach ( $valids as $vct ) {
            if ( strpos($ct, $vct) !== false )  return true;
        }

        return false;
    }
    
    static function getValidContentTypes() {
        return array(
                  'image/',
                  'text/',
                  'application/pdf',
              );
    }
}

?>
