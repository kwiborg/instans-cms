<?php
require_once('xmlrpc-2.2/lib/xmlrpc.inc');

class Weblog_Pinger {
	public function __construct() {
		$prefs = Config::getInstance();
		$this->database_server = $prefs->getProperty("db_host");
		$this->database_name = $prefs->getProperty("db_name");
		$this->database_user = $prefs->getProperty("db_user");
		$this->database_password = $prefs->getProperty("db_pass");
		$this->document_root = $prefs->getProperty("document_root");
		unset($prefs);
	}

    // Weblogs.Com XML-RPC settings
    var $weblogs_com_server = "rpc.weblogs.com";
    var $weblogs_com_port = 80;
    var $weblogs_com_path = "/RPC2";
    var $weblogs_com_method = "weblogUpdates.ping";
    var $weblogs_com_extended_method = "weblogUpdates.extendedPing";
    // Blo.gs XML-RPC settings
    var $blo_gs_server = "ping.blo.gs";
    var $blo_gs_port = 80;
    var $blo_gs_path = "/";
    var $blo_gs_method = "weblogUpdates.ping";
    // Feedburner XML-RPC settings
    var $feedburner_server = "ping.feedburner.com";
    var $feedburner_port = 80;
    var $feedburner_path = "/RPC2";
    var $feedburner_method = "weblogUpdates.ping";
    // Ping-o-Matic XML-RPC settings
    var $ping_o_matic_server = "rpc.pingomatic.com";
    var $ping_o_matic_port = 80;
    var $ping_o_matic_path = "/RPC2";
    var $ping_o_matic_method = "weblogUpdates.ping";
    // Technorati XML-RPC settings
    var $technorati_server = "rpc.technorati.com";
    var $technorati_port = 80;
    var $technorati_path = "/rpc/ping";
    var $technorati_method = "weblogUpdates.ping";
    // Audio.Weblogs.Com XML-RPC settings
    var $audio_weblogs_com_server = "audiorpc.weblogs.com";
    var $audio_weblogs_com_port = 80;
    var $audio_weblogs_com_path = "/RPC2";
    var $audio_weblogs_com_method = "weblogUpdates.ping";
    // Blogbot.dk Settings
    var $blogbot_dk_server = "blogbot.dk";
    var $blogbot_dk_port = 80;
    var $blogbot_dk_path = "/io/xml-rpc.php";
    var $blogbot_dk_method = "weblogUpdates.ping";
    // Overskrift.dk Settings
    var $overskrift_dk_server = "www.overskrift.dk";
    var $overskrift_dk_port = 80;
    var $overskrift_dk_path = "/ping";
    var $overskrift_dk_method = "weblogUpdates.ping";
    // log settings
    var $log_file = "/ping.log";
    var $log_level = "none"; // full, short, or none;   

/*
    // database settings
    // the machine hosting the MySQL server
    var $database_server = "";
    // the MySQL database
    var $database_name = "";
    // the MySQL user with access to the database
    var $database_user = "";
    // the MySQL user password
    var $database_password = "";
*/
    var $software_version = "1.4";
    var $debug = false;

    // report errors
    function report_error($message) {
        error_log("Weblog Pinger: " . $message);
    }
    
    /* Ping Weblogs.Com to indicate that a weblog has been updated. Returns true
    on success and false on failure. */
    function ping_weblogs_com($weblog_name, $weblog_url, $changes_url = "", $category = "") {
        return $this->ping($this->weblogs_com_server, $this->weblogs_com_port,
            $this->weblogs_com_path, $this->weblogs_com_method, $weblog_name,
            $weblog_url, $changes_url, $category);
    }

    /* Ping Blo.gs to indicate that a weblog has been updated. Returns true on success
    and false on failure. */
    function ping_blo_gs($weblog_name, $weblog_url, $changes_url = "", $category = "") {
        return $this->ping($this->blo_gs_server, $this->blo_gs_port,
            $this->blo_gs_path, $this->blo_gs_method, $weblog_name, $weblog_url,
            $changes_url, $category);
    }
    
    /* Ping Technorati to indicate that a weblog has been updated. Returns true on
    success and false on failure. */
    function ping_technorati($weblog_name, $weblog_url, $changes_url = "", $category = "") {
        return $this->ping($this->technorati_server, $this->technorati_port,
            $this->technorati_path, $this->technorati_method, $weblog_name, $weblog_url,
            $changes_url, $category);
    }

    /* Ping Pingomatic to indicate that a weblog has been updated. Returns true on success
    and false on failure. */
    function ping_ping_o_matic($weblog_name, $weblog_url, $changes_url = "", $category = "") {
        return $this->ping($this->ping_o_matic_server, $this->ping_o_matic_port,
            $this->ping_o_matic_path, $this->ping_o_matic_method, $weblog_name,
            $weblog_url, $changes_url, $category);
    }
    /* Ping Feedburner to indicate that a weblog has been updated. Returns true on success
    and false on failure. */
    function ping_feedburner($weblog_name, $weblog_url, $changes_url = "", $category = "") {
        return $this->ping($this->feedburner_server, $this->feedburner_port,
            $this->feedburner_path, $this->feedburner_method, $weblog_name,
            $weblog_url, $changes_url, $category);
    }
    /* Ping Audio.Weblogs.Com to indicate that a weblog with a podcast has been updated.
    Returns true on success and false on failure. */
    function ping_audio_weblogs_com($weblog_name, $weblog_url, $changes_url = "",
        $category = "") {
        
        return $this->ping($this->audio_weblogs_com_server, $this->audio_weblogs_com_port,
            $this->audio_weblogs_com_path, $this->audio_weblogs_com_method, $weblog_name,
            $weblog_url, $changes_url, $category);
    }
    /*Ping Blogbot.dk to indicate that a weblog has been updated. Returns true on
    success and false on failure. */
    function ping_blogbot($weblog_name, $weblog_url, $changes_url = "", $category = ""){
    	return $this->ping($this->blogbot_dk_server, $this->blogbot_dk_port,
    		$this->blogbot_dk_path, $this->blogbot_dk_method, $weblog_name,
    		$weblog_url, $changes_url, $category);
    }
    /*Ping Overskrift.dk to indicate that a weblog has been updated. Returns true on
    success and false on failure. */
    function ping_overskrift($weblog_name, $weblog_url, $changes_url = "", $category = ""){
    	return $this->ping($this->overskrift_dk_server, $this->overskrift_dk_port,
    		$this->overskrift_dk_path, $this->overskrift_dk_method, $weblog_name,
    		$weblog_url, $changes_url, $category);
    }
    /* Pings all of the above services to indicate that a weblog has been updated.
    Returns true on success and false on failure. */
    function ping_all($weblog_name, $weblog_url, $changes_url = "", $category = "") {
        $error[0] = $this->ping_technorati($weblog_name, $weblog_url, $changes_url, $category);
        $error[1] = $this->ping_weblogs_com($weblog_name, $weblog_url, $changes_url, $category);
        $error[2] = $this->ping_ping_o_matic($weblog_name, $weblog_url, $changes_url, $category);
        $error[3] = $this->ping_feedburner($weblog_name, $weblog_url, $changes_url, $category);
        $error[4] = $this->ping_audio_weblogs_com($weblog_name, $weblog_url, $changes_url, $category);
        $error[5] = $this->ping_blogbot($weblog_name, $weblog_url, $changes_url, $category);
        $error[6] = $this->ping_overskrift($weblog_name, $weblog_url, $changes_url, $category);
	    $all_ok = $error[0] & $error[1] & $error[2] & $error[3] & $error[4] & $error[5] & $error[6];
	    return array($all_ok, $error);
    }
    /* Multi-purpose ping for any XML-RPC server that supports the Weblogs.Com interface. */
    function ping($xml_rpc_server, $xml_rpc_port, $xml_rpc_path, $xml_rpc_method,
        $weblog_name, $weblog_url, $changes_url, $cat_or_rss, $extended = false) {

        // see how recently a ping was sent to the server for this url
        $db_response = $this->check_ping($xml_rpc_server, $weblog_url);
        $db_id = 0;
        if ($db_response['TIMECHECKED'] > 0) {
        	$when = strtotime($db_response['TIMECHECKED']);
        	$db_id = $db_response['ID'];
        	if (time() - $when < 300) {
        		// last ping less than 5 minutes ago, so don't send another ping
        		return true;
        	}
        }
        
        // build the parameters
        $name_param = new xmlrpcval($weblog_name, 'string');
        $url_param = new xmlrpcval($weblog_url, 'string');
        $changes_param = new xmlrpcval($changes_url, 'string');
        $cat_or_rss_param = new xmlrpcval($cat_or_rss, 'string');
        $method_name = "weblogUpdates.ping";
        if ($extended) $method_name = "weblogUpdates.extendedPing";
    
        if ($cat_or_rss != "") {
            $params = array($name_param, $url_param, $changes_param, $cat_or_rss_param);
            $call_text = "$method_name(\"$weblog_name\", \"$weblog_url\", \"$changes_url\", \"$cat_or_rss\")";
        } else {
            if ($changes_url != "") {
              $params = array($name_param, $url_param, $changes_param);
              $call_text = "$method_name(\"$weblog_name\", \"$weblog_url\", \"$changes_url\")";
          } else {
              $params = array($name_param, $url_param);
              $call_text = "$method_name(\"$weblog_name\", \"$weblog_url\")";
            }
        } 
    
        // create the message
        $message = new xmlrpcmsg($xml_rpc_method, $params);
        $client = new xmlrpc_client($xml_rpc_path, $xml_rpc_server, 
            $xml_rpc_port);
        $response = $client->send($message);
        // log the message
        $this->log_ping("Request: " . strftime("%D %T") . " " . $xml_rpc_server . $xml_rpc_path . " " . $call_text);
        $this->log_ping($message->serialize(), true);
        // record the ping in the database
        $this->update_ping($db_id, $xml_rpc_server, $weblog_url);
        if ($response == 0) {
            $error_text = "Error: " . $xml_rpc_server . ": " . $client->errno . " "
                . $client->errstring;
            $this->report_error($error_text);
            $this->log_ping($error_text);
            return false;
        }
        if ($response->faultCode() != 0)  {
            $error_text = "Error: " . $xml_rpc_server . ": " . $response->faultCode()
                . " " . $response->faultString();
            $this->report_error($error_text);
            return false;
        }
        $response_value = $response->value();
        if ($this->debug) $this->report_error($response_value->serialize());
        $this->log_ping($response_value->serialize(), true);
        $fl_error = $response_value->structmem('flerror');
        $message = $response_value->structmem('message');
        
        // read the response
        if ($fl_error->scalarval() != false) {
            $error_text = "Error: " . $xml_rpc_server . ": " . $message->scalarval();
            $this->report_error($error_text);
            $this->log_ping($error_text);
            return false;
        }
        
        return true;     
    }
    
    /* Save ping data to a log file */
    function log_ping($message, $xml_data = false) {
        if ($this->log_level == "none") {
            return;
        }
        if (($this->log_level == "short") & ($xml_data)) {
            return;
        }
		$file = $this->document_root;
		$file .= $this->log_file;
		if (file_exists($file)) {
			chmod($file, 0777);
		} else {
	        $fhandle = fopen($file, "a");
			chmod($file, 0777);
	        fclose($fhandle); 
		}

        if (!is_writable($file)) {
            $this->report_error("File {$file} is not writable");
            return;
        }
        $fhandle = fopen($file, "a");
        fwrite($fhandle, $message . "\r\n");
        fclose($fhandle); 
    }
    
    
    /* Configure the MySQL database */
    function configure_database($database_server, $database_user, $database_password, $database_name) {
    	$this->database_server = $database_server;
    	$this->database_user = $database_user;
    	$this->database_password = $database_password;
    	$this->database_name = $database_name;
    	$this->report_error("$database_server, $database_user, $database_password, $database_name");
    }

    /* Connect to the MySQL database */
    function connect_to_database() {
    	// make sure the database has been configured
    	if ($this->database_name == "") {
    		return false;
    	}
        $db = mysql_connect($this->database_server, $this->database_user,
            $this->database_password);
        if (!$db) {
            $this->report_error("Could not connect to database.");
            return false;
        } else {
            mysql_select_db($this->database_name);
            return true;
        }
    }
    
    /* Process a MySQL query */
    function process_query($query) {
        if (!$this->connect_to_database()) return false;
        $result = mysql_query($query);
        if ($result === false) {
            $this->report_error(mysql_error());
            $this->report_error($query);
        }
        return $result;
    }
    
    /* Lock the database */
    function lock_table($read_only = false) {
        $query = "LOCK TABLES $this->database_name WRITE";
        if ($read_only) {
            $query = "LOCK TABLES $this->database_name READ";
        }
        $result = mysql_query($query);
    }
    
    /* Unlock the database */
    function unlock_table() {
        $query = "UNLOCK TABLES";
        $result = mysql_query($query);
    }    

    /* Create the MySQL database */
    function create_database() {
    	$query = "CREATE TABLE PINGCHECK ("
    		. "ID MEDIUMINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, "
    		. "URL TINYTEXT, "
    		. "SERVER TINYTEXT, "
    		. "TIMECHECKED DATETIME"
    		. ");";
    	return $this->process_query($query);
    }
    
    /* Check the last time a server was pinged for a URL */
    function check_ping($server, $url) {
    	if (($server == "") | ($url == "")) {
    		return false;
    	}
    	$query = "SELECT ID, TIMECHECKED FROM PINGCHECK WHERE SERVER = '$server' AND URL = '$url' order by TIMECHECKED desc limit 1";
    	$result = $this->process_query($query);
    	if ($result === false) {
    		return false;
    	}
    	return mysql_fetch_array($result);
    	print_r($result);
    }
    
    /* Record a ping in the database */
    function update_ping($id, $server, $url) {
    	$when = strftime("%Y/%m/%d %H:%M:%S", time());
    	$query = "REPLACE INTO PINGCHECK VALUES($id, '$url', '$server', '$when')";
    	return $this->process_query($query); 
	}       
}
?>