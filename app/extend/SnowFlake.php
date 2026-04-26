<?php
/**
 * 雪花ID生成器 - 兼容TiDB的严格递增ID需求
 * TiDB的AUTO_INCREMENT不是严格递增的，使用雪花算法替代
 */

namespace app\extend;

class SnowFlake
{
    private static $instance = null;
    
    // 开始时间戳 (2020-01-01)
    const EPOCH = 1577836800000;
    
    // 每位占用的位数
    private $timeStampBits = 41;
    private $workerIdBits = 5;
    private $sequenceBits = 12;
    
    // 每位最大值
    private $maxWorkerId = -1 ^ (-1 << 5);  // 31
    private $maxSequence = -1 ^ (-1 << 12); // 4095
    
    // 位移计算
    private $workerIdShift;
    private $timestampLeftShift;
    
    // 机器ID和序列号
    private $workerId;
    private $sequence = 0;
    private $lastTimestamp = -1;
    
    private function __construct($workerId = 1)
    {
        if ($workerId > $this->maxWorkerId || $workerId < 0) {
            $workerId = 1;
        }
        $this->workerId = $workerId;
        
        $this->workerIdShift = $this->sequenceBits;
        $this->timestampLeftShift = $this->workerIdBits + $this->sequenceBits;
    }
    
    public static function getInstance($workerId = 1)
    {
        if (self::$instance === null) {
            self::$instance = new self($workerId);
        }
        return self::$instance;
    }
    
    /**
     * 生成雪花ID
     * @return int
     */
    public function nextId(): int
    {
        $timestamp = $this->timeGen();
        
        if ($timestamp === $this->lastTimestamp) {
            $this->sequence = ($this->sequence + 1) & $this->maxSequence;
            if ($this->sequence === 0) {
                $timestamp = $this->waitNextMillis($this->lastTimestamp);
            }
        } elseif ($timestamp > $this->lastTimestamp) {
            $this->sequence = 0;
        }
        
        $this->lastTimestamp = $timestamp;
        
        return (($timestamp - self::EPOCH) << $this->timestampLeftShift)
            | ($this->workerId << $this->workerIdShift)
            | $this->sequence;
    }
    
    /**
     * 获取当前毫秒时间戳
     * @return int
     */
    private function timeGen(): int
    {
        $t = floor(microtime(true) * 1000);
        return $t;
    }
    
    /**
     * 等待下一毫秒
     * @param int $lastTimestamp
     * @return int
     */
    private function waitNextMillis(int $lastTimestamp): int
    {
        $timestamp = $this->timeGen();
        while ($timestamp <= $lastTimestamp) {
            $timestamp = $this->timeGen();
        }
        return $timestamp;
    }
    
    /**
     * 从雪花ID解析时间戳
     * @param int $snowFlakeId
     * @return int
     */
    public static function getTimestamp(int $snowFlakeId): int
    {
        return ($snowFlakeId >> 42) + self::EPOCH;
    }
    
    /**
     * 从雪花ID解析WorkerID
     * @param int $snowFlakeId
     * @return int
     */
    public static function getWorkerId(int $snowFlakeId): int
    {
        return ($snowFlakeId >> 12) & 31;
    }
}
