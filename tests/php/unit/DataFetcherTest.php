<?php
/**
 * Unit Tests for TC_Data_Fetcher Class
 *
 * @package TableCrafter\Tests
 */

use PHPUnit\Framework\TestCase;

class DataFetcherTest extends TestCase
{
    private TC_Data_Fetcher $fetcher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fetcher = TC_Data_Fetcher::get_instance();
        tc_reset_mock_transients();
    }

    /**
     * Test singleton pattern
     */
    public function test_get_instance_returns_same_instance(): void
    {
        $instance1 = TC_Data_Fetcher::get_instance();
        $instance2 = TC_Data_Fetcher::get_instance();

        $this->assertSame($instance1, $instance2);
    }

    /**
     * Test extract_from_root with empty path
     */
    public function test_extract_from_root_with_empty_path(): void
    {
        $data = array(
            array('id' => 1, 'name' => 'Test'),
            array('id' => 2, 'name' => 'Example')
        );

        $result = $this->fetcher->extract_from_root($data, '');

        $this->assertEquals($data, $result);
    }

    /**
     * Test extract_from_root with single level path
     */
    public function test_extract_from_root_single_level(): void
    {
        $data = array(
            'items' => array(
                array('id' => 1),
                array('id' => 2)
            )
        );

        $result = $this->fetcher->extract_from_root($data, 'items');

        $this->assertCount(2, $result);
        $this->assertEquals(1, $result[0]['id']);
    }

    /**
     * Test extract_from_root with nested path
     */
    public function test_extract_from_root_nested_path(): void
    {
        $data = array(
            'response' => array(
                'data' => array(
                    'items' => array(
                        array('id' => 1),
                        array('id' => 2),
                        array('id' => 3)
                    )
                )
            )
        );

        $result = $this->fetcher->extract_from_root($data, 'response.data.items');

        $this->assertCount(3, $result);
    }

    /**
     * Test extract_from_root with invalid path returns error
     */
    public function test_extract_from_root_invalid_path(): void
    {
        $data = array('items' => array());

        $result = $this->fetcher->extract_from_root($data, 'nonexistent.path');

        $this->assertInstanceOf(WP_Error::class, $result);
    }

    /**
     * Test process_columns with no filters
     */
    public function test_process_columns_no_filters(): void
    {
        $data = array(
            array('id' => 1, 'name' => 'Alice', 'email' => 'alice@test.com'),
            array('id' => 2, 'name' => 'Bob', 'email' => 'bob@test.com')
        );

        $result = $this->fetcher->process_columns($data, '', '');

        $this->assertContains('id', $result['headers']);
        $this->assertContains('name', $result['headers']);
        $this->assertContains('email', $result['headers']);
    }

    /**
     * Test process_columns with include filter
     */
    public function test_process_columns_with_include(): void
    {
        $data = array(
            array('id' => 1, 'name' => 'Alice', 'email' => 'alice@test.com', 'phone' => '123'),
        );

        $result = $this->fetcher->process_columns($data, 'name,email', '');

        $this->assertEquals(array('name', 'email'), $result['headers']);
        $this->assertNotContains('id', $result['headers']);
        $this->assertNotContains('phone', $result['headers']);
    }

    /**
     * Test process_columns with exclude filter
     */
    public function test_process_columns_with_exclude(): void
    {
        $data = array(
            array('id' => 1, 'name' => 'Alice', 'password' => 'secret'),
        );

        $result = $this->fetcher->process_columns($data, '', 'password');

        $this->assertContains('id', $result['headers']);
        $this->assertContains('name', $result['headers']);
        $this->assertNotContains('password', $result['headers']);
    }

    /**
     * Test process_columns with column aliasing
     */
    public function test_process_columns_with_aliasing(): void
    {
        $data = array(
            array('first_name' => 'Alice', 'last_name' => 'Smith'),
        );

        $result = $this->fetcher->process_columns($data, 'first_name:First Name,last_name:Last Name', '');

        $this->assertEquals('First Name', $result['header_map']['first_name']);
        $this->assertEquals('Last Name', $result['header_map']['last_name']);
    }

    /**
     * Test sort_data ascending
     */
    public function test_sort_data_ascending(): void
    {
        $data = array(
            array('name' => 'Charlie'),
            array('name' => 'Alice'),
            array('name' => 'Bob')
        );

        $result = $this->fetcher->sort_data($data, 'name', 'asc');

        $this->assertEquals('Alice', $result[0]['name']);
        $this->assertEquals('Bob', $result[1]['name']);
        $this->assertEquals('Charlie', $result[2]['name']);
    }

    /**
     * Test sort_data descending
     */
    public function test_sort_data_descending(): void
    {
        $data = array(
            array('name' => 'Alice'),
            array('name' => 'Bob'),
            array('name' => 'Charlie')
        );

        $result = $this->fetcher->sort_data($data, 'name', 'desc');

        $this->assertEquals('Charlie', $result[0]['name']);
        $this->assertEquals('Bob', $result[1]['name']);
        $this->assertEquals('Alice', $result[2]['name']);
    }

    /**
     * Test sort_data with numeric values
     */
    public function test_sort_data_numeric(): void
    {
        $data = array(
            array('price' => 100),
            array('price' => 25),
            array('price' => 50)
        );

        $result = $this->fetcher->sort_data($data, 'price', 'asc');

        $this->assertEquals(25, $result[0]['price']);
        $this->assertEquals(50, $result[1]['price']);
        $this->assertEquals(100, $result[2]['price']);
    }

    /**
     * Test sort_data with empty array
     */
    public function test_sort_data_empty_array(): void
    {
        $result = $this->fetcher->sort_data(array(), 'name', 'asc');

        $this->assertEmpty($result);
    }

    /**
     * Test sort_data with empty field
     */
    public function test_sort_data_empty_field(): void
    {
        $data = array(
            array('name' => 'Alice'),
            array('name' => 'Bob')
        );

        $result = $this->fetcher->sort_data($data, '', 'asc');

        // Should return data unchanged
        $this->assertEquals($data, $result);
    }

    /**
     * Test sort_data handles missing field gracefully
     */
    public function test_sort_data_missing_field(): void
    {
        $data = array(
            array('name' => 'Alice', 'score' => 90),
            array('name' => 'Bob'), // Missing 'score'
            array('name' => 'Charlie', 'score' => 85)
        );

        // Should not throw, missing values treated as empty string
        $result = $this->fetcher->sort_data($data, 'score', 'asc');

        $this->assertCount(3, $result);
    }
}
