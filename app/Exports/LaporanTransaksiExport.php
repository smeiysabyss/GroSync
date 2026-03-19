<?php

namespace App\Exports;

use App\Models\Transaksi;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

class LaporanTransaksiExport implements FromView, WithTitle, WithEvents, ShouldAutoSize
{
    protected $dari;
    protected $sampai;
    protected $status;
    protected $kasirId;
    protected int $totalRows;

    public function __construct($dari, $sampai, $status, $kasirId)
    {
        $this->dari    = $dari;
        $this->sampai  = $sampai;
        $this->status  = $status;
        $this->kasirId = $kasirId;
    }

    // ----------------------------------------------------------------
    // Query transaksi — sama persis dengan LaporanController
    // ----------------------------------------------------------------
    private function getTransaksis()
    {
        $query = Transaksi::with(['user', 'detail'])
            ->orderBy('created_at', 'desc');

        if ($this->dari) {
            $query->whereDate('created_at', '>=', $this->dari);
        }
        if ($this->sampai) {
            $query->whereDate('created_at', '<=', $this->sampai);
        }
        if ($this->status) {
            $query->where('status', $this->status);
        }
        if ($this->kasirId) {
            $query->where('id_users', $this->kasirId);
        }

        return $query->get();
    }

    // ----------------------------------------------------------------
    // Render via Blade view
    // ----------------------------------------------------------------
    public function view(): View
    {
        $transaksis       = $this->getTransaksis();
        $this->totalRows  = $transaksis->count();
        $totalPendapatan  = $transaksis->where('status', 'selesai')->sum('total');

        return view('exports.laporan-transaksi', [
            'transaksis'      => $transaksis,
            'totalPendapatan' => $totalPendapatan,
            'dari'            => $this->dari,
            'sampai'          => $this->sampai,
            'status'          => $this->status,
        ]);
    }

    public function title(): string
    {
        return 'Laporan Transaksi';
    }

    // ----------------------------------------------------------------
    // Styling sheet setelah dirender
    // ----------------------------------------------------------------
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet      = $event->sheet->getDelegate();
                $totalRows  = $this->totalRows;
                $lastRow    = $totalRows + 7; // baris data mulai di row 7

                // --- Judul (A1:J2) ---
                $sheet->mergeCells('A1:J1');
                $sheet->mergeCells('A2:J2');
                $sheet->mergeCells('A3:J3');

                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 14, 'name' => 'Arial', 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2D5A1B']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(30);

                $sheet->getStyle('A2:A3')->applyFromArray([
                    'font'      => ['size' => 10, 'name' => 'Arial', 'color' => ['rgb' => '4A7C2F']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0FCE8']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // --- Header kolom (row 5) ---
                $headerRange = 'A5:J5';
                $sheet->getStyle($headerRange)->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 10, 'name' => 'Arial', 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '3A6B1A']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders'   => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '2D5A1B']],
                    ],
                ]);
                $sheet->getRowDimension(5)->setRowHeight(22);

                // --- Baris data ---
                if ($totalRows > 0) {
                    $dataRange = 'A6:J' . $lastRow;
                    $sheet->getStyle($dataRange)->applyFromArray([
                        'font'    => ['size' => 9, 'name' => 'Arial'],
                        'borders' => [
                            'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E5E7EB']],
                        ],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                    ]);

                    // Zebra stripe
                    for ($r = 6; $r <= $lastRow; $r++) {
                        if ($r % 2 === 0) {
                            $sheet->getStyle("A{$r}:J{$r}")->applyFromArray([
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8FAF6']],
                            ]);
                        }
                    }

                    // Kolom Total (F) — bold
                    $sheet->getStyle("F6:F{$lastRow}")->getFont()->setBold(true);

                    // Kolom Status (I) — center
                    $sheet->getStyle("I6:I{$lastRow}")->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // Kolom # (A) dan Item (E) — center
                    $sheet->getStyle("A6:A{$lastRow}")->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("E6:E{$lastRow}")->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                // --- Baris ringkasan (setelah data) ---
                $summaryRow = $lastRow + 2;
                $sheet->getStyle("A{$summaryRow}:J{$summaryRow}")->applyFromArray([
                    'font'    => ['bold' => true, 'size' => 10, 'name' => 'Arial', 'color' => ['rgb' => 'FFFFFF']],
                    'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2D5A1B']],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '1E3D11']],
                    ],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension($summaryRow)->setRowHeight(20);

                // Freeze header
                $sheet->freezePane('A6');
            },
        ];
    }
}