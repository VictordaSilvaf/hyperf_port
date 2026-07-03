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
        Schema::create('pages', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('title', 200);
            $table->string('slug', 200)->unique();
            $table->string('status', 20)->default('draft');
            $table->string('layout', 32)->default('default');
            $table->jsonb('seo')->nullable();
            $table->boolean('is_home')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->softDeletes();
            $table->datetimes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
