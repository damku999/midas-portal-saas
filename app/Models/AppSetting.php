<?php

namespace App\Models;

use Database\Factories\AppSettingFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
/**
 * App\Models\AppSetting
 *
 * @property int $id
 * @property string $key
 * @property string|null $value
 * @property string $type
 * @property string $category
 * @property string|null $description
 * @property bool $is_encrypted
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder|AppSetting active()
 * @method static Builder|AppSetting category($category)
 * @method static AppSettingFactory factory($count = null, $state = [])
 * @method static Builder|AppSetting newModelQuery()
 * @method static Builder|AppSetting newQuery()
 * @method static Builder|AppSetting query()
 * @method static Builder|AppSetting whereCategory($value)
 * @method static Builder|AppSetting whereCreatedAt($value)
 * @method static Builder|AppSetting whereDescription($value)
 * @method static Builder|AppSetting whereId($value)
 * @method static Builder|AppSetting whereIsActive($value)
 * @method static Builder|AppSetting whereIsEncrypted($value)
 * @method static Builder|AppSetting whereKey($value)
 * @method static Builder|AppSetting whereType($value)
 * @method static Builder|AppSetting whereUpdatedAt($value)
 * @method static Builder|AppSetting whereValue($value)
 *
 * @mixin Model
 */
class AppSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'category',
        'description',
        'is_encrypted',
        'is_active',
    ];

    protected $casts = [
        'is_encrypted' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the value attribute, decrypting if needed
     */
    protected function getValueAttribute($value)
    {
        if ($this->is_encrypted && $value) {
            try {
                return Crypt::decrypt($value);
            } catch (\Exception) {
                return $value; // Return original if decryption fails
            }
        }

        if ($this->type === 'json' && $value) {
            return json_decode((string) $value, true);
        }

        return $value;
    }

    /**
     * Set the value attribute, encrypting if needed
     */
    protected function setValueAttribute($value)
    {
        if ($this->is_encrypted) {
            $this->attributes['value'] = Crypt::encrypt($value);
        } elseif ($this->type === 'json' && is_array($value)) {
            $this->attributes['value'] = json_encode($value);
        } else {
            $this->attributes['value'] = $value;
        }
    }

    /**
     * Scope for active settings
     */
    protected function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for specific category
     */
    protected function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }
}
