<?php
/**
 * Created by PhpStorm.
 * User: joe
 * Date: 10.08.15
 * Time: 08:06
 */

namespace justso\justtexts\model;

use justso\justapi\RequestHelper;

interface PageInterface {
    public function __construct($id = null, $value = null, RequestHelper $request = null);
    public function getJSON();
    public function getId();
    public function appendConfig(array &$config);
}
