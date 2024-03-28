<?php

namespace zhqing\module;

use zhqing\extend\Frame;

class RedisHelper {

    public $redis;

    public function __construct($redis) {
        $this->redis = $redis;
    }

    /**
     * 发送数据到redis,配合 receiveData 使用 ,可使用 del 完全删除此key
     * @param string $key 设置key
     * @param int $time 设置时间
     * @param string $data 设置数据
     * @return mixed
     */
    public function sendData(string $key, int $time, string $data): mixed {
        return $this->zAdd($key, $time, $data);
    }

    /**
     * 接收 sendData 的数据，达到sendData设置的时间会接收到,配合 zRem 删除此条信息
     * @param string $key
     * @param int $time
     * @return mixed
     */
    public function receiveData(string $key, int $time): mixed {
        return $this->zRangeByScore($key, '-inf', $time);
    }

    public function del($key) {
        return $this->call('del', [$key]);
    }

    public function zAdd($key, $options, $score1, $value1 = null, $score2 = null, $value2 = null, $scoreN = null, $valueN = null) {
        return $this->call('zAdd', [$key, $options, $score1, $value1, $score2, $value2, $scoreN, $valueN]);
    }

    public function zRangeByScore($key, $start, $end, array $options = []) {
        return $this->call('del', [$key, $start, $end, $options]);
    }

    public function zRem($key, $member1, ...$otherMembers) {
        return $this->call('zRem', [$key, $member1, $otherMembers]);
    }

    public function call(string $method, array $parameter) {
        return call_user_func_array([$this->redis, $method], $parameter);
    }
}