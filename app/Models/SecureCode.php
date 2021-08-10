<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Laravel\Passport\HasApiTokens;

class SecureCode extends Model
{
    use HasFactory, HasApiTokens;
    protected $fillable = ['email','value'];

    protected static int $interval = 60;

    public function scopeValid(Builder $query) :Builder {
        return  $query->where(
            DB::raw('UNIX_TIMESTAMP() - UNIX_TIMESTAMP(created_at)'),
            '<=',
            self::$interval
        );
    }

    public function scopeInvalid(Builder $query): Builder
    {
        return $query->where(
            DB::raw('UNIX_TIMESTAMP() - UNIX_TIMESTAMP(created_at)'),
            '>',
            self::$interval
        );
    }
    public static function canSend(string $email) : bool {
        /**@var \App\Models\SecureCode|null $lastedSecureCode */
        if (!$lastedSecureCode = self::valid()->where('email',$email)->latest()->get()->first()){
            return true;
        }
        $now = Carbon::now('Asia/Almaty');
        $created_at = Carbon::parse($lastedSecureCode->created_at, 'Asia/Almaty');

        return $now->diffInRealSeconds($created_at) > self::$interval;
    }

    public static function deleteInvalid(string $email) : void{
        self::invalid()->where('email',$email)->delete();
    }





}
