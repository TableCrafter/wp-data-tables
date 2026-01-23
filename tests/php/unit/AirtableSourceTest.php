<?php
/**
 * Unit Tests for TC_Airtable_Source
 *
 * @package TableCrafter\Tests
 */

class AirtableSourceTest extends PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Test normalize_records flattens fields correctly
     */
    public function test_normalize_records_flattens_fields(): void
    {
        $records = [
            [
                'id' => 'rec123',
                'createdTime' => '2024-01-15T10:30:00.000Z',
                'fields' => [
                    'Name' => 'John Doe',
                    'Email' => 'john@example.com',
                    'Status' => 'Active'
                ]
            ],
            [
                'id' => 'rec456',
                'fields' => [
                    'Name' => 'Jane Smith',
                    'Email' => 'jane@example.com',
                    'Status' => 'Pending'
                ]
            ]
        ];

        $result = TC_Airtable_Source::normalize_records($records);

        $this->assertCount(2, $result);
        $this->assertEquals('rec123', $result[0]['_airtable_id']);
        $this->assertEquals('John Doe', $result[0]['Name']);
        $this->assertEquals('john@example.com', $result[0]['Email']);
        $this->assertEquals('rec456', $result[1]['_airtable_id']);
        $this->assertEquals('Jane Smith', $result[1]['Name']);
    }

    /**
     * Test normalize_records handles empty records
     */
    public function test_normalize_records_empty_array(): void
    {
        $result = TC_Airtable_Source::normalize_records([]);
        $this->assertEmpty($result);
    }

    /**
     * Test normalize_records handles missing fields
     */
    public function test_normalize_records_missing_fields(): void
    {
        $records = [
            ['id' => 'rec123'],
            ['id' => 'rec456', 'fields' => null]
        ];

        $result = TC_Airtable_Source::normalize_records($records);

        $this->assertCount(2, $result);
        $this->assertEquals('rec123', $result[0]['_airtable_id']);
        $this->assertEquals('rec456', $result[1]['_airtable_id']);
    }

    /**
     * Test normalize_records handles array values (linked records)
     */
    public function test_normalize_records_flattens_array_values(): void
    {
        $records = [
            [
                'id' => 'rec123',
                'fields' => [
                    'Name' => 'Task 1',
                    'Assignees' => ['recUser1', 'recUser2'],
                    'Tags' => ['urgent', 'review']
                ]
            ]
        ];

        $result = TC_Airtable_Source::normalize_records($records);

        $this->assertEquals('recUser1, recUser2', $result[0]['Assignees']);
        $this->assertEquals('urgent, review', $result[0]['Tags']);
    }

    /**
     * Test normalize_records handles attachment URLs
     */
    public function test_normalize_records_extracts_attachment_url(): void
    {
        $records = [
            [
                'id' => 'rec123',
                'fields' => [
                    'Name' => 'Product 1',
                    'Image' => [
                        ['url' => 'https://example.com/image1.jpg', 'filename' => 'image1.jpg'],
                        ['url' => 'https://example.com/image2.jpg', 'filename' => 'image2.jpg']
                    ]
                ]
            ]
        ];

        $result = TC_Airtable_Source::normalize_records($records);

        // Should extract first attachment URL
        $this->assertEquals('https://example.com/image1.jpg', $result[0]['Image']);
    }

    /**
     * Test parse_url with valid airtable:// format
     */
    public function test_parse_url_valid_format(): void
    {
        $url = 'airtable://appXXXXXXXX/Tasks?token=patYYYYYYYY&view=Grid';

        $result = TC_Airtable_Source::parse_url($url);

        $this->assertIsArray($result);
        $this->assertEquals('appXXXXXXXX', $result['base_id']);
        $this->assertEquals('Tasks', $result['table_name']);
        $this->assertEquals('patYYYYYYYY', $result['token']);
        $this->assertEquals('Grid', $result['view']);
    }

    /**
     * Test parse_url without view parameter
     */
    public function test_parse_url_without_view(): void
    {
        $url = 'airtable://appXXXX/Projects?token=patYYYY';

        $result = TC_Airtable_Source::parse_url($url);

        $this->assertIsArray($result);
        $this->assertEquals('appXXXX', $result['base_id']);
        $this->assertEquals('Projects', $result['table_name']);
        $this->assertEquals('patYYYY', $result['token']);
        $this->assertEquals('', $result['view']);
    }

    /**
     * Test parse_url with missing table name
     */
    public function test_parse_url_missing_table(): void
    {
        $url = 'airtable://appXXXX';

        $result = TC_Airtable_Source::parse_url($url);

        $this->assertInstanceOf(WP_Error::class, $result);
        $this->assertEquals('airtable_invalid_url', $result->get_error_code());
    }

    /**
     * Test parse_url with empty url
     */
    public function test_parse_url_empty(): void
    {
        $result = TC_Airtable_Source::parse_url('');

        $this->assertInstanceOf(WP_Error::class, $result);
    }

    /**
     * Test fetch with empty params returns error
     */
    public function test_fetch_empty_params_returns_error(): void
    {
        $result = TC_Airtable_Source::fetch('', '', '');

        $this->assertInstanceOf(WP_Error::class, $result);
        $this->assertEquals('airtable_invalid_params', $result->get_error_code());
    }

    /**
     * Test fetch with missing token returns error
     */
    public function test_fetch_missing_token_returns_error(): void
    {
        $result = TC_Airtable_Source::fetch('appXXXX', 'Tasks', '');

        $this->assertInstanceOf(WP_Error::class, $result);
        $this->assertEquals('airtable_invalid_params', $result->get_error_code());
    }
}
