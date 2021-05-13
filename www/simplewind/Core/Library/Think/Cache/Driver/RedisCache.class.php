<?php

/**
 * Redis操作类
 * @author dufeng
 *
 */
namespace Common\Utils;

class RedisCache {
	private static $redis = null;
	private $cache;
	/**
	 * 获取单例对象
	 */
	public static function getInstance() {
		if (self::$redis == null) {
			self::$redis = new RedisCache ();
		}
		return self::$redis;
	}
	/**
	 * 构造函数，仅供单例使用
	 */
	public function __construct() {
		$this->cache = new \Redis();
		$this->cache->connect ( C ( 'REDIS.REDISIP' ), C ( 'REDIS.REDISPORT' ) );
		$this->cache->auth ( C ( 'REDIS.PASSWORD' ) );
	}
	/**
	 * 判断当前Redis服务是否存活;
	 *
	 * @param ParentRedis $redis        	
	 * @return boolean
	 */
	public function isAlive($redis) {
		return true;
	}
	/**
	 * 设置值
	 *
	 * @param string $key
	 *        	键
	 * @param string $value
	 *        	值
	 * @param string $expireTime        	
	 */
	public function set($key, $val) {
		if (! $this->isAlive ( $this->cache )) {
			return false;
		}
		return $this->cache->set ( $key, json_encode ( $val ) ) && $this->cache->expire ( $key, C ( 'REDIS.EXPIRETIME' ) );
	}
	
	/**
	 * 设置过多少秒失效
	 * @param unknown $key
	 * @param unknown $time 单位秒
	 * @return boolean
	 */
	public function expire($key, $time) {
		if (! $this->isAlive ( $this->cache )) {
			return false;
		}
		return  $this->cache->expire ( $key, $time );
	}
	
	/**
	 * 通过KEY获取数据
	 *
	 * @param string $key
	 *        	KEY名称
	 */
	public function get($key) {
		if (! $this->isAlive ( $this->cache )) {
			return false;
		}
		return json_decode ( ($this->cache->get ( $key )), true );
	}
	
	/**
	 * 删除一条数据
	 *
	 * @param string $key
	 *        	KEY名称
	 */
	public function delete($key) {
		return $this->cache->del ( $key );
	}
	
	/**
	 * 清空数据
	 */
	public function flushAll() {
		return $this->cache->flushAll ();
	}
	/**
	 * 查询key是否存在
	 *
	 * @param string $key
	 *        	键名称
	 */
	public function exists($key) {
		return $this->cache->exists ( $key );
	}
	/**
	 * 获取所有键值
	 */
	public function keys() {
		return $this->cache->keys ( '*' );
	}
}
?>