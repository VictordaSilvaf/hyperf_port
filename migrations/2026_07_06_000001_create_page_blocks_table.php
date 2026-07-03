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
        Schema::create('page_blocks', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('page_id', 36);
            $table->string('type', 64);
            $table->unsignedInteger('sort_order')->default(0);
            $table->jsonb('payload');
            $table->jsonb('settings')->nullable();
            $table->datetimes();
            $table->foreign('page_id')->references('id')->on('pages')->cascadeOnDelete();
            $table->index(['page_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_blocks');
    }
};
