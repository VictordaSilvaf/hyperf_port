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
        Schema::table('projects', function (Blueprint $table) {
            $table->longText('content')->nullable()->after('description');
            $table->string('repository_url', 500)->nullable()->after('content');
            $table->string('demo_url', 500)->nullable()->after('repository_url');
            $table->string('thumbnail_path', 500)->nullable()->after('image_path');
            $table->string('cover_path', 500)->nullable()->after('thumbnail_path');
            $table->boolean('featured')->default(false)->after('status');
            $table->timestamp('published_at')->nullable()->after('featured');
            $table->unsignedBigInteger('views')->default(0)->after('published_at');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn([
                'content',
                'repository_url',
                'demo_url',
                'thumbnail_path',
                'cover_path',
                'featured',
                'published_at',
                'views',
            ]);
        });
    }
};
