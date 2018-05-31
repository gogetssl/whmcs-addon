<?php

namespace MGModule\GGSSLWHMCS\mgLibs\MySQL;
use MGModule\GGSSLWHMCS as main;

/**
 * MySQL Exception
 *
 * @author Michal Czech <michael@modulesgarden.com>
 */
class Exception extends main\mgLibs\exceptions\System {
    private $_query;
    public function __construct($message, $query, $code = 0, $previous = null) {
        $this->_query = $query;
        $code = (int) $code;
        parent::__construct($message, $code, $previous);
    }
}
