<?php
/**
 * Definition of class Page
 * 
 * @copyright  2014-today Justso GmbH
 * @author     j.schirrmacher@justso.de
 * @package    justso\justtexts\model
 */

namespace justso\justtexts\model;

use justso\justapi\RequestHelper;

/**
 * Class Page
 * @package justso\justtexts\model
 */
class Page
{
    private $name;

    public function __construct($id=null, $value=null, RequestHelper $request=null)
    {
        if ($request !== null) {
            $this->name = $request->getIdentifierParam('name');
        } else {
            $this->name = $value;
        }
    }

    public function getJSON()
    {
        return array('id' => $this->name, 'name' => $this->name);
    }

    public function getConfig()
    {
        return $this->name;
    }

    public function getId()
    {
        return $this->name;
    }
}
