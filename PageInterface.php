<?php
/**
 * Definition of PageInterface
 *
 * @copyright  2014-today Justso GmbH
 * @author     j.schirrmacher@justso.de
 * @package    justso\justtexts\model
 */

namespace justso\justtexts;

use justso\justapi\RequestHelper;

interface PageInterface
{
    public function __construct($id = null, $value = null, RequestHelper $request = null);
    public function getJSON();
    public function getId();
    public function appendConfig(array &$config);
}
