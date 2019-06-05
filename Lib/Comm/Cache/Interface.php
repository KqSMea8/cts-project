<?php 

interface Comm_Cache_Interface{
	
	public function configure($config);
	
	public function get($key, $expire = 60);
	
	public function set($key, $value, $expire = 60);
	
	public function del($key);
	
	public function mget(array $keys);
	
	public function mset(array $values, $expire = 60);
	
	public function mdel(array $keys);
}