<?php

namespace App\Http\Controllers\App\Mission;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\Helpers;
use App\Models\Mission;
use Illuminate\View\View;
use App\Repositories\Mission\MissionRepository;
use App\Exceptions\TenantDomainNotFoundException;

//!  Mission social sharing controller
/*!
This controller is responsible for handling mission social sharing set metadata operation.
 */
class MissionSocialSharingController extends Controller
{
    
    /**
     * @var App\Helpers\Helpers
     */
    private $helpers;

    /**
     * @var App\Repositories\Mission\MissionRepository
     */
    private $missionRepository;

    /**
     * Create a new controller instance.
     *
     * @param  App\Helpers\Helpers $helpers
     * @param App\Repositories\Mission\MissionRepository $missionRepository
     * @return void
     */
    public function __construct(Helpers $helpers, MissionRepository $missionRepository)
    {
        $this->helpers = $helpers;
        $this->missionRepository = $missionRepository;
    }

    /**
     * Set meta data for social sharing page
     *
     * @param  string $fqdn
     * @param  int $missionId
     * @param  int $langId
     * @return Illuminate\View\View
     */
    public function setMetaData(string $fqdn, int $missionId, int $langId): View
    {
        try {
            // Need to get tenant id from tenant name
            $tenant = $this->helpers->getTenantDetailsFromName($fqdn);
        } catch (TenantDomainNotFoundException $e) {
            throw $e;
        }
        
        // Get mission details from mission id
        $mission = $this->missionRepository->getMissionDetailsFromId($missionId, $langId);
        
        return view('social-share', compact('mission', 'fqdn', 'missionId', 'langId'));
    }
}
