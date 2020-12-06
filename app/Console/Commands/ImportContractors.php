<?php

namespace App\Console\Commands;

use App\Models\Contractor;
use App\Models\Facility;
use App\Models\HiringOrganization;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\Console\Helper\Table;

class ImportContractors extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:contractors {contractorFilePath : File to be imported.} {hiringOrg : Hiring Organization name to import contractors to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import contractors from a csv';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Get CLI arguments
        $contractorFilePath = $this->argument('contractorFilePath');
        $hiringOrg = $this->argument('hiringOrg');

        // Call importer
        Excel::import(new ContractorImport($hiringOrg, $this, $this->output), $contractorFilePath);

    }
}

// TODO: Move this into a job to be used elsewhere on site
class ContractorImport implements ToCollection
{
    private $hiringOrgName;
    private $console;
    private $output;
    private $newContractors = 0;

    /**
     * Array of columns and their indexes
     * NOTE: Name and email are singular, but facility is multiple
     * Anything defined as null will be set as a single instance
     * Anything defined as [] will have a series of indexes pushed to the array
     *
     * @var array
     */
    protected $columns = [
        'Company Name' => null,
        'Email' => null,
        'Facility' => null,
    ];

    public function __construct($hiringOrgName, $console, $output)
    {
        $this->hiringOrgName = $hiringOrgName;
        $this->console = $console;
        $this->output = $output;
    }

    /**
     * Handles converting the rows into data & importing it into the site
     *
     * @param Collection $rows
     * @return void
     */
    public function collection(Collection $rows)
    {
        // Attempts to get hiring org by name (throws exception if it doesn't exist)
        $hiringOrg = $this->getHiringOrgByName($this->hiringOrgName);

        $this->defineColumns($rows[0]);

        if ($this->output->isVerbose()) {
            $this->console->info("Contractors to import");
            $table = $this->console->table(
                ['Contractor', 'EMail', 'Facility'],
                $rows,
            );
        }

        $missingFacilities = $this->getMissingFacilities($hiringOrg, $rows);

        if (sizeof($missingFacilities) > 0) {
            // $table = $this->console->table(
            //     ['Facility', 'Count'],
            //     $missingFacilities,
            // );
            $toAddFacilities = $this->console->confirm("There are missing facilities in the system. Would you like to add them?");
            if ($toAddFacilities) {
                $this->addFacilities($hiringOrg, $missingFacilities);
            }
        }

        // Checking for duplicate contractors
        $duplicateContractorsCollection = $this->verifyNoContractorDuplicates($rows);

        // Displaying table of duplicate contractors, if they're present
        $duplicateContractors = $duplicateContractorsCollection->all();
        if (sizeof($duplicateContractors) != 0) {
            if ($this->output->isVerbose()) {
                $this->console->table(
                    ['Contractor Name', 'Count'],
                    $duplicateContractors,
                );
            }
            $this->console->warn("Duplicate contractors with the same name found. Please remove the duplicates, and try again.");
            Log::stack(['daily'])
                ->warning(
                    "Duplicate Contractors were found in the import file. Please remove the duplicate contractor names, and try again.",
                    [
                        "Hiring Organization" => $this->hiringOrgName,
                        'Duplicate Contractors' => $duplicateContractorsCollection->map(function ($value, $key) {
                            return $value[1];
                        })
                            ->all(),
                    ]
                );
        } else {
            if ($this->output->isVerbose()) {
                $this->console->info("No duplicate contractors were found");
            }
        }

        // Defining the columns

        $progress = $this->output->createProgressBar(sizeof($rows));
        $progress->setFormat("%current%/%max% [%bar%] %percent:3s%% %message%");

        foreach ($rows as $index => $row) {
            $progress->setMessage("Importing $row[0]...");
            $progress->advance();
            // If the row is the header, verify column names
            // Up to the user to ensure that the data in columns is correctly placed
            if ($index == 0) {
                $this->defineColumns($row);
            } else {
                $this->importContractor($row, $hiringOrg);
            }
        }

        $progress->setMessage("Done. $this->newContractors contractor accounts created");
        $progress->finish();
        $this->console->info("");
        $this->console->info("Completed");
        if (config('app.env') != 'development') {
            Log::stack(['slack'])
                ->notice("Contractors have been imported", [
                    'Hiring Organization' => $hiringOrg->name,
                    'Count' => sizeof($rows),
                    'Environment' => env('APP_NAME'),
                ]);
        }
    }

    public function importContractor($row, $hiringOrg)
    {
        $contractorName = $this->getContractorNameColumn($row);
        $email = $this->getContractorEmailColumn($row);

        Log::info("Importing $contractorName for $hiringOrg->name");

        if (!isset($contractorName) || $contractorName == '') {
            if ($this->output->isVerbose()) {
                $this->console->error("Skipping row as contractor name was not set");
            }
            return;
        }
        if (!isset($email) || $email == '') {
            if ($this->output->isVerbose()) {
                $this->console->error("Skipping row as email was not set");
            }
            return;
        }

        // Check to see if email exists
        $user = User::where('email', $email)->first();

        // Create user account if it does not exist
        if (!isset($user)) {
            $user = $this->createUser($contractorName, $email);
        }

        // Try and get contractor by email
        // If it doesn't exist, create contractor account
        $contractor = $this->getContractorByEmail($email);
        if (!isset($contractor)) {
            $contractor = $this->createContractor($contractorName, null);
        }

        $role = $this->createRole($contractor, $user);

        // Connect the hiring org to the contractor
        $this->connectHiringOrgToContractor($hiringOrg, $contractor);

        // Connect the contractor to the hiring org's facility
        $facilityName = $this->cleanText($this->getContractorFacilityColumn($row));
        if (!isset($facilityName)) {
            $this->console->error("Skipping adding facility for $email - facility was not specified");
            return;
        }
        $this->connectContractorToFacility($contractor, $hiringOrg, $facilityName);
    }

    /**
     * Iterates through the rows and returns a list of contractor names that are duplicated in the list
     *
     * @param [type] $rows
     * @return void
     */
    public function verifyNoContractorDuplicates(Collection $rows)
    {
        // $contractorMap = (object)[];
        $contractorMap = new Collection([]);

        foreach ($rows as $row) {
            // Getting contractor name from row
            $contractorName = $row[0];

            // Initializing map for contractor if it doesn't exist
            if (!$contractorMap->has($contractorName)) {
                $contractorMap->put($contractorName, 0);
            }

            // Increasing contractor count by 1
            $contractorMap->put($contractorName, $contractorMap->get($contractorName) + 1);
        }

        $contractorsWithDuplicates = $contractorMap
            ->filter(function ($value, $key) {
                return $value > 1;
            })
            ->map(function ($value, $key) {
                return [$key, $value];
            });

        return $contractorsWithDuplicates;
    }

    /**
     * Creates & returns user account
     *
     * @param [type] $row
     * @return void
     */
    public function createUser($contractorName, $email)
    {
        if (!isset($email)) {
            throw new Exception("Email was not passed in");
        }
        // Checking to see if user already exists
        $userExists = User::where('email', $email)->first() != null;
        if ($userExists) {
            throw new Exception("E-Mail already exists, can't create a new user with the email of $email");
        }

        $user = User::create([
            'first_name' => $contractorName,
            'last_name' => '',
            'email' => $email,
            'password' => bcrypt(rand(0, 100000000)),
        ]);

        return $user;
    }

    /**
     * Creates a contractor account based on the row and with a role
     *
     * @param [type] $row
     * @param [type] $role
     * @return void
     */
    public function createContractor($contractorName, $role)
    {
        $contractor = Contractor::create([
            'name' => $contractorName,
            'owner_id' => is_null($role) ? 0 : $role->id,
        ]);

        $this->newContractors += 1;

        return $contractor;
    }

    /**
     * Gets the contractor organization by email
     *
     * @param [type] $email
     * @return void
     */
    public function getContractorByEmail($email)
    {
        $user = User::where('email', $email);
        if (!isset($user)) {
            throw new Exception("User with the email $email could not be found. Can't find contractor by email");
        }

        $contractorData = DB::table('users')
            ->where('users.email', $email)
            ->join('roles', 'roles.user_id', '=', 'users.id')
            ->join('contractors', function ($join) {
                $join
                    ->on('contractors.id', '=', 'roles.entity_id')
                    ->where('roles.entity_key', 'contractor');
            })
            ->first();

        if (!isset($contractorData)) {
            if ($this->output->isVerbose()) {
                $this->console->warn("Contractor data was null. Could not find contractor information for the email $email");
            }
            return null;
        }

        $contractor = Contractor::where('id', $contractorData->id)->first();

        if (!isset($contractor)) {
            if ($this->output->isVerbose()) {
                $this->console->warn("Contractor could not be found with a user of $email");
            }
            return null;
        }

        return $contractor;
    }

    /**
     * Creates owner/admin role between the contractor and user
     *
     * @param [type] $contractor
     * @param [type] $user
     * @return void
     */
    public function createRole($contractor, $user)
    {
        $owner = Role::where('entity_key', 'contractor')
            ->where('entity_id', $contractor->id)
            ->where('role', 'owner')
            ->first();

        $user_data = [
            'user_id' => $user->id,
            'entity_key' => 'contractor',
            'role' => 'owner',
            'entity_id' => $contractor->id,
            'requires_payment' => 1,
        ];

        // If owner exists, just connect the account as an admin
        // NOTE: contractors should always be new, so admins shouldn't be created
        $hasOwner = !is_null($owner);
        $user_data['role'] = !$hasOwner ? 'owner' : 'admin';

        $role = Role::create($user_data);

        // Setting new owner ID
        if (!$hasOwner) {
            $contractor->update([
                'owner_id' => $role->id,
            ]);
        }

        return $role;
    }

    /**
     * Attempts to get a hiring org by name.
     * If name doesn't exist, throws an exception
     *
     * @param [type] $hiringOrgName
     * @return void
     */
    public function getHiringOrgByName($hiringOrgName)
    {
        $hiringOrgCount = HiringOrganization::where('name', $hiringOrgName)->count();
        if ($hiringOrgCount > 1) {
            throw new Exception("More than one Hiring Org with the name $hiringOrgName");
        }

        $hiringOrg = HiringOrganization::where('name', $hiringOrgName)
            ->first();
        if (is_null($hiringOrg)) {
            throw new Exception("Hiring org with the name of '$hiringOrgName' could not be found!");
        }
        return $hiringOrg;
    }

    /**
     * Create a connection between the hiring org and contractor
     *
     * @param [type] $hiringOrg
     * @param [type] $contractor
     * @return void
     */
    public function connectHiringOrgToContractor($hiringOrg, $contractor)
    {
        // Error handling
        if (is_null($contractor)) {
            throw new Exception("Contractor was null");
        }
        if (is_null($hiringOrg)) {
            throw new Exception("Hiring Org was null");
        }
        if (is_null($hiringOrg->id)) {
            throw new Exception("Could not get Hiring Org ID");
        }

        // Avoiding duplicates
        $hiringOrgContractorConnection = DB::table('contractor_hiring_organization')
            ->where('hiring_organization_id', $hiringOrg->id)
            ->where('contractor_id', $contractor->id)
            ->first();

        $isConnected = $hiringOrgContractorConnection != null;

        if (!$isConnected) {
            $contractor->hiringOrganizations()->attach($hiringOrg->id);
            DB::table('contractor_hiring_organization')
                ->where('hiring_organization_id', $hiringOrg->id)
                ->where('contractor_id', $contractor->id)
                ->update([
                    'accepted' => 0,
                    'invite_code' => base64_encode(random_bytes(10)),
                ]);
        }
    }

    /**
     * Create a connection between the contractor and facility
     *
     * @param [type] $contractor
     * @param [type] $hiringOrg
     * @param [type] $facilityName
     * @return void
     */
    public function connectContractorToFacility($contractor, $hiringOrg, $facilityName)
    {
        $facility = $hiringOrg
            ->fresh()
            ->facilities
            ->where('name', $facilityName)
            ->first();

        if (is_null($facility)) {
            throw new Exception("'$facilityName' facility could not be found");
        }

        // TODO: This script is only attaching the first one, not all. Need to fix

        // Sync may remove all other connections - second argument normally was provided to maintin existing values, but no longer works
        // Changing ->sync to
        $contractor->facilities()->attach($facility->id);
    }

    /**
     * Takes in first row, and defines $this->columns
     *
     * @param [type] $firstRow
     * @return void
     */
    public function defineColumns($firstRow)
    {

        foreach ($firstRow as $index => $column) {
            try {
                // If column is defined as an array, push index to array
                if ($this->columns[$column] === []) {
                    $this->columns[$column][] = $index;
                }
                // Else if the column is just not set, set the value to be the id
                else if (!isset($this->columns[$column])) {
                    $this->columns[$column] = $index;
                }

            } catch (Exception $e) {
                $this->console->info("");
                $this->console->error("Column '$column' was most likely defined in columns array. Please define it in the importer, and specify if its null or array.");
                $this->console->info("");
                if (!$this->console->confirm('Column will not be used. Do you wish to continue?')) {
                    throw new Exception("Undefined Column in spreadsheet: '$column'");
                }
            }
        }

    }

    public function getContractorNameColumn($row)
    {
        $columnText = 'Company Name';
        return $this->getColumn($row, $columnText);
    }

    public function getContractorEmailColumn($row)
    {
        $columnText = 'Email';
        return $this->getColumn($row, $columnText);
    }

    public function getContractorFacilityColumn($row)
    {
        $columnText = 'Facility';
        return $this->getColumn($row, $columnText);
    }

    public function getColumn($row, $columnText)
    {
        $columnText = $columnText;
        $index = $this->columns[$columnText];
        if (!isset($index) || $index == []) {
            throw new Exception($columnText . " was not set!");
        }
        return $this->cleanText($row[$index]);
    }

    /**
     * Method takes in array of rows
     * For each row, check to see if the facility exists
     * Returns an array of missing facility names
     * Keys = facility names
     * Values = count of missing entries
     *
     * @param [type] $rows
     * @return void
     */
    public function getMissingFacilities($hiringOrg, $rows)
    {
        $missingFacilities = [];
        $existingFacilitiesQuery = $hiringOrg
            ->facilities()
            ->get()
            ->map(function ($value, $key) {
                return $value['name'];
            });

        $existingFacilities = $existingFacilitiesQuery->toArray() ?? [];

        Log::info("Existing facilities");
        Log::info($existingFacilities);

        // For each row in table....
        foreach ($rows as $index => $row) {
            if ($index != 0) {
                // Get facility name in the row
                $facilityName = $this->getContractorFacilityColumn($row);
                $contractorName = $this->getContractorNameColumn($row);
                // If facility is not in existing facilities list, add it to missing list
                if (!in_array($facilityName, $existingFacilities) && $facilityName != '' && $contractorName != '') {
                    Log::info("Facility $facilityName for $contractorName was not in the existing facilities array");
                    // Initializing missing facilities entry
                    if (!isset($missingFacilities[$facilityName])) {
                        $missingFacilities[$facilityName] = 0;
                    }
                    $missingFacilities[$facilityName] += 1;
                }
            }
        }
        return $missingFacilities;
    }

    /**
     * Takes the array of missing facility names ( {name => count} )
     * Adds each facility to the system
     *
     * @param HiringOrganization $hiringOrg
     * @param [{facilityName => countOfRelatedContractors}] $facilitiesToAdd
     * @return void
     */
    public function addFacilities($hiringOrg, $facilitiesToAdd)
    {
        $this->console->info("");
        $this->console->info("Adding Facilities");

        $facilitiesAddedCount = 0;
        $facilitiesToAddCount = sizeof($facilitiesToAdd);

        $progress = $this->output->createProgressBar($facilitiesToAddCount);
        $progress->setFormat("%current%/%max% [%bar%] %percent:3s%% %message%");

        foreach ($facilitiesToAdd as $facility => $missingCount) {

            $facilityName = $this->cleanText($facility);
            Log::info("Adding facility '$facilityName' for $hiringOrg->name");
            $progress->setMessage("Creating $facilityName facility");

            // Verifying facility does not exist
            $existingFacility = Facility::where('name', $facilityName)
                ->where('hiring_organization_id', $hiringOrg->id)
                ->first();

            if (!isset($existingFacility)) {

                $newFacility = Facility::create([
                    'name' => $facilityName,
                    'hiring_organization_id' => $hiringOrg->id,
                ]);
                $newFacility->save();
                $facilitiesAddedCount += 1;
            } else {
                $this->console->warn("Facility '$facilityName' already exists. Skipping adding.");
            }

            $progress->advance();
        }

        $progress->setMessage("Added $facilitiesAddedCount/$facilitiesToAddCount facilities");
        $progress->finish();
        $this->console->info("");
    }

    /**
     * Method takes in a string, and returns a clean & safe string for storing in the DB
     *
     * @param [type] $text
     * @return void
     */
    public function cleanText($text)
    {
        $invalid_characters = array("$", "%", "#", "<", ">", "|");
        $cleanStr = addslashes(
            str_replace($invalid_characters, "",
                strip_tags(
                    trim($text)
                )
            )
        );
        return $cleanStr;
    }
}
