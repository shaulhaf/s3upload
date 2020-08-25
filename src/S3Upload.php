<?php

namespace Capitalc\S3Upload;

use Laravel\Nova\Fields\Field;

class S3Upload extends Field
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 's3-upload';
}
