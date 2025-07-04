<?php

namespace Tests\ApiCVDW;

use Codeception\Configuration;
use Codeception\Scenario;
use Manzano\CvdwCli\Services\Ambientes;
use Tests\Support\ApiTester;

/**
 * Class Common
 * @package Tests
 * @author Gabriel Manzano <gabrielmanzano@gmail.com>
 */
class Common
{
    public const SIM = 'S';
    public const NAO = 'N';

    protected string $email;
    protected string $token;
    protected array $env = [];

    /**
     * Define as variáveis globais a serem utilizadas na requisição.
     *
     * @param \ApiTester $I
     * @param \Codeception\Scenario $scenario
     * @return void
     * @throws \Codeception\Exception\ConfigurationException
     * @author Gabriel Manzano <gabrielmanzano@gmail.com>
     */
    public function _before(ApiTester $I, Scenario $scenario)
    {
        $ambientesObj = new Ambientes();
        $ambientesObj->retornarEnvs();
        $config = Configuration::suiteSettings("ApiCVDW", Configuration::config());
        $I->haveHttpHeader('Content-Type', 'application/json');
        $this->env['CVDW_AMBIENTE'] = $_ENV['CVDW_AMBIENTE'];
        $this->env['CV_EMAIL'] = $_ENV['CV_EMAIL'];
        $this->env['CV_TOKEN'] = $_ENV['CV_TOKEN'];
        $I->haveHttpHeader('email', $this->env['CV_EMAIL']);
        $I->haveHttpHeader('token', $this->env['CV_TOKEN']);
    }
}
