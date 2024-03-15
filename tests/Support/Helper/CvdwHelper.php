<?php
declare(strict_types=1);

namespace Helper;

// aqui você pode definir métodos de asserção personalizados
class CvdwHelper extends \Codeception\Module
{
    public function validarFormatoDaData($dateField, $expectedFormat, $response)
    {
        $dateString = $response[$dateField];
        $date = \DateTime::createFromFormat($expectedFormat, $dateString);
        $this->assertTrue($date && $date->format($expectedFormat) === $dateString, "A data está no formato esperado.");
    }
}
