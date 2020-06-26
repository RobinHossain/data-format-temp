<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

final class AppTest extends TestCase
{

    public function testCannotBeExecuteNullValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        App::executeTransaction(null);
    }

    public function testWithJsonData(): void
    {
        $this->assertEquals(
            '1.00',
            App::executeTransaction('{"bin":"45717360","amount":"100.00","currency":"EUR"}')
        );
    }

    public function testWithJsonData2(): void
    {
        $this->assertEquals(
            '44.38',
            App::executeTransaction('{"bin":"4745030","amount":"2000.00","currency":"GBP"}')
        );
    }
}