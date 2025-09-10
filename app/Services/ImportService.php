<?php
declare (strict_types = 1);

namespace App\Services;

use App\Events\UserImportStatusEvent;
use App\Events\UserRegisterEvent;
use App\Models\Company;
use App\Models\FileImport;
use App\Models\SurveyCategory;
use App\Models\SurveySubCategory;
use App\Models\Team;
use App\Models\User;
use App\Models\ZcQuestion;
use App\Models\ZcQuestionOption;
use App\Models\ZcQuestionType;
use Carbon\Carbon;
use function GuzzleHttp\Promise\all;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * Class ChallengeService
 *
 * @package App\Services
 */
class ImportService
{
    /**
     * @param Fileimport $fileImport
     * @param Company $company
     */
    public function performValidationOnFile(Fileimport $fileImport)
    {
        $company = !empty($fileImport->company_id) ? Company::find($fileImport->company_id) : null;
        $fileLink   = getFileLink($fileImport->getKey(), $fileImport->uploaded_file, $fileImport->module, 1, 1);

        $reader = new Xlsx();
        // Set current excel as read only because later on we need to generate new file.
        $reader->setReadDataOnly(true);

        $moduleIdentificationArray = [
            'questions' => [
                'choices',
            ],
            'users'     => [
                'users',
            ],
        ];

        $sheetnames = $moduleIdentificationArray[$fileImport->module];

        // Load only selected sheet name only
        $reader->setLoadSheetsOnly(array_values($sheetnames));

        if (filter_var($fileLink, FILTER_VALIDATE_URL) && parse_url($fileLink) && isset(parse_url($fileLink)['scheme'])) {
            $fileObjects = file_get_contents($fileLink);
            $tmpFileName = sprintf("/tmp/%s.xls", generateProcessKey());
            file_put_contents($tmpFileName, $fileObjects);
            $fileLink = $tmpFileName;

            // Load selected area only.
            $spreadsheet = $reader->load($fileLink);

            unlink($tmpFileName);
        } else {
            // Load selected area only.
            $spreadsheet = $reader->load($fileLink);
        }

        if (!empty($fileImport) && $fileImport->module == 'users') {
            $this->userImport($spreadsheet, $sheetnames, $fileImport, $company);
        }

        if (!empty($fileImport) && $fileImport->module == 'questions') {
            $this->questionImport($spreadsheet, $sheetnames, $fileImport);
        }
    }

    /**
     * User Import
     * @param $spreadsheet
     * @param $sheetnames
     * @param $fileImport
     * @param $company
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function userImport($spreadsheet, $sheetnames, $fileImport, $company)
    {
        $moderator = (!empty($company)) ? $company->moderators()->first() : null;

        // Set active sheet for reading.
        $activeSheet = $spreadsheet->getSheetByName('users');

        // Skipp rows number from reading of current sheet.
        $skipRows = [1];
        // Read upto this column only.
        $skipColumns = 'G';

        $rules = [
            'A' => ['required', 'max:100', 'hyphen_spaces'], // First Name
            'B' => ['required', 'max:100', 'hyphen_spaces'], // Last Name
            'C' => ['required', 'email_simple', 'max:255', 'unique:users,email'], // Email
            'D' => ['required', 'date_format:Y-m-d'], // date of birth
            'E' => ['required', 'in:male,female,other,none'], // Gender
            'F' => ['required', 'exists:teams,name'], // Team Name
            'G' => ['date_format:Y-m-d'], // start date
        ];
        // messages to set as a comment/note on invalid cell
        $messages = [
            'A.required'      => 'The first name field is required.',
            'A.max'           => 'The first name may not be greater than 100 characters.',
            'A.hyphen_spaces' => 'The first name may only contain letters, hyphen and spaces.',
            'B.required'      => 'The last name field is required.',
            'B.max'           => 'The last name may not be greater than 100 characters.',
            'B.hyphen_spaces' => 'The last name may only contain letters, hyphen and spaces.',
            'C.required'      => 'The email field is required.',
            'C.email_simple'  => 'The email must be a valid email address.',
            'C.max'           => 'The email may not be greater than 255 characters.',
            'C.unique'        => 'The email has already been taken.',
            'D.required'      => 'The date of birth field is required.',
            'D.date_format'   => 'The date of birth does not match the format Y-m-d.',
            'E.required'      => 'The gender field is required.',
            'E.in'            => 'Gender can be either male, female, other or none.',
            'F.required'      => 'The team name field is required.',
            'F.exists'        => 'The team name does not exist.',
            'G.date_format'   => 'The start date does not match the format Y-m-d.',
        ];

        // Collect all validation error in [row][column] = has error Array formate for debugging purpose only.
        $excelFileAsError = [];
        $importUserTeams  = [];
        $requestedEmails  = $duplicateEmails  = [];

        foreach ($activeSheet->getRowIterator() as $key => $cellIterator) {
            $currentRowNumber = $cellIterator->getRowIndex();
            if (in_array($currentRowNumber, $skipRows)) {
                continue;
            }
            foreach ($cellIterator->getCellIterator() as $cell) {
                $currentColumn = $cell->getColumn();

                if ($currentColumn == 'C' && !empty($cell->getValue())) {
                    $requestedEmails[] = $cell->getValue();
                }

                if ($currentColumn == 'F' && !empty($cell->getValue())) {
                    $slug = str_slug(trim($cell->getValue()));
                    if ($company->auto_team_creation) {
                        $importUserTeams[$slug] = ((isset($importUserTeams[$slug])) ? ($importUserTeams[$slug] + 1) : 1);
                    } else {
                        $importUserTeams[$slug] = false;
                    }
                }
            }
        }

        if (!empty($requestedEmails)) {
            $requestedEmails = array_count_values($requestedEmails);
            $duplicateEmails = array_filter($requestedEmails, function ($count) {
                return $count > 1;
            });
            $duplicateEmails = !empty($duplicateEmails) ? array_keys($duplicateEmails) : [];
        }

        foreach ($activeSheet->getRowIterator() as $key => $cellIterator) {
            $currentRowWithCellId         = [];
            $currentRowWithColumnNameOnly = [];
            $currentRowNumber             = $cellIterator->getRowIndex();

            if (in_array($currentRowNumber, $skipRows)) {
                continue;
            }
            foreach ($cellIterator->getCellIterator() as $cell) {
                $currentColumn = $cell->getColumn();

                if ($currentColumn > $skipColumns) {
                    continue;
                }
                $currentCell                                  = $currentColumn . $currentRowNumber;
                $cellValue                                    = $cell->getValue();
                $currentRowWithCellId[$currentCell]           = $cellValue;
                $currentRowWithColumnNameOnly[$currentColumn] = $cellValue;
            }

            $oldUnixValue = $currentRowWithColumnNameOnly['D'];

            if (!empty($oldUnixValue)) {
                if (is_int($oldUnixValue) || is_float($oldUnixValue)) {
                    $unix_value = (int) ($oldUnixValue - 25569) * 86400;
                    $unix_date  = gmdate("Y-m-d", $unix_value);
                    $unix_year  = gmdate("Y", $unix_value);
                } else {
                    if (\DateTime::createFromFormat('Y-m-d', $oldUnixValue)) {
                        $unix_value = Carbon::parse(strtotime($oldUnixValue));
                        $unix_date  = $unix_value->format("Y-m-d");
                        $unix_year  = $unix_value->format("Y");
                    }
                }
            }

            // set date to valid format
            $currentRowWithColumnNameOnly['D'] = (isset($currentRowWithColumnNameOnly['D']) && is_numeric($currentRowWithColumnNameOnly['D'])) ? \Carbon\Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($currentRowWithColumnNameOnly['D']))->toDateString() : $currentRowWithColumnNameOnly['D'];

            $oldUnixValue2 = $currentRowWithColumnNameOnly['G'];

            $invalidStartDate = false;
            if (!empty($oldUnixValue2)) {
                if (is_int($oldUnixValue2) || is_float($oldUnixValue2)) {
                    $unix_value2 = (int) ($oldUnixValue2 - 25569) * 86400;
                    $unix_date2  = gmdate("Y-m-d", $unix_value2);
                    $unix_year2  = gmdate("Y", $unix_value2);
                } else {
                    if (\DateTime::createFromFormat('Y-m-d', $oldUnixValue2)) {
                        $unix_value2 = Carbon::parse(strtotime($oldUnixValue2));
                        $unix_date2  = $unix_value2->format("Y-m-d");
                        $unix_year2  = $unix_value2->format("Y");
                    } else {
                        $invalidStartDate = true;
                    }
                }
            }

            // set date to valid format
            $currentRowWithColumnNameOnly['G'] = (isset($currentRowWithColumnNameOnly['G']) && is_numeric($currentRowWithColumnNameOnly['G'])) ? \Carbon\Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($currentRowWithColumnNameOnly['G']))->toDateString() : $currentRowWithColumnNameOnly['G'];

            $validator = Validator::make($currentRowWithColumnNameOnly, $rules, $messages);

            $team = ((!empty($currentRowWithColumnNameOnly['F']))
                ? Team::select('id', 'default')
                    ->withCount('users')
                    ->where('name', strtolower($currentRowWithColumnNameOnly['F']))
                    ->where('company_id', $company->id)
                    ->first()
                : null);

            if (!empty($duplicateEmails) && !empty($currentRowWithColumnNameOnly['C']) && in_array($currentRowWithColumnNameOnly['C'], $duplicateEmails)) {
                $validator->getMessageBag()->add('C', 'Duplicate email found in sheet.');
            } else {
                if (!empty($currentRowWithColumnNameOnly['C'])) {
                    if (!empty($company) && $company->has_domain) {
                        list(, $domain) = \explode('@', $currentRowWithColumnNameOnly['C']);
                        if (!$company->domains->contains('domain', $domain)) {
                            $validator->getMessageBag()->add('C', 'Invalid email domain.');
                        }
                    }
                }
            }

            if (!empty($currentRowWithColumnNameOnly['F'])) {
                if (empty($team)) {
                    $validator->getMessageBag()->add('F', "Invalid team name");
                } else {
                    if ($company->auto_team_creation) {
                        $slug = str_slug(trim($currentRowWithColumnNameOnly['F']));
                        if (!$team->default && (($importUserTeams[$slug] + $team->users_count) > $company->team_limit)) {
                            $validator->getMessageBag()->add('F', "Can't be imported more than team limit({$company->team_limit}).");
                        }
                    }
                }
            }

            $spreadsheet->getActiveSheet()->getCell('D' . $currentRowNumber)->setValue($currentRowWithColumnNameOnly['D']);

            if (!isset($unix_year)) {
                $validator->getMessageBag()->add('D', "Please enter valid date format");
            }

            if (isset($unix_year) && $unix_date < Carbon::now()->subYears(100)->format('Y-m-d')) {
                $validator->getMessageBag()->add('D', "Please add birth date between 100 years.");
                $spreadsheet->getActiveSheet()->getCell('D' . $currentRowNumber)->setValue($unix_date);
            }

            if (!empty($unix_date)) {
                $age = Carbon::parse($unix_date, config('app.timezone'))->diffInYears();
                if ($age < 18) {
                    $validator->getMessageBag()->add('D', "DOB should not be less than 18 years.");
                }
            }

            $spreadsheet->getActiveSheet()->getCell('G' . $currentRowNumber)->setValue($currentRowWithColumnNameOnly['G']);

            if ($invalidStartDate) {
                $validator->getMessageBag()->add('G', "Please enter valid date format");
            }

            if (isset($unix_year2) && $unix_date2 && (Carbon::parse($unix_date2)->toDateString() < Carbon::today()->toDateString())) {
                $validator->getMessageBag()->add('G', "Please enter current or future date");
                $spreadsheet->getActiveSheet()->getCell('G' . $currentRowNumber)->setValue($unix_date2);
            }

            $validationErrors = $validator->getMessageBag()->getMessages();
            if (empty($currentRowWithColumnNameOnly['G'])) {
                unset($validationErrors['G']);
            }

            if (!empty($validationErrors)) {
                foreach ($validationErrors as $columnName => $errorMessage) {
                    $errorAtWhichCell = $columnName . $currentRowNumber;
                    $error            = '';
                    $error            = implode(',', $errorMessage);

                    $comment = $error;
                    $color   = new Color();

                    // Set back ground color and validation errors as comment on cell
                    $spreadsheet->getActiveSheet()
                        ->getComment($errorAtWhichCell)
                        ->getText()
                        ->createTextRun($comment)
                        ->getFont()
                        ->setBold(true)
                        ->setColor($color->setARGB(Color::COLOR_RED));

                    $spreadsheet->getActiveSheet()->getStyle($errorAtWhichCell)->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB(Color::COLOR_RED);

                    $excelFileAsError[$currentRowNumber][$columnName] = $error;
                }
            }
        }

        if (!empty($excelFileAsError) && count($excelFileAsError) > 0) {
            // generate new file with validation messsages if there is any validation error found for cell
            $fileNameOnStorage = getFileLink($fileImport->getKey(), $fileImport->uploaded_file, 'users');

            $tmpCorrectedFileName = sprintf("/tmp/%s.xls", generateProcessKey());

            $writer = IOFactory::createWriter($spreadsheet, "Xlsx");
            $writer->save($tmpCorrectedFileName);

            $fileNameArr = explode('/', $fileNameOnStorage);
            $fileName    = $fileNameArr[2];

            Storage::disk(config('medialibrary.disk_name'))->put($fileNameOnStorage, file_get_contents($tmpCorrectedFileName), 'public');
            unlink($tmpCorrectedFileName);

            $updateData['in_process']     = 0;
            $updateData['is_processed']   = 1;
            $updateData['validated_file'] = $fileName;

            $data = [
                'userName'       => (!empty($moderator)) ? $moderator->first_name : 'Company',
                'uploaded_file'  => $fileImport->uploaded_file,
                'validated_file' => $fileName,
                'module'         => 'User',
            ];

            $emailRecipients   = config('zevolifesettings.emails');
            $emailRecipients[] = $moderator->email;

            // fire import mail status event
            event(new UserImportStatusEvent($data, $emailRecipients, $company));
        } else {
            $categoriesData = \App\Models\Category::where('in_activity_level', 1)->pluck('id')->toArray();
            // import records in our database
            foreach ($activeSheet->getRowIterator() as $key => $cellIterator) {
                $currentRowWithCellId         = [];
                $currentRowWithColumnNameOnly = [];
                $currentRowNumber             = $cellIterator->getRowIndex();

                if (in_array($currentRowNumber, $skipRows)) {
                    continue;
                }

                foreach ($cellIterator->getCellIterator() as $cell) {
                    $currentColumn = $cell->getColumn();
                    if ($currentColumn > $skipColumns) {
                        continue;
                    }

                    $currentCell                                  = $currentColumn . $currentRowNumber;
                    $cellValue                                    = $cell->getValue();
                    $currentRowWithCellId[$currentCell]           = $cellValue;
                    $currentRowWithColumnNameOnly[$currentColumn] = $cellValue;
                }

                // get team using team name
                $team = Team::where('name', strtolower($currentRowWithColumnNameOnly['F']))->where('company_id', $company->id)->first();

                $currentRowWithColumnNameOnly['D'] = (isset($currentRowWithColumnNameOnly['D']) && is_numeric($currentRowWithColumnNameOnly['D'])) ? \Carbon\Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($currentRowWithColumnNameOnly['D']))->toDateString() : $currentRowWithColumnNameOnly['D'];

                $currentRowWithColumnNameOnly['G'] = (isset($currentRowWithColumnNameOnly['G']) && is_numeric($currentRowWithColumnNameOnly['G'])) ? \Carbon\Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($currentRowWithColumnNameOnly['G']))->toDateString() : $currentRowWithColumnNameOnly['G'];

                if (!empty($team)) {
                    // create user
                    $user = User::create([
                        'first_name'     => $currentRowWithColumnNameOnly['A'],
                        'last_name'      => $currentRowWithColumnNameOnly['B'],
                        'email'          => $currentRowWithColumnNameOnly['C'],
                        'is_premium'     => true,
                        'can_access_app' => true,
                        'start_date'     => isset($currentRowWithColumnNameOnly['G']) ? $currentRowWithColumnNameOnly['G'] : $company->subscription_start_date,
                    ]);

                    // attach app user role to new user
                    $role = \App\Models\Role::where('slug', 'user')->first();
                    $user->roles()->attach($role);

                    // attach team to created user
                    $user->teams()->attach($team, ['company_id' => $team->company_id, 'department_id' => $team->department_id]);

                    // calculate user age
                    $age = Carbon::parse($currentRowWithColumnNameOnly['D'], \config('app.timezone'))->age;

                    $user->profile()->create([
                        'gender'     => $currentRowWithColumnNameOnly['E'],
                        'birth_date' => $currentRowWithColumnNameOnly['D'],
                        'height'     => '100',
                        'age'        => $age,
                    ]);

                    if (!empty($categoriesData)) {
                        $user->expertiseLevels()->attach($categoriesData, ['expertise_level' => "beginner"]);
                    }
                    // set true flag in all notification modules
                    $notificationModules = config('zevolifesettings.notificationModules');
                    if (!empty($notificationModules)) {
                        foreach ($notificationModules as $key => $value) {
                            $user->notificationSettings()->create([
                                'module' => $key,
                                'flag'   => $value,
                            ]);
                        }
                    }

                    $userGoalData             = array();
                    $userGoalData['steps']    = 6000;
                    $userGoalData['calories'] = ($currentRowWithColumnNameOnly['E'] == "male") ? 2500 : 2000;
                    // create or update user goal
                    $user->goal()->updateOrCreate(['user_id' => $user->getKey()], $userGoalData);

                    // save user weight
                    $user->weights()->create([
                        'weight'   => '50',
                        'log_date' => now()->toDateTimeString(),
                    ]);

                    // calculate bmi and store
                    $bmi = 50 / pow((100 / 100.0), 2);

                    $user->bmis()->create([
                        'bmi'      => $bmi,
                        'weight'   => 50, // kg
                        'height'   => 100, // cm
                        'age'      => 0,
                        'log_date' => now()->toDateTimeString(),
                    ]);

                    // fire registration event
                    event(new UserRegisterEvent($user));
                    $user->syncWithSurveyUsers();
                }
            }

            $updateData['in_process']               = 0;
            $updateData['is_processed']             = 1;
            $updateData['is_imported_successfully'] = 1;

            $data = [
                'userName'      => (!empty($moderator)) ? $moderator->first_name : 'Company',
                'uploaded_file' => $fileImport->uploaded_file,
                'module'        => 'User',
            ];

            $emailRecipients   = config('zevolifesettings.emails');
            $emailRecipients[] = $moderator->email;

            // fire import mail status event
            event(new UserImportStatusEvent($data, $emailRecipients, $company));
        }

        $updateData['process_finished_at'] = now()->toDateTimeString();

        // update file import record with process status
        $fileImport->update($updateData);
    }

    /**
     * Question Import
     * @param $spreadsheet
     * @param $sheetnames
     * @param $fileImport
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function questionImport($spreadsheet, $sheetnames, $fileImport)
    {
        $activeSheets = [
            // 'ShortAnswer'  => [
            //     'rules'            => [
            //         'A' => ['required', 'exists:categories,display_name'], // Category
            //         'B' => ['required', 'exists:sub_categories,name'], // Category
            //         'C' => ['required', 'max:191'], // Question
            //     ],
            //     'messages'         => [
            //         'A.required' => 'The category  field is required.',
            //         'A.exists'   => 'The category  field must be available into system.',

            //         'B.required' => 'The subcategory field is required.',
            //         'B.exists'   => 'The subcategory  field must be available into system.',

            //         'C.required' => 'The question field is required.',
            //         'C.max'      => 'The question may not be greater than 191 characters.',
            //     ],
            //     'skipRows'         => [1],
            //     'skipColumns'      => 'C',
            //     'optionsStartFrom' => 'D',
            //     'db-alias'         => 'free-text',
            // ],
            'choices' => [
                'rules'                  => [
                    'A' => ['required', 'exists:zc_categories,display_name'], // Category
                    'B' => ['required', 'exists:zc_sub_categories,display_name'], // Sub Category
                    'C' => ['required', 'max:175'], // Question
                    'D' => ['max:100'], // Answer1
                    'E' => ['max:100'], // Answer2
                    'F' => ['max:100'], // Answer3
                    'G' => ['max:100'], // Answer4
                    'H' => ['max:100'], // Answer5
                    'I' => ['max:100'], // Answer6
                    'J' => ['max:100'], // Answer7
                ],
                'messages'               => [
                    'A.required' => 'The category  field is required.',
                    'A.exists'   => 'The category  field must be available into system.',

                    'B.required' => 'The subcategory field is required.',
                    'B.exists'   => 'The subcategory  field must be available into system.',

                    'C.required' => 'The question field is required.',
                    'C.max'      => 'The question may not be greater than 175 characters.',

                    'D.required' => 'The option field is required.',
                    'D.max'      => 'The option may not be greater than 100 characters.',

                    'E.required' => 'The option field is required.',
                    'E.max'      => 'The option may not be greater than 100 characters.',

                    'F.required' => 'The option field is required.',
                    'F.max'      => 'The option may not be greater than 100 characters.',

                    'G.required' => 'The option field is required.',
                    'G.max'      => 'The option may not be greater than 100 characters.',

                    'H.required' => 'The option field is required.',
                    'H.max'      => 'The option may not be greater than 100 characters.',

                    'I.required' => 'The option field is required.',
                    'I.max'      => 'The option may not be greater than 100 characters.',

                    'J.required' => 'The option field is required.',
                    'J.max'      => 'The option may not be greater than 100 characters.',
                ],
                'skipRows'               => [1],
                'skipColumns'            => 'J',
                'optionsStartFrom'       => 'D',
                'db-alias'               => 'choice',
                'minimumRequiredOptions' => 2,
            ],
        ];
        // Extract data from excel sheet.
        // To array of column and row data as Excel sheet data
        $excelData           = [];
        foreach ($activeSheets as $sheetName => $options) {
            // Set active sheet for reading.
            $activeSheet = $spreadsheet->getSheetByName($sheetName);
            if (null == $activeSheet) {
                unset($activeSheets[$sheetName]);
                continue;
            }
            // Skipp rows number from reading of current sheet.
            $skipRows = $options['skipRows'];
            // Read upto this column only.
            $skipColumns           = $options['skipColumns'];
            $processData           = $this->getExcelData($activeSheet, $skipRows, $skipColumns);
            $excelData[$sheetName] = $processData['excelData'];
        }
        //get categories and subcategories array for fetching id
        $categoriesArray = SurveyCategory::get()
            ->pluck('id', 'display_name')
            ->toArray();
        $categoriesArrayConverted = array_change_key_case($categoriesArray, CASE_LOWER);
        $checkCategories          = [];
        foreach ($categoriesArrayConverted as $key => $value) {
            $checkCategories[] = $key;
        }

        // Validate extracted data with rules and message.
        $excelFileAsError = [];
        // Collect all validation error in [row][column] = has error Array format for debugging purpose only.
        foreach ($activeSheets as $sheetName => $options) {
            $spreadsheet->setActiveSheetIndexByName($sheetName);
            //Value passed as pass by reference, To modify date field.
            foreach ($excelData[$sheetName] as $currentRowNumber => $rowData) {
                $rules    = $options['rules'];
                $messages = $options['messages'];

                if (isset($rowData['A']) && in_array(strtolower($rowData['A']), $checkCategories)) {
                    $categoriesId      = $categoriesArrayConverted[strtolower($rowData['A'])];
                    $subcategoriesList = SurveySubCategory::where('category_id', $categoriesId)
                        ->get()
                        ->pluck('display_name')
                        ->toArray();
                    $subcategoriesListConverted = array_map('strtolower', $subcategoriesList);

                    $rowData['B'] = isset($rowData['B']) ? strtolower($rowData['B']) : null;

                    $rules['B'] = [
                        'required',
                        Rule::in($subcategoriesListConverted),
                    ];

                    $messages['B.in'] = 'The subcategory is not mapped to the category.';
                }

                $validator = Validator::make($rowData, $rules, $messages);
                if ($validator->fails()) {
                    foreach ($validator->getMessageBag()->getMessages() as $columnName => $errorMessage) {
                        $errorAtWhichCell = $columnName . $currentRowNumber;
                        $error            = implode(',', $errorMessage);
                        $color            = new Color();
                        $spreadsheet->getActiveSheet()
                            ->getComment($errorAtWhichCell)
                            ->getText()
                            ->createTextRun($error)
                            ->getFont()
                            ->setBold(true)
                            ->setColor($color->setARGB(Color::COLOR_RED));

                        // Set back ground color
                        $spreadsheet->getActiveSheet()->getStyle($errorAtWhichCell)->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setARGB(Color::COLOR_RED);
                        $excelFileAsError[$currentRowNumber][$columnName] = $error;
                    }
                }
                if ($sheetName == 'choices') {
                    $choiceAvailableOptions = 0;
                    $minimumOptionsRequired = $options['minimumRequiredOptions'];
                    // Validation is passed , Now check weather atlease 2 options are set or not.
                    $currentColumnValue = $options['optionsStartFrom'];
                    $values             = [];
                    foreach ($rowData as $columnName => $value) {
                        $currentColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($currentColumnValue);
                        $skipColumnIndex    = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($columnName);
                        if ($currentColumnIndex > $skipColumnIndex) {
                            // skip unwanted column if not set.
                            continue;
                        }
                        if (isset($value) && !empty($value) && strlen((string) $value) >= 1) {
                            $choiceAvailableOptions = $choiceAvailableOptions + 1;
                            $values[$columnName]    = $value;
                        }
                    }
                    if ($choiceAvailableOptions < $minimumOptionsRequired) {
                        // Minum option is not available to add as error message.
                        $error = 'At least two options are required for this question.';
                        $color = new Color();

                        $newColumnName = $options['optionsStartFrom'];
                        $myCell        = [];
                        for ($i = 0; $i <= 6; $i++) {
                            $errorAtWhichCell = $newColumnName . $currentRowNumber;
                            $myCell[]         = $errorAtWhichCell;
                            $spreadsheet->getActiveSheet()
                                ->getComment($errorAtWhichCell)
                                ->getText()
                                ->createTextRun($error)
                                ->getFont()
                                ->setBold(true)
                                ->setColor($color->setARGB(Color::COLOR_RED));

                            // Set back ground color
                            $spreadsheet->getActiveSheet()->getStyle($errorAtWhichCell)->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->getStartColor()->setARGB(Color::COLOR_RED);
                            $excelFileAsError[$currentRowNumber][$columnName] = $error;
                            $newColumnName++;
                        }
                    }
                }
            }
        }
        // If there is no error found than start import into database.
        if (!empty($excelFileAsError) && count($excelFileAsError) > 0) {
            // generate new file with validation messsages if there is any validation error found for cell
            $fileNameOnStorage = getFileLink($fileImport->getKey(), $fileImport->uploaded_file, 'questions');

            $tmpCorrectedFileName = sprintf("/tmp/%s.xls", generateProcessKey());

            $writer = IOFactory::createWriter($spreadsheet, "Xlsx");
            $writer->save($tmpCorrectedFileName);

            $fileNameArr = explode('/', $fileNameOnStorage);
            $fileName    = $fileNameArr[2];

            Storage::disk(config('medialibrary.disk_name'))->put($fileNameOnStorage, file_get_contents($tmpCorrectedFileName), 'public');
            unlink($tmpCorrectedFileName);

            $updateData['in_process']     = 0;
            $updateData['is_processed']   = 1;
            $updateData['validated_file'] = $fileName;

            $data = [
                'userName'       => 'Zevo Admin',
                'uploaded_file'  => $fileImport->uploaded_file,
                'validated_file' => $fileName,
                'module'         => 'Question',
            ];

            $emailRecipients   = config('zevolifesettings.emails');
            $emailRecipients[] = 'admin@zevo.app';

            // fire import mail status event
            event(new UserImportStatusEvent($data, $emailRecipients));
        } else {
            $categories                 = SurveyCategory::pluck('id', 'display_name')->toArray();
            $categoriesConverted        = array_change_key_case($categories, CASE_LOWER);
            $questionTypes              = ZcQuestionType::pluck('id', 'display_name')->toArray();
            $questionTypesConverted     = array_change_key_case($questionTypes, CASE_LOWER);
            $now                        = Carbon::now();
            foreach ($activeSheets as $sheetName => $options) {
                //Value passed as pass by reference, To modify date field.
                $sheetData = $excelData[$sheetName];
                foreach ($sheetData as $rowValue) {
                    $currentCategory    = strtolower(trim($rowValue['A']));
                    $currentSubCategory = strtolower(trim($rowValue['B']));
                    $subCategories      = SurveySubCategory::select('id', 'display_name')
                        ->where('category_id', $categoriesConverted[$currentCategory])
                        ->get()->pluck('id', 'display_name')->toArray();
                    $subCategoriesConverted = array_change_key_case($subCategories, CASE_LOWER);
                    if ($sheetName == 'choices') {
                        $questionPayLoad = [
                            'category_id'      => $categoriesConverted[$currentCategory],
                            'sub_category_id'  => $subCategoriesConverted[$currentSubCategory],
                            'question_type_id' => $questionTypesConverted[$options['db-alias']],
                            'title'            => $rowValue['C'],
                            'status'           => '0',
                            'created_at'       => $now,
                            'updated_at'       => $now,
                        ];
                        $questionObject = ZcQuestion::create($questionPayLoad);

                        $currentColumnValue = $options['optionsStartFrom'];

                        $questionMeta = [
                            'question_id' => $questionObject->id,
                            'score'       => 0,
                            'choice'      => 'meta',
                            'created_at'  => $now,
                            'updated_at'  => $now,
                        ];
                        $optoinsArray   = [];
                        $optoinsArray[] = $questionMeta;
                        $scaleScore     = 1;
                        foreach ($rowValue as $columnName => $value) {
                            if ($currentColumnValue != $columnName) {
                                // skip unwanted column if not set.
                                continue;
                            }
                            $option = [
                                'question_id' => $questionObject->id,
                                'score'       => $scaleScore,
                                'choice'      => $value,
                                'created_at'  => $now,
                                'updated_at'  => $now,
                            ];
                            $scaleScore++;
                            $currentColumnValue++;
                            if (!empty($value)) {
                                array_push($optoinsArray, $option);
                            }
                        }
                        ZcQuestionOption::insert($optoinsArray);
                    }
                }
            }

            $updateData['in_process']               = 0;
            $updateData['is_processed']             = 1;
            $updateData['is_imported_successfully'] = 1;

            $data = [
                'userName'      => 'Zevo Admin',
                'uploaded_file' => $fileImport->uploaded_file,
                'module'        => 'Question',
            ];

            $emailRecipients = config('zevolifesettings.emails');

            // fire import mail status event
            event(new UserImportStatusEvent($data, $emailRecipients));
        }

        $updateData['process_finished_at'] = now()->toDateTimeString();

        // update file import record with process status
        $fileImport->update($updateData);
    }

    /**
     * To array of excel sheet.
     *
     * @param $activeSheet
     * @return mixed
     */
    public function getExcelData($activeSheet, $skipRows, $skipColumns)
    {
        $excelData = [];
        // Extract data from excel sheet.
        foreach ($activeSheet->getRowIterator() as $cellIterator) {
            $currentRowWithCellId         = [];
            $currentRowWithColumnNameOnly = [];
            $currentRowNumber             = $cellIterator->getRowIndex();
            if (in_array($currentRowNumber, $skipRows)) {
                continue;
            }
            $totalColumn = 0;
            foreach ($cellIterator->getCellIterator() as $cell) {
                $currentColumn      = $cell->getColumn();
                $cellValue          = $cell->getValue();
                $currentColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($currentColumn);
                $skipColumnIndex    = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($skipColumns);
                if ($currentColumnIndex > $skipColumnIndex) {
                    continue;
                }

                $currentRowWithCellId[$currentRowNumber][$currentColumn] = $cellValue;
                $currentRowWithColumnNameOnly[$currentColumn]            = $cellValue;
                $excelData[$currentRowNumber][$currentColumn]            = $cellValue;
                $totalColumn++;
            }
            // Check weather the current row is null.
            $isEmptyCellCount = 0;
            foreach ($excelData[$currentRowNumber] as $valueData) {
                if (empty($valueData)) {
                    $isEmptyCellCount = $isEmptyCellCount + 1;
                }
            }
            if ($isEmptyCellCount === $totalColumn) {
                // If full row is empty then remove it.
                unset($excelData[$currentRowNumber]);
            }
        }
        return ['excelData' => $excelData, 'currentRowWithColumnNameOnly' => $currentRowWithColumnNameOnly];
    }
}
