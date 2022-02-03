<?php

namespace ForestAdmin\LaravelForestAdmin\Exports;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Excel;

class CollectionExport implements FromCollection, WithHeadings, Responsable
{
    use Exportable;

    /**
     * @var Collection $collection
     */
    private Collection $collection;

    /**
     * @var string
     */
    private string $header;

    /**
     * @var string
     */
    private string $fileName;

    /**
     * Optional Writer Type
     */
    private string $writerType = Excel::CSV;

    /**
     * Optional headers
     */
    private array $headers = [
        'Content-Type' => 'text/csv',
    ];


    /**
     * @param Collection $collection
     * @param string     $fileName
     * @param string     $header
     */
    public function __construct(Collection $collection, string $fileName, string $header)
    {
        $this->collection = $collection;
        $this->fileName = $fileName . '.csv';
        $this->header = $header;
    }

    /**
     * @return Collection|\Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->collection;
    }

    /**
     * @return string[]
     */
    public function headings(): array
    {
        return explode(',', $this->header);
    }
}
