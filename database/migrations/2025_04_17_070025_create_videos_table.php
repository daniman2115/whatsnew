<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');     
            $table->string('title', 255);       
            $table->string('name', 255);       
            $table->text('description')->nullable();
            $table->string('url', 255)->nullable();
            $table->string('path', 255)->nullable();
            $table->string('file')->after('name');
            $table->string('thumbnail_url', 255)->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->boolean('is_premium')->default(false);
            $table->float('credibility_score')->nullable();
            $table->timestamp('last_credibility_check')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('allow_likes')->default(true);
            $table->boolean('allow_comments')->default(true);
            $table->boolean('allow_shares')->default(true);

            $table->unsignedBigInteger('like_count')->default(0);
            $table->unsignedBigInteger('comment_count')->default(0);
            $table->unsignedBigInteger('share_count')->default(0);
            $table->unsignedBigInteger('view_count')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
