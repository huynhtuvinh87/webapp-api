<?php

namespace App\Jobs;

use App\Models\Contractor;
use App\Models\File;
use App\Models\User;
use App\Notifications\DailyRegistrationNotification;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;
use Notification;

/**
 * SendDailyRegistrations
 *
 * Script will get the contractors that have signed up today,
 * match the hiring org they signed up with,
 * and provide that report to the success team.
 */
class SendDailyRegistrations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Creating and Storing File
        // Excel::store(new RegistrationsExport($this->today), 'DailyRegistrations.xlsx', 's3', null, [
        //     'visibility' => 'private',
        // ]);
        $file = $this->createFile();

        $this->sendFile($file);
    }

    public function createFile()
    {
        // Getting owner
        $fileOwnerEmail = 'alampert@contractorcompliance.io';
        $fileOwnerUser = User::where('email', $fileOwnerEmail)
            ->first();

        if (!isset($fileOwnerUser)) {
            throw new Exception("Owner user could not be found: " . $fileOwnerEmail);
        }
        $ownerRole = $fileOwnerUser->role;

        // File properties
        $fileArgs = [
            'name' => 'DailyRegistrations.xlsx',
            'ext' => 'xlsx',
            'path' => 'DailyRegistrations.xlsx',
            'disk' => 'public',
            'role_id' => $ownerRole->id,
            'visibility' => 'public',
        ];

        // Creating file from export
        $export = new RegistrationsExport();
        Excel::store($export, $fileArgs['name']);

        // Associating Excel export to File model
        $file = File::create($fileArgs);

        // Moving file to S3
        if (!$file->doesFileExist()) {
            throw new Exception("File failed to be stored: " . $fileArgs['name']);
        }
        $file->move([
            'path' => 'internal/DailyRegistrationReports',
            'name' => Carbon::today()->subDays(1)->toDateString() . '.xlsx',
        ], true);

        return $file;
    }

    public function sendFile($file)
    {

        // Email Link
        $supportUsers = User
			// Support mailing list
			::where('email', '=', config('api.success_email'))
			// Me
            ->orWhere('email', '=', 'alampert@contractorcompliance.io')
            ->get();

        Notification::send($supportUsers, new DailyRegistrationNotification($file, Carbon::today()->subDays(1)));
    }

}

class RegistrationsExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    use Exportable;

    /**
     * It's required to define the fileName within
     * the export class when making use of Responsable.
     */
    // private $fileName = 'DailyRegistrations.xlsx';

    public function collection()
    {
        $args = [
            "createdAtDate" => Carbon::today()->subDays(1),
        ];

        $queryWithArgs = "SELECT
            c.name as 'Contractor Name',
            u_owner.email as 'Contractor Owner Email',
            ho.name as 'Sign Up Hiring Organization Name',
            c.created_at as 'Contractor Created Date'
            FROM (
                -- Getting the hiring org the contractor initially signed up with
                SELECT
                MIN(cho.id) id
                FROM contractor_hiring_organization  cho
                LEFT JOIN contractors c ON c.id = cho.contractor_id
                LEFT JOIN hiring_organizations ho ON ho.id  = cho.hiring_organization_id
                GROUP BY cho.contractor_id
            ) as first_cho
            LEFT JOIN contractor_hiring_organization cho ON cho.id = first_cho.id
            LEFT JOIN contractors c ON c.id = cho.contractor_id
            LEFT JOIN hiring_organizations ho ON ho.id = cho.hiring_organization_id
            LEFT JOIN roles r_owner ON r_owner.entity_id = c.id AND r_owner.entity_key = 'contractor' AND r_owner.role = 'owner'
            LEFT JOIN users u_owner ON u_owner.id = r_owner.user_id
            WHERE
            c.id IS NOT NULL
            AND ho.id IS NOT NULL
            AND c.created_at >= :createdAtDate
            ORDER BY ho.name";

        $selectWithArgs = DB::select($queryWithArgs, $args);

        $results = collect($selectWithArgs);

        return $results;
    }

    public function headings(): array
    {
        return [
            "Contractor Name",
            "Sign Up Hiring Organization Name",
            "Contractor Created Date",
        ];
    }
}
