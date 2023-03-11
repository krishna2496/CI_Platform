<?php
namespace App\Http\Controllers\App\Skill;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Helpers\LanguageHelper;
use App\Helpers\ResponseHelper;
use App\Repositories\Skill\SkillRepository;
use App\Traits\RestExceptionHandlerTrait;
use InvalidArgumentException;

//!  Skill controller
/*!
This controller is responsible for handling skill listing operation.
 */
class SkillController extends Controller
{
    use RestExceptionHandlerTrait;
    /**
     * @var App\Repositories\Skill\SkillRepository
     */
    private $skillRepository;

    /**
     * @var App\Helpers\ResponseHelper
     */
    private $responseHelper;

    /**
     * @var App\Helpers\LanguageHelper
     */
    private $languageHelper;

    /**
     * Create a new controller instance.
     *
     * @param App\Repositories\Skill\SkillRepository $skillRepository
     * @param App\Helpers\ResponseHelper $responseHelper
     * @param App\Helpers\LanguageHelper $languageHelper
     * @return void
     */
    public function __construct(
        SkillRepository $skillRepository,
        ResponseHelper $responseHelper,
        LanguageHelper $languageHelper
    ) {
        $this->skillRepository = $skillRepository;
        $this->responseHelper = $responseHelper;
        $this->languageHelper = $languageHelper;
    }

    /**
    * Get timezone list
    *
    * @param \Illuminate\Http\Request $request
    * @return Illuminate\Http\JsonResponse
    */
    public function index(Request $request) : JsonResponse
    {
        $language = $this->languageHelper->getLanguageDetails($request);
        $languageCode = $language->code;

        $skillList = $this->skillRepository->skillList($request);
        $allSkillData = [];

        if (!empty($skillList) && (isset($skillList))) {
            $returnData = [];
            foreach ($skillList as $key => $value) {
                if ($value) {
                    $arrayKey = array_search($languageCode, array_column($value['translations'], 'lang'));
                    if ($arrayKey !== '') {
                        $returnData[$value['skill_id']] = $value['translations'][$arrayKey]['title'];
                    }
                }
            }
            if (!empty($returnData)) {
                $allSkillData = $returnData;
            }
        }

        $apiData = $allSkillData;
        $apiStatus = Response::HTTP_OK;
        $apiMessage = (!empty($apiData)) ?
        trans('messages.success.MESSAGE_SKILL_LISTING') :
        trans('messages.success.MESSAGE_NO_SKILL_FOUND');
        return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
    }
}
