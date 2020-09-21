<?php

namespace Capitalc\S3Upload;

trait S3uploadTrait
{
	public function getAttribute($key)
	{
		if (!in_array($key, $this->s3uploads ?? [])) {
			return parent::getAttribute($key);
		}

		return optional($this->getMedia($key)->first())->file_name;
	}

	public function setAttribute($key, $value)
	{
		if (!in_array($key, $this->s3uploads)) {
			return parent::setAttribute($key, $value);
		}

		$value = json_decode(request()->$key);

        if (data_get($value, 'deleted')) {
            $this->clearMediaCollection($key);
        } elseif (data_get($value, 'url')) {
            $this->clearMediaCollection($key);

            $name = data_get($value, 'name');

            $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::create([
                'name' => $name,
                'order_column' => 1,
                'file_name' => $name,
                'manipulations' => [],
                'model_id' => $this->id,
                'collection_name' => $key,
                'responsive_images' => [],
                'custom_properties' => [],
                'model_type' => get_class($this),
                'size' => data_get($value, 'size'),
                'mime_type' => data_get($value, 'type'),
                'disk' => config('media-library.disk_name'),
            ]);

            \Storage::disk('s3')
                ->move(
                    data_get($value, 'url'),
                    "media/{$media->id}/$name"
                );
        }
	}
}
