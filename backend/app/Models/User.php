<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
class User extends Authenticatable {
    use HasApiTokens, HasFactory, Notifiable;
    protected $fillable = ['name','email','password','organization_id','role'];
    protected $hidden = ['password','remember_token'];
    protected $casts = ['email_verified_at'=>'datetime','password'=>'hashed'];
    public function organization() { return $this->belongsTo(Organization::class); }
    public function tickets() { return $this->hasMany(Ticket::class); }
    public function assigned_tickets() { return $this->hasMany(Ticket::class,'assigned_to'); }
    public function comments() { return $this->hasMany(Comment::class); }
    public function isAdmin(): bool { return $this->role === 'admin'; }
}
