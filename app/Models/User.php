<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Favorite;

class User extends Authenticatable
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    use HasFactory, Notifiable;

	protected $fillable = [
		'name',
		'email',
		'password',
		'login_count',
		'last_login_at',
		'is_therapist',
		'company_name',
		'company_address',
		'company_email',
		'company_phone',
		'legal_mentions',
	];


    // Ensure last_login_at is treated as a date
    protected $dates = ['last_login_at'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }




public function favorites()
{
    return $this->hasMany(Favorite::class);
}


// app/Models/User.php

    public function isAdmin()
    {
        return $this->is_admin; // Assuming `admin` is a boolean field in your users table
    }    
	
	public function isTherapist()
    {
        return $this->is_therapist; // Assuming `admin` is a boolean field in your users table
    }


    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Obtenir les factures créées par l'utilisateur.
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }



}
