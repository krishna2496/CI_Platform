<?php
namespace App\Repositories\VolunteeringAttribute;

use App\Repositories\VolunteeringAttribute\VolunteeringAttributeInterface;
use Illuminate\Http\Request;
use App\Models\VolunteeringAttribute;
use App\Traits\RestExceptionHandlerTrait;

class VolunteeringAttributeRepository implements VolunteeringAttributeInterface
{
    use RestExceptionHandlerTrait;
    /**
     * @var App\Models\VolunteeringAttribute
     */
    public $volunteeringAttribute;
    
    /**
     * Create a new volunteering attribute repository instance.
     *
     * @param  App\Models\VolunteeringAttribute $volunteeringAttribute
     * @return void
     */
    public function __construct(VolunteeringAttribute $volunteeringAttribute)
    {
        $this->volunteeringAttribute = $volunteeringAttribute;
    }
}
