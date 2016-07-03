<?php
/**
 * Definition of class LanguageServiceTest
 *
 * @copyright  2014-today Justso GmbH
 * @author     j.schirrmacher@justso.de
 * @package    justso\justtexts\test
 */

namespace justso\justtexts\test;

use justso\justapi\Bootstrap;
use justso\justapi\testutil\ServiceTestBase;
use justso\justtexts\service\Language;

/**
 * Class LanguageServiceTest
 * @package justso\justtexts\test
 */
class LanguageServiceTest extends ServiceTestBase
{

    public function testGetLanguages()
    {
        $env = $this->createTestEnvironment();
        $config = array(
            'environments' => array('test' => array('approot' => '/var/www')),
            'languages' => array('de', 'en'),
        );
        $env->getBootstrap()->setTestConfiguration('/var/www', $config);
        $service = new Language($env);
        $service->getAction();
        $this->assertJSONHeader($env);
        $this->assertSame('["de","en"]', $env->getResponseContent());
    }
}
