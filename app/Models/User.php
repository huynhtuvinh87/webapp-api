<?php

namespace App\Models;

use App\ViewModels\ViewEmployeeComplianceByHiringOrg;
use App\ViewModels\ViewEmployeeComplianceByHiringOrgPosition;
use App\ViewModels\ViewEmployeeOverallCompliance;
use App\ViewModels\ViewEmployeeRequirements;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\File;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\HasApiTokens;
use Log;
use App\Notifications\MailResetPasswordNotification;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\URL;

class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'phone_number',
        'avatar_file_id',
        'verified_email',
        'email_verified_at',
        'tc_signed_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'global_admin'
    ];

    protected $appends = [
        'avatar_path',
        'has_password'
    ];

    public function updateLastLogin()
    {
        $this->last_login = date('Y-m-d H:i:s');
        $this->save();
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new MailResetPasswordNotification($token));
    }

    /**
     * Get all roles
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function roles()
    {
        return $this->hasMany(Role::class);
    }

    /**
     * Get current role
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'current_role_id');
    }

    public function highestRole(){
        return $this->hasOne(Role::class)->where('role', 'owner')->orWhere('role', 'admin')->orWhere('role', 'employee');
    }

    public function avatarFile() : BelongsTo {
        return $this->belongsTo(File::class, 'avatar_file_id');
    }

    public function getAvatarPathAttribute(){
        $path = '';
        if(isset($this->avatarFile)){
            $path = $this->avatarFile->getFullPath();
        }
        return $path;
    }

    /**
     * Check if user has role of type
     * @param $type
     * @return mixed
     */
    public function hasRole($type)
    {
        return Role::where('user_id', $this->id)->where('role', $type)->exists();
    }

    /**
     * Check if current role exists, otherwise assign the current role.
     *
     * @return bool
     */
    public function checkCurrentRole(): bool
    {
        if ($this->has('role')) {
            return true;
        } else if ($this->roles()->exists()) {
            $this->current_role_id = $this->roles->first()->id;
            $this->save();
            return true;
        }
        return false;
    }

    /**
     * DEPRECATED
     * Get users an employee can communicate with
     * @return array|bool
     */
    public function employeeCommunication()
    {

        $role = $this->role;

        return [
            "contractors" => $this->contractorToContractorsCommunication($role),
            "hiring_organizations" => $this->contractorToOrganizationCommunication()
        ];

    }

    /**
     * DEPRECATED
     * TODO get company and select user information as well as company, rather than pluck
     * List of employees a user can communicate with (Chat, Tasks etc)
     * @return mixed
     */
    public function contractorToContractorsCommunication($role)
    {

        return \DB::table('users')
            ->select(
                'users.first_name',
                'users.last_name',
                'users.email',
                'contractors.name as company',
                'roles.role as role',
                'roles.user_id',
                'users.email_verified_at'
            )
            ->join('roles', 'users.id', '=', 'roles.user_id')
            ->join('contractors', 'roles.entity_id', '=', 'contractors.id')
            ->where('roles.entity_key', $role->entity_key)
            ->where('roles.entity_id', $role->entity_id)
            ->get();

    }

    //TODO
    //DEPRECATED
    public function contractorToOrganizationCommunication()
    {
        return [];
    }

    /**
     * System notifications
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function notifications(){
        return $this->hasMany(SystemNotification::class);
    }

    /**
     * Tasks this user has created
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function createdTasks()
    {
        return $this->hasMany(Task::class, 'assigned_by');
    }

    /**
     * Tasks this user has been assigned
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assignedTasks()
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }


    /**
     * DEPRECATED
     * This and the following deprecated relationships should exist in ROLE
     * Overall compliance for a user (employee)
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function overallCompliance()
    {
        return $this->hasOne(ViewEmployeeOverallCompliance::class);
    }

    /**
     * DEPRECATED
     * Compliance for a user specific to a hiring organizationn(employee)
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function complianceByHiringOrganization()
    {
        return $this->hasMany(ViewEmployeeComplianceByHiringOrg::class);
    }

    /**
     * DEPRECATED
     * Compliance for a user specific to a position (employee)
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function complianceByHiringOrganizationPositions()
    {
        return $this->hasMany(ViewEmployeeComplianceByHiringOrgPosition::class);
    }

    /**
     * DEPRECATED
     * Requirements assigned to a user through hiring organization positions (employee)
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function requirements()
    {
        return $this->hasMany(ViewEmployeeRequirements::class);
    }

    /**
     * Assign a position to an employee
     * TODO Use sync([], false) to ensure unique relationship, find proper role rather than assuming employees current role is employee
     * FIXME
     * DEPRECATED
     * @param $request
     * @param $role
     * @return PositionRole|null
     */
    public function assignEmployeePosition($request, $role)
    {
        //If role assigned to contractor
        if ($request->user()->role->company->positions()->where('positions.id', $request->get('position_id'))->first()) {
            //if no relationship between role and position exists
            if (!PositionRole::where('role_id', $role->id)->where('position_id', (int)$request->get('position_id'))->first()) {
                //create relationship
                return PositionRole::create([
                    "role_id" => $role->id,
                    "position_id" => (int)$request->get('position_id')
                ]);
            }
        }
        return null;
    }

    /**
     * unassign an employee from a position
     *
     * TODO use detach()
     * FIXME
     * DEPRECATED
     * @param $request
     * @param $role
     * @return bool
     */
    public function unassignEmployeePosition($request, $role){

        //if relationship exists
        if($role->positions()->where('position_id', $request->get('position_id'))->first()){
            //Return if the model was deleted
            return (bool) PositionRole::where('position_id', $request->get('position_id'))->delete();
        }

        return false;

    }

    /**
     * verifyEmail
     * Checks users email format to see if its valid
     * if invalid, sets verified_email to false
     * returns valid or not
     */
    public function verifyEmail(){
        $isValidEmail = filter_var( $this->email, FILTER_VALIDATE_EMAIL );
        if(!$isValidEmail){
            $this->update([
                'verified_email' => false
            ]);
        }

        return $this->verified_email;
    }

    public function notificationLogs(){
        return $this->morphMany('App\Models\NotificationLog', 'notifiable');
    }

    /**
     * Returns whether or not a password has been set
     *
     * @return void
     */
    public function getHasPasswordAttribute(){
        $passwordIsSet = isset($this->password) && $this->password != '';
        return $passwordIsSet;
    }

}
