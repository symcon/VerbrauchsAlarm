<?php

declare(strict_types=1);
include_once __DIR__ . '/stubs/Validator.php';
class VerbrauchsAlarmValidationTest extends TestCaseSymconValidation
{
    public function testValidateVerbrauchsAlarm(): void
    {
        $this->validateLibrary(__DIR__ . '/..');
    }
    public function testValidateConsumptionAlertModule(): void
    {
        $this->validateModule(__DIR__ . '/../ConsumptionAlert');
    }
}