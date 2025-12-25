<?php
require_once 'vendor/autoload.php';
require_once 'core/Database.php';

class ReportPDF extends TCPDF {
    
    public function Header() {
        // Logo
        $this->SetFont('helvetica', 'B', 20);
        $this->SetTextColor(59, 130, 246);
        $this->Cell(0, 15, 'SANIPOINT', 0, 1, 'C');
        
        $this->SetFont('helvetica', '', 12);
        $this->SetTextColor(107, 114, 128);
        $this->Cell(0, 10, 'Laporan Performa Karyawan - IoT Bathroom Monitoring System', 0, 1, 'C');
        
        $this->SetFont('helvetica', '', 10);
        $this->Cell(0, 8, 'Tanggal: ' . date('d F Y'), 0, 1, 'C');
        
        $this->Ln(10);
    }
    
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->SetTextColor(107, 114, 128);
        $this->Cell(0, 10, 'Halaman ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, 0, 'C');
    }
}

class PDFReportGenerator {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function generateEmployeeReport() {
        // Get data
        $employee_performance = $this->getEmployeePerformance();
        $cleaning_stats = $this->getCleaningStats();
        
        // Create PDF
        $pdf = new ReportPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator('SANIPOINT System');
        $pdf->SetAuthor('Admin');
        $pdf->SetTitle('Laporan Performa Karyawan');
        $pdf->SetSubject('Employee Performance Report');
        
        // Set margins
        $pdf->SetMargins(15, 30, 15);
        $pdf->SetHeaderMargin(5);
        $pdf->SetFooterMargin(10);
        
        // Set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 25);
        
        // Add a page
        $pdf->AddPage();
        
        // Summary Section
        $this->addSummarySection($pdf, $employee_performance, $cleaning_stats);
        
        // Employee Performance Table
        $this->addEmployeeTable($pdf, $employee_performance);
        
        // Statistics Section
        $this->addStatisticsSection($pdf, $cleaning_stats);
        
        return $pdf;
    }
    
    private function addSummarySection($pdf, $employee_performance, $cleaning_stats) {
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetTextColor(31, 41, 55);
        $pdf->Cell(0, 10, 'RINGKASAN PERFORMA', 0, 1, 'L');
        $pdf->Ln(5);
        
        // Summary cards
        $total_cleanings = array_sum(array_column($cleaning_stats, 'total_cleanings'));
        $total_points = array_sum(array_column($cleaning_stats, 'total_points'));
        $avg_duration = round(array_sum(array_column($cleaning_stats, 'avg_duration')) / max(count($cleaning_stats), 1));
        $active_employees = count($employee_performance);
        
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetFillColor(239, 246, 255);
        
        // Create summary table
        $html = '
        <table border="1" cellpadding="8" cellspacing="0">
            <tr style="background-color:#EFF6FF;">
                <td width="25%" align="center"><b>Total Pembersihan</b><br/><span style="font-size:16px;color:#2563EB;">' . $total_cleanings . '</span></td>
                <td width="25%" align="center"><b>Total Poin</b><br/><span style="font-size:16px;color:#059669;">' . number_format($total_points) . '</span></td>
                <td width="25%" align="center"><b>Rata-rata Durasi</b><br/><span style="font-size:16px;color:#D97706;">' . $avg_duration . ' menit</span></td>
                <td width="25%" align="center"><b>Karyawan Aktif</b><br/><span style="font-size:16px;color:#7C3AED;">' . $active_employees . '</span></td>
            </tr>
        </table>';
        
        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Ln(10);
    }
    
    private function addEmployeeTable($pdf, $employee_performance) {
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetTextColor(31, 41, 55);
        $pdf->Cell(0, 10, 'PERFORMA KARYAWAN', 0, 1, 'L');
        $pdf->Ln(5);
        
        // Sort by total cleanings
        usort($employee_performance, function($a, $b) {
            return ($b['total_cleanings'] ?? 0) - ($a['total_cleanings'] ?? 0);
        });
        
        $html = '
        <table border="1" cellpadding="6" cellspacing="0">
            <thead>
                <tr style="background-color:#F3F4F6;">
                    <th width="8%" align="center"><b>Rank</b></th>
                    <th width="25%"><b>Nama Karyawan</b></th>
                    <th width="15%" align="center"><b>Kode</b></th>
                    <th width="13%" align="center"><b>Pembersihan</b></th>
                    <th width="13%" align="center"><b>Durasi Avg</b></th>
                    <th width="13%" align="center"><b>Poin Earned</b></th>
                    <th width="13%" align="center"><b>Saldo Poin</b></th>
                </tr>
            </thead>
            <tbody>';
        
        foreach ($employee_performance as $index => $employee) {
            $rank = $index + 1;
            $trophy = $rank <= 3 ? '🏆' : $rank;
            
            $html .= '
                <tr>
                    <td align="center">' . $trophy . '</td>
                    <td>' . htmlspecialchars($employee['full_name']) . '</td>
                    <td align="center">' . htmlspecialchars($employee['employee_code']) . '</td>
                    <td align="center">' . ($employee['total_cleanings'] ?? 0) . '</td>
                    <td align="center">' . round($employee['avg_duration'] ?? 0) . ' min</td>
                    <td align="center">' . number_format($employee['total_points_earned'] ?? 0) . '</td>
                    <td align="center">' . number_format($employee['current_balance'] ?? 0) . '</td>
                </tr>';
        }
        
        $html .= '</tbody></table>';
        
        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Ln(10);
    }
    
    private function addStatisticsSection($pdf, $cleaning_stats) {
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetTextColor(31, 41, 55);
        $pdf->Cell(0, 10, 'STATISTIK HARIAN', 0, 1, 'L');
        $pdf->Ln(5);
        
        $html = '
        <table border="1" cellpadding="6" cellspacing="0">
            <thead>
                <tr style="background-color:#F3F4F6;">
                    <th width="25%"><b>Tanggal</b></th>
                    <th width="25%" align="center"><b>Total Pembersihan</b></th>
                    <th width="25%" align="center"><b>Rata-rata Durasi</b></th>
                    <th width="25%" align="center"><b>Total Poin</b></th>
                </tr>
            </thead>
            <tbody>';
        
        foreach (array_slice($cleaning_stats, 0, 10) as $stat) {
            $html .= '
                <tr>
                    <td>' . date('d F Y', strtotime($stat['date'])) . '</td>
                    <td align="center">' . ($stat['total_cleanings'] ?? 0) . '</td>
                    <td align="center">' . round($stat['avg_duration'] ?? 0) . ' menit</td>
                    <td align="center">' . number_format($stat['total_points'] ?? 0) . '</td>
                </tr>';
        }
        
        $html .= '</tbody></table>';
        
        $pdf->writeHTML($html, true, false, true, false, '');
    }
    
    private function getEmployeePerformance() {
        $query = "
            SELECT 
                u.id,
                u.full_name,
                u.employee_code,
                COUNT(cl.id) as total_cleanings,
                AVG(cl.duration_minutes) as avg_duration,
                SUM(cl.points_earned) as total_points_earned,
                p.current_balance
            FROM users u
            LEFT JOIN cleaning_logs cl ON u.id = cl.user_id AND cl.status = 'completed'
            LEFT JOIN points p ON u.id = p.user_id
            WHERE u.role = 'karyawan' AND u.is_active = 1
            GROUP BY u.id
            ORDER BY total_cleanings DESC
        ";
        
        return $this->db->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function getCleaningStats() {
        $query = "
            SELECT 
                DATE(created_at) as date,
                COUNT(*) as total_cleanings,
                AVG(duration_minutes) as avg_duration,
                SUM(points_earned) as total_points
            FROM cleaning_logs 
            WHERE status = 'completed' 
                AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY DATE(created_at)
            ORDER BY date DESC
        ";
        
        return $this->db->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }
}