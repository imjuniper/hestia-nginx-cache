<?php

if (!defined('ABSPATH')) {
	die();
}

if (!defined('WP_CLI')) return;

class Hestia_Nginx_Cache_CLI_Command extends WP_CLI_Command {

	public function __construct(){
		$this -> plugin = Hestia_Nginx_Cache::get_instance();
	}

	public function purge(){
		$result = $this -> plugin -> purge(true);
		if ($result) {
			$exit_code = wp_remote_retrieve_header($result, 'Hestia-Exit-Code');
		}
		if($exit_code == 0){
			WP_CLI::success('Cache purged');
		} else {
			WP_CLI::error('Cache purge failed');
		}
	}

	public function setup($args, $assoc_args){
		if(empty($assoc_args)){
			WP_CLI::error('Usage wp hestia-cache setup --access_key=YOUR_ACCESS_KEY --secret=YOUR_SECRET_KEY --hestia_user=HESTIA_USER --domain=DOMAIN --host=HOST --port=PORT --disable_automatic_purge=true');
		}
		$access_key = !empty($assoc_args['access_key']) ? $assoc_args['access_key'] : '';
		$secret_key = !empty($assoc_args['secret_key']) ? $assoc_args['secret_key'] : '';
		//hestia user / owner of domain
		//wordpress doesn't like the word 'user' for input arguments so we use 'hestia_user' instead
		$user = !empty($assoc_args['hestia_user']) ? $assoc_args['hestia_user'] : '';
		//domain to purge
		$domain = !empty($assoc_args['domain']) ? $assoc_args['domain'] : str_replace(array('https://','http://'), '', get_bloginfo('wpurl'));
		//server hostname
		$host = !empty($assoc_args['host']) ? $assoc_args['host'] : 'localhost';
		$port = !empty($assoc_args['port']) ? $assoc_args['port'] : 8083;
		$disable_automatic_purge = !empty($assoc_args['disable_automatic_purge']) ? $assoc_args['disable_automatic_purge'] : '';

		if(empty($access_key) || empty($secret_key) || empty($user)){
			WP_CLI::error('Please provide access_key, secret_key and hestia_user');
		}
		//validate
		$port = intval($port);
		$domain = wp_parse_url($domain, PHP_URL_HOST) ?: $domain;

		$options = array('access_key' => $access_key, 'secret_key' => $secret_key, 'user' => $user, 'domain' => $domain, 'host' => $host, 'port' => $port, 'disable_automatic_purge' => $disable_automatic_purge);
		update_option(Hestia_Nginx_Cache::NAME, $options);
	}
}

WP_CLI::add_command('hestia-cache', 'Hestia_Nginx_Cache_CLI_Command');
