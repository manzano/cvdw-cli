<?php

declare(strict_types=1);

namespace Helper;

// aqui você pode definir métodos de asserção personalizados
class CvdwHelper extends \Codeception\Module
{
    public function validarFormatoDaData($dateField, $expectedFormat)
    {
        $data = \DateTime::createFromFormat($expectedFormat, $dateField);
        if ($data && $data->format($expectedFormat) === $dateField) {
            $this->assertTrue(true, "A data está no formato esperado.");
        } else {
            $this->assertTrue(false, "A data não está no formato esperado.");
        }
    }
}
