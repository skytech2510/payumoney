<?php

/**
 * @package ApiException
 * @author tehcvillage <support@techvill.org>
 * @contributor Md Abdur Rahaman Zihad <[zihad.techvill@gmail.com]>
 * @created 04-12-2022
 */

namespace App\Exceptions\Api\V2;

use Exception;

class ApiException extends Exception
{
    /**
     * Stores data passed through exception constructor
     *
     * @var array
     */
    protected $exceptionData = null;


    /**
     * Custom Exception which can store data in array
     *
     * @param string|array $message
     * @param array $data
     *
     */
    public function __construct($message = "", $data = [])
    {
        if (is_array($message)) {
            $this->exceptionData = $message;
            $message = json_encode($message);
        } else {
            $this->exceptionData = $data;
        }
        parent::__construct($message);
    }

    /**
     * Set required data into exception
     *
     * @param array $data
     * @return void
     */
    public function setData($data = [])
    {
        $this->exceptionData = $data;
    }


    /**
     * Get array data from exception
     *
     * @return array
     */
    public function getData()
    {
        return $this->exceptionData;
    }
}
