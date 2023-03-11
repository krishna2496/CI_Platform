<?php

require_once(__DIR__.'/../OneShot.php');

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use optimy\console\OneShot;

class UpdateUserLanguageId extends OneShot
{
    /**
     * Start on shot script
     */
    public function start()
    {
        $tenants = $this->getTenantsDefaultLanguages();
        DB::transaction(function () use ($tenants) {
            $processed = [];
            foreach ($tenants as $tenant) {
                // Set connection for the tenant
                $this->connectTenantDb($tenant->tenant_id);
                $users = $this->getTenantUsersWithoutLanguage();
                if (!$users->isEmpty()) {
                    $userIds = $users->pluck('user_id')->toArray();
                    $updated = $this->updateUsersLanguage($userIds, $tenant->language_id);
                    if ($updated) {
                        $processed['success'][$tenant->tenant_id] = $userIds;
                    } else {
                        $processed['failed'][$tenant->tenant_id] = $userIds;
                    }
                }
            }
            $this->logProcess($processed);
        });
    }

    /**
     * Log script process datas
     *
     * @param array $processed
     *
     * @return void
     */
    private function logProcess(array $processed)
    {
        $this->writeLn();
        $this->writeLn('Done updating tenants users with empty language id.');
        $this->writeLn();
        foreach ($processed as $status => $items) {
            $this->writeLn('%s items:', ucfirst($status));
            foreach ($items as $tenantId => $userIds) {
                $this->writeLn('From TenantID %d: %d users processed.', $tenantId, count($userIds));
            }
        }
    }

    /**
     * Update users language per user ids
     *
     * @param array $ids
     * @param int $languageId
     *
     * @return bool
     */
    private function updateUsersLanguage(array $ids, $languageId): bool
    {
        return $this->getDbTable('user AS u')
            ->whereIn('u.user_id', $ids)
            ->update([
                'language_id' => $languageId
            ]);
    }

    /**
     * Get tenant users without language id
     *
     * @return Collection
     */
    private function getTenantUsersWithoutLanguage(): Collection
    {
        return $this->getDbTable('user AS u')
            ->selectRaw('
                u.user_id
            ')
            ->whereNull('u.language_id')
            ->whereNull('u.deleted_at')
            ->get();
    }

    /**
     * Get tenants default languages
     *
     * @return Collection
     */
    private function getTenantsDefaultLanguages(): Collection
    {
        return $this->getDbTable('tenant AS t')
            ->selectRaw('
                t.tenant_id,
                tl.language_id
            ')
            ->join('tenant_language AS tl', function ($join) {
                $join->on('tl.tenant_id', '=', 't.tenant_id')
                    ->where('tl.default', '1')
                    ->whereNull('tl.deleted_at');
            })
            ->whereNull('t.deleted_at')
            ->get();
    }
}

$cli = $app->make(UpdateUserLanguageId::class);
$cli->start();