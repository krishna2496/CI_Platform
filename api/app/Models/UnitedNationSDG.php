<?php
namespace App\Models;
 
final class UnitedNationSDG{
    
    //The number of UN SDG.
    public $number;
    
    //The UN SDG
    public $unSdg;

    /**
     * Create a new United Nation SDG instance.
     *
     * @param int $number
     * @param string $unSdg
     * @return void
     */

    public function __construct(int $number, string $unSdg)
    {
        $this->number = $number;
        $this->unSdg = $unSdg;
    }

    /**
     * Get number
     *
     * @return int $number
     */
    public function number(): int
    {
        return $this->number;
    }
    
    /**
     * Get UN SDG
     *
     * @return string $unSdg
     */
    public function unSdg(): string
    {
        return $this->unSdg;
    }
    
}