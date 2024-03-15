<?php
namespace MyVendor\AwesomeNeosProject\Tests\Unit\Eel;

use MyVendor\AwesomeNeosProject\Eel\Helper\FileSizeHelper;
use Neos\Flow\Tests\UnitTestCase;

class FileSizeHelperTest extends UnitTestCase
{
    protected static bool $testablePersistenceEnabled = true;

    private FileSizeHelper $fileSizeHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fileSizeHelper = new FileSizeHelper();
    }

    /**
     * @test
     * @dataProvider fileSizeProvider
     *
     * @param float $fileSize
     * @param string $formattedFileSize
     * @return void
     */
    public function test_selectedFileSizes(float $fileSize, string $formattedFileSize): void
    {
        self::assertEquals($formattedFileSize, $this->fileSizeHelper->format($fileSize), "Formatted file size does not match");
    }

    /**
     * test data contains of:
     *  - fileSizeInBytes
     *  - formatted file size
     * @return array<array{int, string}>
     */
    public function fileSizeProvider(): array
    {
        return [
            [-1024, ''],
            [-512, ''],
            [0, '0 B'],
            [500, '500 B'],
            [1024, '1 KB'],
            [2024, '2 KB'],
            [1024 * 10, '10 KB'],
            [1024 * 1000, '1000 KB'],
            [1024 * 1024, '1 MB'],
            [1024 * 2000, '2 MB'],
            [1024 * 2500, '2 MB'],
            [1024 * 1024 * 1024, '1 GB'],
        ];
    }
}
