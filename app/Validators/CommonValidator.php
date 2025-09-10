<?php
declare (strict_types = 1);

namespace App\Validators;

use App\Models\CompanyLocation;
use App\Models\User;
use Hash;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;
use Jenssegers\Date\Date;
use App\Models\Company;

/**
 * Class CommonValidator
 *
 * @package App\Validators
 */
class CommonValidator extends Validator
{
    /**
     * Validates array of integers.
     * Accessed as "array_of_integers"
     *
     * @param  string $attribute
     * @param  mixed  $value
     * @param  array  $parameters
     *
     * @return bool
     */
    protected function validateEmailSimple($attribute, $value, array $parameters): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validates first,last name
     * Accessed as "first_last_name"
     *
     * @param  string $attribute
     * @param  mixed  $value
     * @param  array  $parameters
     *
     * @return bool
     */
    protected function validateFirstLastName($attribute, $value, array $parameters): bool
    {
        $pattern = '/^[\pL\s-_.,\'`]+$/u';
        if (\count($parameters) > 0) {
            $pattern = \array_shift($parameters);
        }

        return $this->validateAgainstPattern($value, $pattern);
    }

    /**
     * Validates generic title.
     * Accessed as "generic_title"
     *
     * @param  string $attribute
     * @param  mixed  $value
     * @param  array  $parameters
     *
     * @return bool
     */
    protected function validateGenericTitle($attribute, $value, array $parameters): bool
    {
        $pattern = '/^[\pL\p{Pd}\s0-9-_.,:\'\"’\/§|`~$@#%*!\<\>\[\]\{\}\(\)]+$/u';
        if (\count($parameters) > 0) {
            $pattern = \array_shift($parameters);
        }

        return $this->validateAgainstPattern($value, $pattern);
    }

    /**
     * Validates alpha + spaces
     * Accessed as "alpha_spaces"
     *
     * @param  string $attribute
     * @param  mixed  $value
     * @param  array  $parameters
     *
     * @return bool
     */
    protected function validateAlphaSpaces($attribute, $value, array $parameters): bool
    {
        $pattern = '/^[\pL\s]+$/u';
        if (\count($parameters) > 0) {
            $pattern = \array_shift($parameters);
        }

        return $this->validateAgainstPattern($value, $pattern);
    }

    /**
     * Validates alpha + num + spaces.
     * Accessed as "alpha_num_spaces"
     *
     * @param  string $attribute
     * @param  mixed  $value
     * @param  array  $parameters
     *
     * @return bool
     */
    protected function validateAlphaNumSpaces($attribute, $value, array $parameters): bool
    {
        $pattern = '/^[\pL\s0-9]+$/u';
        if (\count($parameters) > 0) {
            $pattern = \array_shift($parameters);
        }

        return $this->validateAgainstPattern($value, $pattern);
    }

    /**
     * Validates simple phone format.
     * Accessed as "phone_simple"
     *
     * @param  string $attribute
     * @param  mixed  $value
     * @param  array  $parameters
     *
     * @return bool
     */
    protected function validatePhoneSimple($attribute, $value, array $parameters): bool
    {
        $pattern = '/^[0-9\+\-\(\)\s]+$/';
        if (\count($parameters) > 0) {
            $pattern = \array_shift($parameters);
        }

        return $this->validateAgainstPattern($value, $pattern);
    }

    /**
     * Validates array of integers.
     * Accessed as "array_of_integers"
     *
     * @param  string $attribute
     * @param  mixed  $value
     * @param  array  $parameters
     *
     * @return bool
     */
    protected function validateArrayOfIntegers($attribute, $value, array $parameters): bool
    {
        return \is_array($value) && $value === \array_filter($value, 'is_int');
    }

    /**
     * @param string $attribute
     * @param mixed  $value
     * @param array  $parameters
     *
     * @return bool
     */
    protected function validateArrayUnique($attribute, $value, array $parameters): bool
    {
        $value = \array_filter($value);

        return \count($value) === \count(\array_unique($value));
    }

    /**
     * Validate string against provided pattern
     *
     * @param string $value
     * @param string $pattern
     *
     * @return bool
     */
    protected function validateAgainstPattern($value, $pattern): bool
    {
        return (boolean) \filter_var($value, FILTER_VALIDATE_REGEXP, [
            'options' => [
                'regexp' => $pattern,
            ],
        ]);
    }

    /**
     * @param       $attribute
     * @param       $value
     * @param array $parameters
     *
     * @return bool
     */
    protected function validateNotLess($attribute, $value, array $parameters): bool
    {
        $valueDate = Date::parse($value);
        $date      = Date::parse($this->getValue($parameters[0]));

        return $date->diffInDays($valueDate, false) >= 0;
    }

    /**
     * @param       $attribute
     * @param       $value
     * @param array $parameters
     * @param       $validator
     *
     * @return bool
     */
    protected function validateDomainName($attribute, $value, array $parameters, $validator): bool
    {
        if (Str::length($value) > 253) {
            return false;
        }
        $pattern = "/^(?!\-)(?:[a-zA-Z\d\-]{0,62}[a-zA-Z\d]\.){1,126}(?!\d+)[a-zA-Z\d]{1,63}$/";

        return (boolean) preg_match($pattern, $value);
    }

    /**
     * Validates that string include single char
     * Accessed as "include_char"
     *
     * @param  string $attribute
     * @param  mixed  $value
     * @param  array  $parameters
     *
     * @return bool
     */
    protected function validateYoutubeUrl($attribute, $value, array $parameters): bool
    {
        \preg_match(
            '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i',
            $value,
            $matches
        );

        return 2 === \count($matches);
    }

    /**
     * @param       $attribute
     * @param       $value
     * @param array $parameters
     * @param       $validator
     *
     * @return bool
     */
    protected function validateAudioFile($attribute, $value, array $parameters, $validator): bool
    {
        return (in_array($value->getClientMimeType(), array('audio/mpeg', 'audio/mp3')) !== false);
    }

    public function validateDescription($attribute, $value)
    {

        if (!empty($value)) {
            $descriptionLength = strlen(preg_replace('/\s\s+/u', '', trim(str_replace(["\r", "\n", "\r\n", "&nbsp;", "&nbsp; ", " &nbsp; ", " &nbsp;", "&rsquo;"], "", strip_tags(htmlspecialchars_decode($value))))));

            if ($descriptionLength > 5000 || $descriptionLength == 0) {
                return false;
            }
        } else {
            return true;
        }
        return true;
    }

    public function validateDirection($attribute, $value)
    {

        if (!empty($value)) {
            $descriptionLength = strlen(preg_replace('/\s\s+/u', '', trim(str_replace(["\r", "\n", "\r\n", "&nbsp;", "&nbsp; ", " &nbsp; ", " &nbsp;", "&rsquo;"], "", strip_tags(htmlspecialchars_decode($value))))));

            if ($descriptionLength > 1000 || $descriptionLength == 0) {
                return false;
            }
        } else {
            return true;
        }
        return true;
    }

    public function validateIntroduction($attribute, $value, array $parameters)
    {
        if (!empty($value)) {
            $length = ($parameters[0] ?? 500);

            $descriptionLength = strlen(preg_replace('/\s\s+/u', '', trim(str_replace(["\r", "\n", "\r\n", "&nbsp;", "&nbsp; ", " &nbsp; ", " &nbsp;", "&rsquo;"], "", strip_tags(htmlspecialchars_decode($value))))));

            if ($descriptionLength > $length || $descriptionLength == 0) {
                return false;
            }
        } else {
            return true;
        }
        return true;
    }

    public function validateSlidedescription($attribute, $value, array $parameters)
    {
        if (!empty($value)) {
            $length = ($parameters[0] ?? 500);

            $descriptionLength = strlen(preg_replace('/\s\s+/u', '', trim(str_replace(["\r", "\n", "\r\n", "&nbsp;", "&nbsp; ", " &nbsp; ", " &nbsp;", "&rsquo;"], "", strip_tags(htmlspecialchars_decode($value))))));

            if ($descriptionLength > $length || $descriptionLength == 0) {
                return false;
            }
        } else {
            return true;
        }
        return true;
    }

    /**
     * Validates length of about me field of user while add/update, it will be
     * based on passed value
     * Use as "user_about_me:role,length"
     *
     * @param  string $attribute
     * @param  mixed  $value
     * @param  array  $parameters
     *
     * @return bool
     */
    public function validateUserAboutMe($attribute, $value, array $parameters)
    {
        if (!empty($value)) {
            $length        = ($parameters[0] ?? 200);
            $aboutMeLength = strlen(preg_replace('/\s\s+/u', '', trim(str_replace(["\r", "\n", "\r\n", "&nbsp;", "&nbsp; ", " &nbsp; ", " &nbsp;", "&rsquo;"], "", strip_tags(htmlspecialchars_decode($value))))));
            if ($aboutMeLength > $length || $aboutMeLength == 0) {
                return false;
            }
        } else {
            return true;
        }
        return true;
    }

    /**
     * Validates length of field, it will be based on passed value
     * Use as "custom_max_length:length"
     *
     * @param  string $attribute
     * @param  mixed  $value
     * @param  array  $parameters
     *
     * @return bool
     */
    public function validateCustomMaxLength($attribute, $value, array $parameters)
    {
        if (!empty($value)) {
            $length        = ($parameters[0] ?? 200);
            $contentLength = strlen(preg_replace('/\s\s+/u', '', trim(str_replace(["\r", "\n", "\r\n", "&nbsp;", "&nbsp; ", " &nbsp; ", " &nbsp;", "&rsquo;"], "", strip_tags(htmlspecialchars_decode($value))))));
            if ($contentLength > $length || $contentLength == 0) {
                return false;
            }
        } else {
            return false;
        }
        return true;
    }

    /**
     * Validates alpha + num + spaces.
     * Accessed as "alpha_num_spaces"
     *
     * @param  string $attribute
     * @param  mixed  $value
     * @param  array  $parameters
     *
     * @return bool
     */
    protected function validateAddress($attribute, $value, array $parameters): bool
    {
        $pattern = '/([- ,\/0-9a-zA-Z]+)/';
        if (\count($parameters) > 0) {
            $pattern = \array_shift($parameters);
        }

        return $this->validateAgainstPattern($value, $pattern);
    }

    public function validateMinMembers($attribute, $value)
    {
        $data = $value;

        if (!is_array($data)) {
            return true;
        }
        return count($data) >= 2;
    }

    /**
     * Validates that json is empty or not
     * Accessed as "empty_json_data"
     *
     * @param  string $attribute
     * @param  mixed  $value
     * @param  array  $parameters
     *
     * @return bool
     */
    protected function validateEmptyJsonData($attribute, $value, array $parameters): bool
    {
        $data = json_decode($value);

        if (empty($data)) {
            return false;
        }
        return true;
    }

    /**
     * Validates that minimum two companies are selected or not
     *
     * @param  string $attribute
     * @param  mixed  $value
     *
     * @return bool
     */
    public function validateMinCompanies($attribute, $value)
    {
        $data = $value;

        if (!is_array($data)) {
            return true;
        }

        $companyIds = [];
        foreach ($data as $value) {
            $companyIds[] = \App\Models\Team::find($value)->company()->first()->id;
        }

        $uniqueCompanyIds = array_unique($companyIds);

        return count($uniqueCompanyIds) >= 2;
    }

    /**
     * To validate number of files count from request
     *
     * @param  string $attribute
     * @param  mixed  $value
     *
     * @return bool
     */
    public function validateFileCount($attribute, $value, $parameters)
    {
        $files = Request::file($parameters[0]);
        return (count($files) <= $parameters[1]);
    }

    /**
     * Validates that string include single char
     * Accessed as "include_char"
     *
     * @param  string $attribute
     * @param  mixed  $value
     * @param  array  $parameters
     *
     * @return bool
     */
    protected function validateVimeoUrl($attribute, $value, array $parameters): bool
    {
        \preg_match(
            '/(?:http:|https:|)\/\/(?:player.|www.)?vimeo\.com\/(?:video\/|embed\/|watch\?\S*v=|v\/)?(\d*)/',
            $value,
            $matches
        );
        $id = getIdFromVimeoURL($value);
        return 2 === \count($matches) && !empty($id);
    }

    /**
     * For old password check validation
     * Accessed as "include_char"
     *
     * @param  string $attribute
     * @param  mixed  $value
     * @param  array  $parameters
     *
     * @return bool
     */
    // protected function validateCurrentPassword($attribute, $value, array $parameters): bool
    // {
    //     $user = User::find(auth()->user()->id);
    //     return Hash::check($value, $user->password);
    // }

    /**
     * Validates hyphen + spaces
     * Accessed as "hyphen_spaces"
     *
     * @param  string $attribute
     * @param  mixed  $value
     * @param  array  $parameters
     *
     * @return bool
     */
    protected function validateHyphenSpaces($attribute, $value, array $parameters): bool
    {
        $pattern = '/^[a-zA-Z]([\w -]*[a-zA-Z])?$/';
        if (\count($parameters) > 0) {
            $pattern = \array_shift($parameters);
        }

        return $this->validateAgainstPattern($value, $pattern);
    }

    /**
     * Check Company Credit 
     *
     * @param  string $attribute
     * @param  mixed  $value
     * @param  array  $parameters
     *
     * @return bool
     */
    protected function validateCheckCredit($attribute, $value, array $parameters): bool
    {
        $company = Company::find($value);
        return ($company->credits > 0);
    }

    /**
     * Validates Disposable Email.
     * Accessed as "array_of_integers"
     *
     * @param  string $attribute
     * @param  mixed  $value
     * @param  array  $parameters
     *
     * @return bool
     */
    protected function validateEmailDisposable($attribute, $value, array $parameters): bool
    {
        $appEnvironment = app()->environment();
        if($appEnvironment == 'performance' || $appEnvironment == 'preprod' || $appEnvironment == 'uat' || $appEnvironment == 'production') {
            $disposableEmail = config('emaildisposable');
            $emailExtenstion = strtolower(explode('@', $value)[1]);
            return !in_array($emailExtenstion, $disposableEmail);
        }
        return true;
    }
}