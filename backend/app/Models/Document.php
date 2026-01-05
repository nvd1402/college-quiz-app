<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Traits\Searchable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Document
 *
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property string $file_path
 * @property string $file_name
 * @property string|null $file_size
 * @property string $mime_type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class Document extends Model
{
    use Searchable;

    const FULLTEXT = ['title', 'description'];

    protected $table = 'documents';

    protected $searchable = [
        'title',
        'description',
    ];

    protected $fillable = [
        'title',
        'description',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
