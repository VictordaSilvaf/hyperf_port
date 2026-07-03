<?php

declare(strict_types=1);
/**
 * Hyperf API — DDD / Hexagonal
 *
 * @link     https://github.com/VictordaSilvaf/hyperf_port
 * @document https://github.com/VictordaSilvaf/hyperf_port/doc
 * @contact  victordasilvafernandes@gmail.com
 * @see      https://github.com/VictordaSilvaf/hyperf_port.git
 */
use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('name', 120);
            $table->string('slug', 120)->unique();
            $table->datetimes();
        });

        Schema::create('technologies', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('name', 120);
            $table->string('slug', 120)->unique();
            $table->datetimes();
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('name', 120);
            $table->string('slug', 120)->unique();
            $table->datetimes();
        });

        Schema::create('uploads', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('path', 500);
            $table->string('url', 500)->nullable();
            $table->string('mime_type', 120)->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->string('original_name', 255)->nullable();
            $table->datetimes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uploads');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('technologies');
        Schema::dropIfExists('categories');
    }
};
