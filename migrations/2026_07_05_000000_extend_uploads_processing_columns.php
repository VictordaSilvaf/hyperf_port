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
        Schema::table('uploads', function (Blueprint $table) {
            $table->string('processing_status', 32)->default('skipped')->after('original_name');
            $table->string('webp_path', 500)->nullable()->after('processing_status');
            $table->string('webp_url', 500)->nullable()->after('webp_path');
            $table->string('thumbnail_path', 500)->nullable()->after('webp_url');
            $table->string('thumbnail_url', 500)->nullable()->after('thumbnail_path');
            $table->unsignedInteger('width')->nullable()->after('thumbnail_url');
            $table->unsignedInteger('height')->nullable()->after('width');
        });
    }

    public function down(): void
    {
        Schema::table('uploads', function (Blueprint $table) {
            $table->dropColumn([
                'processing_status',
                'webp_path',
                'webp_url',
                'thumbnail_path',
                'thumbnail_url',
                'width',
                'height',
            ]);
        });
    }
};
