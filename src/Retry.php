<?php

class Retry
{
    private $delay;

    private $tries;

    private $exceptions;

    private $attempt = 0;

    /**
     * Retry constructor.
     * @param int $delay
     * @param int $tries
     */
    public function __construct(int $delay = 3, int $tries = 3)
    {
        $this->delay = $delay;
        $this->tries = $tries;
    }

    /**
     * 设置需要监听的异常
     * @param array $exceptions
     */
    public function setException(array $exceptions)
    {
        $this->exceptions = $exceptions;
    }

    /**
     * retry调用
     * @param callable $callable
     * @param $params
     */
    public function call(callable $callable, ...$params)
    {
        attempt: // Make an attempt to (re)try.

        $throwable = null;
        try {
            call_user_func($callable, ...$params);
        } catch (\Exception $e) {
            $throwable = $e;
        }

        if ($this->retryCriteriaValid($throwable)) {
            $this->beforeRetry();
            goto attempt;
        }
    }

    /**
     * retry之前 attempt + 1，delay
     */
    private function beforeRetry()
    {
        if ($this->delay) {
            sleep($this->delay);
        }
        $this->attempt++;
    }

    /**
     * 检测是否是要捕获的异常
     * @param $exception
     * @return bool
     */
    private function retryException($exception)
    {
        $exceptions = $this->exceptions;

        if (is_null($exceptions) or empty($exceptions)) return true;

        foreach ($exceptions as $e) {

            if (stripos($e, '\\') !== 0) {
                $e = '\\' . $e;
            }

            if ($exception instanceof $e) return true;
        }

        return false;
    }

    /**
     * 检测是否应该retry
     * @param $throwable
     * @return bool
     */
    protected function retryCriteriaValid($throwable)
    {
        if (is_null($throwable)) return false;
        if ($this->retryTriesLimit()) return false;

        $shouldRetry = $this->retryException($throwable);

        return $shouldRetry;
    }

    /**
     * 检测是否达到重试次数
     * @return bool
     */
    private function retryTriesLimit()
    {
        $retryTries = $this->tries;

        if ($retryTries === 0) {
            return true;
        } elseif ($retryTries > 0) {
            return ($this->attempt >= $retryTries);
        } else {
            return false;
        }
    }

}