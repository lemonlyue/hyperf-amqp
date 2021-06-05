<?php


use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;

class CreateTaskTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create(config('amqp.retry.task_table'), function (Blueprint $table) {
            $table->bigIncrements('id')->comment('主键');
            $table->string('key', 64)->unique()->comment('消息唯一key');
            $table->string('product_system', 32)->comment('消息生产的系统');
            $table->string('exchange', 32)->comment('交换机');
            $table->string('routing_key', 32)->comment('路由');
            $table->text('request_data')->comment('请求数据');
            $table->text('response_data')->comment('响应数据');
            $table->string('status', 30)->comment('状态');
            $table->unsignedTinyInteger('retry_count')->comment('重试次数');
            $table->timestamps();
            $table->index(['exchange', 'routing_key'], 'idx_exchange');
            $table->index(['created_at'], 'idx_created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('true');
    }
}