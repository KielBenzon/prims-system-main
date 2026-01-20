<?php

namespace App\Export;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BaptismalCertificate implements FromCollection, WithHeadings
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return [$this->data];
    }

    public function headings(): array
    {
        return [
            'Name of Child',
            'Date of Birth',
            'Place of Birth',
            'Date of Baptism',
            'Name of Father',
            'Name of Mother',
        ];
    }
}
