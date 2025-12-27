<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Check if index already exists before creating
        $indexExists = DB::select("PRAGMA index_list(tasks)");
        $hasUserIdIndex = collect($indexExists)->contains(function ($index) {
            return str_contains($index->name, 'user_id');
        });
        
        if (!$hasUserIdIndex) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->index('user_id');
            });
        }
    }

    public function down()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });
    }
};