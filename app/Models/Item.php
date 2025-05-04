<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = [
        'file_id',
        'unique_key',
        'product_title',
        'product_description',
        'style',
        'sanmar_mainframe_color',
        'size',
        'color_name',
        'piece_price'
    ];
}