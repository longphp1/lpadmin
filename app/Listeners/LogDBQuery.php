<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


class LogDBQuery
{
// 慢查询阈值(毫秒)
    protected const SLOW_QUERY_THRESHOLD = 500;

    /**
     * 处理查询事件
     */
    public function handle(QueryExecuted $event): void
    {
        // 只在本地和测试环境记录
        if(!App::environment(['local', 'testing'])){
            return;
        }

        // 随机抽取 10% 的查询日志
        /*if(random_int(1, 100) > 10){
            return;
        }*/

        $context = $this->buildLogContext($event);

        if($event->time > self::SLOW_QUERY_THRESHOLD){
            Log::channel('sqllog')->warning("Slow query detected", $context);
        }else{
            Log::channel('sqllog')->debug("Query executed", $context);
        }
    }

    /**
     * 格式化 SQL 语句
     */
    protected function formatSql(string $sql, array $bindings): string
    {
        if(empty($bindings)){
            return $sql;
        }

        $escapedBindings = array_map(fn($value) => $this->escapeBinding($value), $bindings);

        return Str::replaceArray('?', $escapedBindings, $sql);
    }

    /**
     * 转义绑定参数
     */
    protected function escapeBinding(mixed $value): string
    {
        if(is_null($value)){
            return 'NULL';
        }

        if(is_bool($value)){
            return $value ? '1' : '0';
        }

        if(is_numeric($value)){
            return (string)$value;
        }

        return "'" . addslashes((string)$value) . "'";
    }

    /**
     * 构建日志上下文
     */
    protected function buildLogContext(QueryExecuted $event): array
    {
        return [
            'sql' => $this->formatSql($event->sql, $event->bindings),
            'time' => $event->time . 'ms',
            'connection' => $event->connectionName,
            'bindings' => $event->bindings,
            'raw_sql' => $event->sql,
        ];
    }
}
