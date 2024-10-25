<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class CheckValidFile implements Rule
{
    /**
     * Set error message.
     *
     * @param  string  $errorMessage
     * @var string
     */
    protected $errorMessage;

    /**
     * Allowed file extention.
     *
     * @var array
     */
    protected $allowFile;

    /**
     * Check maximum file size
     *
     * @var bool
     */
    protected $checkMaxFileSize;

    /**
     * Set $allowFile and $checkMaxFileSize.
     *
     * @param  array  $allowFile
     * @param  bool  $checkMaxFileSize
     * @return void
     */
    public function __construct(array $allowFile = [], bool $checkMaxFileSize = true)
    {
        $this->allowFile = $allowFile;
        $this->checkMaxFileSize = $checkMaxFileSize;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $isValid = in_array($value->getClientOriginalExtension(), $this->allowFile) ? true : false;

        if ($isValid == false && !empty($this->allowFile)) {
            $this->errorMessage = __('Allowed File Extensions: ') . implode(", ", $this->allowFile);
            return false;
        }

        if ($this->checkMaxFileSize) {
            $maxFileSize = maxFileSize($_FILES[$attribute]["size"]);
            if ($maxFileSize['status'] == 0) {
                $this->errorMessage = $maxFileSize['message'];
                return false;
            }
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->errorMessage;
    }
}
