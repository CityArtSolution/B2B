<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    
        public function getTitleAttribute()
    {
        
        if (app()->getLocale() == 'ar') {
            // الوصول للقيمة الخام من قاعدة البيانات
            return $this->attributes['title'] ?? null;
        } else {
            return $this->attributes['title_en'] ?? null;
        }
    }
    
    public function getDescriptionAttribute()
    {
        if (session()->get('locale') == 'ar') {
            // الوصول للقيمة الخام من قاعدة البيانات
            return $this->attributes['description'] ?? null;
        } else {
            
            return $this->attributes['description_en'] ?? null;
        }
    }

}
