<template>
    <default-field :field="field" :errors="errors">
        <template slot="field">
            <div class="gallery editable" v-if="name.length && !deleted">
                <div class="gallery-list clearfix">
                    <div class="gallery-item gallery-item-file mb-3 p-3 mr-3">
                        <div class="gallery-item-info">
                            <span class="label">
                                {{name}}
                            </span>
                            <a class="delete ml-2" @click="delete_image">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 20" aria-labelledby="delete" role="presentation" class="fill-current"><path fill-rule="nonzero" d="M6 4V2a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2h5a1 1 0 0 1 0 2h-1v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6H1a1 1 0 1 1 0-2h5zM4 6v12h12V6H4zm8-2V2H8v2h4zM8 8a1 1 0 0 1 1 1v6a1 1 0 0 1-2 0V9a1 1 0 0 1 1-1zm4 0a1 1 0 0 1 1 1v6a1 1 0 0 1-2 0V9a1 1 0 0 1 1-1z"></path></svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div v-if="this.uploadProgress > 0 && !this.deleted" :style="`width:${this.uploadProgress}%;`" style="height:12px;background:var(--primary);border-radius:100px;"></div>
            <div v-if="this.uploadProgress > 0 && !this.deleted" style="position:relative;top:-12px;font-size:12px;" :style="`left:calc(${this.uploadProgress}% + 5px;`">{{this.uploadProgress}}%</div>
            <div class="custom-file">
                <label for="file" class="form-file-btn btn btn-default btn-primary">
                    {{field.value ? 'Replace File' : 'Upload File'}}
                </label>

                <input type="file" class="form-file-input" id="file" ref="file" @input="vapor">
                <!-- <span v-if="url.length==0">{{field.value}}</span>
                <span v-if="name.length > 0">{{name}}</span> -->
            </div>
        </template>
    </default-field>
</template>

<script>
import { FormField, HandlesValidationErrors } from 'laravel-nova'

export default {
    mixins: [FormField, HandlesValidationErrors],

    props: ['resourceName', 'resourceId', 'field'],

    data() {
        return {
            uploadProgress: 0,
            url: '',
            name: '',
            deleted: false,
        }
    },
    methods: {
        vapor() {
            this.disableForm()
            this.delete_image()
            this.deleted = false

            this.store(this.$refs.file.files[0], {
                progress: progress => {
                    this.uploadProgress = Math.round(progress * 100);
                }
            })
            .then(response => {
                this.url = response.key
                this.name = this.$refs.file.files[0].name
                this.enableForm()
            }).catch(e => {
                this.deleted = true
                this.enableForm()
            }); 
        },
        delete_image() {
            this.url = ''
            this.name = ''
            this.deleted = true
        },
        setInitialValue() {
            this.value = this.field.value || ''
            this.name = this.field.value || ''
        },
        disableForm(){
            this.$parent.$parent.isWorking = true
        },
        enableForm(){
            this.$parent.$parent.isWorking = false
        },

        fill(formData) {
            if (this.deleted) {
                formData.append(this.field.attribute, JSON.stringify({ deleted:true }))
            }
            else {
                let file = this.$refs.file.files[0];
                if (file) {
                    formData.append(this.field.attribute, JSON.stringify({
                        url:this.url,
                        name:this.name,
                        type: file.type,
                        size: file.size,
                    }))
                }
            }
        },

        /**
         * Update the field's internal value.
         */
        handleChange(value) {
            this.value = value
        },
        async store(file, options = {}) {
            const response = await axios.post('/vapor/signed-storage-url', {
                'bucket': options.bucket || '',
                'content_type': options.contentType || file.type,
                'expires': options.expires || '',
                'visibility': options.visibility || '',
            }, {
                baseURL: options.baseURL || null
            });

            let headers = response.data.headers;

            if ('Host' in headers) {
                delete headers.Host;
            }

            if (typeof options.progress === 'undefined') {
                options.progress = () => {};
            }

            await axios.put(response.data.url, file, {
                headers: headers,
                onUploadProgress: (progressEvent) => {
                    options.progress(progressEvent.loaded / progressEvent.total);
                }
            });

            response.data.extension = file.name.split('.').pop()

            return response.data;
        }
    },
}
</script>
