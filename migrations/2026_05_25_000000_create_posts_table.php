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
        Schema::create('posts', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('project_id', 36);
            $table->string('title', 200);
            $table->text('body');
            $table->string('status', 20)->default('draft');
            $table->foreign('project_id')->references('id')->on('projects')->cascadeOnDelete();
            $table->datetimes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
