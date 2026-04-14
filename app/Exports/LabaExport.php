<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class LabaExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $data;
    
    public function __construct($data)
    {
        $this->data = $data;
    }
    
    public function array(): array
    {
        $rows = [];
        
        // Header ringkasan
        $rows[] = ['LAPORAN LABA'];
        $rows[] = ['Periode', $this->data['periode']];
        $rows[] = ['Tanggal', $this->data['tanggal_mulai'] . ' - ' . $this->data['tanggal_selesai']];
        $rows[] = [];
        
        // Ringkasan
        $rows[] = ['RINGKASAN'];
        $rows[] = ['Total Pendapatan', 'Rp ' . number_format($this->data['total_pendapatan'], 0, ',', '.')];
        $rows[] = ['Total HPP', 'Rp ' . number_format($this->data['total_hpp'], 0, ',', '.')];
        $rows[] = ['Laba Bersih', 'Rp ' . number_format($this->data['laba_bersih'], 0, ',', '.')];
        $rows[] = [];
        
        // Detail per produk
        $rows[] = ['DETAIL LABA PER PRODUK'];
        $rows[] = ['No', 'Produk', 'Satuan', 'Terjual', 'Pendapatan', 'HPP', 'Laba'];
        
        $no = 1;
        foreach ($this->data['laba_per_produk'] as $item) {
            $rows[] = [
                $no++,
                $item['nama_produk'],
                $item['satuan'],
                $item['jumlah'],
                'Rp ' . number_format($item['pendapatan'], 0, ',', '.'),
                'Rp ' . number_format($item['hpp'], 0, ',', '.'),
                'Rp ' . number_format($item['laba'], 0, ',', '.'),
            ];
        }
        
        $rows[] = [];
        $rows[] = ['Dicetak pada: ' . $this->data['tanggal_export']];
        
        return $rows;
    }
    
    public function headings(): array
    {
        return [];
    }
    
    public function styles(Worksheet $sheet)
    {
        // Style untuk judul
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Style untuk ringkasan
        $sheet->getStyle('A5:G5')->getFont()->setBold(true);
        $sheet->getStyle('A5:G5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('E8F5D9');
        
        // Style untuk header tabel
        $sheet->getStyle('A9:G9')->getFont()->setBold(true);
        $sheet->getStyle('A9:G9')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('3A6B1A');
        $sheet->getStyle('A9:G9')->getFont()->getColor()->setARGB('FFFFFF');
        
        // Lebar kolom
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        return [];
    }
}