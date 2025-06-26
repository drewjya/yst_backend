<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class TagTherapist extends Pivot
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tag_therapist';

    // This model doesn't need any other methods or properties for this solution.
    // It just needs to exist to act as a bridge.
}