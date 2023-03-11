<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUniqueIndexToTenantSetting extends Migration
{
    const TENANT_SETTING_TABLE = 'tenant_setting';
    const TENANT_UNIQUE_COLUMN = 'key';

    public function up()
    {
        DB::table(self::TENANT_SETTING_TABLE)
            ->whereNotNull('deleted_at')
            ->delete();
        Schema::table(self::TENANT_SETTING_TABLE,
            function (Blueprint $table) {
                $table->unique(self::TENANT_UNIQUE_COLUMN);
            });
    }

    public function down()
    {
        Schema::table(self::TENANT_SETTING_TABLE,
            function (Blueprint $table) {
                $table->dropUnique($this->getUniqueIndexName(self::TENANT_UNIQUE_COLUMN));
            });
    }

    private function getUniqueIndexName(string $columnName): string
    {
        return sprintf('%s_%s_unique', self::TENANT_SETTING_TABLE, $columnName);
    }
}
