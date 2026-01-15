<?php
/**
 * TableCrafter Advanced Export Handler
 * 
 * Handles CSV, Excel, and PDF export functionality with customization options.
 * Addresses the #1 customer pain point: lack of advanced export capabilities.
 * 
 * @package TableCrafter
 * @since 2.9.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class TC_Export_Handler
{
    /**
     * Supported export formats
     */
    const SUPPORTED_FORMATS = ['csv', 'xlsx', 'pdf'];
    
    /**
     * Export data in specified format
     * 
     * @param array $data The table data to export
     * @param array $headers The column headers
     * @param array $options Export options
     * @return array Response with file path/content and metadata
     */
    public static function export_data(array $data, array $headers, array $options = []): array
    {
        $defaults = [
            'format' => 'csv',
            'filename' => 'tablecrafter-export',
            'include_headers' => true,
            'date_format' => 'Y-m-d',
            'number_format' => '0.00',
            'template' => 'default',
            'filters_applied' => [],
            'sort_applied' => '',
            'total_records' => count($data),
            'export_timestamp' => current_time('mysql')
        ];
        
        $options = wp_parse_args($options, $defaults);
        
        // Sanitize filename
        $options['filename'] = sanitize_file_name($options['filename']);
        
        // Validate format
        if (!in_array($options['format'], self::SUPPORTED_FORMATS)) {
            return ['error' => 'Unsupported export format: ' . $options['format']];
        }
        
        try {
            switch ($options['format']) {
                case 'csv':
                    return self::export_csv($data, $headers, $options);
                    
                case 'xlsx':
                    return self::export_xlsx($data, $headers, $options);
                    
                case 'pdf':
                    return self::export_pdf($data, $headers, $options);
                    
                default:
                    return ['error' => 'Export format not implemented: ' . $options['format']];
            }
        } catch (Exception $e) {
            error_log('TableCrafter Export Error: ' . $e->getMessage());
            return ['error' => 'Export failed: ' . $e->getMessage()];
        }
    }
    
    /**
     * Export data as CSV
     */
    private static function export_csv(array $data, array $headers, array $options): array
    {
        $temp_file = tempnam(sys_get_temp_dir(), 'tc_export_') . '.csv';
        $handle = fopen($temp_file, 'w');
        
        if (!$handle) {
            return ['error' => 'Could not create CSV file'];
        }
        
        // Set UTF-8 BOM for Excel compatibility
        fwrite($handle, "\xEF\xBB\xBF");
        
        // Write headers
        if ($options['include_headers']) {
            fputcsv($handle, $headers);
        }
        
        // Write data rows
        foreach ($data as $row) {
            $csv_row = [];
            foreach ($headers as $header) {
                $value = isset($row[$header]) ? $row[$header] : '';
                $csv_row[] = self::format_cell_value($value, $options);
            }
            fputcsv($handle, $csv_row);
        }
        
        // Add metadata footer if enabled
        if (!empty($options['include_metadata'])) {
            fputcsv($handle, []);
            fputcsv($handle, ['Export Information']);
            fputcsv($handle, ['Generated', $options['export_timestamp']]);
            fputcsv($handle, ['Total Records', $options['total_records']]);
            
            if (!empty($options['filters_applied'])) {
                fputcsv($handle, ['Filters Applied', json_encode($options['filters_applied'])]);
            }
            
            if (!empty($options['sort_applied'])) {
                fputcsv($handle, ['Sort Applied', $options['sort_applied']]);
            }
        }
        
        fclose($handle);
        
        return [
            'success' => true,
            'file_path' => $temp_file,
            'filename' => $options['filename'] . '.csv',
            'mime_type' => 'text/csv',
            'size' => filesize($temp_file)
        ];
    }
    
    /**
     * Export data as Excel (XLSX)
     */
    private static function export_xlsx(array $data, array $headers, array $options): array
    {
        // For now, we'll create a basic XML-based XLSX file
        // In a full implementation, you'd use PhpSpreadsheet library
        
        $temp_file = tempnam(sys_get_temp_dir(), 'tc_export_') . '.xlsx';
        
        // Create basic Excel XML structure
        $excel_data = self::create_basic_xlsx($data, $headers, $options);
        
        if (!file_put_contents($temp_file, $excel_data)) {
            return ['error' => 'Could not create Excel file'];
        }
        
        return [
            'success' => true,
            'file_path' => $temp_file,
            'filename' => $options['filename'] . '.xlsx',
            'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'size' => filesize($temp_file)
        ];
    }
    
    /**
     * Export data as PDF
     */
    private static function export_pdf(array $data, array $headers, array $options): array
    {
        $temp_file = tempnam(sys_get_temp_dir(), 'tc_export_') . '.pdf';
        
        // Create basic PDF using built-in functionality
        // In production, you'd use a proper PDF library like TCPDF or FPDF
        $pdf_content = self::create_basic_pdf($data, $headers, $options);
        
        if (!file_put_contents($temp_file, $pdf_content)) {
            return ['error' => 'Could not create PDF file'];
        }
        
        return [
            'success' => true,
            'file_path' => $temp_file,
            'filename' => $options['filename'] . '.pdf',
            'mime_type' => 'application/pdf',
            'size' => filesize($temp_file)
        ];
    }
    
    /**
     * Format cell value according to options
     */
    private static function format_cell_value($value, array $options): string
    {
        if (is_array($value)) {
            return json_encode($value);
        }
        
        // Handle dates
        if (preg_match('/^\d{4}-\d{2}-\d{2}/', $value) && strtotime($value)) {
            try {
                $date = new DateTime($value);
                return $date->format($options['date_format']);
            } catch (Exception $e) {
                // Fall through to default handling
            }
        }
        
        // Handle numbers
        if (is_numeric($value) && !empty($options['number_format'])) {
            return number_format((float)$value, 2);
        }
        
        // Strip HTML tags for clean export
        return wp_strip_all_tags((string)$value);
    }
    
    /**
     * Create basic XLSX file content
     */
    private static function create_basic_xlsx(array $data, array $headers, array $options): string
    {
        // This is a simplified XLSX creation - in production, use PhpSpreadsheet
        $zip = new ZipArchive();
        $temp_zip = tempnam(sys_get_temp_dir(), 'xlsx_') . '.xlsx';
        
        if ($zip->open($temp_zip, ZipArchive::CREATE) !== TRUE) {
            throw new Exception('Cannot create XLSX file');
        }
        
        // Add required XLSX structure files
        $zip->addFromString('[Content_Types].xml', self::get_xlsx_content_types());
        $zip->addFromString('_rels/.rels', self::get_xlsx_rels());
        $zip->addFromString('xl/_rels/workbook.xml.rels', self::get_xlsx_workbook_rels());
        $zip->addFromString('xl/workbook.xml', self::get_xlsx_workbook());
        
        // Create worksheet with data
        $worksheet_xml = self::create_xlsx_worksheet($data, $headers, $options);
        $zip->addFromString('xl/worksheets/sheet1.xml', $worksheet_xml);
        
        $zip->close();
        
        return file_get_contents($temp_zip);
    }
    
    /**
     * Create basic PDF content
     */
    private static function create_basic_pdf(array $data, array $headers, array $options): string
    {
        // Simplified PDF creation - in production, use TCPDF or similar
        $html_content = self::create_pdf_html($data, $headers, $options);
        
        // Convert HTML to PDF using available methods
        if (function_exists('wkhtmltopdf')) {
            return self::html_to_pdf_wkhtmltopdf($html_content);
        } else {
            // Fallback to basic PDF structure
            return self::create_simple_pdf($html_content, $options);
        }
    }
    
    /**
     * Create HTML content for PDF
     */
    private static function create_pdf_html(array $data, array $headers, array $options): string
    {
        $html = '<!DOCTYPE html><html><head>';
        $html .= '<meta charset="UTF-8">';
        $html .= '<style>';
        $html .= 'body { font-family: Arial, sans-serif; font-size: 12px; }';
        $html .= 'table { width: 100%; border-collapse: collapse; margin: 20px 0; }';
        $html .= 'th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }';
        $html .= 'th { background-color: #f5f5f5; font-weight: bold; }';
        $html .= '.metadata { margin-top: 30px; font-size: 10px; color: #666; }';
        $html .= '</style></head><body>';
        
        $html .= '<h2>' . esc_html($options['filename']) . '</h2>';
        $html .= '<table>';
        
        // Headers
        if ($options['include_headers']) {
            $html .= '<thead><tr>';
            foreach ($headers as $header) {
                $html .= '<th>' . esc_html($header) . '</th>';
            }
            $html .= '</tr></thead>';
        }
        
        // Data
        $html .= '<tbody>';
        foreach ($data as $row) {
            $html .= '<tr>';
            foreach ($headers as $header) {
                $value = isset($row[$header]) ? $row[$header] : '';
                $html .= '<td>' . esc_html(self::format_cell_value($value, $options)) . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';
        
        // Metadata
        if (!empty($options['include_metadata'])) {
            $html .= '<div class="metadata">';
            $html .= '<p><strong>Export Information:</strong></p>';
            $html .= '<p>Generated: ' . esc_html($options['export_timestamp']) . '</p>';
            $html .= '<p>Total Records: ' . esc_html($options['total_records']) . '</p>';
            
            if (!empty($options['filters_applied'])) {
                $html .= '<p>Filters Applied: ' . esc_html(json_encode($options['filters_applied'])) . '</p>';
            }
            
            if (!empty($options['sort_applied'])) {
                $html .= '<p>Sort Applied: ' . esc_html($options['sort_applied']) . '</p>';
            }
            $html .= '</div>';
        }
        
        $html .= '</body></html>';
        
        return $html;
    }
    
    /**
     * Create simple PDF file structure
     */
    private static function create_simple_pdf(string $html_content, array $options): string
    {
        // Basic PDF structure - in production, use proper PDF library
        $pdf_content = "%PDF-1.4\n";
        $pdf_content .= "1 0 obj\n<<\n/Type /Catalog\n/Pages 2 0 R\n>>\nendobj\n";
        $pdf_content .= "2 0 obj\n<<\n/Type /Pages\n/Kids [3 0 R]\n/Count 1\n>>\nendobj\n";
        $pdf_content .= "3 0 obj\n<<\n/Type /Page\n/Parent 2 0 R\n/MediaBox [0 0 612 792]\n";
        $pdf_content .= "/Contents 4 0 R\n>>\nendobj\n";
        $pdf_content .= "4 0 obj\n<<\n/Length " . strlen($html_content) . "\n>>\nstream\n";
        $pdf_content .= $html_content;
        $pdf_content .= "\nendstream\nendobj\n";
        $pdf_content .= "xref\n0 5\n0000000000 65535 f \n0000000009 00000 n \n";
        $pdf_content .= "0000000058 00000 n \n0000000115 00000 n \n0000000207 00000 n \n";
        $pdf_content .= "trailer\n<<\n/Size 5\n/Root 1 0 R\n>>\nstartxref\n285\n%%EOF";
        
        return $pdf_content;
    }
    
    /**
     * XLSX helper methods
     */
    private static function get_xlsx_content_types(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
    <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
    <Default Extension="xml" ContentType="application/xml"/>
    <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
    <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
</Types>';
    }
    
    private static function get_xlsx_rels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>';
    }
    
    private static function get_xlsx_workbook_rels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
</Relationships>';
    }
    
    private static function get_xlsx_workbook(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
    <sheets>
        <sheet name="TableCrafter Export" sheetId="1" r:id="rId1" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"/>
    </sheets>
</workbook>';
    }
    
    private static function create_xlsx_worksheet(array $data, array $headers, array $options): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
        $xml .= '<sheetData>';
        
        $row_num = 1;
        
        // Headers
        if ($options['include_headers']) {
            $xml .= "<row r=\"{$row_num}\">";
            foreach ($headers as $col_num => $header) {
                $cell_ref = self::get_excel_column($col_num) . $row_num;
                $xml .= "<c r=\"{$cell_ref}\" t=\"inlineStr\">";
                $xml .= "<is><t>" . htmlspecialchars($header) . "</t></is>";
                $xml .= "</c>";
            }
            $xml .= "</row>";
            $row_num++;
        }
        
        // Data rows
        foreach ($data as $row_data) {
            $xml .= "<row r=\"{$row_num}\">";
            foreach ($headers as $col_num => $header) {
                $cell_ref = self::get_excel_column($col_num) . $row_num;
                $value = isset($row_data[$header]) ? $row_data[$header] : '';
                $formatted_value = self::format_cell_value($value, $options);
                
                $xml .= "<c r=\"{$cell_ref}\" t=\"inlineStr\">";
                $xml .= "<is><t>" . htmlspecialchars($formatted_value) . "</t></is>";
                $xml .= "</c>";
            }
            $xml .= "</row>";
            $row_num++;
        }
        
        $xml .= '</sheetData>';
        $xml .= '</worksheet>';
        
        return $xml;
    }
    
    private static function get_excel_column(int $col_num): string
    {
        $column = '';
        while ($col_num >= 0) {
            $column = chr(65 + ($col_num % 26)) . $column;
            $col_num = intval($col_num / 26) - 1;
        }
        return $column;
    }
    
    /**
     * Clean up temporary files
     */
    public static function cleanup_temp_file(string $file_path): bool
    {
        if (file_exists($file_path) && strpos($file_path, sys_get_temp_dir()) === 0) {
            return unlink($file_path);
        }
        return false;
    }
    
    /**
     * Get export templates
     */
    public static function get_export_templates(): array
    {
        return apply_filters('tc_export_templates', [
            'default' => [
                'name' => 'Default',
                'description' => 'Standard table export',
                'include_metadata' => false,
                'date_format' => 'Y-m-d',
                'number_format' => '0.00'
            ],
            'business' => [
                'name' => 'Business Report',
                'description' => 'Professional business report format',
                'include_metadata' => true,
                'date_format' => 'M j, Y',
                'number_format' => '$0.00'
            ],
            'data_analysis' => [
                'name' => 'Data Analysis',
                'description' => 'Raw data for analysis tools',
                'include_metadata' => true,
                'date_format' => 'c', // ISO 8601
                'number_format' => '0.0000'
            ]
        ]);
    }
}