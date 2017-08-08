<?php

    /**
     * api client
     *
     * This library is free software; you can redistribute it and/or
     * modify it under the terms of the GNU Lesser General Public
     * License as published by the Free Software Foundation; either
     * version 2.1 of the License, or (at your option) any later version.
     *
     * This library is distributed in the hope that it will be useful,
     * but WITHOUT ANY WARRANTY; without even the implied warranty of
     * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
     * Lesser General Public License for more details.
     *
     * You should have received a copy of the GNU Lesser General Public
     * License along with this library; if not, write to the Free Software
     * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
     */


    /**
     * api client
     *
     * PHP client class for api
     *
     * @version 1.8.4
     * @license LGPL (http://www.gnu.org/licenses/lgpl.html)
     */
    class apiclient {

        const VERSION = '1.8.4';

        const HOST = 'api.emailsys.net';

        /**
         * Node id
         *
         * @var integer
         */
        private $node_id = NULL;

        /**
         * Recipient list id
         *
         * @var integer
         */
        private $recipientlist_id = NULL;

        /**
         * Api key for authentication (API-Schlüssel)
         *
         * @var string
         */
        private $apikey = '';

        /**
         * Use secure connection (SSL)
         *
         * @var boolean
         */
        private $use_ssl = true;

        /**
         * Use debug mode
         *
         * @var boolean
         */
        private $debug_mode = false;

        /**
         * Response type ok
         *
         * @var string
         */
        const RESPONSE_TYPE_OK = 'ok';

        /**
         * Response type error
         *
         * @var string
         */
        const RESPONSE_TYPE_ERROR = 'error';

        /**
         * Transfer method get
         *
         * @var string
         */
        const TRANSFER_METHOD_GET = 'GET';

        /**
         * Transfer method post
         *
         * @var string
         */
        const TRANSFER_METHOD_POST = 'POST';

        /**
         * Recipient status active
         *
         * @var string
         */
        const RECIPIENT_STATUS_ACTIVE = 'active';

        /**
         * Recipient status bounced
         *
         * @var string
         */
        const RECIPIENT_STATUS_BOUNCED = 'bounced';

        /**
         * Recipient status deleted
         *
         * @var string
         */
        const RECIPIENT_STATUS_DELETED = 'deleted';

        /**
         * Recipient status new
         *
         * @var string
         */
        const RECIPIENT_STATUS_NEW = 'new';

        /**
         * Constructor
         *
         * @param integer $node_id Node id
         * @param integer $recipientlist_id Recipient list id
         * @param string $apikey Api key for authentication (API-Schlüssel)
         * @param bool $use_ssl Whether to use SSL or not
         * @param bool $debug_mode Use debug mode
         */
        public function __construct($node_id, $recipientlist_id, $apikey, $use_ssl = true, $debug_mode = false) {

            utils::check_int($node_id, 'node_id');
            utils::check_int($recipientlist_id, 'recipientlist_id');
            utils::check_string($apikey, 'apikey');
            utils::check_bool($use_ssl, 'use_ssl');
            utils::check_bool($debug_mode, 'debug_mode');

            $this->node_id = $node_id;
            $this->recipientlist_id = $recipientlist_id;
            $this->apikey = $apikey;
            $this->use_ssl = $use_ssl;
            $this->debug_mode = $debug_mode;

        }

        /**
         * Get one recipient from current recipient list
         *
         * @param string $email Email
         * @return array
         */
        public function get_recipient($email) {

            utils::check_string($email, 'email');

            return $this->api_call('recipient_get', array('email' => $email));

        }

        /**
         * Get all recipients from current recipient list
         *
         * @param string $status Status
         * @param array $fields
         * @return array
         */
        public function get_recipients($status = self::RECIPIENT_STATUS_ACTIVE, $fields = array()) {

            utils::check_string($status, 'status');
            utils::check_array($fields, 'fields', true);

            return $this->api_call('recipient_get_multi', array('status' => $status, 'fields' => $fields));

        }

        /**
         * Add recipients to current recipient list from csv file
         *
         * @param string $file_path Path to csv file
         * @param string $enclosure Csv enclosure, for example "
         * @param string $delimiter Csv delimiter, for example ;
         * @param string $recipient_exist What to do if recipient exists
         * @param string $recipient_missing What to do if recipient is missing
         * @param string $recipient_deleted What to do if recipient is deleted
         * @return array
         */
        public function add_recipients($file_path, $enclosure = '"', $delimiter = ';', $recipient_exist = 'stock', $recipient_missing = '', $recipient_deleted = '') {

            utils::check_string($file_path, 'file_path');
            utils::check_string($enclosure, 'enclosure');
            utils::check_string($delimiter, 'delimiter');
            utils::check_string($recipient_missing, 'recipient_missing', true, array('delete', 'softdelete', ''));
            utils::check_string($recipient_deleted, 'recipient_deleted', true, array('import', ''));

            $parameters = array(
                'file' => '@FILE@' . $file_path,
                'enclosure' => $enclosure,
                'delimiter' => $delimiter,
                'recipient_exist' => $recipient_exist,
                'recipient_missing' => $recipient_missing,
                'recipient_deleted' => $recipient_deleted,
            );

            return $this->api_call('recipient_new_multi', $parameters, self::TRANSFER_METHOD_POST);

        }

        /**
         * Add recipient to current recipient list
         *
         * @param string $email E-Mail-Address of recipient to add
         * @param array $recipient_data Recipient data, see documentation for details
         * @return array
         */
        public function add_recipient($email, $recipient_data = array()) {

            utils::check_string($email, 'email');
            utils::check_array($recipient_data, 'recipient_data', true);

            $recipient_data['email'] = $email;

            if (empty($recipient_data['status']) || $recipient_data['status'] == 'active') {
                $recipient_data['status'] = 'active';
                $recipient_data['activationmail'] = 'no';
            }

            return $this->api_call('recipient_new', $recipient_data);

        }

        /**
         *
         * @param string $email E-Mail-Address of recipient to edit
         * @param array $recipient_data Recipient data, see documentation for details
         * @return array
         */
        public function edit_recipient($email, $recipient_data) {

            utils::check_string($email, 'email');
            utils::check_array($recipient_data, 'recipient_data');

            $recipient_data['email'] = $email;

            return $this->api_call('recipient_edit', $recipient_data);

        }

        /**
         * Delete recipient
         *
         * @param string $email E-Mail-Address of recipient to delete
         * @param string $send_goodbye (possible values: yes,no)
         * @param string $track_stats (possible values: yes,no)
         * @return array
         */
        public function delete_recipient($email, $send_goodbye = 'no', $track_stats = 'no') {

            utils::check_string($email, 'email');
            utils::check_string($send_goodbye, 'send_goodbye', false, array('yes', 'no'));
            utils::check_string($track_stats, 'track_stats', false, array('yes', 'no'));

            $parameters = array(
                'email' => $email,
                'sendgoodbye' => $send_goodbye,
                'track_stats' => $track_stats
            );

            return $this->api_call('recipient_delete', $parameters);

        }

        /**
         * Delete recipients
         *
         * @return array
         */
        public function delete_recipients() {
            return $this->api_call('recipient_delete_multi', array());
        }

        /**
         * Send new mailing to current recipient list
         *
         * @param string $sender_name Sender name
         * @param string $sender_email Sender email
         * @param string $subject Subject
         * @param string $send_at Send at (ISO datetime, yyyy-mm-dd hh:mm)
         * @param string $zip_file Path to zipfile containing email
         * @param array $settings Settings, see documentation for details
         * @return array
         */
        public function add_mailing($sender_name, $sender_email, $subject, $send_at = NULL, $zip_file, $settings = array()) {

            utils::check_string($sender_name, 'sender_name');
            utils::check_string($sender_email, 'sender_email');
            utils::check_string($subject, 'subject');
            utils::check_array($settings, 'settings', true);

            if ($send_at !== NULL) {
                utils::check_string($send_at, 'send_at');
            }

            utils::check_string($zip_file, 'zip_file');

            $parameters = array(
                'sender_name' => $sender_name,
                'sender_email' => $sender_email,
                'subject' => $subject,
                'send_at' => $send_at,
                'file' => '@FILE@' . $zip_file,
            );

            if (!empty($settings['charset'])) {
                utils::check_string($settings['charset'], 'charset');
                $parameters['charset'] = $settings['charset'];
            } else {
                $parameters['charset'] = NULL;
            }

            if (!empty($settings['draft'])) {
                utils::check_string($settings['draft'], 'draft', false, array('yes', 'no'));
                $parameters['draft'] = $settings['draft'];
            }

            if (!empty($settings['robinson'])) {
                utils::check_string($settings['robinson'], 'robinson', false, array('yes', 'no'));
                $parameters['robinson'] = $settings['robinson'];
            }

            if (!empty($settings['ecg'])) {
                utils::check_string($settings['ecg'], 'ecg', false, array('yes', 'no'));
                $parameters['ecg'] = $settings['ecg'];
            }

            if (!empty($settings['domain'])) {
                utils::check_string($settings['domain'], 'domain', false);
                $parameters['domain'] = $settings['domain'];
            }

            $data = $this->api_call('mailing_new', $parameters, self::TRANSFER_METHOD_POST);

            return $data['api_data']['mailing_id'];

        }

        /**
         * Return statistics to one mailing
         *
         * @param integer $mailing_id Mailing id
         * @param integer $publiclink_validity Validity in days of public link
         * @return array
         */
        public function get_mailing_statistics($mailing_id, $publiclink_validity = 3) {

            utils::check_int($mailing_id, 'mailing_id');
            utils::check_int($publiclink_validity, 'publiclink_validity', false, 1, 30);

            $parameters = array(
                'mailing_id' => $mailing_id,
                'publiclink_validity' => $publiclink_validity
            );

            return $this->api_call('statistics_mailing_get', $parameters);

        }

        /**
         * Returns mailings
         *
         * @return array
         */
        public function get_mailings() {
            return $this->api_call('mailings_get', array());
        }

        /**
         * Returns recipientlist informations
         *
         * @return array
         */
        public function get_metadata() {
            return $this->api_call('metadata_get', array());
        }

        /**
         * Changes recipientlist informations
         *
         * @param array $data Recipientlists data, "name", "description"
         * @return array
         */
        public function set_metadata($data) {
            return $this->api_call('metadata_set', $data, self::TRANSFER_METHOD_POST);
        }

        /**
         * Build api host
         *
         * @return string
         */
        protected function get_api_host() {
            return 'http' . ($this->use_ssl ? 's' : '') . '://' . self::HOST;
        }

        /**
         * Api call, used by all methods
         *
         * @param string $module Module
         * @param array $parameters Parameters
         * @param string $method Method
         * @return array
         * @throws apiclient_io_exception
         * @throws apiclient_response_exception
         */
        private function api_call($module, $parameters, $method = self::TRANSFER_METHOD_GET) {

            utils::check_string($module, 'module');
            utils::check_array($parameters, 'parameters', true);
            utils::check_string($method, 'method', false, array(self::TRANSFER_METHOD_GET, self::TRANSFER_METHOD_POST));

            $host = $this->get_api_host();
            $url = '/rest/' . $this->apikey . '/' . $this->node_id . '/' . $module . '/?recipientlist_id=' . $this->recipientlist_id . '&version=' . self::VERSION;

            $data = '';

            if ($method == self::TRANSFER_METHOD_GET) {

                foreach ($parameters AS $k => $v) {

                    if (is_array($v)) {

                        if (count($v) > 0) {

                            foreach ($v AS $v_sub) {
                                $url .= '&' . $k . '[]=' . urlencode($v_sub);
                            }

                        }

                    } else {
                        $url .= '&' . $k . '=' . urlencode($v);
                    }

                }

                $header = 'GET ' . $url . ' HTTP/1.0' . "\r\n" .
                          'Host: ' . self::HOST . "\r\n\r\n";

            } else {

                $header = 'POST ' . $url . ' HTTP/1.0' . "\r\n" .
                          'Host: ' . self::HOST . "\r\n";

                $boundary = md5(microtime(true) + (rand(0, 1) * 100));

                $data = '';

                foreach ($parameters AS $k => $v) {

                    $data .= '--' . $boundary . "\r\n";

                    if (substr($v, 0, 6) == '@FILE@') {

                        $path_name = substr($v, 6);

                        if (!is_file($path_name)) {
                            throw new apiclient_io_exception('File "' . $path_name . '" not found');
                        }

                        $filename = basename($path_name);

                        $data .= 'Content-Disposition: form-data; name="' . $k . '"; filename="' . $filename . '"' . "\r\n";
                        $data .= 'Content-Type: application/octet-stream' . "\r\n";
                        $data .= 'Content-Transfer-Encoding: binary' . "\r\n\r\n";
                        $data .= file_get_contents($path_name) . "\r\n";

                    } else {

                        if (is_array($v)) {

                            if (count($v) > 0) {

                                foreach ($v AS $v_sub) {

                                    $data .= 'Content-Disposition: form-data; name="' . $k . '[]"' . "\r\n\r\n";
                                    $data .= $v_sub . "\n";

                                }

                            }

                        } else {

                            $data .= 'Content-Disposition: form-data; name="' . $k . '"' . "\r\n\r\n";
                            $data .= $v . "\n";

                        }

                    }

                }

                $data .= '--' . $boundary . '--' . "\r\n";

                $header .= 'Content-Type: multipart/form-data; boundary=' . $boundary . "\r\n" .
                           'Content-Length: ' . strlen($data) . "\r\n\r\n";

            }

            if ($this->debug_mode) {

                echo "*** DEBUG MODE ACTIVE ***\n";
                echo 'Node ID: ' . $this->node_id . "\n";
                echo 'Recipientlist ID: ' . $this->recipientlist_id . "\n";
                echo 'API Key: ' . $this->apikey. "\n";
                echo 'Host: ' . $host . "\n";
                echo 'URL: ' . $url . "\n";

                if ($this->use_ssl) {
                    echo "Socket: ssl://" . self::HOST . ":443\n";
                } else {
                    echo "WARNING: SSL mode disabled. Your data will be transfered UNSECURE\n";
                    echo "Socket: tcp://" . self::HOST . ":80\n";
                }

                if ($method == self::TRANSFER_METHOD_POST) {

                    echo "*** POST DATA ***\n";

                    foreach ($parameters AS $k => $v) {
                        $k . ' => ' . $v . "\n";
                    }

                }

            }

            if ($this->use_ssl) {
                $fh = fsockopen('ssl://' . self::HOST, 443, $errno, $errstr);
            } else {
                $fh = fsockopen('tcp://' . self::HOST, 80, $errno, $errstr);
            }

            if (!$fh) {
                throw new apiclient_io_exception('Error while connecting to ' . self::HOST . ' (' . $errno . ', ' . $errstr . ')');
            }

            $res = fwrite($fh, $header . $data);

            if (!$res) {
                throw new apiclient_io_exception('Error writing on stream');
            }

            $xml = '';

            while (!feof($fh)) {
                $xml .= fgets($fh, 1024);
            }

            $xml = trim(substr($xml, strpos($xml, '<rsp')));

            if ($xml == '') {
                throw new apiclient_io_exception('No response received');
            }

            $result = $this->build_result($xml);

            if ($result['@attributes']['status'] != self::RESPONSE_TYPE_OK) {
                throw new apiclient_response_exception('(' . $result['@attributes']['status_code'] . ') ' . $result['@attributes']['status_description'], (int)$result['@attributes']['status_code']);
            }

            return $result;

        }

        /**
         * Build array from xml
         *
         * @param string $xml Xml
         * @return array
         * @throws apiclient_io_exception
         */
        private function build_result($xml) {

            utils::check_string($xml, 'xml');

            $xml = @simplexml_load_string($xml, NULL, LIBXML_NOCDATA);

            if (!$xml) {
                throw new apiclient_io_exception('Error while parsing XML response');
            }

            return self::xml_to_array($xml);

        }

        /**
         * Xml to array
         *
         * @param object $xml Simple xml object
         * @return array
         */
        protected static function xml_to_array($xml) {

            $index = array();

            if ($xml instanceof SimpleXMLElement) {
                $xml = (array)$xml;
            }

            foreach ($xml AS $element => $value) {

                if (is_array($value) || is_object($value)) {

                    $vars = (array)$value;

                    if (count($vars) == 0) {
                        $index[$element] = NULL;
                    } else {
                        $index[$element] = self::xml_to_array($value);
                    }

                } else {
                    $index[$element] = (string)$value;
                }

            }

            return $index;

        }

    }

    /**
     * Base exception
     */
    class apiclient_base_exception extends Exception {
    }

    /**
     * Exception for wrong parameter
     */
    class apiclient_parameter_exception extends apiclient_base_exception {
    }

    /**
     * IO exception
     */
    class apiclient_io_exception extends apiclient_base_exception {
    }

    /**
     * Response exception
     */
    class apiclient_response_exception extends apiclient_base_exception {
    }

    /**
     * utils
     *
     * Helper
     *
     * @version 1.0
     * @license LGPL (http://www.gnu.org/licenses/lgpl.html)
     */
    class utils {

        /**
         * Checks if a variable is an integer
         *
         * @param integer $value Variable
         * @param string $name Name of variable, for error output
         * @param boolean $zero_allowed True: 0 is allowed, false: 0 is not allowed
         * @param integer $min Minimum value allowed
         * @param integer $max Maximum value allowed
         * @throws apiclient_parameter_exception
         */
        public static function check_int(&$value, $name, $zero_allowed = false, $min = NULL, $max = NULL) {

            if (!is_scalar($value) || preg_match('/[^0-9-]/i', $value)) {
                throw new apiclient_parameter_exception('$' . $name . ' must be a integer [0-9-]* ($value = ' . $value . ')');
            }

            $value = (int)$value;

            if ($zero_allowed != true && $value == 0) {
                throw new apiclient_parameter_exception('$' . $name . ' must be integer and is not allowed to be zero ($value = ' . $value . ')');
            }

            if ($min !== NULL && $value < $min) {
                throw new apiclient_parameter_exception('$' . $name . ' is below the allowed minimum of ' . $min);
            }

            if ($max !== NULL && $value > $max) {
                throw new apiclient_parameter_exception('$' . $name . ' is above the allowed maximum of ' . $max);
            }

        }

        /**
         * Checks if a variable is a string
         *
         * @param string $value Variable
         * @param string $name Name of variable, for error output
         * @param boolean $empty_allowed True: string can be empty (""), false: must not be empty
         * @param array $allowed_values Array with allowed values
         * @param array $disallowed_values Array with disallowed values
         * @throws apiclient_parameter_exception
         */
        public static function check_string($value, $name, $empty_allowed = false, $allowed_values = NULL, $disallowed_values = NULL) {

            if (!is_string($value)) {
                throw new apiclient_parameter_exception('$' . $name . ' must be a string');
            }

            if ($empty_allowed != true && $value == '') {
                throw new apiclient_parameter_exception('$' . $name . ' must not be empty');
            }

            if ($allowed_values !== NULL && is_array($allowed_values) && in_array($value, $allowed_values) == false) {
                throw new apiclient_parameter_exception('$' . $name . ' value is "' . $value .'" which is not among the allowed values (' . implode(',', $allowed_values)  . ')');
            }

            if ($disallowed_values !== NULL && is_array($disallowed_values) && in_array($value, $disallowed_values) == true) {
                throw new apiclient_parameter_exception('$' . $name . ' value is "' . $value .'" which is among the disallowed values (' . implode(',', $disallowed_values)  . ')');
            }

        }

        /**
         * Checks if a variable is a array
         *
         * @param array $value Variable
         * @param string $name Name of variable, for error output
         * @param boolean $empty_allowed True: array can be empty, false: must not be empty
         * @throws apiclient_parameter_exception
         */
        public static function check_array($value, $name, $empty_allowed = false) {

            if (!is_array($value)) {
                throw new apiclient_parameter_exception('$' . $name . ' must be an array');
            }

            if ($empty_allowed != true && count($value) == 0) {
                throw new apiclient_parameter_exception('$' . $name . ' must not be empty');
            }

        }

        /**
         * Checks if a variable is a boolean
         *
         * @param boolean $value Variable
         * @param string $name Name of variable, for error output
         * @throws apiclient_parameter_exception
         */
        public static function check_bool($value, $name) {

            if (!is_bool($value)) {
                throw new apiclient_parameter_exception('$' . $name . ' must be boolean');
            }

        }

    }
