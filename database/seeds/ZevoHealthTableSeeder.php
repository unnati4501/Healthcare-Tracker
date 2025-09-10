<?php
namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ZevoHealthTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            // Connect to ZevoHealth's database
            $zevohealth_database = DB::connection('mysql-zevohealth');

            $zevoCompaniesData = $zevohealth_database->table('companies')->whereIn('id', [8, 9, 13, 30, 42, 43, 44, 49, 51, 52, 53])->get();

            // Get table data from zevohealth tables
            $zevoCompanies = [];
            foreach ($zevoCompaniesData as $c => $zevoCompany) {
                // Save data to staging database - default db connection

                $zevoCompanies[$c]['name']        = $zevoCompany->name;
                $zevoCompanies[$c]['industry_id'] = $zevoCompany->industry_id;
                $zevoCompanies[$c]['description'] = $zevoCompany->description;
                $zevoCompanies[$c]['size']        = $zevoCompany->size;
                $zevoCompanies[$c]['has_domain']  = $zevoCompany->is_domain_agnostic;

                //fetch all domains of company
                $zevoDomains = $zevohealth_database->table('company_domains')->where('company_id', $zevoCompany->id)->get();

                if ($zevoCompany->is_domain_agnostic && $zevoDomains->count() > 0) {
                    $zevoCompanies[$c]['zevoDomains'] = [];
                    foreach ($zevoDomains as $do => $zevoDomain) {
                        $zevoCompanies[$c]['zevoDomains'][$do]['domain'] = $zevoDomain->domain;
                    }
                }

                //fetch all locations of company
                $zevoLocations = $zevohealth_database->table('company_location')->where('company_id', $zevoCompany->id)->get();

                if ($zevoLocations->count() > 0) {
                    $zevoCompanies[$c]['zevoLocations'] = [];
                    foreach ($zevoLocations as $l => $zevoLocation) {
                        $zevoCompanies[$c]['zevoLocations'][$l]['name']          = $zevoLocation->name;
                        $zevoCompanies[$c]['zevoLocations'][$l]['address_line1'] = $zevoLocation->address_line1;
                        $zevoCompanies[$c]['zevoLocations'][$l]['address_line2'] = $zevoLocation->address_line2;
                        $zevoCompanies[$c]['zevoLocations'][$l]['state_id']      = $zevoLocation->state_id;
                        $zevoCompanies[$c]['zevoLocations'][$l]['country_id']    = $zevoLocation->country_id;
                        $zevoCompanies[$c]['zevoLocations'][$l]['zipcode']       = $zevoLocation->zipcode;
                        $zevoCompanies[$c]['zevoLocations'][$l]['timezone']      = $zevoLocation->timezone;
                        $zevoCompanies[$c]['zevoLocations'][$l]['default']       = $zevoLocation->default;
                    }
                }

                $zevoCompanyModerators = $zevohealth_database->table('company_moderator')->join('users', 'users.id', '=', 'company_moderator.user_id')->select('users.*')->where('company_id', $zevoCompany->id)->get();

                if ($zevoCompanyModerators->count() > 0) {
                    $zevoCompanies[$c]['companyModerators'] = [];
                    foreach ($zevoCompanyModerators as $cm => $zevoCompanyModerator) {
                        $mdtrProfile = $zevohealth_database->table('user_profiles')->where('user_id', $zevoCompanyModerator->id)->first();

                        $zevoCompanies[$c]['companyModerators'][$cm]['first_name']     = $mdtrProfile->first_name ?? "First Name";
                        $zevoCompanies[$c]['companyModerators'][$cm]['last_name']      = $mdtrProfile->last_name ?? "Last Name";
                        $zevoCompanies[$c]['companyModerators'][$cm]['email']          = $zevoCompanyModerator->email;
                        $zevoCompanies[$c]['companyModerators'][$cm]['password']       = $zevoCompanyModerator->password;
                        $zevoCompanies[$c]['companyModerators'][$cm]['timezone']       = $zevoCompanyModerator->timezone;
                        $zevoCompanies[$c]['companyModerators'][$cm]['last_login_at']  = $zevoCompanyModerator->last_login_at ?? now()->format('Y-m-d h:i:s');
                        $zevoCompanies[$c]['companyModerators'][$cm]['can_access_app'] = false;
                        $zevoCompanies[$c]['companyModerators'][$cm]['is_premium']     = true;
                        $zevoCompanies[$c]['companyModerators'][$cm]['birth_date']     = $mdtrProfile->birth_date ?? '1990-01-01';
                        $zevoCompanies[$c]['companyModerators'][$cm]['age']            = $mdtrProfile->age ?? 29;
                        $zevoCompanies[$c]['companyModerators'][$cm]['gender']         = $mdtrProfile->gender ?? 'm';
                    }
                }

                $zevoCompanies[$c]['zevoDepartments'] = [];

                $zevoDepts = $zevohealth_database->table('company_departments')->where('company_id', $zevoCompany->id)->get();

                if ($zevoDepts->count() > 0) {
                    foreach ($zevoDepts as $d => $zevoDept) {
                        $zevoCompanies[$c]['zevoDepartments'][$d]['name'] = $zevoDept->name;

                        $zevoCompanies[$c]['zevoDepartments'][$d]['zevoTeams'] = [];

                        $zevoTeams = $zevohealth_database->table('department_teams')->where('department_id', $zevoDept->id)->get();

                        if ($zevoTeams->count() > 0) {
                            foreach ($zevoTeams as $t => $zevoTeam) {
                                $zevoCompanies[$c]['zevoDepartments'][$d]['zevoTeams'][$t]['name'] = $zevoTeam->name;
                                $zevoCompanies[$c]['zevoDepartments'][$d]['zevoTeams'][$t]['code'] = $zevoTeam->code;

                                $zevoUsers = $zevohealth_database->table('users')->join('team_user', 'users.id', '=', 'team_user.user_id')->select('users.*')->where('team_user.team_id', $zevoTeam->id)->get();

                                $zevoCompanies[$c]['zevoDepartments'][$d]['zevoTeams'][$t]['zevoUsers'] = [];

                                if ($zevoUsers->count() > 0) {
                                    foreach ($zevoUsers as $u => $zevoUser) {
                                        $userProfile = $zevohealth_database->table('user_profiles')->where('user_id', $zevoUser->id)->first();

                                        $zevoCompanies[$c]['zevoDepartments'][$d]['zevoTeams'][$t]['zevoUsers'][$u]['first_name']     = $userProfile->first_name ?? "First Name";
                                        $zevoCompanies[$c]['zevoDepartments'][$d]['zevoTeams'][$t]['zevoUsers'][$u]['last_name']      = $userProfile->last_name ?? "Last Name";
                                        $zevoCompanies[$c]['zevoDepartments'][$d]['zevoTeams'][$t]['zevoUsers'][$u]['email']          = $zevoUser->email;
                                        $zevoCompanies[$c]['zevoDepartments'][$d]['zevoTeams'][$t]['zevoUsers'][$u]['password']       = $zevoUser->password;
                                        $zevoCompanies[$c]['zevoDepartments'][$d]['zevoTeams'][$t]['zevoUsers'][$u]['timezone']       = $zevoUser->timezone;
                                        $zevoCompanies[$c]['zevoDepartments'][$d]['zevoTeams'][$t]['zevoUsers'][$u]['last_login_at']  = $zevoUser->last_login_at ?? now()->format('Y-m-d h:i:s');
                                        $zevoCompanies[$c]['zevoDepartments'][$d]['zevoTeams'][$t]['zevoUsers'][$u]['can_access_app'] = true;
                                        $zevoCompanies[$c]['zevoDepartments'][$d]['zevoTeams'][$t]['zevoUsers'][$u]['is_premium']     = true;
                                        $zevoCompanies[$c]['zevoDepartments'][$d]['zevoTeams'][$t]['zevoUsers'][$u]['birth_date']     = $userProfile->birth_date ?? '1990-01-01';
                                        $zevoCompanies[$c]['zevoDepartments'][$d]['zevoTeams'][$t]['zevoUsers'][$u]['age']            = $userProfile->age ?? 29;
                                        $zevoCompanies[$c]['zevoDepartments'][$d]['zevoTeams'][$t]['zevoUsers'][$u]['gender']         = $userProfile->gender ?? 'm';
                                    }
                                }
                            }
                        }
                    }
                }
            }

            \DB::beginTransaction();

            foreach ($zevoCompanies as $zevoCompany) {
                // Save data to staging database - default db connection
                $company = Company::create([
                    'industry_id' => $zevoCompany['industry_id'],
                    'name'        => $zevoCompany['name'],
                    'description' => $zevoCompany['description'],
                    'size'        => $zevoCompany['size'],
                    'has_domain'  => $zevoCompany['has_domain'],
                    'status'      => true,
                ]);

                $defaultDept = $company->departments()->create([
                    'name'       => 'Default',
                    'default'    => true,
                    'company_id' => $company->id,
                ]);

                $defaultTeam = $defaultDept->teams()->create([
                    'name'       => 'Default',
                    'default'    => true,
                    'company_id' => $company->id,
                ]);

                if (!empty($zevoCompany['zevoDomains']) && count($zevoCompany['zevoDomains'])) {
                    foreach ($zevoCompany['zevoDomains'] as $zevoDomain) {
                        $company->domains()->create(['domain' => $zevoDomain['domain']]);
                    }
                }

                if (!empty($zevoCompany['zevoLocations']) && count($zevoCompany['zevoLocations'])) {
                    foreach ($zevoCompany['zevoLocations'] as $zevoLocation) {
                        $company->locations()->create([
                            'name'          => $zevoLocation['name'],
                            'address_line1' => $zevoLocation['address_line1'],
                            'address_line2' => $zevoLocation['address_line2'],
                            'state_id'      => $zevoLocation['state_id'],
                            'country_id'    => $zevoLocation['country_id'],
                            'postal_code'   => $zevoLocation['zipcode'],
                            'timezone'      => $zevoLocation['timezone'],
                            'default'       => $zevoLocation['default'],
                        ]);
                    }
                }

                if (!empty($zevoCompany['companyModerators']) && count($zevoCompany['companyModerators'])) {
                    // attach company admin role to new user
                    $adminRole = \App\Models\Role::where('slug', 'company_admin')->first();

                    foreach ($zevoCompany['companyModerators'] as $companyModerator) {
                        $alreadyTaken = User::where('email', $companyModerator['email'])->first();

                        if ($alreadyTaken == null) {
                            $mdtr = User::create([
                                'first_name'    => $companyModerator['first_name'],
                                'last_name'     => $companyModerator['last_name'],
                                'email'         => $companyModerator['email'],
                                'timezone'      => $companyModerator['timezone'],
                                'password'      => $companyModerator['password'],
                                'last_login_at' => $companyModerator['last_login_at'],
                            ]);

                            $mdtr->roles()->attach($adminRole);

                            $mdtr->profile()->create([
                                'birth_date' => $companyModerator['birth_date'],
                                'age'        => $companyModerator['age'],
                                'gender'     => ($companyModerator['gender'] == 'm') ? 'male' : 'female',
                            ]);

                            $defaultTeam->users()->attach($mdtr, [
                                'company_id'    => $company->id,
                                'department_id' => $defaultDept->id,
                            ]);

                            $company->moderators()->attach($mdtr);
                        }
                    }
                }

                $defaultLocation = $company->getDefaultLocation();

                if (!empty($zevoCompany['zevoDepartments']) && count($zevoCompany['zevoDepartments'])) {
                    foreach ($zevoCompany['zevoDepartments'] as $zevoDepartment) {
                        $department = $company->departments()->create([
                            'name'       => $zevoDepartment['name'],
                            'company_id' => $company->id,
                        ]);

                        $department->departmentlocations()->attach($defaultLocation, [
                            'company_id'          => $company->id,
                            'department_id'       => $department->id,
                            'company_location_id' => $defaultLocation->id,
                            'created_at'          => Carbon::now(),
                        ]);

                        if (!empty($zevoDepartment['zevoTeams']) && count($zevoDepartment['zevoTeams'])) {
                            foreach ($zevoDepartment['zevoTeams'] as $zevoT) {
                                $team = $department->teams()->create([
                                    'name'       => $zevoT['name'],
                                    'company_id' => $company->id,
                                ]);

                                $team->teamlocation()->attach($defaultLocation, [
                                    'team_id'             => $team->id,
                                    'department_id'       => $department->id,
                                    'company_id'          => $company->id,
                                    'company_location_id' => $defaultLocation->id,
                                    'created_at'          => Carbon::now(),
                                ]);

                                if (!empty($zevoT['zevoUsers']) && count($zevoT['zevoUsers'])) {
                                    // attach user role to new user
                                    $role = \App\Models\Role::where('slug', 'user')->first();

                                    foreach ($zevoT['zevoUsers'] as $zevoU) {
                                        $alreadyTaken = User::where('email', $zevoU['email'])->first();

                                        if ($alreadyTaken == null) {
                                            $user = User::create([
                                                'first_name'    => $zevoU['first_name'],
                                                'last_name'     => $zevoU['last_name'],
                                                'email'         => $zevoU['email'],
                                                'timezone'      => $zevoU['timezone'],
                                                'password'      => $zevoU['password'],
                                                'last_login_at' => $zevoU['last_login_at'],
                                            ]);

                                            $user->roles()->attach($role);

                                            $user->profile()->create([
                                                'birth_date' => $zevoU['birth_date'],
                                                'age'        => $zevoU['age'],
                                                'gender'     => ($zevoU['gender'] == 'm') ? 'male' : 'female',
                                            ]);

                                            $team->users()->attach($user, [
                                                'company_id'    => $company->id,
                                                'department_id' => $department->id,
                                            ]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            \DB::commit();

        } catch (\Illuminate\Database\QueryException $e) {
            \DB::rollback();
            $this->command->error("SQL Error: " . $e->getMessage() . "\n");
        }
    }
}
