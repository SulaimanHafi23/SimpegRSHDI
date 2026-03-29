<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function foreignKeyExists(string $table, string $constraintName): bool
    {
        $database = DB::getDatabaseName();

        $result = DB::selectOne(
            "SELECT COUNT(*) AS total
             FROM information_schema.TABLE_CONSTRAINTS
             WHERE CONSTRAINT_SCHEMA = ?
               AND TABLE_NAME = ?
               AND CONSTRAINT_NAME = ?
               AND CONSTRAINT_TYPE = 'FOREIGN KEY'",
            [$database, $table, $constraintName]
        );

        return ((int) ($result->total ?? 0)) > 0;
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('workers', function (Blueprint $table) {
            if (!Schema::hasColumn('workers', 'gender')) {
                $table->enum('gender', ['Laki-laki', 'Perempuan'])->nullable()->after('birth_place');
            }

            if (!Schema::hasColumn('workers', 'religion')) {
                $table->enum('religion', ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'])->nullable()->after('gender');
            }
        });

        if (Schema::hasColumn('workers', 'gender_id')) {
            DB::statement("UPDATE workers w LEFT JOIN genders g ON g.id = w.gender_id SET w.gender = COALESCE(g.name, 'Laki-laki') WHERE w.gender IS NULL");
        } else {
            DB::table('workers')->whereNull('gender')->update(['gender' => 'Laki-laki']);
        }

        if (Schema::hasColumn('workers', 'religion_id')) {
            DB::statement("UPDATE workers w LEFT JOIN religions r ON r.id = w.religion_id SET w.religion = COALESCE(r.name, 'Islam') WHERE w.religion IS NULL");
        } else {
            DB::table('workers')->whereNull('religion')->update(['religion' => 'Islam']);
        }

        if (Schema::hasColumn('workers', 'gender_id')) {
            if ($this->foreignKeyExists('workers', 'workers_gender_id_foreign')) {
                Schema::table('workers', function (Blueprint $table) {
                    $table->dropForeign('workers_gender_id_foreign');
                });
            }

            Schema::table('workers', function (Blueprint $table) {
                $table->dropColumn('gender_id');
            });
        }

        if (Schema::hasColumn('workers', 'religion_id')) {
            if ($this->foreignKeyExists('workers', 'workers_religion_id_foreign')) {
                Schema::table('workers', function (Blueprint $table) {
                    $table->dropForeign('workers_religion_id_foreign');
                });
            }

            Schema::table('workers', function (Blueprint $table) {
                $table->dropColumn('religion_id');
            });
        }

        DB::table('workers')->whereNull('gender')->update(['gender' => 'Laki-laki']);
        DB::table('workers')->whereNull('religion')->update(['religion' => 'Islam']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workers', function (Blueprint $table) {
            if (!Schema::hasColumn('workers', 'gender_id')) {
                $table->uuid('gender_id')->nullable()->after('birth_place');
            }

            if (!Schema::hasColumn('workers', 'religion_id')) {
                $table->uuid('religion_id')->nullable()->after('gender_id');
            }
        });

        Schema::table('workers', function (Blueprint $table) {
            if (Schema::hasColumn('workers', 'gender')) {
                $table->dropColumn('gender');
            }

            if (Schema::hasColumn('workers', 'religion')) {
                $table->dropColumn('religion');
            }
        });
    }
};
