Nova.booting((Vue, router, store) => {
  Vue.component('index-s3-upload', require('./components/IndexField'))
  Vue.component('detail-s3-upload', require('./components/DetailField'))
  Vue.component('form-s3-upload', require('./components/FormField'))
})
