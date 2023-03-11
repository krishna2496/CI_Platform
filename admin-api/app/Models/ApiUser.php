<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Hash;
use App\Models\Tenant;

class ApiUser extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'api_user';
    
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'api_user_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['tenant_id','api_key','api_secret','status'];
    
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at','updated_at','deleted_at'];
    

    /**
    * Getter method to retrun decode value of api_key
    * @param  string $apiKey
    * @return string
    */
    public function getApiKeyAttribute(string $apiKey): string
    {
        return base64_decode($apiKey);
    }

    /**
     * Setter method to set api_key as in base64 format
     *
     * @param  string $apiKey
     * @return string
     */
    public function setApiKeyAttribute(string $apiKey): string
    {
        return $this->attributes['api_key'] = base64_encode($apiKey);
    }

    /**
     * Setter method to set api_secret as in hashed format
     *
     * @param  string $apiSecret
     * @return string
     */
    public function setApiSecretAttribute(string $apiSecret): string
    {
        return  $this->attributes['api_secret'] = Hash::make($apiSecret);
    }
}
