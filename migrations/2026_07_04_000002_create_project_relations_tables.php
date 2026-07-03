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
        Schema::create('category_project', function (Blueprint $table) {
            $table->string('project_id', 36);
            $table->string('category_id', 36);
            $table->primary(['project_id', 'category_id']);
            $table->foreign('project_id')->references('id')->on('projects')->cascadeOnDelete();
            $table->foreign('category_id')->references('id')->on('categories')->cascadeOnDelete();
        });

        Schema::create('project_technology', function (Blueprint $table) {
            $table->string('project_id', 36);
            $table->string('technology_id', 36);
            $table->primary(['project_id', 'technology_id']);
            $table->foreign('project_id')->references('id')->on('projects')->cascadeOnDelete();
            $table->foreign('technology_id')->references('id')->on('technologies')->cascadeOnDelete();
        });

        Schema::create('project_tag', function (Blueprint $table) {
            $table->string('project_id', 36);
            $table->string('tag_id', 36);
            $table->primary(['project_id', 'tag_id']);
            $table->foreign('project_id')->references('id')->on('projects')->cascadeOnDelete();
            $table->foreign('tag_id')->references('id')->on('tags')->cascadeOnDelete();
        });

        Schema::create('project_images', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('project_id', 36);
            $table->string('upload_id', 36);
            $table->string('caption', 255)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->datetimes();
            $table->foreign('project_id')->references('id')->on('projects')->cascadeOnDelete();
            $table->foreign('upload_id')->references('id')->on('uploads')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_images');
        Schema::dropIfExists('project_tag');
        Schema::dropIfExists('project_technology');
        Schema::dropIfExists('category_project');
    }
};
