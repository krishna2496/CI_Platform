<?php
namespace App\Helpers;

class ExportCSV
{
    /**
     * @var string
     */
    protected $fileName;

    /**
     * @var array
     */
    protected $headLines = [];

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var string
     */
    protected $path;

    /**
     * Create a new class instance.
     *
     * @param string $fileName
     * @return void
     */
    public function __construct(string $fileName)
    {
        $this->fileName = $fileName;
    }
    
    /**
     * Set headLine variable
     *
     * @param array $headLines
     * @return void
     */
    public function setHeadlines(array $headLines)
    {
        $this->headLines = $headLines;
    }

    /**
     * Push row into, data variable
     *
     * @param array $row
     * @return void
     */
    public function appendRow(array $row)
    {
        array_push($this->data, $row);
    }

    /**
     * Write and store file on given path
     *
     * @param string $path
     * @return string
     */
    public function export(string $path): string
    {
        $this->path = str_replace("\\", "/", \storage_path($path));

        // Make directory
        @mkdir(\storage_path($path), 0755, true);

        // Create and open file from location
        $csv = fopen($this->path.'/'.$this->fileName, 'w');

        // Add Headings in file
        fputcsv($csv, $this->headLines);

        // Write rows into file
        foreach ($this->data as $row) {
            fputcsv($csv, $row);
        }

        fclose($csv);

        return (\file_exists($this->path)) ? $this->path.'/'.$this->fileName : '';
    }
}
