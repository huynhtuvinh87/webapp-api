<?php

namespace App\Jobs;

use App\Models\Contractor;
use App\Models\File;
use App\Models\User;
use App\Notifications\QueryReportNotification;
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
use Log;
use App\Models\HiringOrganization;

class SendWSIBReports implements ShouldQueue
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
        // Getting hiring org & file name
        $HOHiramWalker = HiringOrganization::where('id', 44)->first();
        $HOLFX = HiringOrganization::where('id', 102)->first();

        $hiramWalkerFile = $this->createFile($HOHiramWalker);
        $LFXFile = $this->createFile($HOLFX);

        $this->sendFile($hiramWalkerFile, $HOHiramWalker);
        $this->sendFile($LFXFile, $HOLFX);
    }

    public function createFile($hiringOrg)
    {
        if($hiringOrg == null){
            throw new Exception("Hiring Org could not be found");
        }

        $hiringOrgFileName = $this->getFileNameFromHiringOrg($hiringOrg);

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
            'name' => $hiringOrgFileName,
            'ext' => 'xlsx',
            'path' => $hiringOrgFileName,
            'disk' => 'public',
            'role_id' => $ownerRole->id,
            'visibility' => 'public',
        ];

        // Creating file from export
        $export = new WSIBReport($hiringOrg);
        Excel::store($export, $fileArgs['name']);

        // Associating Excel export to File model
        $file = File::create($fileArgs);

        // Moving file to S3
        if (!$file->doesFileExist()) {
            throw new Exception("File failed to be stored: " . $fileArgs['name']);
        }
        $file->move([
            'path' => "internal/WSIBReports",
            'name' => $hiringOrgFileName,
        ], true);

        return $file;
    }

    public function sendFile($file, $hiringOrg)
    {

        // Email Link
        $supportUsers = User
            // Me
            ::where('email', '=', 'alampert@contractorcompliance.io')
            // Adam
            ->orWhere('email', '=', 'aweeks@contractorcompliance.io')
            ->get();

        $notification = new QueryReportNotification("Re: WSIB Report for " . ucwords($hiringOrg->name), $file, [
            "Attached is the WSIB numbers for " . $hiringOrg->name . ' as of ' . Carbon::today()->toDateString() . '.',
            "This report has been scheduled to come out on the 15th of Feb / May / August / Nov. In the event that this report is not issued by the 17th, please let the development team know and we will send the report manually."
        ]);

        Notification::send($supportUsers, $notification);
    }

    protected function getFileNameFromHiringOrg($hiringOrg){
        $trimmed = trim($hiringOrg->name);
        $noTags = strip_tags($trimmed);
        $filter = filter_var($noTags, FILTER_SANITIZE_STRING, [
            FILTER_FLAG_STRIP_HIGH,
            FILTER_FLAG_STRIP_LOW
            ]);
        $noSpaces = str_replace(' ', '', $filter);
        $hiringOrgCleanName = $noSpaces;
        return $hiringOrgCleanName . '-' . Carbon::today()->toDateString() . "-WSIBReport.xlsx";
    }

}

class WSIBReport implements FromCollection, WithHeadings, ShouldAutoSize
{
    use Exportable;
    public $hiringOrg = null;

    public function __construct($hiringOrg = null){
        $this->hiringOrg = $hiringOrg;
    }

    /**
     * It's required to define the fileName within
     * the export class when making use of Responsable.
     */
    // private $fileName = 'DailyRegistrations.xlsx';

    public function collection()
    {
        $args = [
            "hiringOrgId" => $this->hiringOrg->id,
        ];

        $queryWithArgs = "SELECT
        ho.name AS 'Hiring Organization Name',
        c.name AS 'Contractor Name',
        u.email AS 'Contractor Owner EMail',
        c.wsib_number AS 'WSIB Number'
        FROM contractors c
        LEFT JOIN contractor_hiring_organization cho ON cho.contractor_id = c.id
        LEFT JOIN hiring_organizations ho ON ho.id = cho.hiring_organization_id
        LEFT JOIN roles r ON r.entity_id = c.id AND r.entity_key = 'contractor' AND r.role = 'owner'
        LEFT JOIN users u ON u.id = r.user_id
        WHERE ho.id = :hiringOrgId";

        $selectWithArgs = DB::select($queryWithArgs, $args);

        $results = collect($selectWithArgs);

        return $results;
    }

    public function headings(): array
    {
        return [
            "Hiring Organization Name",
            "Contractor Name",
            "Contractor Owner EMail",
            "WSIB Number",
        ];
    }
}
