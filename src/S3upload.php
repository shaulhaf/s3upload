<?php

namespace Shaulhaf\S3upload;

use Laravel\Nova\Fields\Field;

class S3upload extends Field
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 's3-upload';
}
