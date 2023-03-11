<?php
namespace App\Repositories\Slider;

use App\Repositories\Slider\SliderInterface;
use App\Models\Slider;
use Illuminate\Database\Eloquent\Collection;

class SliderRepository implements SliderInterface
{
    /**
     * @var App\Models\Slider
     */
    public $slider;

    /**
     * Create a new repository instance.
     *
     * @param App\Models\Slider $slider
     * @return void
     */
    public function __construct(Slider $slider)
    {
        $this->slider = $slider;
    }
    
    /**
    * Get a count of slider.
    *
    * @return int
    */
    public function getAllSliderCount(): ?int
    {
        return $this->slider->count();
    }
    
    /**
     * Store tenant slider data.
     *
     * @param  array $data
     * @return App\Models\Slider
     */
    public function storeSlider(array $data): Slider
    {
        return $this->slider->create($data);
    }
    
    /**
     * Update tenant slider data.
     *
     * @param array $data
     * @param int $id
     * @return bool
     */
    public function updateSlider(array $data, int $id): bool
    {
        $slider = $this->slider->findOrFail($id);
        return $slider->update($data);
    }

    /**
     * Get tenant sliders
     *
     * @return Illuminate\Database\Eloquent\Collection;
     */
    public function getSliders(): Collection
    {
        return $this->slider->orderBy('sort_order')->get();
    }

    /**
     * Delete Slider
     *
     * @param  int  $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->slider->deleteSlider($id);
    }

    /**
     * Get tenant sliders
     *
     * @return array;
     */
    public function getAllSliders(): array
    {
        return $this->slider->select('url', 'translations', 'sort_order')->orderBy('sort_order')->get()->toArray();
    }

    /**
     * Find Slider
     *
     * @param  int  $id
     * @return App\Models\Slider
     */
    public function find(int $id): Slider
    {
        return $this->slider->findOrFail($id);
    }
}
