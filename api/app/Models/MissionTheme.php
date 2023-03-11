<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Mission;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MissionTheme extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'mission_theme';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'mission_theme_id';

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = ['mission_theme_id', 'theme_name', 'translations', 'total_minutes'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['theme_name', 'translations'];

    /**
     * Set translations attribute on the model.
     *
     * @param  array $value
     * @return void
     */
    public function setTranslationsAttribute(array $value): void
    {
        $translations = [];
        foreach ($value as $translation) {
            $translations[$translation['lang']] = $translation['title'];
        }

        $this->attributes['translations'] = json_encode($translations,  JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Get an attribute from the model.
     *
     * @param  string $value
     * @return array
     */
    public function getTranslationsAttribute(string $value): array
    {
        $data = @json_decode($value);

        $trans = [];
        if ($data !== null) {
            $translations = json_decode($value, true);

            foreach ($translations as $lang => $title) {
                $trans[] = [
                    'lang' => $lang,
                    'title' => $title,
                ];
            }
        }
        return $trans;
    }

    /**
     * Find the specified resource.
     *
     * @param  int  $id
     * @return MissionTheme
     */
    public function findMissionTheme(int $id): MissionTheme
    {
        return static::findOrFail($id);
    }

    /**
     * Delete the specified resource.
     *
     * @param  int  $id
     * @return bool
     */
    public function deleteMissionTheme(int $id): bool
    {
        return static::findOrFail($id)->delete();
    }

    /**
     * Get the mission which belongs to mission theme
     *
     * @return void
     */
    public function mission()
    {
        return $this->belongsTo(Mission::class, 'mission_theme_id', 'theme_id');
    }
}
