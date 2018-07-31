<?php
/**
 * 会员信息表
 */
namespace App\Http\Models ;

use Illuminate\Database\Eloquent\Model;

class Member extends Model {
	protected $table = 'shop_member';
	protected $primaryKey = 'uid' ;

}