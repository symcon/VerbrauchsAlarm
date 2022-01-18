<?php

declare(strict_types=1);
include_once __DIR__ . '/stubs/Validator.php';
class VerbrauchsAlarmValidationTest extends TestCaseSymconValidation
{
    public function testValidateVerbrauchsAlarm(): void
    {
        $this->validateLibrary(__DIR__ . '/..');
    }
    public function testValidateVerbrauchsAlarmModule(): void
    {
        $this->validateModule(__DIR__ . '/../VerbrauchsAlarm');
    }
}