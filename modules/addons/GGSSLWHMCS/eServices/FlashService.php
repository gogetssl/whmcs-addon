<?php

namespace MGModule\GGSSLWHMCS\eServices;

class FlashService {

    const STEP_ONE_ERROR = 'GGSSLWHMCS_FLASH_ERROR_STEP_ONE';
    const AUTO_FILL = 'GGSSLWHMCS_FIELDS_AUTO_FILL';

    public static function setStepOneError($message) {
        self::set(self::STEP_ONE_ERROR, $message);
    }

    public static function getStepOneError() {
        $message = self::getAndUnset(self::STEP_ONE_ERROR);

        if (is_null($message)) {
            return [];
        } else {
            return [
                'errormessage' => '<li>' . $message . '</li>'
            ];
        }
    }

    public static function setFieldsMemory($md5, $fields) {
        self::set(self::AUTO_FILL . '_' . $md5, $fields);
    }

    public static function getFieldsMemory($md5, $key = null) {
        if (is_null(self::get(self::AUTO_FILL . '_' . $md5))) {
            return [];
        } elseif ($key === null) {
            return self::get(self::AUTO_FILL . '_' . $md5);
        }
        foreach (self::get(self::AUTO_FILL . '_' . $md5) as $field) {
            if($field['name'] === $key){
                return $field['value'];
            }
        }
    }

    public static function set($key, $message) {
        $_SESSION[$key] = $message;
    }

    public static function getAndUnset($key) {
        $msg = $_SESSION[$key];
        unset($_SESSION[$key]);
        return $msg;
    }

    public static function get($key) {
        return $_SESSION[$key];
    }

}
